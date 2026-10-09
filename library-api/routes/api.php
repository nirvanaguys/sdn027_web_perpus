<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\ReadingLibraryController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
| Pengunjung dapat mendaftar, login, dan melihat katalog serta metadata ebook.
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Lupa kata sandi mandiri (tanpa email/SMTP): verifikasi identitas
// email + NISN/NIP -> tiket reset 10 menit -> ganti sandi baru.
Route::post('/forgot-password/verify', [AuthController::class, 'forgotPasswordVerify']);
Route::post('/forgot-password/reset', [AuthController::class, 'forgotPasswordReset']);

// Katalog ebook publik (hanya metadata)
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated via Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'data'    => $request->user(),
        ]);
    });

    // Akses membaca isi berkas ebook (wajib login)
    Route::get('/books/{id}/read', [BookController::class, 'read']);
    Route::get('/reading-history', [ReadingLibraryController::class, 'history']);
    Route::get('/reading-list', [ReadingLibraryController::class, 'readingList']);
    Route::post('/reading-list/{bookId}', [ReadingLibraryController::class, 'saveBook']);
    Route::delete('/reading-list/{bookId}', [ReadingLibraryController::class, 'removeBook']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Restricted with IsAdmin Middleware)
    |--------------------------------------------------------------------------
    | Pustakawan/Admin dapat menambah, mengubah, mengunggah berkas, dan menghapus ebook.
    */
    Route::middleware('is_admin')->group(function () {
        Route::post('/admin/books', [BookController::class, 'store']);
        Route::post('/admin/books/{id}', [BookController::class, 'update']); // Mendukung multipart upload penggantian file
        Route::put('/admin/books/{id}', [BookController::class, 'update']);
        Route::delete('/admin/books/{id}', [BookController::class, 'destroy']);

        // Pengelolaan akun anggota oleh pustakawan/admin
        Route::get('/admin/users', [UserController::class, 'index']);
        Route::put('/admin/users/{id}', [UserController::class, 'update']);
        Route::post('/admin/users/{id}/reset-password', [UserController::class, 'resetPassword']);
        Route::post('/admin/users/{id}/set-active', [UserController::class, 'setActive']);
    });
});
