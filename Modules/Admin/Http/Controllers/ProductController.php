<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\Attribute\Models\Attribute;
use Modules\Core\Support\Connector\ConnectorRegistry;
use Modules\Product\DataGrids\ProductDataGrid;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use Modules\Product\Http\Resources\ProductResource;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAttributeValue;
use Modules\ProductTypes\Models\ProductType;

class ProductController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', Product::class);

        $families = ProductType::query()
            ->select(['id', 'name', 'code'])
            ->with([
                'variants:id,product_type_id,name,code,level_count',
                'variants.axes:id,product_type_variant_id,level,attribute_id,position',
                'variants.axes.attribute:id,name,code,type',
            ])
            ->orderBy('name')
            ->get();

        return ProductDataGrid::make()->render('admin::products.index', [
            'families' => $families,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        Gate::authorize('create', Product::class);

        return redirect()->route('admin.catalog.products.index', ['create' => 1]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        Gate::authorize('create', Product::class);

        $product = DB::transaction(function () use ($request): Product {
            $isConfigurable = $request->input('type') === 'configurable';

            return Product::create([
                'sku' => $request->input('sku'),
                'type' => $request->input('type'),
                'product_type_id' => $request->input('product_type_id'),
                'product_type_variant_id' => $isConfigurable ? $request->input('product_type_variant_id') : null,
                'status' => true,
            ]);
        });

        $product->load(['productType', 'productTypeVariant']);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => new ProductResource($product),
            'redirect_url' => route('admin.catalog.products.edit', $product),
        ], 201);
    }

    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);

        $product->load([
            'connectors',
            'productType.groups.attributeGroup:id,name,code',
            'productType.groups.groupAttributes.attribute.options',
            'productTypeVariant.axes.attribute.options',
            'productTypeVariant.variantAttributes.options',
            'variants.attributeValues.attribute',
            'attributeValues.attribute:id,name,code,type',
        ]);

        $axes = $product->productTypeVariant?->axes ?? collect();
        $l1AttrIds = $axes->where('level', 1)->pluck('attribute_id')->all();
        $l2AttrIds = $axes->where('level', 2)->pluck('attribute_id')->all();
        $variantAttrIds = $product->productTypeVariant?->variantAttributes->pluck('id')->all() ?? [];

        // Build structure of groups with their attributes and options
        $groupsData = [];
        if ($product->productType) {
            foreach ($product->productType->groups as $ptGroup) {
                $groupAttributes = [];
                foreach ($ptGroup->groupAttributes as $ga) {
                    if ($ga->attribute) {
                        $attr = $ga->attribute;
                        $scope = 'parent';
                        if (in_array($attr->id, $l1AttrIds, true)) {
                            $scope = 'axis_1';
                        } elseif (in_array($attr->id, $l2AttrIds, true)) {
                            $scope = 'axis_2';
                        } elseif (in_array($attr->id, $variantAttrIds, true)) {
                            $scope = 'variant';
                        }

                        $groupAttributes[] = [
                            'id' => $attr->id,
                            'name' => $attr->name,
                            'code' => $attr->code,
                            'type' => $attr->type,
                            'scope' => $scope,
                            'required' => (bool) $attr->required,
                            'is_unique' => (bool) $attr->is_unique,
                            'options' => $attr->options->map(fn ($opt) => [
                                'id' => $opt->id,
                                'admin_name' => $opt->admin_name,
                                'code' => $opt->code,
                            ])->values()->all(),
                        ];
                    }
                }

                // Skip groups that have no attributes assigned
                if (empty($groupAttributes)) {
                    continue;
                }

                $groupsData[] = [
                    'id' => $ptGroup->id,
                    'name' => $ptGroup->attributeGroup?->name ?? 'Attributes',
                    'code' => $ptGroup->attributeGroup?->code ?? 'attributes',
                    'position' => $ptGroup->position,
                    'attributes' => $groupAttributes,
                ];
            }
        }

        // Build initial attribute values map for parent product
        $valuesMap = [];
        foreach ($product->attributeValues as $valRecord) {
            $valuesMap[$valRecord->attribute_id] = $valRecord->getValue();
        }

        // Child variants data for configurable product
        $childVariantsData = [];
        if ($product->isConfigurable()) {
            foreach ($product->variants as $child) {
                $childValues = [];
                $childAxesValues = [];
                foreach ($child->attributeValues as $cav) {
                    $val = $cav->getValue();
                    $childValues[$cav->attribute_id] = $val;
                    if (in_array($cav->attribute_id, $l1AttrIds, true) || in_array($cav->attribute_id, $l2AttrIds, true)) {
                        $childAxesValues[$cav->attribute_id] = $val;
                    }
                }

                $childVariantsData[] = [
                    'id' => $child->id,
                    'sku' => $child->sku,
                    'status' => (bool) $child->status,
                    'axes_values' => $childAxesValues,
                    'values' => $childValues,
                ];
            }
        }

        // Prepare variation axes details
        $variantAxesData = $axes->map(fn ($axis) => [
            'id' => $axis->id,
            'level' => (int) $axis->level,
            'attribute_id' => $axis->attribute_id,
            'attribute' => $axis->attribute ? [
                'id' => $axis->attribute->id,
                'name' => $axis->attribute->name,
                'code' => $axis->attribute->code,
                'type' => $axis->attribute->type,
                'options' => $axis->attribute->options->map(fn ($opt) => [
                    'id' => $opt->id,
                    'admin_name' => $opt->admin_name,
                    'code' => $opt->code,
                ])->values()->all(),
            ] : null,
        ])->values()->all();

        // Prepare variant-level attributes details
        $variantAttributesData = ($product->productTypeVariant?->variantAttributes ?? collect())->map(fn ($attr) => [
            'id' => $attr->id,
            'name' => $attr->name,
            'code' => $attr->code,
            'type' => $attr->type,
            'required' => (bool) $attr->required,
            'is_unique' => (bool) $attr->is_unique,
            'options' => $attr->options->map(fn ($opt) => [
                'id' => $opt->id,
                'admin_name' => $opt->admin_name,
                'code' => $opt->code,
            ])->values()->all(),
        ])->values()->all();

        $connectorRegistry = app(ConnectorRegistry::class);
        $availableConnectors = $connectorRegistry->all();
        $initialConnectors = $product->connectors->pluck('connector_code')->values()->all();

        return view('admin::products.edit', [
            'product' => $product,
            'groupsData' => $groupsData,
            'initialValues' => $valuesMap,
            'childVariantsData' => $childVariantsData,
            'variantAxesData' => $variantAxesData,
            'variantAttributesData' => $variantAttributesData,
            'availableConnectors' => $availableConnectors,
            'initialConnectors' => $initialConnectors,
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        Gate::authorize('view', $product);

        $product->load([
            'productType',
            'productTypeVariant.axes.attribute',
            'productTypeVariant.variantAttributes',
            'variants.attributeValues.attribute',
            'attributeValues.attribute',
        ]);

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        Gate::authorize('update', $product);

        DB::transaction(function () use ($request, $product): void {
            if ($request->has('sku')) {
                $product->sku = $request->input('sku');
            }

            if ($request->has('status')) {
                $product->status = (bool) $request->input('status');
            }

            $product->save();

            // Sync assigned connectors / sales channels
            if ($request->has('connectors') && is_array($request->input('connectors'))) {
                $product->syncConnectors($request->input('connectors'));
            }

            // Save parent attribute values
            if ($request->has('values') && is_array($request->input('values'))) {
                $submittedValues = $request->input('values');
                $attributeIds = array_keys($submittedValues);

                $attributes = Attribute::query()
                    ->whereIn('id', $attributeIds)
                    ->get()
                    ->keyBy('id');

                foreach ($submittedValues as $attributeId => $value) {
                    $attribute = $attributes->get($attributeId);
                    if (! $attribute) {
                        continue;
                    }

                    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                        ProductAttributeValue::query()
                            ->where('product_id', $product->id)
                            ->where('attribute_id', $attributeId)
                            ->delete();

                        continue;
                    }

                    $attrVal = ProductAttributeValue::firstOrNew([
                        'product_id' => $product->id,
                        'attribute_id' => $attributeId,
                    ]);

                    $attrVal->setValue($value, $attribute->type);
                    $attrVal->save();
                }
            }

            // Synchronize connectors (independent modules)
            if ($request->has('connectors') && is_array($request->input('connectors'))) {
                $product->syncConnectors($request->input('connectors'));
            }

            // Synchronize child variants for configurable products
            if ($product->isConfigurable() && $request->has('variants') && is_array($request->input('variants'))) {
                $submittedVariants = $request->input('variants');
                $keptVariantIds = [];

                foreach ($submittedVariants as $vData) {
                    $vSku = trim((string) ($vData['sku'] ?? ''));
                    if ($vSku === '') {
                        continue;
                    }

                    $vId = ! empty($vData['id']) ? (int) $vData['id'] : null;

                    $childProduct = null;
                    if ($vId) {
                        $childProduct = Product::where('id', $vId)->where('parent_id', $product->id)->first();
                    }
                    if (! $childProduct) {
                        $childProduct = Product::where('sku', $vSku)->where('parent_id', $product->id)->first();
                    }
                    if (! $childProduct) {
                        $childProduct = new Product;
                        $childProduct->parent_id = $product->id;
                        $childProduct->product_type_id = $product->product_type_id;
                        $childProduct->product_type_variant_id = $product->product_type_variant_id;
                        $childProduct->type = 'simple';
                    }

                    $childProduct->sku = $vSku;
                    $childProduct->status = isset($vData['status']) ? (bool) $vData['status'] : true;
                    $childProduct->save();

                    $keptVariantIds[] = $childProduct->id;

                    // Combine axis values and variant values for the child
                    $allChildValues = [];
                    if (! empty($vData['axes_values']) && is_array($vData['axes_values'])) {
                        foreach ($vData['axes_values'] as $attrId => $val) {
                            $allChildValues[$attrId] = $val;
                        }
                    }
                    if (! empty($vData['values']) && is_array($vData['values'])) {
                        foreach ($vData['values'] as $attrId => $val) {
                            $allChildValues[$attrId] = $val;
                        }
                    }

                    if (! empty($allChildValues)) {
                        $childAttrIds = array_keys($allChildValues);
                        $childAttributes = Attribute::query()->whereIn('id', $childAttrIds)->get()->keyBy('id');

                        foreach ($allChildValues as $attrId => $val) {
                            $childAttr = $childAttributes->get($attrId);
                            if (! $childAttr) {
                                continue;
                            }

                            if ($val === null || $val === '' || (is_array($val) && empty($val))) {
                                ProductAttributeValue::query()
                                    ->where('product_id', $childProduct->id)
                                    ->where('attribute_id', $attrId)
                                    ->delete();

                                continue;
                            }

                            $attrVal = ProductAttributeValue::firstOrNew([
                                'product_id' => $childProduct->id,
                                'attribute_id' => $attrId,
                            ]);

                            $attrVal->setValue($val, $childAttr->type);
                            $attrVal->save();
                        }
                    }
                }

                // Delete child variants that were removed
                Product::query()
                    ->where('parent_id', $product->id)
                    ->whereNotIn('id', $keptVariantIds)
                    ->delete();
            }
        });

        $product->load([
            'connectors',
            'productType',
            'productTypeVariant.variantAttributes',
            'variants.attributeValues',
            'attributeValues',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    public function destroy(Product $product): JsonResponse|RedirectResponse
    {
        Gate::authorize('delete', $product);

        DB::transaction(function () use ($product): void {
            $product->delete();
        });

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ]);
        }

        return redirect()->route('admin.catalog.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
