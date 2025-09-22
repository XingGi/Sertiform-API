<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SubmissionController extends Controller
{
    /**
     * Menampilkan semua submission dari sebuah form.
     * Ini adalah method baru kita.
     */
    public function index(Form $form)
    {
        // Ambil semua submission milik form ini.
        // Gunakan nested eager loading untuk mengambil data jawaban
        // DAN juga data pertanyaan (field) terkait setiap jawaban.
        $submissions = $form->submissions()
                            ->with('submissionData.formField')
                            ->latest('submitted_at') // Urutkan dari yang terbaru
                            ->get();

        return response()->json($submissions);
    }
    
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
            } else if ($field->type === 'file') {
                $fieldRules[] = 'string'; // Path file disimpan sebagai string
            } else {
                $fieldRules[] = 'string';
            }

            $rules[$field->name] = $fieldRules;
        }

        // 3. Lakukan validasi
        $validatedData = Validator::make($request->all(), $rules)->validate();

        // 4. Gunakan Database Transaction untuk keamanan data
        try {
            $submission = DB::transaction(function () use ($form, $request, $formFields, $validatedData) {
                // Buat record baru di tabel 'submissions'
                $submission = $form->submissions()->create([
                    'form_id' => $form->id,
                    'submitted_at' => now(),
                ]);

                // Simpan setiap jawaban ke tabel 'submission_data'
                foreach ($formFields as $field) {
                    if (isset($validatedData[$field->name])) {
                        $submission->submissionData()->create([
                            'form_field_id' => $field->id,
                            'value' => $validatedData[$field->name],
                        ]);
                    }
                }

                return $submission;
            });

            $redirectUrl = $form->success_redirect_url;

            $redirectRules = $form->redirects()->get();

            if ($redirectRules->isNotEmpty()) {
                foreach ($validatedData as $fieldName => $value) {
                    $field = $formFields->firstWhere('name', $fieldName);
                    if (!$field) continue;

                    // Cari aturan yang cocok dengan jawaban user
                    $matchingRule = $redirectRules->first(function ($rule) use ($field, $value) {
                        return $rule->form_field_id == $field->id && $rule->trigger_value == $value;
                    });

                    // Jika ketemu, gunakan URL dari aturan tersebut dan hentikan pencarian
                    if ($matchingRule) {
                        $redirectUrl = $matchingRule->redirect_url;
                        break;
                    }
                }
            }

            return response()->json([
                'message' => 'Form submitted successfully!',
                'submission_id' => $submission->id,
                'redirect_url' => $redirectUrl,
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