<?php

namespace Modules\Attribute\Policies;

use App\Models\User;
use Modules\Attribute\Models\AttributeGroup;
use Modules\Core\Support\Menu\MenuAuthorizer;

class AttributeGroupPolicy
{
    public function __construct(private readonly MenuAuthorizer $authorizer) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorizer->allows($user, 'catalog.attribute-groups.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AttributeGroup $group): bool
    {
        return $this->authorizer->allows($user, 'catalog.attribute-groups.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->authorizer->allows($user, 'catalog.attribute-groups.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AttributeGroup $group): bool
    {
        return $this->authorizer->allows($user, 'catalog.attribute-groups.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AttributeGroup $group): bool
    {
        return $this->authorizer->allows($user, 'catalog.attribute-groups.delete');
    }
}
