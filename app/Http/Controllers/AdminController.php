<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    /**
     * Menampilkan semua data admin.
     */
    public function index()
    {
        return response()->json(User::latest()->get());
    }

    /**
     * Menyimpan admin baru.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Hash password sebelum disimpan untuk keamanan
        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        return response()->json($user, 201);
    }

    /**
     * Menampilkan satu data admin.
     */
    public function show(User $admin)
    {
        return response()->json($admin);
    }

    /**
     * Mengupdate data admin.
     */
    public function update(Request $request, User $admin)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            // Pastikan email unik, kecuali untuk user ini sendiri
            'email' => 'required|string|email|max:255|unique:users,email,' . $admin->id,
            // Password tidak wajib diisi saat update
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        // Cek jika ada password baru yang diinput
        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            // Jika tidak ada, hapus dari array agar password lama tidak tertimpa
            unset($validatedData['password']);
        }

        $admin->update($validatedData);

        return response()->json($admin);
    }

    /**
     * Menghapus data admin.
     */
    public function destroy(User $admin)
    {
        // Best Practice: Tambahkan proteksi agar admin tidak bisa menghapus dirinya sendiri
        if (Auth::id() === $admin->id) {
            return response()->json(['message' => 'Anda tidak bisa menghapus akun Anda sendiri.'], 403);
        }

        $admin->delete();

        return response()->json(null, 204);
    }
}