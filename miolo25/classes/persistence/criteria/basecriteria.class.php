<?php

class BaseCriteria
{
    private $operand1;
    private $operator;
    private $operand2;

    public function __construct($operand1, $operator, $operand2)
    {
        $this->operand1 = $operand1;
        $this->operator = $operator;
        $this->operand2 = $operand2;
    }

    public function getSql()
    {
        $condition = "(";
        $condition .= $this->operand1->getSqlWhere();
        $condition .= ' ' . $this->operator . ' ';
        $condition .= $this->operand2->getSqlWhere();
        $condition .= ")";
        return $condition;
    }
}
?>
