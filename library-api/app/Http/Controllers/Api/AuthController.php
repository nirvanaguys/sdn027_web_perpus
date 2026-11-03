<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users',
            'password'        => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = User::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'role'            => 'member',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data'    => [
                'user'  => $user->fresh(),
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
     * Lupa kata sandi langkah 1: kirim tautan reset ke email.
     * Balasan selalu sama (terdaftar atau tidak) agar tidak bisa dipakai
     * menebak email mana yang punya akun.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->is_active) {
            PasswordBroker::sendResetLink(['email' => $user->email]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kalau email itu terdaftar, tautan reset sudah dikirim. Periksa kotak masuk atau folder spam (berlaku 60 menit).',
        ]);
    }

    /**
     * Lupa kata sandi langkah 2: tukar token dari email dengan sandi baru.
     * Token sekali pakai dan otomatis kedaluwarsa.
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $status = PasswordBroker::reset($validated, function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password)])->save();

            // TG-05: cabut seluruh token sesi lama setelah sandi diganti
            $user->tokens()->delete();
        });

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            return response()->json([
                'success' => false,
                'message' => 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diganti. Silakan masuk dengan kata sandi baru.',
        ]);
    }
}
