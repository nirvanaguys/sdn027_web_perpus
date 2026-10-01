<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ReadingLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_reading_list_is_private_and_idempotent(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = $this->createEbook();

        $this->getJson('/api/reading-list')->assertUnauthorized();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/reading-list/{$book->id}")
            ->assertCreated();

        $this->postJson("/api/reading-list/{$book->id}")->assertOk();
        $this->getJson('/api/reading-list')->assertJsonCount(1, 'data');

        $this->actingAs($otherUser, 'sanctum')
            ->getJson('/api/reading-list')
            ->assertJsonCount(0, 'data');

        $this->deleteJson("/api/reading-list/{$book->id}")->assertOk();
        $this->assertDatabaseMissing('reading_lists', [
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
        ]);
        $this->assertDatabaseHas('reading_lists', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_reading_file_requires_authentication_and_records_history(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ebooks/sample.pdf', '%PDF sample');
        $book = $this->createEbook();

        $this->getJson("/api/books/{$book->id}/read")->assertUnauthorized();

        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->get("/api/books/{$book->id}/read")
            ->assertOk();

        $this->getJson('/api/reading-history')
            ->assertOk()
            ->assertJsonPath('data.0.book.id', $book->id);
    }

    public function test_reading_list_rejects_books_without_ebook_files(): void
    {
        $user = User::factory()->create();
        $book = $this->createEbook();
        $book->update(['file_path' => null]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/reading-list/{$book->id}")
            ->assertNotFound();

        $this->assertSame(0, ReadingList::count());
    }

    public function test_admin_can_upload_a_public_cover_without_exposing_ebook_path(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/books', [
            'title' => 'Covered Ebook',
            'author' => 'Test Author',
            'publisher' => 'Test Publisher',
            'isbn' => fake()->unique()->isbn13(),
            'cover' => UploadedFile::fake()->create('cover.jpg', 50, 'image/jpeg'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.cover_url', fn ($url) => str_starts_with($url, '/storage/book-covers/'))
            ->assertJsonMissingPath('data.file_path');

        $book = Book::firstOrFail();
        Storage::disk('public')->assertExists($book->cover_image);
    }

    private function createEbook(): Book
    {
        return Book::create([
            'title' => 'Sample Ebook',
            'author' => 'Test Author',
            'publisher' => 'Test Publisher',
            'isbn' => fake()->unique()->isbn13(),
            'category' => 'Umum',
            'file_path' => 'ebooks/sample.pdf',
            'file_format' => 'pdf',
            'file_size' => 11,
        ]);
    }
}