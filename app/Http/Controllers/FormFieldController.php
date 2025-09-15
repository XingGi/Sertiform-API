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
        return response()->json($form->formFields()->with('options')->get());
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
        ]);

        $formField = DB::transaction(function () use ($form, $validatedData, $request) {
            $fieldData = $validatedData;
            unset($fieldData['options']);
            $fieldData['name'] = Str::slug($validatedData['label'], '_');
            
            $formField = $form->formFields()->create($fieldData);

            if ($request->filled('options')) {
                foreach ($validatedData['options'] as $option) {
                    // Perbaikan: Ambil hanya data yang diperlukan
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
        $validatedData = $request->validate([
            'label' => 'sometimes|string|max:255',
            'type' => ['sometimes', Rule::in(['text', 'email', 'number', 'date', 'textarea', 'dropdown', 'radio', 'file'])],
            'is_required' => 'sometimes|boolean',
            'options' => 'nullable|array',
            'options.*.label' => 'required_with:options|string',
            'options.*.value' => 'required_with:options|string',
        ]);

        DB::transaction(function () use ($formField, $validatedData, $request) {
            $fieldData = $validatedData;
            unset($fieldData['options']);
            if (isset($validatedData['label'])) {
                $fieldData['name'] = Str::slug($validatedData['label'], '_');
            }
            $formField->update($fieldData);

            if ($request->has('options')) {
                $formField->options()->delete();
                foreach ($validatedData['options'] as $option) {
                    // Perbaikan: Ambil hanya data yang diperlukan
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
}