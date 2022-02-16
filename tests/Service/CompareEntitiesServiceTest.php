<?php

namespace Bitsman\CompareEntity\Tests\Service;

use Bitsman\CompareEntity\Enums\CompareEntitiesEnum;
use Bitsman\CompareEntity\Interfaces\EntityToArrayInterface;
use Bitsman\CompareEntity\Service\CompareEntitiesService;
use Bitsman\CompareEntity\Tests\Resources\Classes\Foo;
use Bitsman\CompareEntity\Tests\Resources\Classes\NoToArrayEntity;
use PHPUnit\Framework\TestCase;
use Bitsman\CompareEntity\Tests\Resources\Classes\Bar;


class CompareEntitiesServiceTest extends TestCase
{
    private CompareEntitiesService $compareEntitiesService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compareEntitiesService = new CompareEntitiesService();
    }

    /**
     * @dataProvider getDifferentEntities
     */
    public function testCompareEntitiesSuccessfully(
        bool $expectedAreEquals,
        EntityToArrayInterface $entity1,
        EntityToArrayInterface $entity2,
        ?CompareEntitiesEnum $action = null,
        ?array $params = null,
    ): void
    {
        if ($this->dataName() === 'Deep Comparison WITH SKIP_PARAM Action (1) KO') xdebug_break();
        $this->assertEquals(
            $expectedAreEquals,
            $this->compareEntitiesService->areEquals($entity1, $entity2, $action, $params)
        );
    }

    private function getDifferentEntities(): array
    {
        $datetime = new \DateTime();
        return [
            'Basic Comparison OK' => [
                true,
                new Bar(1, $datetime),
                new Bar(1, $datetime),
            ],
            'Deep Comparison Need certain params OK' => [
                true,
                new Foo(
                    'name',
                    [new Bar(1, $datetime)],
                    new Bar(1, $datetime),
                    new NoToArrayEntity('a'),
                    $datetime,
                ),
                new Foo(
                    'name',
                    [new Bar(2, $datetime)], // Is different
                    new Bar(2, $datetime), // Is different
                    new NoToArrayEntity('b'),
                    $datetime,
                ),
            ],
            'Deep Comparison WITH SKIP_PARAM Action OK' => [
                true,
                new Foo(
                    'name',
                    [new Bar(1, $datetime)],
                    new Bar(1, $datetime),
                    new NoToArrayEntity('a'),
                    $datetime,
                ),
                new Foo(
                    'name',
                    [new Bar(1, $datetime)],
                    new Bar(1, $datetime),
                    new NoToArrayEntity('b'),
                    $datetime,
                ),
                CompareEntitiesEnum::SKIP_PARAMS,
            ],
            'Deep Comparison WITH SKIP_PARAM Action KO' => [
                false,
                new Foo(
                    'name',
                    [new Bar(1, $datetime)],
                    new Bar(1, $datetime),
                    new NoToArrayEntity('a'),
                    $datetime,
                ),
                new Foo(
                    'name',
                    [new Bar(2, $datetime)], // Is different
                    new Bar(1, $datetime),
                    new NoToArrayEntity('b'),
                    $datetime,
                ),
                CompareEntitiesEnum::SKIP_PARAMS,
            ],
            'Deep Comparison WITH Diff CreatedAt params in deep params KO' => [
                false,
                new Foo(
                    'name',
                    [new Bar(1, new \DateTime())],
                    new Bar(1, new \DateTime()),
                    new NoToArrayEntity('a'),
                    $datetime,
                ),
                new Foo(
                    'name',
                    [new Bar(1, new \DateTime())],
                    new Bar(1, new \DateTime()),
                    new NoToArrayEntity('b'),
                    $datetime,
                ),
                CompareEntitiesEnum::SKIP_PARAMS,
            ],
            'Deep Comparison WITH Skipping CreatedAt param Globally OK' => [
                true,
                new Foo(
                    'name',
                    [new Bar(1, new \DateTime())],
                    new Bar(1, new \DateTime()),
                    new NoToArrayEntity('a'),
                    $datetime,
                ),
                new Foo(
                    'name',
                    [new Bar(1, new \DateTime())],
                    new Bar(1, new \DateTime()),
                    new NoToArrayEntity('b'),
                    $datetime,
                ),
                CompareEntitiesEnum::SKIP_PARAMS,
                ['^.createdAt'],
            ],
            'Only Deep Comparison WITH Specific params OK' => [
                false,
                new Foo(
                    'name1',
                    [new Bar(1, new \DateTime())],
                    new Bar(1, new \DateTime()),
                    new NoToArrayEntity('a'),
                    $datetime,
                ),
                new Foo(
                    'name2',
                    [new Bar(1, new \DateTime())],
                    new Bar(1, new \DateTime()),
                    new NoToArrayEntity('b'),
                    $datetime,
                ),
                CompareEntitiesEnum::ESTABLISH_PARAMS,
                ['bar.number', 'bars.number'],
            ],
            'Basic Comparison KO' => [
                false,
                new Bar(1, $datetime),
                new Bar(2, $datetime),
            ],
            'Basic Comparison With DateTime object KO' => [
                false,
                new Bar(1, $datetime),
                new Bar(1, new \DateTime()),
            ],
        ];
    }
}
