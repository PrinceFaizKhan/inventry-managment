<?php

namespace App\Http\Requests;

use App\Support\Permissions\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(PermissionRegistry $permissions): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')],
            'access' => ['required', Rule::in(['administration', 'custom'])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($permissions->keys())],
        ];
    }
}
