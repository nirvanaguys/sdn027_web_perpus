<?php

namespace App\Support;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookCoverGenerator
{
    /**
     * Palet gradien sampul (diselaraskan dengan tema hijau perpustakaan).
     */
    protected static array $palettes = [
        ['#14532d', '#0e2f14'],
        ['#1c6d00', '#0e2f14'],
        ['#0f766e', '#134e4a'],
        ['#1e40af', '#172554'],
        ['#7c2d12', '#431407'],
        ['#4a044e', '#1e1b4b'],
    ];

    /**
     * Buat berkas sampul SVG untuk sebuah buku dan kembalikan path relatif
     * terhadap disk "public" (mis. "book-covers/slug.svg").
     */
    public static function generate(Book $book): string
    {
        $palette = self::$palettes[((int) $book->id) % count(self::$palettes)];
        [$from, $to] = $palette;

        $title = trim((string) ($book->title ?? 'Tanpa Judul'));
        $author = trim((string) ($book->author ?? 'Anonim'));
        $category = trim((string) ($book->category ?? 'Koleksi Ebook'));
        $format = strtoupper(trim((string) ($book->file_format ?? 'EBOOK')) ?: 'EBOOK');

        $lines = self::wrapTitle($title, 20, 5);
        $titleFontSize = count($lines) >= 4 ? 34 : 40;
        $lineHeight = $titleFontSize * 1.18;
        $blockHeight = $lineHeight * count($lines);
        // Pusatkan blok judul di area tengah sampul (kanvas 600x800).
        $firstBaseline = 400 - ($blockHeight / 2) + ($titleFontSize * 0.85);

        $titleText = '';
        foreach ($lines as $i => $line) {
            $y = $firstBaseline + ($i * $lineHeight);
            $titleText .= '<text x="48" y="' . round($y, 1) . '" font-family="Georgia, \'Times New Roman\', serif" font-size="' . $titleFontSize . '" font-weight="bold" fill="#ffffff">' . e($line) . '</text>';
        }

        $slug = Str::slug(Str::limit($title, 40, '')) ?: 'buku-' . $book->id;
        $filename = "book-covers/{$slug}-{$book->id}.svg";

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="800" viewBox="0 0 600 800">'
            . '<defs>'
            . '<linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">'
            . '<stop offset="0" stop-color="' . $from . '"/>'
            . '<stop offset="1" stop-color="' . $to . '"/>'
            . '</linearGradient>'
            . '</defs>'
            . '<rect width="600" height="800" fill="url(#bg)"/>'
            . '<circle cx="520" cy="90" r="150" fill="#ffffff" opacity="0.08"/>'
            . '<circle cx="70" cy="720" r="170" fill="#ffffff" opacity="0.06"/>'
            . '<rect x="0" y="0" width="26" height="800" fill="#ffffff" opacity="0.22"/>'
            . '<rect x="34" y="0" width="4" height="800" fill="#000000" opacity="0.25"/>'
            // Kategori di atas
            . '<text x="60" y="80" font-family="Verdana, Arial, sans-serif" font-size="22" font-weight="bold" letter-spacing="4" fill="#ffffff" opacity="0.8">' . e(mb_strtoupper($category)) . '</text>'
            . '<rect x="60" y="100" width="64" height="4" fill="#ffffff" opacity="0.6"/>'
            // Judul (tengah)
            . $titleText
            // Penulis
            . '<text x="60" y="600" font-family="Verdana, Arial, sans-serif" font-size="24" fill="#ffffff" opacity="0.9">' . e($author) . '</text>'
            // Footer sekolah
            . '<text x="60" y="748" font-family="Verdana, Arial, sans-serif" font-size="19" font-weight="bold" letter-spacing="1" fill="#ffffff" opacity="0.75">SDN 027 BALIKPAPAN UTARA</text>'
            // Badge format
            . '<rect x="452" y="36" width="112" height="44" rx="22" fill="#000000" opacity="0.35"/>'
            . '<text x="508" y="65" text-anchor="middle" font-family="Verdana, Arial, sans-serif" font-size="22" font-weight="bold" fill="#ffffff">' . e($format) . '</text>'
            . '</svg>';

        Storage::disk('public')->put($filename, $svg);

        return $filename;
    }

    /**
     * Pecah judul menjadi beberapa baris seimbang.
     *
     * @return string[]
     */
    protected static function wrapTitle(string $title, int $maxChars = 20, int $maxLines = 5): array
    {
        $words = preg_split('/\s+/', $title, -1, PREG_SPLIT_NO_EMPTY) ?: [$title];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            // Kata super panjang dipotong agar tidak meluber.
            if (mb_strlen($word) > $maxChars) {
                if ($current !== '') {
                    $lines[] = $current;
                    $current = '';
                }
                $lines[] = mb_substr($word, 0, $maxChars - 1) . '…';
                continue;
            }

            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if (mb_strlen($candidate) <= $maxChars) {
                $current = $candidate;
            } else {
                $lines[] = $current;
                $current = $word;
            }

            if (count($lines) === $maxLines - 1 && count($words) > 0) {
                // Baris terakhir menampung sisa kata (dipotong bila perlu).
                $rest = $current;
                // Cari sisa kata yang belum diproses.
                $remaining = array_slice($words, array_search($word, $words) + 1);
                if ($remaining) {
                    $rest .= ' ' . implode(' ', $remaining);
                }
                if (mb_strlen($rest) > $maxChars + 4) {
                    $rest = mb_substr($rest, 0, $maxChars + 3) . '…';
                }
                return array_merge($lines, [$rest]);
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines ?: [$title];
    }
}
