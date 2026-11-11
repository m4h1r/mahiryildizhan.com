<?php

namespace App\Http\Requests\Alice;

use App\Support\Crm\CrmRules;

class PersonAssessmentRequest extends AliceRequest
{
    public function rules(): array
    {
        return CrmRules::assessment($this->isMethod('PATCH') || $this->isMethod('PUT'));
    }
}
