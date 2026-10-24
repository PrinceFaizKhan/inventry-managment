<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Attribute\DataGrids\AttributeGroupDataGrid;
use Modules\Attribute\Http\Requests\StoreAttributeGroupRequest;
use Modules\Attribute\Http\Requests\UpdateAttributeGroupRequest;
use Modules\Attribute\Http\Resources\AttributeGroupResource;
use Modules\Attribute\Models\AttributeGroup;

class AttributeGroupController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', AttributeGroup::class);

        return AttributeGroupDataGrid::make()->render('admin::attributes.groups.index');
    }

    public function create(): View
    {
        Gate::authorize('create', AttributeGroup::class);

        return view('admin::attributes.groups.create');
    }

    public function edit(AttributeGroup $attribute_group): View
    {
        Gate::authorize('update', $attribute_group);

        return view('admin::attributes.groups.edit', [
            'group' => (new AttributeGroupResource($attribute_group))->resolve(),
        ]);
    }

    public function store(StoreAttributeGroupRequest $request): JsonResponse
    {
        Gate::authorize('create', AttributeGroup::class);

        $group = AttributeGroup::create($request->validated());

        return (new AttributeGroupResource($group))
            ->response()
            ->setStatusCode(201);
    }

    public function show(AttributeGroup $attribute_group): JsonResponse
    {
        Gate::authorize('view', $attribute_group);

        return (new AttributeGroupResource($attribute_group))->response();
    }

    public function update(UpdateAttributeGroupRequest $request, AttributeGroup $attribute_group): JsonResponse
    {
        Gate::authorize('update', $attribute_group);

        $attribute_group->update($request->validated());

        return (new AttributeGroupResource($attribute_group))->response();
    }

    public function destroy(AttributeGroup $attribute_group): JsonResponse
    {
        Gate::authorize('delete', $attribute_group);

        $attribute_group->delete();

        return response()->json([], 204);
    }
}
