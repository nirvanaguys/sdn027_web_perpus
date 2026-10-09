<?php

namespace App\Support;

use App\Models\Book;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EbookCoverExtractor
{
    /**
     * Ekstrak sampul halaman depan dari berkas ebook milik buku.
     *
     * Prioritas:
     *  1. Gambar full-page di halaman pertama PDF.
     *  2. Render halaman pertama PDF.
     *  3. Gambar sampul dari metadata EPUB (atau gambar pertama).
     *  4. Render halaman teks pertama EPUB.
     *
     * Mengembalikan path relatif terhadap disk "public"
     * (mis. "book-covers/slug-id.png"), atau null bila gagal.
     */
    public static function extract(Book $book, bool $overwrite = true): ?string
    {
        if (empty($book->file_path) || !Storage::disk('local')->exists($book->file_path)) {
            return null;
        }

        // Sampul upload manual (bukan hasil generate) jangan ditimpa.
        if (!$overwrite && !empty($book->cover_image)) {
            return $book->cover_image;
        }

        $script = base_path('scripts/extract-cover.py');
        if (!is_file($script)) {
            Log::warning('Skrip extract-cover.py tidak ditemukan.');
            return null;
        }

        $absEbook = Storage::disk('local')->path($book->file_path);
        $slug = Str::slug(Str::limit($book->title ?? 'buku', 40, '')) ?: 'buku-' . $book->id;
        $filename = "book-covers/{$slug}-{$book->id}-cover.png";
        $absOut = Storage::disk('public')->path($filename);

        if (!is_dir(dirname($absOut))) {
            mkdir(dirname($absOut), 0755, true);
        }

        // Interpreter Python: pakai venv khusus aplikasi bila ada
        // (dibuat saat setup, berisi pymupdf+pillow), fallback ke python3.
        $venvPython = storage_path('app/.coverenv/bin/python');
        $python = is_file($venvPython) ? $venvPython : 'python3';

        $cmd = escapeshellarg($python) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($absEbook) . ' ' . escapeshellarg($absOut) . ' --width 600 2>&1';
        $output = [];
        $exitCode = 0;
        exec($cmd . ' ', $output, $exitCode);

        if ($exitCode !== 0 || !is_file($absOut) || filesize($absOut) === 0) {
            Log::warning('Ekstrak sampul gagal untuk buku #' . $book->id . ': ' . trim(implode("\n", $output)));
            return null;
        }

        return $filename;
    }
}
