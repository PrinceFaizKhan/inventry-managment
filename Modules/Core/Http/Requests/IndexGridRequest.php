<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Core\Support\Grid\GridDataProvider;
use Modules\Core\Support\Grid\GridRegistry;

class IndexGridRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(GridRegistry $grids): array
    {
        $grid = $grids->find($this->route('grid'));

        return [
            'search' => ['nullable', 'string', 'max:120'],
            'filter' => ['nullable', 'array'],
            'sort' => ['nullable', 'string', Rule::in(array_keys($grid['sorts']))],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in($grid['page_sizes'])],
        ];
    }

    public function after(GridRegistry $grids): array
    {
        return [function (Validator $validator) use ($grids): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $grid = $grids->find($this->route('grid'));
            $filters = $this->input('filter', []);

            foreach ($filters as $key => $value) {
                if (! array_key_exists($key, $grid['filters'])) {
                    $validator->errors()->add("filter.$key", 'This filter is not available.');

                    continue;
                }

                /** @var GridDataProvider $provider */
                $provider = app($grid['provider']);
                if (! $provider->acceptsFilterValue($key, $value)) {
                    $validator->errors()->add("filter.$key", 'The selected filter value is invalid.');
                }
            }
        }];
    }
}
