<?php

namespace Tests\Feature\Admin;

use App\Models\Interaction;
use App\Models\InteractionType;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WomenInCircleTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_already_in_first_circle_is_excluded_from_candidates(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Controller hardcodes interaction_type_id = 5 and Person::find(1) as the
        // center person, so pad the id sequences to match.
        Person::query()->create(['name' => 'Center', 'surname' => 'Person']); // id 1
        InteractionType::query()->create(['name' => 'filler-1']);
        InteractionType::query()->create(['name' => 'filler-2']);
        InteractionType::query()->create(['name' => 'filler-3']);
        InteractionType::query()->create(['name' => 'filler-4']);
        $circleType = InteractionType::query()->create(['name' => 'circle']); // id 5
        $this->assertSame(5, $circleType->id);

        $inCircle = Person::query()->create(['name' => 'Ayşe', 'surname' => 'Yıldız', 'notes' => '#wiccandidate']);
        Interaction::query()->create([
            'person_id' => $inCircle->id,
            'interaction_type_id' => $circleType->id,
            'date' => now(),
        ]);

        $onlyCandidate = Person::query()->create(['name' => 'Zeynep', 'surname' => 'Ak', 'notes' => '#wiccandidate']);

        $response = $this->actingAs($admin)->get(route('admin.interactions.women-in-circle'));

        $response->assertOk();
        $people = $response->viewData('people');
        $candidates = $response->viewData('candidates');

        $this->assertTrue($people->contains('id', $inCircle->id));
        $this->assertFalse(
            $candidates->contains('id', $inCircle->id),
            'Person already in the first circle must not appear among candidates.'
        );
        $this->assertTrue($candidates->contains('id', $onlyCandidate->id));
    }
}
