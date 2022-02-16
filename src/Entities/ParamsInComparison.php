<?php

namespace Bitsman\CompareEntity\Entities;


class ParamsInComparison
{
    /** @var string[] */
    public array $globalParams;
    /** @var string[] */
    public array $paramsForThisDeep;
    /** @var string[][] */
    public array $paramsForNextDeep;
}
