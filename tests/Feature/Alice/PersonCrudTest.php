<?php

namespace Tests\Feature\Alice;

use App\Models\Person;

class PersonCrudTest extends AliceTestCase
{
    public function test_index_returns_paginated_people(): void
    {
        Person::create(['name' => 'Yusuf', 'surname' => 'Naltekin', 'notes' => '']);

        $this->aliceGet('people')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_store_creates_person(): void
    {
        $this->alicePost('people', [
            'name' => 'Yusuf',
            'surname' => 'Naltekin',
            'mobile' => '+905051234567',
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.full_name', 'Yusuf Naltekin');

        $this->assertDatabaseHas('people', ['name' => 'Yusuf', 'surname' => 'Naltekin']);
    }

    public function test_store_validation_fails_without_name(): void
    {
        $this->alicePost('people', ['surname' => 'Naltekin'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_show_returns_person(): void
    {
        $person = Person::create(['name' => 'Ali', 'surname' => 'Veli', 'notes' => '']);

        $this->aliceGet("people/{$person->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $person->id);
    }

    public function test_show_returns_404(): void
    {
        $this->aliceGet('people/99999')->assertStatus(404);
    }

    public function test_update_patches_person(): void
    {
        $person = Person::create(['name' => 'Ali', 'surname' => 'Veli', 'notes' => '']);

        $this->alicePatch("people/{$person->id}", ['mobile' => '+905059876543'])
            ->assertStatus(200)
            ->assertJsonPath('data.mobile', '+905059876543');
    }

    public function test_destroy_soft_deletes_person(): void
    {
        $person = Person::create(['name' => 'Ali', 'surname' => 'Veli', 'notes' => '']);

        $this->aliceDelete("people/{$person->id}")->assertStatus(200);
        $this->assertSoftDeleted('people', ['id' => $person->id]);
    }

    public function test_search_by_name(): void
    {
        Person::create(['name' => 'Mehmet', 'surname' => 'Yılmaz', 'notes' => '']);
        Person::create(['name' => 'Ahmet', 'surname' => 'Kaya', 'notes' => '']);

        $this->aliceGet('people', ['q' => 'Mehmet'])
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }
}
