<?php

class CriteriaAttribute
{
    private $attributeMap;
    private $functionName;

    public function __construct($attributeMap, $functionName = '')
    {
        $this->attributeMap = $attributeMap;
        $this->functionName = $functionName;
    }

    public function getSql()
    {
        $s = ($this->functionName != '') ? $this->functionName . '(' : '';
        $s .= $this->attributeMap->getColumnMap()->getColumnName();
        $s .= ($this->functionName != '') ? ')' : '';
        return $s;
    }
}
?>
