<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // The @example values pre-fill the request in the API docs with the demo writer account.
        return [
            /** @example writer@technova.com */
            'email' => 'required|email',
            /** @example password */
            'password' => 'required',
        ];
    }
}
