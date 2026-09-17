<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    // Member: Pinjam Buku
    public function borrow(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        return DB::transaction(function () use ($request) {
            // Lock baris buku untuk konsistensi stok
            $book = Book::lockForUpdate()->findOrFail($request->book_id);

            if ($book->stock < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maaf, stok buku ini sedang habis.'
                ], 422);
            }

            // Kurangi stok buku
            $book->decrement('stock');

            // Durasi pinjam: 7 hari
            $borrowDate = Carbon::now();
            $dueDate = Carbon::now()->addDays(7);

            $transaction = Transaction::create([
                'user_id'     => $request->user()->id,
                'book_id'     => $book->id,
                'borrow_date' => $borrowDate,
                'due_date'    => $dueDate,
                'status'      => 'borrowed',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil dipinjam. Batas pengembalian: ' . $dueDate->format('d-m-Y'),
                'data'    => $transaction->load('book')
            ], 201);
        });
    }

    // Admin / Member: Pengembalian Buku
    public function returnBook(Request $request, $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $transaction = Transaction::lockForUpdate()->findOrFail($id);

            // Validasi otorisasi: hanya admin atau user pemilik pinjaman
            if ($request->user()->role !== 'admin' && $request->user()->id !== $transaction->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk transaksi ini.'
                ], 403);
            }

            if ($transaction->status === 'returned') {
                return response()->json([
                    'success' => false,
                    'message' => 'Buku ini sudah dikembalikan sebelumnya.'
                ], 422);
            }

            $transaction->update([
                'status'      => 'returned',
                'return_date' => Carbon::now(),
            ]);

            // Kembalikan stok buku
            $transaction->book()->increment('stock');

            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil dikembalikan. Stok telah diperbarui.',
                'data'    => $transaction
            ]);
        });
    }

    // Riwayat transaksi pengguna saat ini
    public function myTransactions(Request $request)
    {
        $transactions = Transaction::with('book')
            ->where('user_id', $request->user()->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $transactions
        ]);
    }
}
