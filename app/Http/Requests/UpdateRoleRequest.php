<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $organizationId = session('organization_id');

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('roles', 'name')
                    ->where('organization_id', $organizationId)
                    ->ignore($this->route('role')),
            ],
            'code' => [
                'nullable', 'string', 'max:100',
                Rule::unique('roles', 'code')
                    ->where('organization_id', $organizationId)
                    ->ignore($this->route('role')),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }
}
