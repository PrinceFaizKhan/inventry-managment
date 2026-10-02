<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Modules\Catalog\Actions\CreateAttribute;
use Modules\Catalog\Actions\UpdateAttribute;
use Modules\Catalog\Attributes\AttributeTypeRegistry;
use Modules\Catalog\Http\Requests\StoreAttributeRequest;
use Modules\Catalog\Http\Requests\UpdateAttributeRequest;
use Modules\Catalog\Models\Attribute;

class AttributeController extends Controller
{
    public function types(AttributeTypeRegistry $types): JsonResponse
    {
        Gate::authorize('create', Attribute::class);

        return response()->json(['data' => $types->typeOptions()]);
    }

    public function store(StoreAttributeRequest $request, CreateAttribute $create): JsonResponse
    {
        Gate::authorize('create', Attribute::class);

        return response()->json(['data' => $create->execute($request->validated())], 201);
    }

    public function show(Attribute $attribute, AttributeTypeRegistry $types): JsonResponse
    {
        Gate::authorize('update', $attribute);

        $settings = $attribute->settings;

        return response()->json([
            'data' => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'code' => $attribute->code,
                'type' => $attribute->type,
                'required' => $attribute->required,
                'settings' => $settings?->getArrayCopy() ?? [],
                'options' => $attribute->options->map(fn ($option): array => [
                    'id' => $option->id,
                    'code' => $option->code,
                    'label' => $option->label,
                    'sort_order' => $option->sort_order,
                ])->all(),
            ],
            'types' => $types->frontendDefinitions(),
            'currencies' => config('currencies', []),
        ]);
    }

    public function update(UpdateAttributeRequest $request, Attribute $attribute, UpdateAttribute $update): JsonResponse
    {
        Gate::authorize('update', $attribute);

        return response()->json(['data' => $update->execute($attribute, $request->validated())]);
    }

    public function destroy(Attribute $attribute): JsonResponse
    {
        Gate::authorize('delete', $attribute);
        $attribute->delete();

        return response()->json([], 204);
    }
}
