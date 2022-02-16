<?php

namespace Bitsman\CompareEntity\Traits;

use Bitsman\CompareEntity\Interfaces\EntityToArrayInterface;

trait ToArrayTrait
{
    public function toArray(): array
    {
        $class = self::class;
        $reflection = new \ReflectionClass($class);

        $properties = [];
        $reflectionProperties = $reflection->getProperties(
            (\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED) &
            ~\ReflectionProperty::IS_STATIC
        );
        foreach($reflectionProperties as $property) {
            if ($property->getDeclaringClass()->getName() !== $class) {
                continue;
            }
            $name = $property->getName();
            $value = $this->{$name};
            if ($value instanceof EntityToArrayInterface) {
                $properties[$name] = $value->toArray();
                continue;
            }

            if ($value instanceof \DateTime) {
                $properties[$name] = $value->format('Y-m-d H:i:s.u');
                continue;
            }

            if (is_object($value)) {
                continue;
            }

            if (is_array($value)) {
                $properties[$name] = array_map(
                    static fn($value) => $value instanceof EntityToArrayInterface ? $value->toArray() : $value,
                    $value,
                );
                continue;
            }

            $properties[$name] = $value;
        }

        return $properties;
    }
}
