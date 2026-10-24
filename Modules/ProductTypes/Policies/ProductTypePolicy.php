<?php

namespace Modules\ProductTypes\Policies;

use App\Models\User;
use Modules\Core\Support\Menu\MenuAuthorizer;
use Modules\ProductTypes\Models\ProductType;

class ProductTypePolicy
{
    public function __construct(private readonly MenuAuthorizer $authorizer) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorizer->allows($user, 'catalog.product-types.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProductType $productType): bool
    {
        return $this->authorizer->allows($user, 'catalog.product-types.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->authorizer->allows($user, 'catalog.product-types.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductType $productType): bool
    {
        return $this->authorizer->allows($user, 'catalog.product-types.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductType $productType): bool
    {
        return $this->authorizer->allows($user, 'catalog.product-types.delete');
    }
}
