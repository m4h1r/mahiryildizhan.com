<?php

namespace App\Http\Resources\Alice;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Person
 */
class PersonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->fullName(),
            'name' => $this->name,
            'surname' => $this->surname,
            'second_surname' => $this->second_surname,
            'nickname' => $this->nickname,
            'birthday' => $this->birthday?->toDateString(),
            'birth_day' => $this->birth_day,
            'birth_month' => $this->birth_month,
            'birth_year' => $this->birth_year,
            'zodiac' => $this->zodiacName(),
            'deathday' => $this->deathday?->toDateString(),
            'birth_place' => $this->birth_place,
            'death_place' => $this->death_place,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'notes' => $this->notes,
            'address' => $this->address,
            'gender' => $this->whenLoaded('gender', fn () => $this->gender ? ['id' => $this->gender->id, 'name' => $this->gender->name] : null),
            'blood_type' => $this->whenLoaded('bloodType', function () {
                $bt = $this->resource->getRelation('bloodType');

                return $bt ? ['id' => $bt->id, 'name' => $bt->name] : null;
            }),
            'eye_color' => $this->whenLoaded('eyeColor', fn () => $this->eyeColor ? ['id' => $this->eyeColor->id, 'name' => $this->eyeColor->name] : null),
            'hair_color' => $this->whenLoaded('hairColor', fn () => $this->hairColor ? ['id' => $this->hairColor->id, 'name' => $this->hairColor->name] : null),
            'father_id' => $this->father_id,
            'mother_id' => $this->mother_id,
            'partner_id' => $this->partner_id,
            'relationship_scope' => $this->relationship_scope,
            'relationship_label' => $this->relationship_label,
            'relationship_bases' => $this->relationship_bases ?? [],
            'company' => $this->company,
            'job_title' => $this->job_title,
            'stakeholder_id' => $this->stakeholder_id,
            'character_summary' => $this->character_summary,
            'interests' => $this->interests ?? [],
            'hobbies' => $this->hobbies ?? [],
            'tendencies' => $this->tendencies ?? [],
            'met_at' => $this->met_at?->toDateString(),
            'met_how' => $this->met_how,
            'alice_slug' => $this->alice_slug,
            'picture_url' => $this->pictureUrl,
            'channels' => PersonChannelResource::collection($this->whenLoaded('channels')),
            'latest_assessment' => $this->whenLoaded('latestAssessment', fn () => $this->latestAssessment ? new PersonAssessmentResource($this->latestAssessment) : null),
            'faces' => PersonFaceResource::collection($this->whenLoaded('faces')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
