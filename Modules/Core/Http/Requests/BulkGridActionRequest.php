<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Core\Support\Grid\GridRegistry;

class BulkGridActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(GridRegistry $grids): array
    {
        $grid = $grids->find($this->route('grid'));
        $rules = [
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', 'min:1'],
            'changes' => ['sometimes', 'array', 'min:1'],
        ];

        if ($this->route('action') !== 'edit_selected') {
            return $rules;
        }

        foreach ($grid['bulk_edit_fields'] as $field => $definition) {
            $fieldRules = ['sometimes'];
            if ($definition['type'] === 'select') {
                if (($definition['options_source'] ?? null) === 'roles') {
                    $options = app($grid['provider'])->filterOptions()['role_id'] ?? [];
                    $fieldRules[] = Rule::in(array_column($options, 'value'));
                } else {
                    $fieldRules[] = Rule::in(array_keys($definition['options'] ?? []));
                }
            } else {
                $fieldRules = [...$fieldRules, 'string', 'max:255'];
            }
            if ($definition['nullable'] ?? false) {
                $fieldRules[] = 'nullable';
            }
            $rules["changes.$field"] = $fieldRules;
        }

        return $rules;
    }

    public function after(GridRegistry $grids): array
    {
        return [function (Validator $validator) use ($grids): void {
            $grid = $grids->find($this->route('grid'));
            $action = $this->route('action');

            if (! $grid['selectable'] || ! array_key_exists($action, $grid['bulk_actions']) || ! isset($grid['bulk_handler'])) {
                $validator->errors()->add('action', 'This bulk action is not enabled for this grid.');

                return;
            }

            if ($action === 'edit_selected') {
                $changes = $this->input('changes', []);
                if (! is_array($changes) || $changes === []) {
                    $validator->errors()->add('changes', 'Choose at least one field to change.');

                    return;
                }

                foreach (array_keys($changes) as $field) {
                    if (! array_key_exists($field, $grid['bulk_edit_fields'])) {
                        $validator->errors()->add("changes.$field", 'This field cannot be changed in bulk.');
                    }
                }
            } elseif ($this->has('changes')) {
                $validator->errors()->add('changes', 'Fields can only be submitted with Edit selected.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'ids.max' => 'Select no more than 100 records at a time.',
            'ids.*.distinct' => 'Selected record IDs must be unique.',
        ];
    }
}
