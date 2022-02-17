<?php

namespace Bitsman\CompareEntity\Service;

use Bitsman\CompareEntity\Entities\ParamsInComparison;
use Bitsman\CompareEntity\Enums\CompareEntitiesEnum;
use Bitsman\CompareEntity\Interfaces\EntityToArrayInterface;


class CompareEntitiesService
{
    readonly public string $globalParamPrefix;

    public function __construct(
        readonly public string $paramDeepSeparator = '.',
        private string $globalPrefixCharacter = '^',
    )
    {
        $this->globalParamPrefix = $this->globalPrefixCharacter . $this->paramDeepSeparator;
    }

    public function areEquals(
        EntityToArrayInterface $object1,
        EntityToArrayInterface $object2,
        ?CompareEntitiesEnum $action = null,
        ?array $params = null,
    ): bool
    {
        $action = $action ?? CompareEntitiesEnum::ESTABLISH_PARAMS;
        $params = $params ?? ($action === CompareEntitiesEnum::ESTABLISH_PARAMS ?
            $this->getObjectProperties($object1) :
            []);

        return (
            $this->getArrayForCompare($object1, $action, $params) ===
            $this->getArrayForCompare($object2, $action, $params)
        );
    }

    private function getArrayForCompare(
        EntityToArrayInterface $object,
        CompareEntitiesEnum $action,
        array $params,
    ): array
    {
        return $this->getArrayForCompareRecursive($object->toArray(), $action, $params);
    }

    private function getArrayForCompareRecursive(
        array $object,
        CompareEntitiesEnum $action,
        array $params,
    ): array
    {
        $paramsInComparison = $this->getSplitParams($params);

        $arrayForCompare = [];
        foreach ($object as $property => $value) {
            $skipLoopIfNotObjectOrObjectArray = false;
            if (
                (
                    $action === CompareEntitiesEnum::SKIP_PARAMS &&
                    in_array($property, $paramsInComparison->paramsForThisDeep
                )) ||
                (
                    $action === CompareEntitiesEnum::ESTABLISH_PARAMS &&
                    !in_array($property, $paramsInComparison->paramsForThisDeep)
                )
            ) {
                if (count($paramsInComparison->globalParams) === 0) {
                    continue;
                }
                $skipLoopIfNotObjectOrObjectArray = true;
            }

            if ($this->isObjectProperty($value)) {
                $arrayForCompare[$property] = $this->getArrayForCompareRecursive(
                    $value,
                    $action,
                    array_merge(
                        $paramsInComparison->globalParams,
                        $paramsInComparison->paramsForNextDeep[$property] ?? []
                    ),
                );
                continue;
            }

            if ($this->isObjectPropertyArray($value)) {
                $arrayForCompare[$property] = array_map(
                    fn($arrayElement) => $this->getArrayForCompareRecursive(
                        $arrayElement,
                        $action,
                        array_merge(
                            $paramsInComparison->globalParams,
                            $paramsInComparison->paramsForNextDeep[$property] ?? []
                        ),
                    ),
                    $value
                );
                continue;
            }

            if ($skipLoopIfNotObjectOrObjectArray) {
                continue;
            }

            $arrayForCompare[$property] = $value;
        }

        return $arrayForCompare;
    }

    private function isObjectProperty(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    private function isObjectPropertyArray(mixed $value): bool
    {
        if (!is_array($value) || !isset($value[0]) || !is_array($value[0])) {
            return false;
        }

        return true;
    }

    private function getSplitParams(array $params): ParamsInComparison
    {
        $paramsInComparison = new ParamsInComparison();
        foreach ($params as $param) {
            $explodeResult = explode($this->paramDeepSeparator, $param, 2);
            if (count($explodeResult) === 1) {
                $paramsInComparison->paramsForThisDeep[] = $param;
                continue;
            }

            [$prefix, $nextParam] = $explodeResult;

            if ($prefix === $this->globalPrefixCharacter) {
                $paramsInComparison->globalParams[] = $param;
                $paramsInComparison->paramsForThisDeep[] = $nextParam;
                continue;
            }

            if (!isset($paramsInComparison->paramsForNextDeep[$prefix])) {
                $paramsInComparison->paramsForNextDeep[$prefix] = [];
            }
            $paramsInComparison->paramsForNextDeep[$prefix][] = $nextParam;
        }

        return $paramsInComparison;
    }

    private function getObjectProperties(EntityToArrayInterface $object): array
    {
        $class = $object::class;
        $reflection = new \ReflectionClass($class);

        $properties = [];
        $reflectionProperties = $reflection->getProperties(
            (\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED) &
            ~\ReflectionProperty::IS_STATIC
        );
        foreach($reflectionProperties as $property) {
            if ($property->getDeclaringClass()->getName() === $class) {
                $properties[] = $property->getName();
            }
        }
        return $properties;
    }
}
