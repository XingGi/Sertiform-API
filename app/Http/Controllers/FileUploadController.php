<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi file yang di-upload
        $request->validate([
            'file' => [
                'required',
                'file', // Pastikan ini adalah file
                'mimes:pdf,doc,docx,jpg,jpeg,png', // Tipe file yang diizinkan
                'max:2048', // Ukuran maksimal 2MB (2048 KB)
            ],
        ]);

        // 2. Simpan file dan dapatkan path-nya
        // File akan disimpan di storage/app/public/uploads
        $path = $request->file('file')->store('uploads', 'public');

        // 3. Kembalikan path file dalam format JSON
        return response()->json([
            'path' => $path,
            'url' => Storage::disk('public')->url($path) // URL lengkap ke file
        ], 201);
    }
}