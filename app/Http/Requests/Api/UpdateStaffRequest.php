<?php

namespace App\Http\Requests\Api;

class UpdateStaffRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
