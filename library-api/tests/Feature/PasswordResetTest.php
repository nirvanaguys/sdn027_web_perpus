<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Lupa kata sandi via tautan email (Laravel password broker).
 * Tidak butuh SMTP sungguhan: notifikasi ditangkap Notification::fake().
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_is_emailed_to_registered_address(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'siswa@sdn027.sch.id']);

        $this->postJson('/api/forgot-password', ['email' => $user->email])
            ->assertOk()
            ->assertJsonPath('success', true);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            return str_contains($notification->toMail($user)->actionUrl, '/reset-password?token=')
                && str_contains($notification->toMail($user)->actionUrl, urlencode($user->email));
        });
    }

    public function test_unknown_email_gets_same_answer_without_sending_mail(): void
    {
        Notification::fake();

        $this->postJson('/api/forgot-password', ['email' => 'tidakada@sdn027.sch.id'])
            ->assertOk()
            ->assertJsonPath('success', true);

        Notification::assertNothingSent();
    }

    public function test_full_reset_flow_replaces_password_and_revokes_old_sessions(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'guru@sdn027.sch.id']);
        $oldToken = $user->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();

        // Token mentah hanya ada di email; DB menyimpan versi hash-nya.
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $n) use (&$token) {
            $token = $n->token;
            return true;
        });
        $this->assertNotEmpty($token);

        $reset = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'sandibaru123',
            'password_confirmation' => 'sandibaru123',
        ]);

        $reset->assertOk()->assertJsonPath('success', true);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'sandibaru123',
        ])->assertOk();

        // Sesi lama dicabut
        $this->withHeader('Authorization', "Bearer {$oldToken}")
            ->getJson('/api/me')
            ->assertUnauthorized();

        // Token sekali pakai
        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'cobolagi123',
            'password_confirmation' => 'cobolagi123',
        ])->assertStatus(422);
    }
}
