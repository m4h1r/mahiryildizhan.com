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

it('never renders a form nested inside another form', function (): void {
    // Regression: the time-ranges tab's per-day forms were once nested inside
    // the main settings form. Browsers silently drop the inner <form> open
    // tag and close the outer form on the first stray </form>, which broke
    // both the "Save Settings" button (ended up outside any form) and the
    // per-day "Kaydet" buttons (submitted to the wrong action).
    $html = $this->actingAs(actingAsAdmin())->get(route('admin.settings'))->getContent();

    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    foreach ($dom->getElementsByTagName('form') as $form) {
        $ancestor = $form->parentNode;
        while ($ancestor) {
            expect($ancestor->nodeName)->not->toBe('form');
            $ancestor = $ancestor->parentNode;
        }
    }
});

it('keeps the brand color input and the save button in the same settings form', function (): void {
    $html = $this->actingAs(actingAsAdmin())->get(route('admin.settings'))->getContent();

    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    $settingsForm = null;
    foreach ($dom->getElementsByTagName('form') as $form) {
        if (str_contains((string) $form->getAttribute('action'), route('admin.settings.update'))) {
            $settingsForm = $form;
        }
    }

    expect($settingsForm)->not->toBeNull();
    $innerHtml = $dom->saveHTML($settingsForm);
    expect($innerHtml)->toContain('brand_primary')
        ->and(substr_count((string) $innerHtml, 'type="submit"'))->toBeGreaterThan(0);
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
