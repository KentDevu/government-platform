<?php

namespace App\Http\Requests\Api;

class OtpVerifyRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'string', 'size:6'],
        ];
    }
}
