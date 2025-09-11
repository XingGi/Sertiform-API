<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Menampilkan semua data kategori. (GET /api/categories)
     */
    public function index()
    {
        return response()->json(Category::all());
    }

    /**
     * Menyimpan kategori baru. (POST /api/categories)
     */
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
        ]);

        // Buat kategori baru
        $category = Category::create([
            'name' => $validatedData['name'],
            'slug' => Str::slug($validatedData['name']), // Otomatis membuat slug, contoh: "Marketing Campaign" -> "marketing-campaign"
        ]);

        // Kirim response dalam format JSON dengan status 201 (Created)
        return response()->json($category, 201);
    }

    /**
     * Menampilkan satu data kategori spesifik. (GET /api/categories/{id})
     */
    public function show(Category $category)
    {
        // Laravel's Route Model Binding otomatis akan mencari kategori berdasarkan ID
        return response()->json($category);
    }

    /**
     * Mengupdate data kategori. (PUT /api/categories/{id})
     */
    public function update(Request $request, Category $category)
    {
        $validatedData = $request->validate([
            // unique:categories,name, ... -> pastikan nama unik, kecuali untuk dirinya sendiri
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $validatedData['name'],
            'slug' => Str::slug($validatedData['name']),
        ]);

        return response()->json($category);
    }

    /**
     * Menghapus data kategori. (DELETE /api/categories/{id})
     */
    public function destroy(Category $category)
    {
        $category->delete();

        // Kirim response kosong dengan status 204 (No Content)
        return response()->json(null, 204);
    }
}
