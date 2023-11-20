<?php

namespace Inetris\Nocode\Strategy\Filter;

interface FilterInterface
{
    public function createFilter($value, $dbField, $operationType): array;
}
