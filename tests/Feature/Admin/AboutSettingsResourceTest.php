<?php

use App\Models\Setting;

it('requires authentication to view the about page', function (): void {
    $this->get(route('admin.about'))->assertRedirect(route('login'));
});

it('denies non-admin users from the about page', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.about'))->assertForbidden();
});

it('lets an admin view the about page', function (): void {
    $this->actingAs(actingAsAdmin())
        ->get(route('admin.about'))
        ->assertOk()
        ->assertViewIs('admin.about');
});

it('lets an admin update the about page content', function (): void {
    $admin = actingAsAdmin();

    $this->actingAs($admin)->post(route('admin.about.update'), [
        'content_en' => 'Hello',
        'content_tr' => 'Merhaba',
    ])->assertRedirect(route('admin.about'));

    $this->assertDatabaseHas('settings', ['key' => 'about_content_en', 'value' => 'Hello']);
    $this->assertDatabaseHas('settings', ['key' => 'about_content_tr', 'value' => 'Merhaba']);
});

it('requires authentication to view settings', function (): void {
    $this->get(route('admin.settings'))->assertRedirect(route('login'));
});

it('denies non-admin users from settings', function (): void {
    $this->actingAs(actingAsNonAdmin())->get(route('admin.settings'))->assertForbidden();
});

it('lets an admin view the settings page', function (): void {
    $this->actingAs(actingAsAdmin())
        ->get(route('admin.settings'))
        ->assertOk()
        ->assertViewIs('admin.settings');
});

it('lets an admin update settings', function (): void {
    $admin = actingAsAdmin();

    $this->actingAs($admin)->post(route('admin.settings.update'), [
        'settings' => ['site_name' => 'My Site'],
    ])->assertRedirect(route('admin.settings'));

    $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'My Site']);
});

it('lets an admin update the brand colors', function (): void {
    $admin = actingAsAdmin();

    $this->actingAs($admin)->post(route('admin.settings.update'), [
        'settings' => [
            'brand_primary' => '#123ABC',
            'brand_secondary' => '#ABC123',
        ],
    ])->assertRedirect(route('admin.settings'));

    $this->assertDatabaseHas('settings', ['key' => 'brand_primary', 'value' => '#123ABC', 'group' => 'brand']);
    $this->assertDatabaseHas('settings', ['key' => 'brand_secondary', 'value' => '#ABC123', 'group' => 'brand']);
});

it('rejects an invalid brand color value', function (): void {
    $admin = actingAsAdmin();

    $this->actingAs($admin)->post(route('admin.settings.update'), [
        'settings' => ['brand_primary' => 'not-a-hex-color'],
    ])->assertSessionHasErrors(['settings.brand_primary']);

    $this->assertDatabaseMissing('settings', ['key' => 'brand_primary', 'value' => 'not-a-hex-color']);
});

it('renders the saved brand color as a CSS override on the public layout', function (): void {
    Setting::query()->create(['key' => 'brand_primary', 'value' => '#123ABC', 'group' => 'brand']);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('--brand-primary: #123ABC;', false);
});
