<?php

namespace App\Rules;

use Psr\Http\Message\ServerRequestInterface as Request;

interface InterfaceFactoryRule {
    public function __construct(Request $request);
}