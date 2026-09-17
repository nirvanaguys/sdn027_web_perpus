<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // Public: List buku dengan pencarian & filter
    public function index(Request $request)
    {
        $query = Book::query();

        // Fitur pencarian berdasarkan judul, penulis, atau ISBN
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Filter kategori
        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        $books = $query->orderBy('created_at', 'desc')->paginate(12);

        return response()->json([
            'success' => true,
            'data'    => $books
        ]);
    }

    // Public: Detail buku
    public function show($id)
    {
        $book = Book::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $book
        ]);
    }

    // Admin Only: Tambah buku baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'publisher'      => 'required|string|max:255',
            'isbn'           => 'required|string|unique:books,isbn|max:50',
            'category'       => 'nullable|string|max:100',
            'stock'          => 'required|integer|min:0',
            'shelf_location' => 'required|string|max:50',
        ]);

        $book = Book::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil ditambahkan',
            'data'    => $book
        ], 201);
    }

    // Admin Only: Perbarui data buku
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'publisher'      => 'required|string|max:255',
            'isbn'           => 'required|string|max:50|unique:books,isbn,' . $book->id,
            'category'       => 'nullable|string|max:100',
            'stock'          => 'required|integer|min:0',
            'shelf_location' => 'required|string|max:50',
        ]);

        $book->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil diperbarui',
            'data'    => $book
        ]);
    }

    // Admin Only: Hapus buku
    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil dihapus'
        ]);
    }
}
