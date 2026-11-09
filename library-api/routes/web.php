<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/covers/{path}', fn (string $path) => Storage::disk('public')->exists($path)
    ? Storage::disk('public')->response($path, null, ['Cache-Control' => 'public, max-age=86400'])
    : response()->json(['success' => false, 'message' => "Berkas tidak ada: {$path}"], 404)
)->where('path', '.*');
