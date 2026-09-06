<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'policy' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Почта уже занята',
            'policy.accepted' => 'Необходимо согласиться с политикой конфиденциальности',
            'password.confirmed' => 'Пароли не совпадают',
        ];
    }
}
