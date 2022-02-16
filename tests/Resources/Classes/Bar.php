<?php

namespace Bitsman\CompareEntity\Tests\Resources\Classes;

use Bitsman\CompareEntity\Interfaces\EntityToArrayInterface;
use Bitsman\CompareEntity\Traits\ToArrayTrait;

class Bar implements EntityToArrayInterface
{
    use ToArrayTrait;

    public function __construct(
        readonly int $number,
        readonly \DateTime $createdAt,
    ) {}
}
