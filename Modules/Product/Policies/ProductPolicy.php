<?php

namespace Modules\Product\Policies;

use App\Models\User;
use Modules\Core\Support\Menu\MenuAuthorizer;
use Modules\Product\Models\Product;

class ProductPolicy
{
    public function __construct(private readonly MenuAuthorizer $authorizer) {}

    /**
     * Determine whether the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorizer->allows($user, 'catalog.products.view');
    }

    /**
     * Determine whether the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        return $this->authorizer->allows($user, 'catalog.products.view');
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User $user): bool
    {
        return $this->authorizer->allows($user, 'catalog.products.create');
    }

    /**
     * Determine whether the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        return $this->authorizer->allows($user, 'catalog.products.edit');
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        return $this->authorizer->allows($user, 'catalog.products.delete');
    }
}
