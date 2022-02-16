<?php

namespace Bitsman\CompareEntity\Tests\Resources\Classes;

class NoToArrayEntity
{
    public function __construct(
        readonly string $message = 'test',
    ) {}
}
