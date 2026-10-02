<?php

namespace Modules\Catalog\Attributes;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AttributeTypeRegistry
{
    /** @return array<string, array{label: string, settings: array<string, array<string, mixed>>}> */
    public function definitions(): array
    {
        return require base_path('Modules/Catalog/Config/attribute_types.php');
    }

    /** @return list<string> */
    public function keys(): array
    {
        return array_keys($this->definitions());
    }

    /** @return array<string, string> */
    public function typeOptions(): array
    {
        return array_map(
            fn (array $definition): string => $definition['label'],
            $this->definitions(),
        );
    }

    /** @return array<string, array{label: string, settings: array<string, mixed>}> */
    public function frontendDefinitions(): array
    {
        $definitions = $this->definitions();
        $currencies = config('currencies', []);

        foreach ($definitions as &$definition) {
            foreach ($definition['settings'] as &$field) {
                if ($field['type'] === 'currency') {
                    $field['type'] = 'select';
                    $field['options'] = $currencies;
                }
            }
            unset($field);
        }
        unset($definition);

        return $definitions;
    }

    /** @return array<string, mixed> */
    public function defaults(string $type): array
    {
        $definition = $this->definition($type);
        $defaults = [];

        foreach ($definition['settings'] as $key => $field) {
            if (array_key_exists('default', $field)) {
                $defaults[$key] = $field['default'];
            }
        }

        return $defaults;
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public function validateSettings(string $type, array $settings): array
    {
        $definition = $this->definition($type);
        $fields = $definition['settings'];
        $errors = [];
        $validationMode = $settings['validation'] ?? $fields['validation']['default'] ?? null;

        foreach (array_keys($settings) as $key) {
            if (! array_key_exists($key, $fields)) {
                $errors["settings.$key"][] = 'This setting is not available for the selected attribute type.';
            }
        }

        $rules = [];
        foreach ($fields as $key => $field) {
            $value = $settings[$key] ?? null;
            if (isset($field['applies_to']) && ! in_array($validationMode, $field['applies_to'], true) && $value !== null && $value !== '') {
                $errors["settings.$key"][] = 'This setting does not apply to the selected validation.';

                continue;
            }

            $fieldRules = [];
            switch ($field['type']) {
                case 'select':
                    $fieldRules = ['required', 'string', Rule::in(array_keys($field['options']))];
                    break;
                case 'currency':
                    $fieldRules = ['required', 'string', Rule::in(array_keys(config('currencies', [])))];
                    break;
                case 'multi_select':
                    $fieldRules = ['required', 'array', 'min:1', 'max:'.count($field['options'])];
                    $rules["settings.$key.*"] = ['required', 'string', Rule::in(array_keys($field['options']))];
                    break;
                case 'boolean':
                    $fieldRules = ['required', 'boolean'];
                    break;
                case 'integer':
                    $fieldRules = ['sometimes', 'nullable', 'integer'];
                    if (isset($field['min'])) {
                        $fieldRules[] = 'min:'.$field['min'];
                    }
                    if (isset($field['max'])) {
                        $fieldRules[] = 'max:'.$field['max'];
                    }
                    break;
                case 'decimal':
                    $fieldRules = ['sometimes', 'nullable', 'string', 'regex:/^-?\\d{1,12}(?:\\.\\d{1,8})?$/'];
                    break;
            }

            $rules["settings.$key"] = $fieldRules;
        }

        $validator = Validator::make(['settings' => $settings], $rules);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $messages) {
                $errors[$key] = [...($errors[$key] ?? []), ...$messages];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $normalized = $this->defaults($type);
        foreach ($fields as $key => $field) {
            if (! array_key_exists($key, $settings)) {
                continue;
            }

            if ($field['type'] === 'integer' && $settings[$key] !== null) {
                $normalized[$key] = (int) $settings[$key];
            } else {
                $normalized[$key] = $settings[$key];
            }
        }

        $this->validateRanges($type, $normalized);

        return $normalized;
    }

    /** @param array<string, mixed> $settings */
    private function validateRanges(string $type, array $settings): void
    {
        $ranges = [['min_length', 'max_length'], ['min_value', 'max_value']];
        foreach ($ranges as [$minimum, $maximum]) {
            if (($settings[$minimum] ?? null) !== null && ($settings[$maximum] ?? null) !== null) {
                $comparison = $type === 'textarea' || (str_ends_with($minimum, 'length'))
                    ? ((int) $settings[$minimum] <=> (int) $settings[$maximum])
                    : $this->compareDecimals((string) $settings[$minimum], (string) $settings[$maximum]);
                if ($comparison > 0) {
                    throw ValidationException::withMessages(["settings.$maximum" => 'The maximum must be greater than or equal to the minimum.']);
                }
            }
        }

        $precision = $settings['decimal_precision'] ?? null;
        if (is_int($precision)) {
            foreach (['min_value', 'max_value'] as $key) {
                $value = $settings[$key] ?? null;
                if (is_string($value) && str_contains($value, '.') && strlen(rtrim(substr(strrchr($value, '.') ?: '', 1), '0')) > $precision) {
                    throw ValidationException::withMessages(["settings.$key" => 'This value exceeds the configured decimal precision.']);
                }
            }
        }

        if ($type === 'text' && ($settings['validation'] ?? null) === 'numeric') {
            foreach (['min_value', 'max_value'] as $key) {
                if (isset($settings[$key]) && str_contains((string) $settings[$key], '.')) {
                    throw ValidationException::withMessages(["settings.$key" => 'Numeric limits must be whole numbers.']);
                }
            }
        }
    }

    private function compareDecimals(string $left, string $right): int
    {
        $leftNegative = str_starts_with($left, '-');
        $rightNegative = str_starts_with($right, '-');
        $left = ltrim($left, '-');
        $right = ltrim($right, '-');
        [$leftWhole, $leftFraction] = array_pad(explode('.', $left, 2), 2, '');
        [$rightWhole, $rightFraction] = array_pad(explode('.', $right, 2), 2, '');
        $leftWhole = ltrim($leftWhole, '0') ?: '0';
        $rightWhole = ltrim($rightWhole, '0') ?: '0';

        $comparison = strlen($leftWhole) <=> strlen($rightWhole);
        if ($comparison === 0) {
            $comparison = strcmp($leftWhole, $rightWhole) <=> 0;
        }
        if ($comparison === 0) {
            $scale = max(strlen($leftFraction), strlen($rightFraction));
            $comparison = strcmp(str_pad($leftFraction, $scale, '0'), str_pad($rightFraction, $scale, '0')) <=> 0;
        }

        if ($leftNegative !== $rightNegative) {
            return $leftNegative ? -1 : 1;
        }

        return $leftNegative ? -$comparison : $comparison;
    }

    /** @return array{label: string, settings: array<string, array<string, mixed>>} */
    private function definition(string $type): array
    {
        $definition = $this->definitions()[$type] ?? null;
        if (! is_array($definition)) {
            throw ValidationException::withMessages(['type' => 'Choose a supported attribute type.']);
        }

        return $definition;
    }
}
