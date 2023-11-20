<?php

namespace Inetris\Nocode\DTO;

class ConditionDTO
{
    public $operation;
    public $value;
    public $criterion;
    public $rule;
    public $type;
    public $suborder;
    public $id;
    public $field;

    public function __construct($rule, $type, $criterion, $operation, $value, $suborder, $id, $field)
    {
        $this->id = $id;
        $this->rule = $rule;
        $this->type = $type;
        $this->criterion = $criterion;
        $this->operation = $operation;
        $this->value = $value;
        $this->suborder = $suborder;
        $this->field = $field;
    }
}
