<?php

namespace App\Http\Requests\Api;

class OtpSendRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}
