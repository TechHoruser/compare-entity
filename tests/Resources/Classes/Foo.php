<?php

namespace Bitsman\CompareEntity\Tests\Resources\Classes;

use Bitsman\CompareEntity\Interfaces\EntityToArrayInterface;
use Bitsman\CompareEntity\Traits\ToArrayTrait;

class Foo implements EntityToArrayInterface
{
    use ToArrayTrait;

    public function __construct(
        readonly string $name,
        readonly array $bars,
        readonly Bar $bar,
        readonly NoToArrayEntity $noToArrayEntity,
        readonly \DateTime $createdAt,
    ) {}
}
