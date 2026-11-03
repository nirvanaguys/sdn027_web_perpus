<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Akun cukup email + kata sandi. NISN/NIP dihapus dari sistem,
 * lupa kata sandi ditangani pustakawan lewat panel admin.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_only_needs_name_email_and_password(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Siswa Baru',
            'email' => 'baru@sdn027.sch.id',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertStatus(201)->assertJsonPath('success', true);

        $this->postJson('/api/login', [
            'email' => 'baru@sdn027.sch.id',
            'password' => 'rahasia123',
        ])->assertStatus(200);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'ada@sdn027.sch.id']);

        $this->postJson('/api/register', [
            'name' => 'Siswa Baru',
            'email' => 'ada@sdn027.sch.id',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }
}
