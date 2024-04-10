<?php
/*
Абстрактная фабрика (инструментарий) - это порождающий паттерн проектирования, 
который позволяет создавать семейства связанных объектов, 
не привязываясь к конкретным классам создаваемых объектов.
*/

namespace App\Policies;

use App\Exceptions\Factory\BuilderFactoryException;

class FactoryPolicy
{
    public static function build(string $buildPolicy, string $method): InterfaceFactoryPolicy
    {
        return new $buildPolicy($method);
    }
}
