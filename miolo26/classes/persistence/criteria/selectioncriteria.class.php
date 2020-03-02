<?php
class SelectionCriteria
{
    public $attributeMap;
    public $operator;
    public $value;

    public function selectionCriteria($attributeMap, $operator, $value)
    {
        $this->attributeMap = $attributeMap;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function _getValue()
    {
        if (is_array($this->value))
        {
            $value = "(";
            $i = 0;

            foreach ($this->value as $v)
            {
                $value .= ($i++ > 0) ? ", " : "";
                $value .= "'$v'";
            }

            $value .= ")";
        }
        elseif (is_object($this->value))
        {
            if ($this->value instanceof RetrieveCriteria)
            {
                $value = "(" . $this->value->getSqlStatement()->select() . ")";
            }
        }
        else
        {
            $value = $this->value;
        }

        return $value;
    }

    public function setWhereStatement($statement)
    {
        $condition = "(";
        $condition .= $this->attributeMap->getColumnMap()->getColumnName() . ' ' . $this->operator . ' ' . $this->_getValue();
        $condition .= ")";
        $statement->setWhere($condition);
    }

    public function getWhereSql()
    {
        $condition = "(";
        $conv = $cm->getConverter();
        $condition .= $this->attributeMap->getColumnMap()->getColumnName() . ' ' . $this->operator . ' ' . $this->_getValue();
        $condition .= ")";
        return $condition;
    }
}
?>
