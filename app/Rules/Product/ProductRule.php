<?php

namespace App\Rules\Product;

use Psr\Http\Message\ServerRequestInterface as Request;

use App\Rules\FormRule;


class ProductRule extends FormRule
{

    public function store(): array
    {
        $this->validator->rule('required', 'name');
        $this->validator->rule('lengthMin', 'code', '5');
        $this->validator->rule('required', 'iblock_id');

        return $this->validate();
    }
}
