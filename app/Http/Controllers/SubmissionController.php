<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SubmissionController extends Controller
{
    /**
     * Menerima dan menyimpan data dari isian form.
     */
    public function store(Request $request, Form $form)
    {
        // 1. Ambil semua field yang seharusnya ada di form ini
        $formFields = $form->formFields;

        // 2. Bangun aturan validasi secara dinamis berdasarkan definisi di formFields
        $rules = [];
        foreach ($formFields as $field) {
            $fieldRules = [];
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Tambahkan aturan spesifik berdasarkan tipe field
            if ($field->type === 'email') {
                $fieldRules[] = 'email';
            } elseif ($field->type === 'number') {
                $fieldRules[] = 'numeric';
            } else {
                $fieldRules[] = 'string';
            }

            $rules[$field->name] = $fieldRules;
        }

        // 3. Lakukan validasi
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 4. Gunakan Database Transaction untuk keamanan data
        try {
            $submission = DB::transaction(function () use ($form, $request, $formFields) {
                // Buat record baru di tabel 'submissions'
                $submission = $form->submissions()->create([
                    'submitted_at' => now(),
                ]);

                // Simpan setiap jawaban ke tabel 'submission_data'
                foreach ($formFields as $field) {
                    if ($request->has($field->name)) {
                        $submission->submissionData()->create([
                            'form_field_id' => $field->id,
                            'value' => $request->input($field->name),
                        ]);
                    }
                }

                return $submission;
            });

            return response()->json([
                'message' => 'Form submitted successfully!',
                'submission_id' => $submission->id,
            ], 201);

        } catch (\Exception $e) {
            // Jika terjadi error di dalam transaction, kembalikan response error
            return response()->json([
                'message' => 'An error occurred while submitting the form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}