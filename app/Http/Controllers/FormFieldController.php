<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class FormFieldController extends Controller
{
    public function index(Form $form)
    {
        return response()->json($form->formFields()->with('options')->orderBy('ordering')->get());
    }

    public function store(Request $request, Form $form)
    {
        $validatedData = $request->validate([
            'label' => 'required|string|max:255',
            'type' => ['required', Rule::in(['text', 'email', 'number', 'date', 'textarea', 'dropdown', 'radio', 'file'])],
            'is_required' => 'required|boolean',
            'options' => 'nullable|array',
            'options.*.label' => 'required_with:options|string',
            'options.*.value' => 'required_with:options|string',
            'conditional_logic_enabled' => 'sometimes|boolean',
            'conditional_on_field_id' => 'nullable|exists:form_fields,id',
            'conditional_on_value' => 'nullable|string|max:255',
        ]);

        $formField = DB::transaction(function () use ($form, $validatedData, $request) {
            $fieldData = $validatedData;
            unset($fieldData['options']);
            $fieldData['name'] = Str::slug($validatedData['label'], '_');
            
            // 2. TAMBAHKAN LOGIKA PENYIMPANAN di sini
            // Jika checkbox tidak dicentang, pastikan data kondisional dikosongkan
            if (empty($validatedData['conditional_logic_enabled'])) {
                $fieldData['conditional_on_field_id'] = null;
                $fieldData['conditional_on_value'] = null;
            }
            
            $formField = $form->formFields()->create($fieldData);

            if ($request->filled('options')) {
                foreach ($validatedData['options'] as $option) {
                    $formField->options()->create([
                        'label' => $option['label'],
                        'value' => $option['value'],
                    ]);
                }
            }
            return $formField;
        });

        return response()->json($formField->load('options'), 201);
    }

    public function show(Form $form, FormField $formField)
    {
        return response()->json($formField->load('options'));
    }

    public function update(Request $request, Form $form, FormField $formField)
    {
        // 1. TAMBAHKAN VALIDASI BARU di sini
        $validatedData = $request->validate([
            'label' => 'sometimes|string|max:255',
            'type' => ['sometimes', Rule::in(['text', 'email', 'number', 'date', 'textarea', 'dropdown', 'radio', 'file'])],
            'is_required' => 'sometimes|boolean',
            'options' => 'nullable|array',
            'options.*.label' => 'required_with:options|string',
            'options.*.value' => 'required_with:options|string',
            // --- Baris-baris baru untuk logika kondisional ---
            'conditional_logic_enabled' => 'sometimes|boolean',
            'conditional_on_field_id' => 'nullable|exists:form_fields,id',
            'conditional_on_value' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($formField, $validatedData, $request) {
            $fieldData = $validatedData;
            unset($fieldData['options']);
            if (isset($validatedData['label'])) {
                $fieldData['name'] = Str::slug($validatedData['label'], '_');
            }

            // 2. TAMBAHKAN LOGIKA PENYIMPANAN di sini
            // Jika checkbox tidak dicentang, pastikan data kondisional dikosongkan
            if (empty($validatedData['conditional_logic_enabled'])) {
                $fieldData['conditional_on_field_id'] = null;
                $fieldData['conditional_on_value'] = null;
            }

            $formField->update($fieldData);

            if ($request->has('options')) {
                $formField->options()->delete();
                foreach ($validatedData['options'] as $option) {
                    $formField->options()->create([
                        'label' => $option['label'],
                        'value' => $option['value'],
                    ]);
                }
            }
        });

        return response()->json($formField->load('options'));
    }

    public function destroy(Form $form, FormField $formField)
    {
        $formField->delete();
        return response()->json(null, 204);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'fields' => 'required|array',
            'fields.*' => 'integer|exists:form_fields,id',
        ]);

        foreach ($request->fields as $index => $fieldId) {
            FormField::where('id', $fieldId)->update(['ordering' => $index]);
        }

        return response()->json(['message' => 'Urutan field berhasil diperbarui.']);
    }
}