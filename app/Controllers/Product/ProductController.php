<?php

namespace App\Controllers\Product;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Services\Product\ProductService;
use App\Rules\FactoryRule;
use App\Rules\Product\ProductRule;

class ProductController
{
    private ProductService $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
    }

    public function index(Request $request)
    {
        return response($this->productService->index())->success();
    }

    public function store(Request $request)
    {
        return response($this->productService->store(
            FactoryRule::build(ProductRule::class)
                ->request($request)
                ->method('store')
                ->validate()
        ))->success();
    }
}
