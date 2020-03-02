<?php

class CriteriaCondition
{
    private $parts = array();

    public function __construct()
    {
    }

    public function getSize()
    {
        return count($this->parts);
    }

    public function addCriteria($criteria, $conjuntion = 'AND')
    {
        $this->parts[] = array($criteria,$conjuntion);
        return $this;
    }

    public function addOrCriteria($criteria)
    {
        return $this->addCriteria($criteria, 'OR');
    }

    public function addAndCriteria($criteria)
    {
        return $this->addCriteria($criteria, 'AND');
    }

    public function addCondition($condition)
    {
        $this->parts = array_merge($this->parts, $condition->parts);
        return $this;
    }

    public function getSql()
    {
        $sql = '';
        $n = $this->getSize();

        for ($i = 0; $i < $n; $i++)
        {
            if ($i != 0)
            {
                $sql .= " " . $this->parts[$i][1] . " ";
            }

            $criteria = $this->parts[$i][0];
            $sql .= $criteria->getSql();
        }

        if ($n > 1)
            $sql = "(" . $sql . ")";

        return $sql;
    }
}
?>