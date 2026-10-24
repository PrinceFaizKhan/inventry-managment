<?php

namespace Modules\Attribute\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreAttributeGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        if (empty($this->code) && filled($this->name)) {
            $this->merge([
                'code' => Str::slug($this->name, '_'),
            ]);
        } elseif (is_string($this->code)) {
            $this->merge([
                'code' => Str::lower(trim($this->code)),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:120', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:attribute_groups,code'],
        ];
    }
}
