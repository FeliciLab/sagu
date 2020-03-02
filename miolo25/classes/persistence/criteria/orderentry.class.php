<?php

class OrderEntry
{
    private $attribute;
    private $ascend;

    public function __construct($attribute, $ascend)
    {
        $this->attribute = $attribute;
        $this->ascend = $ascend;
    }

    public function isAscend()
    {
        return $this->ascend;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function getStatement($criteria)
    {
        $s = $criteria->getOperand($this->attribute)->getSqlOrder();
        $s .= $this->ascend ? ' ASC' : ' DESC';
        return $s;
    }
}
?>
