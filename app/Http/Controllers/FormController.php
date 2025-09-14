<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // Memuat relasi category dan juga formFields
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
        // Pastikan yang di-clone adalah sebuah template
        if (!$form->is_template) {
            return response()->json(['message' => 'Hanya template yang bisa di-clone.'], 400);
        }

        // Validasi judul untuk form baru
        $validated = $request->validate(['title' => 'required|string|max:255']);

        // Gunakan transaction untuk memastikan semua proses berhasil atau tidak sama sekali
        $newForm = DB::transaction(function () use ($form, $validated) {
            // 1. Duplikasi data form (template) menggunakan replicate()
            $newForm = $form->replicate();
            
            // 2. Ubah propertinya menjadi form aktif dengan judul baru
            $newForm->title = $validated['title'];
            $newForm->is_template = false; // Ini penting!
            $newForm->is_active = true; // Langsung aktifkan form baru
            $newForm->created_at = now();
            $newForm->updated_at = now();
            $newForm->save();

            // 3. Duplikasi semua field yang ada di template
            foreach ($form->formFields as $field) {
                $newField = $field->replicate();
                $newField->form_id = $newForm->id; // Hubungkan ke form baru
                $newField->save();
            }

            return $newForm;
        });

        return response()->json($newForm->load('formFields'), 201);
    }
}
