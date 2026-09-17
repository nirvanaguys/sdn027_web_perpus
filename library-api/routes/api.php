<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\TransactionController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Katalog buku publik
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
            'data'    => $request->user()
        ]);
    });

    // Sirkulasi Peminjaman (Member & Admin)
    Route::post('/transactions/borrow', [TransactionController::class, 'borrow']);
    Route::put('/transactions/return/{id}', [TransactionController::class, 'returnBook']);
    Route::get('/transactions/my', [TransactionController::class, 'myTransactions']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Restricted with IsAdmin Middleware)
    |--------------------------------------------------------------------------
    */
    Route::middleware('is_admin')->group(function () {
        Route::post('/admin/books', [BookController::class, 'store']);
        Route::put('/admin/books/{id}', [BookController::class, 'update']);
        Route::delete('/admin/books/{id}', [BookController::class, 'destroy']);
    });
});
