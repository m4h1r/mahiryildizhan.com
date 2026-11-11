<?php

namespace App\Http\Requests\Alice;

use App\Support\Crm\CrmRules;

class PersonChannelRequest extends AliceRequest
{
    public function rules(): array
    {
        $rules = CrmRules::channel();

        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            $rules['type'][0] = 'sometimes';
            $rules['value'][0] = 'sometimes';
        }

        return $rules;
    }
}
