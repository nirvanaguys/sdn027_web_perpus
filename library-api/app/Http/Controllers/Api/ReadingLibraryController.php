<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\ReadingHistory;
use App\Models\ReadingList;
use Illuminate\Http\Request;

class ReadingLibraryController extends Controller
{
    public function history(Request $request)
    {
        $history = ReadingHistory::with('book')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_read_at')
            ->get();

        return response()->json(['success' => true, 'data' => $history]);
    }

    public function readingList(Request $request)
    {
        $readingList = ReadingList::with('book')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $readingList]);
    }

    public function saveBook(Request $request, $bookId)
    {
        $book = Book::whereNotNull('file_path')->findOrFail($bookId);

        try {
            $entry = ReadingList::firstOrCreate([
                'user_id' => $request->user()->id,
                'book_id' => $book->id,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // RD-07: Tangani race condition pada unique key secara anggun
            $entry = ReadingList::where('user_id', $request->user()->id)
                ->where('book_id', $book->id)
                ->firstOrFail();
        }

        return response()->json([
            'success' => true,
            'data' => $entry->load('book'),
        ], $entry->wasRecentlyCreated ? 201 : 200);
    }

    public function removeBook(Request $request, $bookId)
    {
        $deleted = ReadingList::where('user_id', $request->user()->id)
            ->where('book_id', $bookId)
            ->delete();

        // RD-03: Kembalikan 404 jika tidak ada entri yang dihapus
        if ($deleted === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Buku tidak ditemukan di Reading List Anda.',
            ], 404);
        }

        return response()->json(['success' => true]);
    }
}