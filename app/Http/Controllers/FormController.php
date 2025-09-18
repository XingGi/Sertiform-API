<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    /**
     * Menampilkan data form, sekarang dengan filter is_template.
     */
    public function index(Request $request)
    {
        $query = Form::with('category');

        // Filter berdasarkan is_template
        $query->when($request->has('is_template'), function ($q) use ($request) {
            $q->where('is_template', $request->query('is_template'));
        });

        // Fitur Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where('title', 'like', "%{$searchTerm}%");
        }

        // Fitur Filter by Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Fitur Sort
        $sortField = $request->input('sort_field', $request->input('sort', 'created_at')); 
    
        // Ambil parameter direction. Utamakan 'sort_direction', jika tidak ada, cari 'direction'.
        $sortDirection = $request->input('sort_direction', $request->input('direction', 'desc'));

        // Validasi untuk keamanan
        if (in_array($sortField, ['title', 'created_at']) && in_array($sortDirection, ['asc', 'desc'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest('created_at'); // Fallback ke default
        }

        return response()->json($query->paginate(10));
    }

    /**
     * Menyimpan form baru.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required_if:is_template,false', 'nullable', 'string', 'alpha_dash', 'max:255', 'unique:forms,slug'],
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'is_template' => 'sometimes|boolean',
            'meta_pixel_code' => 'nullable|string',
            'success_redirect_url' => 'nullable|url',
        ]);

        $form = Form::create($validatedData);
        $form->load('category');

        return response()->json($form, 201);
    }

    /**
     * Menampilkan satu data form spesifik (untuk publik dan admin).
     */
    public function show(Form $form)
    {
        if (!Auth::check() && ($form->is_template || !$form->is_active)) {
            return response()->json(['message' => 'Form tidak ditemukan.'], 404);
        }

        $form->load(['category', 'formFields.options']);
        return response()->json($form);
    }
    
    /**
     * Menampilkan satu data form spesifik hanya untuk admin (digunakan di FormDesigner).
     */
    public function showForAdmin(Form $form)
    {
         $form->load([
            'category', 
            'formFields.options', 
            'submissions.submissionData.formField'
        ]);
        return response()->json($form);
    }

    /**
     * Mengupdate data form.
     */
    public function update(Request $request, Form $form)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required_if:is_template,false', 'nullable', 'string', 'alpha_dash', 'max:255', 'unique:forms,slug,' . $form->id],
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'sometimes|boolean',
            'is_template' => 'sometimes|boolean',
            'meta_pixel_code' => 'nullable|string',
            'success_redirect_url' => 'nullable|url',
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

    /**
     * Meng-clone sebuah template menjadi form aktif.
     */
    public function clone(Request $request, Form $form)
    {
        if (!$form->is_template) {
            return response()->json(['message' => 'Hanya template yang bisa di-clone.'], 400);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|alpha_dash|max:255|unique:forms,slug',
        ]);

        $newForm = DB::transaction(function () use ($form, $validated) {
            $newForm = $form->replicate(['meta_pixel_code']);
            $newForm->title = $validated['title'];
            $newForm->slug = $validated['slug'];
            $newForm->is_template = false;
            $newForm->is_active = true;
            $newForm->created_at = now();
            $newForm->updated_at = now();
            $newForm->save();

            foreach ($form->formFields as $field) {
                $newField = $field->replicate();
                $newField->form_id = $newForm->id;
                $newField->save();

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