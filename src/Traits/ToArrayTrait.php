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
        foreach($reflection->getProperties(
            (\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED) &
            ~\ReflectionProperty::IS_STATIC
        ) as $property) {
            if ($property->getDeclaringClass()->getName() === $class) {
                if ($property->getValue() instanceof EntityToArrayInterface) {
                    $properties[$property->getName()] = $property->getValue()->toArray();
                    continue;
                }

                if (is_array($property->getValue())) {
                    $properties[$property->getName()] = array_map(
                        static fn($value) => $value instanceof EntityToArrayInterface ? $value->toArray() : $value,
                        $property->getValue(),
                    );
                    continue;
                }

                $properties[$property->getName()] = $property->getValue();
            }
        }

        return $properties;
    }
}
