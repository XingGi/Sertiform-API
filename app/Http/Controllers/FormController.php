<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    /**
     * Menampilkan semua data form beserta kategorinya.
     */
    public function index(Request $request)
    {
        $forms = Form::with('category')
            ->when($request->has('is_template'), function ($query) use ($request) {
                // Jika ada parameter ?is_template=... di URL, filter berdasarkan itu.
                return $query->where('is_template', $request->query('is_template'));
            })
            ->latest()
            ->get();

        return response()->json($forms);
    }

    /**
     * Menyimpan form baru.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            // 'exists:categories,id' memastikan category_id yang dikirim ada di tabel categories.
            'category_id' => 'required|exists:categories,id',
            'is_template' => 'sometimes|boolean',
            'meta_pixel_code' => 'nullable|string',
        ]);

        $form = Form::create($validatedData);

        // Memuat relasi category agar ikut tampil di response JSON
        $form->load('category');

        return response()->json($form, 201);
    }

    /**
     * Menampilkan satu data form spesifik.
     */
    public function show(Form $form)
    {
        // Jika user yang meminta adalah user yang sudah login (admin),
    // mereka boleh melihat form apa saja.
    if (Auth::check()) {
        $form->load(['category', 'formFields.options']);
        return response()->json($form);
    }

    // Jika user adalah tamu (tidak login), jalankan aturan keamanan publik.
    if ($form->is_template || !$form->is_active) {
        return response()->json(['message' => 'Form tidak ditemukan.'], 404);
    }

    // Jika lolos, berikan data form publik.
    $form->load(['category', 'formFields.options']);
    return response()->json($form);
    }

    public function showForAdmin(Form $form)
    {
        $form->load(['category', 'formFields.options']);
        return response()->json($form);
    }

    /**
     * Mengupdate data form.
     */
    public function update(Request $request, Form $form)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'sometimes|boolean', // 'sometimes' berarti hanya validasi jika ada di request
            'is_template' => 'sometimes|boolean',
            'meta_pixel_code' => 'nullable|string',
        ]);

        $form->update($validatedData);
        $form->load('category');

        return response()->json($form);
    }

    /**
     * Menghapus data form.
     */
    public function destroy(Form $form)
    {
        $form->delete();
        return response()->json(null, 204);
    }

    public function clone(Request $request, Form $form)
    {
        if (!$form->is_template) {
            return response()->json(['message' => 'Hanya template yang bisa di-clone.'], 400);
        }

        $validated = $request->validate(['title' => 'required|string|max:255']);

        $newForm = DB::transaction(function () use ($form, $validated) {
            $newForm = $form->replicate(['meta_pixel_code']); // Replicate, tapi kosongkan pixel code
            $newForm->title = $validated['title'];
            $newForm->is_template = false;
            $newForm->is_active = true;
            $newForm->created_at = now();
            $newForm->updated_at = now();
            $newForm->save();

            foreach ($form->formFields as $field) {
                $newField = $field->replicate();
                $newField->form_id = $newForm->id;
                $newField->save();

                // Duplikasi juga options untuk field tersebut
                if ($field->options->isNotEmpty()) {
                    foreach($field->options as $option) {
                        $newOption = $option->replicate();
                        $newOption->form_field_id = $newField->id;
                        $newOption->save();
                    }
                }
            }
            return $newForm;
        });

        return response()->json($newForm->load('formFields'), 201);
    }
}
