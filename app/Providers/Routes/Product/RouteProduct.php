<?php

namespace App\Providers\Routes\Product;

use Slim\Routing\RouteCollectorProxy;

use App\Providers\Slim\SlimProvider;
use App\Controllers\Product\ProductController;
use App\Policies\FactoryPolicy;
use App\Policies\Product\ProductPolicy;

class RouteProduct
{
    public function __construct(private ?RouteCollectorProxy $group = null)
    {
        ($this->group ?? SlimProvider::getApp())->get('/api/products', [ProductController::class, 'index'])
            ->add(FactoryPolicy::build(ProductPolicy::class, 'index'));

        ($this->group ?? SlimProvider::getApp())->post('/api/products', [ProductController::class, 'store'])
            ->add(FactoryPolicy::build(ProductPolicy::class, 'store'));
    }
}
