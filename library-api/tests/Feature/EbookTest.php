<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EbookTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $memberUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name'  => 'Admin Test',
            'email' => 'admin@test.com',
            'role'  => 'admin',
        ]);

        $this->memberUser = User::factory()->create([
            'name'  => 'Member Test',
            'email' => 'member@test.com',
            'role'  => 'member',
        ]);
    }

    /**
     * Tamu/pengguna belum login DITOLAK membaca file ebook (401 Unauthorized).
     */
    public function test_unauthenticated_user_cannot_access_ebook_file(): void
    {
        Storage::fake('local');
        $fakeFile = UploadedFile::fake()->create('sample.pdf', 100, 'application/pdf');
        $path = $fakeFile->store('ebooks', 'local');

        $book = Book::create([
            'title'       => 'Buku Rahasia Digital',
            'author'      => 'Penulis Hebat',
            'publisher'   => 'Penerbit Digital',
            'isbn'        => '978-0000000001',
            'category'    => 'Teknologi',
            'file_path'   => $path,
            'file_format' => 'pdf',
            'file_size'   => 102400,
        ]);

        $response = $this->getJson('/api/books/' . $book->id . '/read');

        $response->assertStatus(401);
    }

    /**
     * Pengguna yang sudah login DIIZINKAN membaca/mengakses stream file ebook (200 OK).
     */
    public function test_authenticated_user_can_access_ebook_file(): void
    {
        Storage::fake('local');
        $fakeFile = UploadedFile::fake()->create('sample.pdf', 100, 'application/pdf');
        $path = $fakeFile->store('ebooks', 'local');

        $book = Book::create([
            'title'       => 'Buku Pemrograman Web',
            'author'      => 'John Doe',
            'publisher'   => 'Tech Press',
            'isbn'        => '978-0000000002',
            'category'    => 'Komputer',
            'file_path'   => $path,
            'file_format' => 'pdf',
            'file_size'   => 102400,
        ]);

        Sanctum::actingAs($this->memberUser);

        $response = $this->get('/api/books/' . $book->id . '/read');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Tamu belum login DAPAT melihat katalog metadata, dan path privat tidak bocor di JSON.
     */
    public function test_guest_can_view_book_catalog_and_metadata_without_exposing_file_path(): void
    {
        Storage::fake('local');
        $fakeFile = UploadedFile::fake()->create('guide.epub', 150, 'application/epub+zip');
        $path = $fakeFile->store('ebooks', 'local');

        $book = Book::create([
            'title'       => 'Panduan Belajar Vue 3',
            'author'      => 'Evan You Fan',
            'publisher'   => 'Open Publisher',
            'isbn'        => '978-0000000003',
            'category'    => 'Teknologi',
            'description' => 'Buku pengantar belajar Vue 3.',
            'file_path'   => $path,
            'file_format' => 'epub',
            'file_size'   => 153600,
        ]);

        // List catalog
        $listResponse = $this->getJson('/api/books');
        $listResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // Show metadata detail
        $detailResponse = $this->getJson('/api/books/' . $book->id);
        $detailResponse->assertStatus(200)
            ->assertJsonPath('data.title', 'Panduan Belajar Vue 3')
            ->assertJsonPath('data.file_format', 'epub')
            ->assertJsonPath('data.has_ebook', true);

        // Pastikan kolom file_path tersembunyi (hidden)
        $detailResponse->assertJsonMissing(['file_path' => $path]);
    }

    /**
     * Admin/Pustakawan dapat mengunggah berkas ebook baru beserta metadatanya.
     */
    public function test_admin_can_upload_and_create_ebook(): void
    {
        Storage::fake('local');
        Sanctum::actingAs($this->adminUser);

        $file = UploadedFile::fake()->create('laskar_pelangi.pdf', 500, 'application/pdf');

        $payload = [
            'title'       => 'Laskar Pelangi Ebook Edition',
            'author'      => 'Andrea Hirata',
            'publisher'   => 'Bentang Pustaka',
            'isbn'        => '978-9791227189',
            'category'    => 'Sastra',
            'description' => 'Kisah inspiratif anak-anak Belitong.',
            'file'        => $file,
        ];

        $response = $this->postJson('/api/admin/books', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Laskar Pelangi Ebook Edition')
            ->assertJsonPath('data.file_format', 'pdf');

        $createdBook = Book::where('isbn', '978-9791227189')->first();
        $this->assertNotNull($createdBook);
        $this->assertNotNull($createdBook->file_path);
        Storage::disk('local')->assertExists($createdBook->file_path);
    }

    /**
     * Validasi menolak berkas dengan format selain PDF atau EPUB.
     */
    public function test_ebook_upload_validation_rejects_invalid_file_format(): void
    {
        Storage::fake('local');
        Sanctum::actingAs($this->adminUser);

        $invalidFile = UploadedFile::fake()->create('dangerous_script.exe', 100, 'application/x-msdownload');

        $payload = [
            'title'     => 'Buku Palsu',
            'author'    => 'Penipu',
            'publisher' => 'Penerbit Gelap',
            'isbn'      => '978-0000000999',
            'file'      => $invalidFile,
        ];

        $response = $this->postJson('/api/admin/books', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /**
     * Pengguna biasa (non-admin) tidak boleh mengunggah atau mengelola koleksi ebook (403 Forbidden).
     */
    public function test_non_admin_cannot_upload_or_modify_ebook(): void
    {
        Storage::fake('local');
        Sanctum::actingAs($this->memberUser);

        $file = UploadedFile::fake()->create('sample.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/admin/books', [
            'title'     => 'Buku Hacker',
            'author'    => 'Anon',
            'publisher' => 'Dark Web',
            'isbn'      => '978-6666666666',
            'file'      => $file,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Admin dapat mengganti file ebook lama dengan file baru.
     */
    public function test_admin_can_replace_ebook_file(): void
    {
        Storage::fake('local');
        Sanctum::actingAs($this->adminUser);

        $oldFile = UploadedFile::fake()->create('old.pdf', 100, 'application/pdf');
        $oldPath = $oldFile->store('ebooks', 'local');

        $book = Book::create([
            'title'       => 'Buku Revisi',
            'author'      => 'Penulis',
            'publisher'   => 'Penerbit',
            'isbn'        => '978-1111222233',
            'file_path'   => $oldPath,
            'file_format' => 'pdf',
            'file_size'   => 102400,
        ]);

        Storage::disk('local')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->create('new_revised.epub', 200, 'application/epub+zip');

        $response = $this->postJson('/api/admin/books/' . $book->id, [
            'title'     => 'Buku Revisi Edisi 2',
            'author'    => 'Penulis',
            'publisher' => 'Penerbit',
            'isbn'      => '978-1111222233',
            'file'      => $newFile,
        ]);

        $response->assertStatus(200);

        $book->refresh();
        $this->assertEquals('epub', $book->file_format);
        $this->assertNotEquals($oldPath, $book->file_path);

        // File lama terhapus dan file baru tersimpan
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($book->file_path);
    }

    /**
     * Admin dapat menghapus ebook beserta berkas fisiknya dari storage privat.
     */
    public function test_admin_can_delete_ebook_and_its_private_file(): void
    {
        Storage::fake('local');
        Sanctum::actingAs($this->adminUser);

        $file = UploadedFile::fake()->create('book_to_delete.pdf', 100, 'application/pdf');
        $filePath = $file->store('ebooks', 'local');

        $book = Book::create([
            'title'       => 'Buku Akan Dihapus',
            'author'      => 'Penulis',
            'publisher'   => 'Penerbit',
            'isbn'        => '978-9999888877',
            'file_path'   => $filePath,
            'file_format' => 'pdf',
        ]);

        Storage::disk('local')->assertExists($filePath);

        $response = $this->deleteJson('/api/admin/books/' . $book->id);
        $response->assertStatus(200);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        Storage::disk('local')->assertMissing($filePath);
    }
}
