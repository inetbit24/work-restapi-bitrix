<?php

namespace App\Policies;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface;

use App\Policies\InterfaceFactoryPolicy;
use App\Services\Auth\AuthService;
use App\Exceptions\Policy\PolicyException;

class FormPolicy implements InterfaceFactoryPolicy
{
    public Request $request;

    public string $method;

    protected array $user;

    public function __construct(string $method)
    {
        $this->method = $method;

        $this->user = AuthService::getUser();
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $policy = call_user_func_array([$this, $this->method], [$this->user]);

        if (!$policy) throw new PolicyException('Нет доступа');

        return $handler->handle($request);
    }

    public function hasPermission(string $permission): bool
    {
        return !empty($this->user['PERMISSIONS'])
            ? array_search($permission, array_column($this->user['PERMISSIONS'], 'CODE')) !== false
            : false;
    }
}
