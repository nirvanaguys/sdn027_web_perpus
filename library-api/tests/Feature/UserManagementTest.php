<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Fitur saran dosen:
 * 1. Kelola akun oleh admin (pengganti "lupa password" tanpa email/SMTP):
 *    list, edit, reset password, nonaktifkan/aktifkan.
 * 2. Jenjang kelas buku (kelas 1-6 + umum) + filter di katalog.
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Pustakawan',
            'email' => 'pustakawan@sdn027.sch.id',
            'role' => 'admin',
        ]);

        $this->member = User::factory()->create([
            'name' => 'Siswa Kelas 3',
            'email' => 'siswa@sdn027.sch.id',
            'role' => 'member',
        ]);
    }

    public function test_admin_can_list_users(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertGreaterThanOrEqual(2, count($response->json('data.data')));
    }

    public function test_member_cannot_list_users(): void
    {
        Sanctum::actingAs($this->member);

        $this->getJson('/api/admin/users')->assertStatus(403);
    }

    public function test_admin_can_edit_user_data(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/admin/users/' . $this->member->id, [
            'name' => 'Siswa Kelas 3 Baru',
        ]);

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertEquals('Siswa Kelas 3 Baru', $this->member->fresh()->name);
    }

    public function test_admin_can_reset_user_password_and_member_can_login_with_new_password(): void
    {
        Sanctum::actingAs($this->admin);

        $reset = $this->postJson('/api/admin/users/' . $this->member->id . '/reset-password', [
            'password' => 'barusandi123',
            'password_confirmation' => 'barusandi123',
        ]);
        $reset->assertStatus(200)->assertJsonPath('success', true);

        // Password baru bisa dipakai login.
        $login = $this->postJson('/api/login', [
            'email' => 'siswa@sdn027.sch.id',
            'password' => 'barusandi123',
        ]);
        $login->assertStatus(200)->assertJsonPath('success', true);
    }

    public function test_deactivated_user_cannot_login(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/admin/users/' . $this->member->id . '/set-active', [
            'is_active' => false,
        ])->assertStatus(200);

        $this->assertFalse($this->member->fresh()->is_active);

        $login = $this->postJson('/api/login', [
            'email' => 'siswa@sdn027.sch.id',
            'password' => 'password',
        ]);
        $login->assertStatus(422);

        // Diaktifkan lagi -> bisa login.
        $this->postJson('/api/admin/users/' . $this->member->id . '/set-active', [
            'is_active' => true,
        ])->assertStatus(200);

        $this->postJson('/api/login', [
            'email' => 'siswa@sdn027.sch.id',
            'password' => 'password',
        ])->assertStatus(200);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/admin/users/' . $this->admin->id . '/set-active', [
            'is_active' => false,
        ])->assertStatus(422);

        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_books_can_be_filtered_by_grade_level(): void
    {
        Book::create([
            'title' => 'Matematika Kelas 3',
            'author' => 'Guru A',
            'publisher' => 'Kemendikbud',
            'isbn' => '978-0000000101',
            'category' => 'Pelajaran',
            'grade_level' => 'kelas-3',
        ]);
        Book::create([
            'title' => 'Dongeng Nusantara',
            'author' => 'Penulis B',
            'publisher' => 'Penerbit C',
            'isbn' => '978-0000000102',
            'category' => 'Cerita',
            'grade_level' => 'umum',
        ]);

        $response = $this->getJson('/api/books?grade_level=kelas-3');

        $response->assertStatus(200);
        $titles = collect($response->json('data.data'))->pluck('title')->all();
        $this->assertContains('Matematika Kelas 3', $titles);
        $this->assertNotContains('Dongeng Nusantara', $titles);
    }

    public function test_admin_create_book_rejects_invalid_grade_level(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/admin/books', [
            'title' => 'Buku Aneh',
            'author' => 'X',
            'publisher' => 'Y',
            'isbn' => '978-0000000103',
            'grade_level' => 'kelas-99',
        ])->assertStatus(422)->assertJsonValidationErrors(['grade_level']);
    }

    public function test_login_response_contains_is_active_flag(): void
    {
        $login = $this->postJson('/api/login', [
            'email' => 'siswa@sdn027.sch.id',
            'password' => 'password',
        ]);

        $login->assertStatus(200);
        $this->assertTrue($login->json('data.user.is_active'));
    }
}
