<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookController extends Controller
{
    /**
     * Public: List katalog ebook dengan pencarian & filter.
     */
    public function index(Request $request)
    {
        $query = Book::query();

        // Pencarian judul, penulis, penerbit, isbn, atau deskripsi
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter kategori
        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        // Filter format file (pdf, epub)
        if ($request->filled('format')) {
            $query->where('file_format', strtolower($request->query('format')));
        }

        $perPage = $request->query('per_page', 12);
        $books = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $books,
        ]);
    }

    /**
     * Public: Detail metadata buku/ebook.
     */
    public function show($id)
    {
        $book = Book::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $book,
        ]);
    }

    /**
     * Protected (auth:sanctum): Membaca file stream ebook secara privat.
     */
    public function read(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        if (!$book->file_path || !Storage::disk('local')->exists($book->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas ebook belum tersedia atau tidak ditemukan di penyimpanan server.',
            ], 404);
        }

        $format = strtolower($book->file_format ?: pathinfo($book->file_path, PATHINFO_EXTENSION));
        $contentType = match ($format) {
            'pdf'  => 'application/pdf',
            'epub' => 'application/epub+zip',
            default => 'application/octet-stream',
        };

        $fullPath = Storage::disk('local')->path($book->file_path);
        $downloadName = ($book->title ?: 'ebook') . '.' . $format;
        $safeFilename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $downloadName);

        return response()->file($fullPath, [
            'Content-Type'        => $contentType,
            'Content-Disposition' => 'inline; filename="' . $safeFilename . '"',
            'Cache-Control'       => 'private, no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    /**
     * Admin Only: Tambah ebook baru beserta metadata & upload file.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'publisher'      => 'required|string|max:255',
            'isbn'           => 'required|string|unique:books,isbn|max:50',
            'category'       => 'nullable|string|max:100',
            'description'    => 'nullable|string',
            'stock'          => 'nullable|integer|min:0',
            'shelf_location' => 'nullable|string|max:50',
            'file'           => 'nullable|file|mimes:pdf,epub|max:51200', // maks 50MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            $path = $file->store('ebooks', 'local');

            $validated['file_path'] = $path;
            $validated['file_format'] = $ext;
            $validated['file_size'] = $file->getSize();
        }

        unset($validated['file']);
        $book = Book::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ebook berhasil ditambahkan',
            'data'    => $book,
        ], 201);
    }

    /**
     * Admin Only: Perbarui data ebook & ganti file jika diunggah baru.
     */
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'publisher'      => 'required|string|max:255',
            'isbn'           => 'required|string|max:50|unique:books,isbn,' . $book->id,
            'category'       => 'nullable|string|max:100',
            'description'    => 'nullable|string',
            'stock'          => 'nullable|integer|min:0',
            'shelf_location' => 'nullable|string|max:50',
            'file'           => 'nullable|file|mimes:pdf,epub|max:51200',
        ]);

        if ($request->hasFile('file')) {
            // Hapus file lama jika ada
            if ($book->file_path && Storage::disk('local')->exists($book->file_path)) {
                Storage::disk('local')->delete($book->file_path);
            }

            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            $path = $file->store('ebooks', 'local');

            $validated['file_path'] = $path;
            $validated['file_format'] = $ext;
            $validated['file_size'] = $file->getSize();
        }

        unset($validated['file']);
        $book->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data ebook berhasil diperbarui',
            'data'    => $book,
        ]);
    }

    /**
     * Admin Only: Hapus ebook beserta berkas privatnya dari storage.
     */
    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        if ($book->file_path && Storage::disk('local')->exists($book->file_path)) {
            Storage::disk('local')->delete($book->file_path);
        }

        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ebook dan berkas berhasil dihapus',
        ]);
    }
}
