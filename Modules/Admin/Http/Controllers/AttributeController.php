<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Attribute\Actions\CreateAttribute;
use Modules\Attribute\Actions\UpdateAttribute;
use Modules\Attribute\AttributeTypeRegistry;
use Modules\Attribute\DataGrids\AttributeDataGrid;
use Modules\Attribute\Http\Requests\StoreAttributeRequest;
use Modules\Attribute\Http\Requests\UpdateAttributeRequest;
use Modules\Attribute\Models\Attribute;

class AttributeController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', Attribute::class);

        return AttributeDataGrid::make()->render('admin::attributes.index');
    }

    public function create(AttributeTypeRegistry $types): View
    {
        Gate::authorize('create', Attribute::class);

        return view('admin::attributes.create', [
            'types' => $types->typeOptions(),
        ]);
    }

    public function edit(Attribute $attribute, AttributeTypeRegistry $types): View
    {
        Gate::authorize('update', $attribute);

        return view('admin::attributes.edit', [
            'attribute' => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'code' => $attribute->code,
                'type' => $attribute->type,
                'required' => $attribute->required,
                'is_filterable' => $attribute->is_filterable,
                'settings' => $attribute->settings?->getArrayCopy() ?? [],
                'options' => $attribute->options->map(fn ($opt): array => [
                    'id' => $opt->id,
                    'code' => $opt->code,
                    'label' => $opt->label,
                    'sort_order' => $opt->sort_order,
                ])->all(),
            ],
            'types' => $types->frontendDefinitions(),
            'currencies' => config('currencies', []),
        ]);
    }

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

    public function filterable(): JsonResponse
    {
        Gate::authorize('viewAny', Attribute::class);

        $attributes = Attribute::query()
            ->filterable()
            ->with(['options'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $attributes->map(fn (Attribute $attr): array => [
                'id' => $attr->id,
                'name' => $attr->name,
                'code' => $attr->code,
                'type' => $attr->type,
                'required' => $attr->required,
                'is_filterable' => true,
                'options' => $attr->options->map(fn ($opt): array => [
                    'id' => $opt->id,
                    'code' => $opt->code,
                    'label' => $opt->label,
                    'sort_order' => $opt->sort_order,
                ])->all(),
            ]),
        ]);
    }

    public function show(Attribute $attribute, AttributeTypeRegistry $types): JsonResponse
    {
        Gate::authorize('update', $attribute);

        return response()->json([
            'data' => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'code' => $attribute->code,
                'type' => $attribute->type,
                'required' => $attribute->required,
                'is_filterable' => $attribute->is_filterable,
                'settings' => $attribute->settings?->getArrayCopy() ?? [],
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
