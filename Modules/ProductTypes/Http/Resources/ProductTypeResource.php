<?php

namespace Modules\ProductTypes\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProductTypes\Models\ProductType;

/**
 * @mixin ProductType
 */
class ProductTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $groups = [];

        if ($this->relationLoaded('groups')) {
            foreach ($this->groups as $group) {
                $groupAttributes = [];
                if ($group->relationLoaded('groupAttributes')) {
                    foreach ($group->groupAttributes as $ga) {
                        $attr = $ga->attribute;
                        if ($attr) {
                            $groupAttributes[] = [
                                'id' => $attr->id,
                                'mapping_id' => $ga->id,
                                'name' => $attr->name,
                                'code' => $attr->code,
                                'type' => $attr->type,
                                'required' => (bool) $attr->required,
                                'is_unique' => (bool) $attr->is_unique,
                                'is_filterable' => (bool) $attr->is_filterable,
                                'position' => $ga->position,
                            ];
                        }
                    }
                }

                $ag = $group->attributeGroup;
                $groups[] = [
                    'id' => $group->id,
                    'attribute_group_id' => $group->attribute_group_id,
                    'name' => $ag?->name ?? 'Group',
                    'code' => $ag?->code ?? 'group',
                    'position' => $group->position,
                    'attributes' => $groupAttributes,
                ];
            }
        }

        $totalAttributesCount = 0;
        foreach ($groups as $g) {
            $totalAttributesCount += count($g['attributes']);
        }

        $variants = [];
        if ($this->relationLoaded('variants')) {
            foreach ($this->variants as $variant) {
                $level1 = [];
                $level2 = [];
                if ($variant->relationLoaded('axes')) {
                    foreach ($variant->axes as $axis) {
                        $attr = $axis->attribute;
                        if ($attr) {
                            $item = [
                                'id' => $attr->id,
                                'name' => $attr->name,
                                'code' => $attr->code,
                                'type' => $attr->type,
                                'position' => $axis->position,
                            ];
                            if ((int) $axis->level === 1) {
                                $level1[] = $item;
                            } else {
                                $level2[] = $item;
                            }
                        }
                    }
                }

                $variantAttributes = [];
                if ($variant->relationLoaded('variantAttributes')) {
                    foreach ($variant->variantAttributes as $attr) {
                        $variantAttributes[] = [
                            'id' => $attr->id,
                            'name' => $attr->name,
                            'code' => $attr->code,
                            'type' => $attr->type,
                        ];
                    }
                }

                $variants[] = [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'code' => $variant->code,
                    'level_count' => (int) $variant->level_count,
                    'level_1_attributes' => $level1,
                    'level_2_attributes' => $level2,
                    'variant_attributes' => $variantAttributes,
                ];
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'groups_count' => count($groups),
            'attributes_count' => $totalAttributesCount,
            'variants_count' => count($variants),
            'groups' => $groups,
            'variants' => $variants,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
