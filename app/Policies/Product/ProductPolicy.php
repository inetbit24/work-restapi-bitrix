<?php

namespace App\Policies\Product;

use App\Policies\FormPolicy;

class ProductPolicy extends FormPolicy
{
    public function index(array $user): bool
    {
        return $this->hasPermission('catalog_products_show');
    }

    public function store(array $user): bool
    {
        return $this->hasPermission('catalog_product_create');
    }
}
