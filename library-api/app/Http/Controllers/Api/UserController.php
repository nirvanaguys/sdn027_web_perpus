<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Admin Only: Pengelolaan akun anggota.
 *
 * Alur "lupa password" versi SD tanpa email/SMTP: user melapor ke
 * pustakawan, lalu admin mereset password dari panel ini.
 * Akun bermasalah DINONAKTIFKAN (is_active=false), bukan dihapus
 * permanen, agar riwayat baca/pinjam tetap utuh.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->select(['id', 'name', 'email', 'role', 'is_active', 'created_at']);

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && in_array($request->query('role'), ['admin', 'member'], true)) {
            $query->where('role', $request->query('role'));
        }

        if ($request->filled('status')) {
            if ($request->query('status') === 'aktif') {
                $query->where('is_active', true);
            } elseif ($request->query('status') === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        // SD-02 & TG-02: Batasi batas bawah dan batas atas per_page
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // SD-04: Cegah admin menurunkan perannya sendiri agar tidak terkunci dari panel
        if ($user->id === $request->user()->id && $request->has('role') && $request->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menurunkan peran akun Anda sendiri.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => 'sometimes|required|in:admin,member',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data akun berhasil diperbarui',
            'data'    => $user->fresh(),
        ]);
    }

    /**
     * Reset password akun oleh admin (pengganti alur "lupa password"
     * via email yang tidak tersedia di lingkungan SD).
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        // TG-05: Cabut seluruh token login sesi saat password direset oleh admin
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => "Kata sandi akun {$user->name} berhasil direset",
        ]);
    }

    public function setActive(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Admin tidak boleh menonaktifkan akunnya sendiri agar tidak
        // terkunci keluar dari panel.
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menonaktifkan akun sendiri.',
            ], 422);
        }

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $user->is_active = $validated['is_active'];
        $user->save();

        // TG-03: Cabut semua token sesi jika akun dinonaktifkan
        if (!$user->is_active) {
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => $user->is_active
                ? "Akun {$user->name} berhasil diaktifkan kembali"
                : "Akun {$user->name} berhasil dinonaktifkan",
            'data'    => $user->fresh(),
        ]);
    }
}
