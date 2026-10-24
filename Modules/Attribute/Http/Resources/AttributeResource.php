<?php

namespace Modules\Attribute\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Attribute\Models\Attribute;

/**
 * @mixin Attribute
 */
class AttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'required' => (bool) $this->required,
            'is_filterable' => (bool) $this->is_filterable,
            'settings' => $this->settings?->getArrayCopy() ?? (array) $this->settings,
            'options' => $this->relationLoaded('options')
                ? $this->options->map(fn ($option) => [
                    'id' => $option->id,
                    'code' => $option->code,
                    'label' => $option->label,
                    'sort_order' => $option->sort_order,
                ])->values()->all()
                : [],
        ];
    }
}
