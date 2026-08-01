<?php

namespace App\Http\Resources\Alice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StakeholderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'name' => $this->name,
            'surname' => $this->surname,
            'vkn_tckn' => $this->vkn_tckn,
            'tax_office_id' => $this->tax_office_id,
            'tax_office_name' => $this->taxOffice?->name,
            'company_type' => $this->company_type,
            'sector_id' => $this->sector_id,
            'sector' => $this->sector?->name,
            'city' => $this->city,
            'country' => $this->country,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'note' => $this->note,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
