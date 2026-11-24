<?php

/**
 * S1 (Sync v2): the admin sidebar is reduced to the Alice-aligned primary
 * headings, with every other page regrouped under a single collapsed "Gelişmiş".
 * No route was removed, so the moved links must still render.
 */
it('shows the Alice-aligned primary headings', function (): void {
    $response = $this->actingAs(actingAsAdmin())->get(route('dashboard'))->assertOk();

    foreach (['Kişiler', 'Görevler', 'Finans', 'Gelişmiş'] as $label) {
        $response->assertSee($label, false);
    }
});

it('drops the old primary group labels', function (): void {
    $response = $this->actingAs(actingAsAdmin())->get(route('dashboard'))->assertOk();

    foreach (['People &amp; Relationships', 'Personel', 'Systems'] as $label) {
        $response->assertDontSee($label, false);
    }
});

it('keeps regrouped pages reachable under Gelişmiş', function (): void {
    $response = $this->actingAs(actingAsAdmin())->get(route('dashboard'))->assertOk();

    // Pages moved to Gelişmiş are regrouped, never removed: their links stay in the DOM.
    foreach ([
        route('admin.posts.index'),
        route('admin.nodes.index'),
        route('admin.foods.index'),
        route('admin.settings'),
    ] as $url) {
        $response->assertSee($url, false);
    }

    // The Gelişmiş sub-headings are present.
    foreach (['Site', 'Bilgi', 'Beslenme', 'Sistem'] as $subheading) {
        $response->assertSee($subheading, false);
    }
});
