<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin Default (jika belum ada)
        User::firstOrCreate(
            ['email' => 'admin@perpustakaan.com'],
            [
                'name'     => 'Pustakawan Admin',
                'password' => bcrypt('admin123'),
                'role'     => 'admin',
            ]
        );

        // Member Default (jika belum ada)
        User::firstOrCreate(
            ['email' => 'member@perpustakaan.com'],
            [
                'name'     => 'Ahmad Member',
                'password' => bcrypt('member123'),
                'role'     => 'member',
            ]
        );

        // Pastikan folder privat ebooks ada
        Storage::disk('local')->makeDirectory('ebooks');

        // Buat file sample PDF jika belum ada
        $pdfPath = 'ebooks/sample_standard.pdf';
        if (!Storage::disk('local')->exists($pdfPath)) {
            $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n4 0 obj\n<< /Length 260 >>\nstream\nBT\n/F1 22 Tf\n50 720 Td\n(Perpustakaan Digital SDN 027 Balikpapan Utara) Tj\n0 -40 Td\n/F1 16 Tf\n(Format Dokumen: PDF Digital Ebook) Tj\n0 -30 Td\n/F1 12 Tf\n(Selamat datang di fasilitas membaca buku online perpustakaan.) Tj\n0 -20 Td\n(Fitur pembaca web menyediakan pembesaran, navigasi halaman, dan mode layar penuh.) Tj\nET\nendstream\nendobj\n5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\nxref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000241 00000 n \n0000000554 00000 n \ntrailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n625\n%%EOF\n";
            Storage::disk('local')->put($pdfPath, $pdfContent);
        }

        // Buat file sample EPUB jika belum ada
        $epubPath = 'ebooks/sample_standard.epub';
        if (!Storage::disk('local')->exists($epubPath)) {
            $fullEpubPath = Storage::disk('local')->path($epubPath);
            $zip = new ZipArchive();
            if ($zip->open($fullEpubPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zip->addFromString("mimetype", "application/epub+zip");
                $zip->setCompressionName("mimetype", ZipArchive::CM_STORE);
                $zip->addFromString("META-INF/container.xml", "<?xml version=\"1.0\"?>\n<container version=\"1.0\" xmlns=\"urn:oasis:names:tc:opendocument:xmlns:container\">\n  <rootfiles>\n    <rootfile full-path=\"OEBPS/content.opf\" media-type=\"application/oebps-package+xml\"/>\n  </rootfiles>\n</container>");
                $zip->addFromString("OEBPS/content.opf", "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<package xmlns=\"http://www.idpf.org/2007/opf\" unique-identifier=\"BookID\" version=\"2.0\">\n  <metadata xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n    <dc:title>Ebook Digital Perpustakaan</dc:title>\n    <dc:creator>PerpusKu Digital</dc:creator>\n    <dc:identifier id=\"BookID\">urn:uuid:perpusku-001</dc:identifier>\n    <dc:language>id</dc:language>\n  </metadata>\n  <manifest>\n    <item id=\"ncx\" href=\"toc.ncx\" media-type=\"application/x-dtbncx+xml\"/>\n    <item id=\"chapter1\" href=\"chapter1.xhtml\" media-type=\"application/xhtml+xml\"/>\n  </manifest>\n  <spine toc=\"ncx\">\n    <itemref idref=\"chapter1\"/>\n  </spine>\n</package>");
                $zip->addFromString("OEBPS/toc.ncx", "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE ncx PUBLIC \"-//NISO//DTD ncx 2005-1//EN\" \"http://www.daisy.org/z3986/2005/ncx-2005-1.dtd\">\n<ncx xmlns=\"http://www.daisy.org/z3986/2005/ncx/\" version=\"2005-1\">\n  <head>\n    <meta name=\"dtb:uid\" content=\"urn:uuid:perpusku-001\"/>\n    <meta name=\"dtb:depth\" content=\"1\"/>\n    <meta name=\"dtb:totalPageCount\" content=\"0\"/>\n    <meta name=\"dtb:maxPageNumber\" content=\"0\"/>\n  </head>\n  <docTitle><text>Ebook Digital Perpustakaan</text></docTitle>\n  <navMap>\n    <navPoint id=\"navpoint-1\" playOrder=\"1\">\n      <navLabel><text>Bab 1: Pengenalan Pembaca EPUB</text></navLabel>\n      <content src=\"chapter1.xhtml\"/>\n    </navPoint>\n  </navMap>\n</ncx>");
                $zip->addFromString("OEBPS/chapter1.xhtml", "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n  <title>Bab 1: Pengenalan</title>\n  <style type=\"text/css\">\n    body { font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; line-height: 1.8; padding: 24px; color: #1e293b; background: #ffffff; }\n    h1 { color: #4338ca; border-bottom: 2px solid #e0e7ff; padding-bottom: 10px; }\n    p { margin-bottom: 1.2em; font-size: 1.05rem; }\n    .callout { background: #eef2ff; border-left: 4px solid #6366f1; padding: 12px 16px; border-radius: 4px; margin: 16px 0; }\n  </style>\n</head>\n<body>\n  <h1>Bab 1: Selamat Membaca Ebook</h1>\n  <p>Selamat datang di pembaca digital resmi Perpustakaan SDN 027 Balikpapan Utara.</p>\n  <div class=\"callout\">\n    Format EPUB memungkinkan Anda memperbesar atau memperkecil teks dan navigasi halaman dengan fleksibel.\n  </div>\n  <p>Nikmati kemudahan mengakses ilmu pengetahuan dan literasi kapan saja dan di mana saja melalui koleksi digital kami.</p>\n</body>\n</html>");
                $zip->close();
            }
        }

        $pdfSize = Storage::disk('local')->size($pdfPath);
        $epubSize = Storage::disk('local')->size($epubPath);

        // Update or create sample books
        $sampleBooks = [
            [
                'title'       => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author'      => 'Robert C. Martin',
                'publisher'   => 'Prentice Hall',
                'isbn'        => '978-0132350884',
                'category'    => 'Teknologi',
                'description' => 'Panduan komprehensif penulisan kode program yang bersih, mudah dipelihara, dan berkualitas tinggi.',
                'file_path'   => $pdfPath,
                'file_format' => 'pdf',
                'file_size'   => $pdfSize,
            ],
            [
                'title'       => 'The Pragmatic Programmer: Your Journey To Mastery',
                'author'      => 'David Thomas, Andrew Hunt',
                'publisher'   => 'Addison-Wesley',
                'isbn'        => '978-0135957059',
                'category'    => 'Teknologi',
                'description' => 'Prinsip, filosofi, dan praktik terbaik rekayasa perangkat lunak untuk perjalanan menjadi pemrogram ahli.',
                'file_path'   => $epubPath,
                'file_format' => 'epub',
                'file_size'   => $epubSize,
            ],
            [
                'title'       => 'Refactoring: Improving the Design of Existing Code',
                'author'      => 'Martin Fowler',
                'publisher'   => 'Addison-Wesley Professional',
                'isbn'        => '978-0201485677',
                'category'    => 'Teknologi',
                'description' => 'Teknik merombak arsitektur dan struktur kode internal tanpa mengubah perilaku eksternal sistem.',
                'file_path'   => $pdfPath,
                'file_format' => 'pdf',
                'file_size'   => $pdfSize,
            ],
            [
                'title'       => 'Laskar Pelangi',
                'author'      => 'Andrea Hirata',
                'publisher'   => 'Bentang Pustaka',
                'isbn'        => '978-9791227186',
                'category'    => 'Fiksi / Sastra',
                'description' => 'Novel inspiratif tentang perjuangan sepuluh anak di Belitong dalam menuntut ilmu di tengah keterbatasan.',
                'file_path'   => $epubPath,
                'file_format' => 'epub',
                'file_size'   => $epubSize,
            ],
            [
                'title'       => 'Atomic Habits',
                'author'      => 'James Clear',
                'publisher'   => 'Gramedia Pustaka Utama',
                'isbn'        => '978-6020633176',
                'category'    => 'Pengembangan Diri',
                'description' => 'Perubahan-perubahan kecil yang memberikan hasil luar biasa dalam membangun kebiasaan baik dan membuang kebiasaan buruk.',
                'file_path'   => $pdfPath,
                'file_format' => 'pdf',
                'file_size'   => $pdfSize,
            ],
        ];

        foreach ($sampleBooks as $data) {
            Book::updateOrCreate(
                ['isbn' => $data['isbn']],
                $data
            );
        }
    }
}
