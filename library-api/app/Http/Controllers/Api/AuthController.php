<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users',
            'identity_number' => 'required|string|max:30|unique:users,identity_number',
            'password'        => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'identity_number' => $validated['identity_number'],
            'password'        => Hash::make($validated['password']),
            'role'            => 'member',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data'    => [
                'user'  => $user,
                'token' => $token,
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
            ]);
        }

        // Akun yang dinonaktifkan admin tidak boleh login. Pesan dibuat
        // jelas agar user tahu harus menghubungi pustakawan (bukan bug).
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda sedang dinonaktifkan. Silakan hubungi pustakawan SDN 027 untuk mengaktifkan kembali.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data'    => [
                'user'  => $user,
                'token' => $token,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout'
        ]);
    }

    /**
     * Langkah 1 lupa kata sandi (mandiri, tanpa email/SMTP, 24 jam):
     * user membuktikan kepemilikan akun dengan email + NISN/NIP.
     * Berhasil -> server menerbitkan tiket reset berumur 10 menit.
     *
     * Pesan gagal dibuat generik agar tidak membocorkan akun mana
     * yang terdaftar (hindari enumerasi akun oleh pihak iseng).
     */
    public function forgotPasswordVerify(Request $request)
    {
        $request->validate([
            'email'           => 'required|email',
            'identity_number' => 'required|string|max:30',
        ]);

        $user = User::where('email', $request->email)
            ->where('identity_number', $request->identity_number)
            ->first();

        if (!$user || !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Data tidak cocok. Periksa kembali email dan NISN/NIP Anda, atau hubungi pustakawan.'],
            ]);
        }

        $ticket = $user->createToken(
            'password_reset',
            ['password-reset'],
            now()->addMinutes(10)
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Identitas terverifikasi. Silakan buat kata sandi baru.',
            'data'    => [
                'reset_ticket' => $ticket,
            ],
        ]);
    }

    /**
     * Langkah 2 lupa kata sandi: tukar tiket reset (10 menit) dengan
     * kata sandi baru. Tiket langsung dihapus agar tidak dipakai ulang.
     */
    public function forgotPasswordReset(Request $request)
    {
        $validated = $request->validate([
            'reset_ticket' => 'required|string',
            'password'     => 'required|string|min:8|confirmed',
        ]);

        $token = \Laravel\Sanctum\PersonalAccessToken::findToken($validated['reset_ticket']);

        if (!$token || !$token->can('password-reset') || $token->expires_at?->isPast()) {
            $token?->delete();

            return response()->json([
                'success' => false,
                'message' => 'Tiket reset kedaluwarsa. Silakan ulangi verifikasi identitas.',
            ], 422);
        }

        $user = $token->tokenable;

        if (!$user || !$user->is_active) {
            $token->delete();

            return response()->json([
                'success' => false,
                'message' => 'Akun tidak dapat direset. Silakan hubungi pustakawan.',
            ], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        // Tiket sekali pakai: hapus segera setelah berhasil.
        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diganti. Silakan masuk dengan kata sandi baru.',
        ]);
    }
}
