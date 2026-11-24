<?php

namespace Tests\Feature\Alice;

use App\Http\Resources\Alice\PersonResource;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Regression for the production 500 on GET /people with legacy rows whose
 * `birthday` carries a time-of-day on a zodiac cusp day (20 Apr, 23 Oct,
 * 21 Dec, 20 Jan ...). The zodiac library matches sign periods with the cusp
 * boundary at midnight, so a time component matched no sign and threw.
 */
class PersonZodiacRegressionTest extends AliceTestCase
{
    /**
     * Write a legacy-style row directly, bypassing the model's saving hook that
     * would otherwise derive birth_day/month/year and hide the Carbon::parse path.
     */
    private function legacyPerson(string $rawBirthday): Person
    {
        $person = Person::create(['name' => 'Cusp', 'surname' => 'Case', 'notes' => '']);

        DB::table('people')->where('id', $person->id)->update([
            'birthday' => $rawBirthday,
            'birth_day' => null,
            'birth_month' => null,
            'birth_year' => null,
        ]);

        return $person->fresh();
    }

    public function test_index_does_not_500_on_cusp_birthday_with_time(): void
    {
        $this->legacyPerson('2000-12-21 22:38:48');

        $this->aliceGet('people', ['per_page' => 1, 'with_trashed' => 1])
            ->assertStatus(200)
            // Normalised to the start of the day, 21 December resolves to Sagittarius.
            ->assertJsonPath('data.0.zodiac', 'Yay');
    }

    public function test_resource_degrades_invalid_date_to_null_zodiac(): void
    {
        $person = $this->legacyPerson('0000-00-00 00:00:00');

        $arr = (new PersonResource($person))->toArray(Request::create('/'));

        $this->assertNull($arr['zodiac']);
    }

    public function test_cusp_birthday_with_time_resolves_sign_without_throwing(): void
    {
        $person = $this->legacyPerson('2000-04-20 23:59:59');

        // 20 April at the start of the day is Aries (Koç), not an exception.
        $this->assertSame('Koç', $person->zodiacName());
        $this->assertSame('♈︎', $person->zodiacSymbol());
    }
}
