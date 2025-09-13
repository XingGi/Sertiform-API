<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;

class FormController extends Controller
{
    /**
     * Menampilkan semua data form beserta kategorinya.
     */
    public function index()
    {
        // 'with('category')' adalah Eager Loading.
        // Ini membuat query lebih efisien dengan mengambil data form
        // dan category sekaligus dalam satu waktu.
        $forms = Form::with('category')->latest()->get();

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
}
