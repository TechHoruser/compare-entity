<?php

namespace Bitsman\CompareEntity\Entities;


class ParamsInComparison
{
    public function __construct(
        /** @var string[] */
        public array $globalParams = [],
        /** @var string[] */
        public array $paramsForThisDeep = [],
        /** @var string[][] */
        public array $paramsForNextDeep = [],
    ) {}
}
