<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FormFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Form $form)
    {
        return response()->json($form->formFields()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Form $form)
    {
        $validatedData = $request->validate([
            'label' => 'required|string|max:255',
            'type' => [
                'required',
                Rule::in(['text', 'email', 'number', 'date', 'textarea', 'dropdown', 'radio', 'file']), // Tipe field yg diizinkan
            ],
            'is_required' => 'required|boolean',
        ]);

        // Membuat 'name' secara otomatis dari 'label'
        // contoh: "Nama Lengkap" -> "nama_lengkap"
        $validatedData['name'] = Str::slug($validatedData['label'], '_');

        // Membuat field baru menggunakan relasi,
        // ini otomatis mengisi `form_id`. Sangat praktis!
        $formField = $form->formFields()->create($validatedData);

        return response()->json($formField, 201);
    }
    // Method show, update, dan destroy biasanya tidak terlalu sering digunakan
    // secara terpisah dalam konteks ini, tapi kita buatkan untuk kelengkapan.

    /**
     * Menampilkan satu field spesifik.
     * GET /api/forms/{form}/form-fields/{form_field}
     */
    /**
     * Display the specified resource.
     */
    public function show(Form $form, FormField $formField)
    {
        return response()->json($formField);
    }
    /**
     * Mengupdate field.
     * PUT /api/forms/{form}/form-fields/{form_field}
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Form $form, FormField $formField)
    {
        $validatedData = $request->validate([
            'label' => 'sometimes|string|max:255',
            'type' => [
                'sometimes',
                Rule::in(['text', 'email', 'number', 'date', 'textarea', 'dropdown', 'radio', 'file']),
            ],
            'is_required' => 'sometimes|boolean',
        ]);

        if (isset($validatedData['label'])) {
            $validatedData['name'] = Str::slug($validatedData['label'], '_');
        }

        $formField->update($validatedData);

        return response()->json($formField);
    }

    /**
     * Menghapus field.
     * DELETE /api/forms/{form}/form-fields/{form_field}
     */
    public function destroy(Form $form, FormField $formField)
    {
        $formField->delete();
        return response()->json(null, 204);
    }
}
