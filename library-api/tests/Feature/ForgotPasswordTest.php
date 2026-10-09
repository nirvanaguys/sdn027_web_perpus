<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Lupa kata sandi mandiri 24 jam tanpa email/SMTP:
 * verifikasi email + NISN/NIP -> tiket reset 10 menit -> sandi baru.
 */
class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siswa = User::factory()->create([
            'name' => 'Budi Kelas 1',
            'email' => 'budi@sdn027.sch.id',
            'identity_number' => '0091234567',
            'role' => 'member',
        ]);
    }

    public function test_register_requires_identity_number(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Siswa Baru',
            'email' => 'baru@sdn027.sch.id',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['identity_number']);
    }

    public function test_register_with_nisn_succeeds(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Siswa Baru',
            'email' => 'baru@sdn027.sch.id',
            'identity_number' => '0097654321',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertStatus(201)->assertJsonPath('success', true);
        $this->assertEquals('0097654321', User::where('email', 'baru@sdn027.sch.id')->first()->identity_number);
    }

    public function test_forgot_password_full_flow(): void
    {
        // Langkah 1: verifikasi email + NISN.
        $verify = $this->postJson('/api/forgot-password/verify', [
            'email' => 'budi@sdn027.sch.id',
            'identity_number' => '0091234567',
        ]);

        $verify->assertStatus(200)->assertJsonPath('success', true);
        $ticket = $verify->json('data.reset_ticket');
        $this->assertNotEmpty($ticket);

        // Langkah 2: tukar tiket dengan sandi baru.
        $reset = $this->postJson('/api/forgot-password/reset', [
            'reset_ticket' => $ticket,
            'password' => 'sandibaru123',
            'password_confirmation' => 'sandibaru123',
        ]);

        $reset->assertStatus(200)->assertJsonPath('success', true);

        // Sandi baru bisa dipakai login.
        $this->postJson('/api/login', [
            'email' => 'budi@sdn027.sch.id',
            'password' => 'sandibaru123',
        ])->assertStatus(200);

        // Tiket sekali pakai: tidak bisa dipakai ulang.
        $this->postJson('/api/forgot-password/reset', [
            'reset_ticket' => $ticket,
            'password' => 'cobolagi123',
            'password_confirmation' => 'cobolagi123',
        ])->assertStatus(422);
    }

    public function test_forgot_password_verify_rejects_wrong_nisn(): void
    {
        $this->postJson('/api/forgot-password/verify', [
            'email' => 'budi@sdn027.sch.id',
            'identity_number' => '0000000000',
        ])->assertStatus(422);
    }

    public function test_forgot_password_verify_rejects_deactivated_account(): void
    {
        $this->siswa->is_active = false;
        $this->siswa->save();

        $this->postJson('/api/forgot-password/verify', [
            'email' => 'budi@sdn027.sch.id',
            'identity_number' => '0091234567',
        ])->assertStatus(422);
    }

    public function test_forgot_password_reset_rejects_invalid_ticket(): void
    {
        $this->postJson('/api/forgot-password/reset', [
            'reset_ticket' => 'token-palsu-12345',
            'password' => 'sandibaru123',
            'password_confirmation' => 'sandibaru123',
        ])->assertStatus(422);
    }

    public function test_admin_can_fill_identity_number_for_old_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $oldUser = User::factory()->create([
            'email' => 'lama@sdn027.sch.id',
            'identity_number' => null,
        ]);

        $this->putJson('/api/admin/users/' . $oldUser->id, [
            'identity_number' => '0090001111',
        ])->assertStatus(200);

        $this->assertEquals('0090001111', $oldUser->fresh()->identity_number);
    }
}
