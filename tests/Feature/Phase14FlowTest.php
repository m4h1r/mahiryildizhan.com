<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Phase14FlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_grouped_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'settings' => [
                'site_name' => 'My CRM Blog',
                'default_meta_description' => 'Default description',
                'weather_city_name' => 'Istanbul',
            ],
        ]);

        $response->assertRedirect(route('admin.settings'));

        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'value' => 'My CRM Blog',
            'group' => 'seo',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'weather_city_name',
            'value' => 'Istanbul',
            'group' => 'weather',
        ]);
    }

    public function test_recaptcha_service_uses_setting_secret_when_env_missing(): void
    {
        putenv('RECAPTCHA_SECRET');
        config(['services.recaptcha.secret' => null]);

        Setting::query()->create([
            'key' => 'recaptcha_secret_key',
            'value' => 'setting-secret',
            'group' => 'recaptcha',
            'is_secret' => true,
        ]);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.9,
            ]),
        ]);

        $service = app(RecaptchaService::class);
        $result = $service->verify('token', '127.0.0.1');

        $this->assertTrue($result['success']);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.google.com/recaptcha/api/siteverify'
                && ($request['secret'] ?? null) === 'setting-secret';
        });
    }
}
