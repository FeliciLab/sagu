<?php

class HavingCriteria
{
    private $criteriaAttribute;
    private $operator;
    private $value;

    public function __construct($criteriaAttribute, $operator, $value)
    {
        $this->criteriaAttribute = $criteriaAttribute;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function setHavingStatement($statement)
    {
        $cm = $this->criteriaAttribute->attributeMap->getColumnMap();
        $condition = "(";
        $condition .= ($this->criteriaAttribute->functionName != '')
            ? $this->criteriaAttribute->functionName . '(' : '';
        $condition .= $cm->getFullyQualifiedName();
        $condition .= ($this->criteriaAttribute->functionName != '') ? ')' : '';
        $condition .= ' ' . $this->operator . ' ' . $this->value;
        $condition .= ")";
        $statement->setHaving($condition);
    }

    public function getHavingSql()
    {
        $cm = $this->criteriaAttribute->attributeMap->getColumnMap();
        $condition = "(";
        $condition .= ($this->criteriaAttribute->functionName != '')
            ? $this->criteriaAttribute->functionName . '(' : '';
        $condition .= $cm->getFullyQualifiedName();
        $condition .= ($this->criteriaAttribute->functionName != '') ? ')' : '';
        $condition .= ' ' . $this->operator . ' ' . $this->value;
        $condition .= ")";
        return $condition;
    }
}
?>
