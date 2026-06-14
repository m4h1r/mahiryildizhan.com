<?php

namespace Tests\Feature\Alice;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class AliceTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->token = $this->admin->createToken('alice-bridge')->plainTextToken;
    }

    protected function aliceHeaders(array $extra = []): array
    {
        return array_merge([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
            'X-Alice-Source' => 'alice',
        ], $extra);
    }

    protected function aliceGet(string $path, array $query = []): \Illuminate\Testing\TestResponse
    {
        $url = '/api/v1/alice/' . ltrim($path, '/');
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        return $this->getJson($url, $this->aliceHeaders());
    }

    protected function alicePost(string $path, array $data = [], array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(
            '/api/v1/alice/' . ltrim($path, '/'),
            $data,
            $this->aliceHeaders($headers)
        );
    }

    protected function alicePatch(string $path, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->patchJson(
            '/api/v1/alice/' . ltrim($path, '/'),
            $data,
            $this->aliceHeaders()
        );
    }

    protected function aliceDelete(string $path): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson(
            '/api/v1/alice/' . ltrim($path, '/'),
            [],
            $this->aliceHeaders()
        );
    }
}
