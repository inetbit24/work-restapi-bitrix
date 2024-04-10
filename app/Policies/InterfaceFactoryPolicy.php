<?php

namespace App\Policies;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface;

interface InterfaceFactoryPolicy
{
    public function __construct(string $method);
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface;
    public function hasPermission(string $permission): bool;
}
