<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormRedirectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Form $form)
    {
       return response()->json($form->redirects()->with('formField:id,label')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function syncRedirects(Request $request, Form $form)
    {
        $validated = $request->validate([
            'rules' => 'present|array',
            'rules.*.form_field_id' => 'required|exists:form_fields,id',
            'rules.*.trigger_value' => 'required|string|max:255',
            'rules.*.redirect_url' => 'required|url|max:255',
        ]);

        DB::transaction(function () use ($form, $validated) {
            // 1. Hapus semua aturan lama
            $form->redirects()->delete();

            // 2. Buat kembali semua aturan dari data baru
            $form->redirects()->createMany($validated['rules']);
        });

        return response()->json(['message' => 'Aturan redirect berhasil disimpan.']);
    }
}
