<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Modules\Attribute\Models\Attribute;
use Modules\Attribute\Models\AttributeGroup;
use Modules\ProductTypes\DataGrids\ProductTypeDataGrid;
use Modules\ProductTypes\Http\Requests\StoreProductTypeRequest;
use Modules\ProductTypes\Http\Requests\UpdateProductTypeRequest;
use Modules\ProductTypes\Http\Resources\ProductTypeResource;
use Modules\ProductTypes\Models\ProductType;

class ProductTypeController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', ProductType::class);

        return ProductTypeDataGrid::make()->render('admin::product-types.index');
    }

    public function create(): View
    {
        Gate::authorize('create', ProductType::class);

        $allGroups = AttributeGroup::query()
            ->select(['id', 'name', 'code'])
            ->orderBy('name')
            ->get();

        $allAttributes = Attribute::query()
            ->select(['id', 'name', 'code', 'type', 'required', 'is_unique', 'is_filterable'])
            ->orderBy('name')
            ->get();

        $selectAttributes = Attribute::query()
            ->select(['id', 'name', 'code', 'type', 'required', 'is_unique', 'is_filterable'])
            ->whereIn('type', ['select', 'multiselect', 'checkbox', 'radio'])
            ->orderBy('name')
            ->get();

        return view('admin::product-types.create', [
            'allGroups' => $allGroups,
            'allAttributes' => $allAttributes,
            'selectAttributes' => $selectAttributes,
        ]);
    }

    public function edit(ProductType $product_type): View
    {
        Gate::authorize('update', $product_type);

        $product_type->load([
            'groups.attributeGroup:id,name,code',
            'groups.groupAttributes.attribute:id,name,code,type,required,is_unique,is_filterable',
            'variants.axes.attribute:id,name,code,type,required,is_unique,is_filterable',
            'variants.variantAttributes:id,name,code,type,required,is_filterable',
        ]);

        $allGroups = AttributeGroup::query()
            ->select(['id', 'name', 'code'])
            ->orderBy('name')
            ->get();

        $allAttributes = Attribute::query()
            ->select(['id', 'name', 'code', 'type', 'required', 'is_unique', 'is_filterable'])
            ->orderBy('name')
            ->get();

        $selectAttributes = Attribute::query()
            ->select(['id', 'name', 'code', 'type', 'required', 'is_unique', 'is_filterable'])
            ->whereIn('type', ['select', 'multiselect', 'checkbox', 'radio'])
            ->orderBy('name')
            ->get();

        return view('admin::product-types.edit', [
            'productType' => (new ProductTypeResource($product_type))->resolve(),
            'allGroups' => $allGroups,
            'allAttributes' => $allAttributes,
            'selectAttributes' => $selectAttributes,
        ]);
    }

    public function store(StoreProductTypeRequest $request): JsonResponse
    {
        Gate::authorize('create', ProductType::class);

        $productType = null;

        DB::transaction(function () use ($request, &$productType): void {
            $productType = ProductType::create([
                'name' => $request->validated('name'),
                'code' => $request->validated('code'),
            ]);

            $this->syncGroupsAndAttributes($productType, $request->validated('groups', []));
            $this->syncVariants($productType, $request->validated('variants', []));
        });

        $productType->load([
            'groups.attributeGroup:id,name,code',
            'groups.groupAttributes.attribute:id,name,code,type,required,is_unique,is_filterable',
            'variants.axes.attribute:id,name,code,type,required,is_unique,is_filterable',
            'variants.variantAttributes:id,name,code,type,required,is_filterable',
        ]);

        return (new ProductTypeResource($productType))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ProductType $product_type): JsonResponse
    {
        Gate::authorize('view', $product_type);

        $product_type->load([
            'groups.attributeGroup:id,name,code',
            'groups.groupAttributes.attribute:id,name,code,type,required,is_unique,is_filterable',
            'variants.axes.attribute:id,name,code,type,required,is_unique,is_filterable',
            'variants.variantAttributes:id,name,code,type,required,is_filterable',
        ]);

        return (new ProductTypeResource($product_type))->response();
    }

    public function update(UpdateProductTypeRequest $request, ProductType $product_type): JsonResponse
    {
        Gate::authorize('update', $product_type);

        DB::transaction(function () use ($request, $product_type): void {
            $product_type->update([
                'name' => $request->validated('name'),
                'code' => $request->validated('code'),
            ]);

            $this->syncGroupsAndAttributes($product_type, $request->validated('groups', []));
            $this->syncVariants($product_type, $request->validated('variants', []));
        });

        $product_type->load([
            'groups.attributeGroup:id,name,code',
            'groups.groupAttributes.attribute:id,name,code,type,required,is_unique,is_filterable',
            'variants.axes.attribute:id,name,code,type,required,is_unique,is_filterable',
            'variants.variantAttributes:id,name,code,type,required,is_filterable',
        ]);

        return (new ProductTypeResource($product_type))->response();
    }

    public function destroy(ProductType $product_type): JsonResponse
    {
        Gate::authorize('delete', $product_type);

        $product_type->delete();

        return response()->json([], 204);
    }

    /**
     * Synchronize groups and their assigned attributes for the given product type.
     *
     * @param  array<int, array<string, mixed>>  $groups
     */
    protected function syncGroupsAndAttributes(ProductType $productType, array $groups): void
    {
        $productType->groups()->delete();

        $seenGroupIds = [];
        $groupPos = 1;

        foreach ($groups as $groupData) {
            $groupId = (int) ($groupData['attribute_group_id'] ?? 0);
            if ($groupId <= 0 || isset($seenGroupIds[$groupId])) {
                continue;
            }
            $seenGroupIds[$groupId] = true;

            $group = $productType->groups()->create([
                'attribute_group_id' => $groupId,
                'position' => isset($groupData['position']) ? (int) $groupData['position'] : $groupPos++,
            ]);

            if (! empty($groupData['attributes']) && is_array($groupData['attributes'])) {
                $seenAttrIds = [];
                $attrPos = 1;

                foreach ($groupData['attributes'] as $attrData) {
                    $attrId = (int) ($attrData['id'] ?? 0);
                    if ($attrId <= 0 || isset($seenAttrIds[$attrId])) {
                        continue;
                    }
                    $seenAttrIds[$attrId] = true;

                    $group->groupAttributes()->create([
                        'attribute_id' => $attrId,
                        'position' => isset($attrData['position']) ? (int) $attrData['position'] : $attrPos++,
                    ]);
                }
            }
        }
    }

    /**
     * Synchronize 1-level and 2-level variant types for configurable products.
     *
     * @param  array<int, array<string, mixed>>  $variants
     */
    protected function syncVariants(ProductType $productType, array $variants): void
    {
        $productType->variants()->delete();

        $seenCodes = [];
        foreach ($variants as $vData) {
            $rawCode = trim((string) ($vData['code'] ?? ''));
            $rawName = trim((string) ($vData['name'] ?? ''));

            if ($rawCode === '' && $rawName !== '') {
                $rawCode = Str::slug($rawName, '_');
            }

            if ($rawCode === '' || isset($seenCodes[$rawCode])) {
                continue;
            }
            $seenCodes[$rawCode] = true;

            $levelCount = (int) ($vData['level_count'] ?? 1);
            $levelCount = in_array($levelCount, [1, 2], true) ? $levelCount : 1;

            $variant = $productType->variants()->create([
                'name' => $rawName !== '' ? $rawName : $rawCode,
                'code' => $rawCode,
                'level_count' => $levelCount,
            ]);

            // Level 1 attributes (Variant Group / Direct Variant axes)
            if (! empty($vData['level_1_attributes']) && is_array($vData['level_1_attributes'])) {
                $seenAttrs = [];
                $pos1 = 1;
                foreach ($vData['level_1_attributes'] as $attrData) {
                    $attrId = (int) ($attrData['id'] ?? 0);
                    if ($attrId <= 0 || isset($seenAttrs[$attrId])) {
                        continue;
                    }
                    $seenAttrs[$attrId] = true;

                    $variant->axes()->create([
                        'level' => 1,
                        'attribute_id' => $attrId,
                        'position' => isset($attrData['position']) ? (int) $attrData['position'] : $pos1++,
                    ]);
                }
            }

            // Level 2 attributes (Final Variant axes if 2-level)
            if ($levelCount === 2 && ! empty($vData['level_2_attributes']) && is_array($vData['level_2_attributes'])) {
                $seenAttrs2 = [];
                $pos2 = 1;
                foreach ($vData['level_2_attributes'] as $attrData) {
                    $attrId = (int) ($attrData['id'] ?? 0);
                    if ($attrId <= 0 || isset($seenAttrs2[$attrId])) {
                        continue;
                    }
                    $seenAttrs2[$attrId] = true;

                    $variant->axes()->create([
                        'level' => 2,
                        'attribute_id' => $attrId,
                        'position' => isset($attrData['position']) ? (int) $attrData['position'] : $pos2++,
                    ]);
                }
            }

            // Variant-level attributes (fillable on child variants)
            $variantAttrIds = [];
            if (! empty($vData['variant_attributes']) && is_array($vData['variant_attributes'])) {
                foreach ($vData['variant_attributes'] as $attrData) {
                    $attrId = (int) ($attrData['id'] ?? 0);
                    if ($attrId > 0 && ! in_array($attrId, $variantAttrIds, true)) {
                        $variantAttrIds[] = $attrId;
                    }
                }
            }
            $variant->variantAttributes()->sync($variantAttrIds);
        }
    }
}
