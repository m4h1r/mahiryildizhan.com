<?php

namespace App\Http\Resources\Alice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'birthday' => $this->birthday?->toDateString(),
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
            'picture_url' => $this->pictureUrl,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
