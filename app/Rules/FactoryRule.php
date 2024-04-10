<?php
/*
Абстрактная фабрика (инструментарий) - это порождающий паттерн проектирования, 
который позволяет создавать семейства связанных объектов, 
не привязываясь к конкретным классам создаваемых объектов.
*/

namespace App\Rules;

use Psr\Http\Message\ServerRequestInterface as Request;
use App\Exceptions\Factory\BuilderFactoryException;

class FactoryRule
{
    private string $buildRule;

    private Request $request;

    private string $method;

    private InterfaceFactoryRule $serviceRule;

    private function __construct(string $buildRule)
    {
        $this->buildRule = $buildRule;
    }

    public static function build(string $buildRule): self
    {
        return new self($buildRule);
    }

    public function request(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function method(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function validate(): array
    {
        // вызовим объект

        $this->serviceRule = new $this->buildRule($this->request);

        if (!method_exists($this->serviceRule, $this->method))
            throw new BuilderFactoryException("Вызываемый метод [" . $this->method . "] не найден");

        $returnCallUserFunc = call_user_func([$this->serviceRule, $this->method]);

        return $returnCallUserFunc;
    }
}
