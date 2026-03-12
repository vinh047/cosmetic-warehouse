<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name'      => ['sometimes', 'required', 'string', 'max:255'],

            'email'     => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user)
            ],

            'password'  => ['nullable', 'string', Password::defaults()],

            'role'      => ['sometimes', 'required', 'string', 'in:admin,manager,staff'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
