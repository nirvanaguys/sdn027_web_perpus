<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_api_responses_do_not_leak_php_version_and_carry_security_headers(): void
    {
        $response = $this->getJson('/api/books');

        $response->assertHeaderMissing('X-Powered-By');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_cors_only_echoes_whitelisted_origin(): void
    {
        $this->withHeaders(['Origin' => 'http://localhost:5173'])
            ->getJson('/api/books')
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');

        $this->withHeaders(['Origin' => 'https://evil.example'])
            ->getJson('/api/books')
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }
}
