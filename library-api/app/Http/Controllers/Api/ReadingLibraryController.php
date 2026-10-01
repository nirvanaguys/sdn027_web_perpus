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

        $entry = ReadingList::firstOrCreate([
            'user_id' => $request->user()->id,
            'book_id' => $book->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $entry->load('book'),
        ], $entry->wasRecentlyCreated ? 201 : 200);
    }

    public function removeBook(Request $request, $bookId)
    {
        ReadingList::where('user_id', $request->user()->id)
            ->where('book_id', $bookId)
            ->delete();

        return response()->json(['success' => true]);
    }
}