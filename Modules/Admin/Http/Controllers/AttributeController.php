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
use Modules\Attribute\Http\Resources\AttributeResource;
use Modules\Attribute\Models\Attribute;

class AttributeController extends Controller
{
    public function index(Request $request, AttributeTypeRegistry $types): View|JsonResponse
    {
        Gate::authorize('viewAny', Attribute::class);

        return AttributeDataGrid::make()->render('admin::attributes.index', [
            'types' => $types->typeOptions(),
        ]);
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
            'attribute' => (new AttributeResource($attribute->loadMissing('options')))->resolve(),
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

        $attribute = $create->execute($request->validated());

        return (new AttributeResource($attribute->loadMissing('options')))
            ->response()
            ->setStatusCode(201);
    }

    public function filterable(): JsonResponse
    {
        Gate::authorize('viewAny', Attribute::class);

        $attributes = Attribute::query()
            ->filterable()
            ->with(['options'])
            ->orderBy('name')
            ->get();

        return AttributeResource::collection($attributes)->response();
    }

    public function show(Attribute $attribute, AttributeTypeRegistry $types): JsonResponse
    {
        Gate::authorize('update', $attribute);

        return response()->json([
            'data' => (new AttributeResource($attribute->loadMissing('options')))->resolve(),
            'types' => $types->frontendDefinitions(),
            'currencies' => config('currencies', []),
        ]);
    }

    public function update(UpdateAttributeRequest $request, Attribute $attribute, UpdateAttribute $update): JsonResponse
    {
        Gate::authorize('update', $attribute);

        $attribute = $update->execute($attribute, $request->validated());

        return (new AttributeResource($attribute))->response();
    }

    public function destroy(Attribute $attribute): JsonResponse
    {
        Gate::authorize('delete', $attribute);
        $attribute->delete();

        return response()->json([], 204);
    }
}
