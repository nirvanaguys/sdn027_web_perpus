<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Support\BookCoverGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateBookCovers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:generate-covers {--force : Buat ulang sampul walau buku sudah punya sampul} {--from-files : Ekstrak sampul dari halaman depan file ebook (bukan generatif)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buatkan sampul untuk buku: dari halaman depan file ebook, atau generatif bila file tak ada';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $fromFiles = (bool) $this->option('from-files');

        $books = Book::query()
            ->when(! $force, fn ($q) => $q->whereNull('cover_image'))
            ->orderBy('id')
            ->get();

        if ($books->isEmpty()) {
            $this->info('Semua buku sudah memiliki sampul. Tidak ada yang perlu dibuat.');
            return self::SUCCESS;
        }

        $done = 0;
        foreach ($books as $book) {
            if ($force && $book->cover_image
                && Storage::disk('public')->exists($book->cover_image)) {
                Storage::disk('public')->delete($book->cover_image);
            }

            $path = null;
            // Prioritas: halaman depan file asli -> sampul generatif.
            if ($fromFiles || $book->file_path) {
                $path = \App\Support\EbookCoverExtractor::extract($book);
            }
            if (!$path) {
                $path = \App\Support\BookCoverGenerator::generate($book);
            }
            $book->cover_image = $path;
            $book->saveQuietly();
            $done++;
            $this->line("  ✓ [{$book->id}] {$book->title}");
        }

        $this->info("Selesai: {$done} sampul dibuat di storage/app/public/book-covers/.");

        return self::SUCCESS;
    }
}
