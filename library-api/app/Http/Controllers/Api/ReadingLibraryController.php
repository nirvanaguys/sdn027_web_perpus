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
        return $this->respond(ReadingHistory::with('book')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_read_at'), $request);
    }

    public function readingList(Request $request)
    {
        return $this->respond(ReadingList::with('book')
            ->where('user_id', $request->user()->id)
            ->latest(), $request);
    }

    // ponytail: tanpa ?per_page tetap kirim semua (dipakai katalog untuk menandai buku tersimpan)
    private function respond($query, Request $request)
    {
        if (! $request->has('per_page')) {
            return response()->json(['success' => true, 'data' => $query->get()]);
        }

        $perPage = max(1, min((int) $request->query('per_page'), 100));

        return response()->json(['success' => true] + $query->paginate($perPage)->toArray());
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