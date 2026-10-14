<?php

namespace Modules\Catalog\Actions;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Catalog\Attributes\AttributeTypeRegistry;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Models\AttributeOption;

class UpdateAttribute
{
    public function __construct(private readonly AttributeTypeRegistry $types) {}

    /** @param array<string, mixed> $data */
    public function execute(Attribute $record, array $data): Attribute
    {
        try {
            return DB::transaction(function () use ($record, $data): Attribute {
                $attribute = Attribute::query()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();
                $settings = array_key_exists('settings', $data)
                    ? $this->types->validateSettings($attribute->type, $data['settings'])
                    : (array) $attribute->settings;

                $isFilterable = array_key_exists('is_filterable', $data)
                    ? (bool) $data['is_filterable']
                    : (array_key_exists('is_filter', $data) ? (bool) $data['is_filter'] : $attribute->is_filterable);

                $attribute->fill([
                    'name' => $data['name'],
                    'required' => $data['required'],
                    'is_filterable' => $isFilterable,
                    'settings' => $settings,
                ])->save();

                if (array_key_exists('options', $data)) {
                    $this->syncOptions($attribute, $data['options']);
                }

                return $attribute->load('options');
            });
        } catch (QueryException $exception) {
            if (in_array($exception->errorInfo[0] ?? null, ['23000', '23505'], true)) {
                throw ValidationException::withMessages(['options' => 'Option codes must be unique within this attribute.']);
            }

            throw $exception;
        }
    }

    /** @param list<array{id?: int|null, code: string, label: string, sort_order: int}> $options */
    private function syncOptions(Attribute $attribute, array $options): void
    {
        if (! in_array($attribute->type, ['select', 'multiselect', 'radio', 'checkbox'], true)) {
            throw ValidationException::withMessages(['options' => 'Options are only available for Select, Multiselect, Radio and Checkbox attributes.']);
        }

        $existing = $attribute->options()->lockForUpdate()->get()->keyBy('id');
        $submittedIds = [];
        foreach ($options as $index => $option) {
            if (! empty($option['id'])) {
                $id = (int) $option['id'];
                if (! $existing->has($id)) {
                    throw ValidationException::withMessages(["options.$index.id" => 'This option does not belong to the selected attribute.']);
                }
                if ($existing[$id]->code !== $option['code']) {
                    throw ValidationException::withMessages(["options.$index.code" => 'Saved option codes cannot be changed.']);
                }
                $submittedIds[] = $id;
            }
        }

        $attribute->options()->whereNotIn('id', $submittedIds)->delete();

        foreach ($options as $option) {
            if (! empty($option['id'])) {
                /** @var AttributeOption $savedOption */
                $savedOption = $existing[(int) $option['id']];
                $savedOption->fill([
                    'label' => $option['label'],
                    'sort_order' => $option['sort_order'],
                ])->save();

                continue;
            }

            $attribute->options()->create([
                'code' => $option['code'],
                'label' => $option['label'],
                'sort_order' => $option['sort_order'],
            ]);
        }
    }
}
