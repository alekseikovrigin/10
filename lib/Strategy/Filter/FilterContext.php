<?php

namespace Inetris\Nocode\Strategy\Filter;

class FilterContext
{
    private $strategy;

    public function setStrategy(FilterInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function createFilter($value, $dbField, $operationType)
    {
        return $this->strategy->createFilter($value, $dbField, $operationType);
    }
}
