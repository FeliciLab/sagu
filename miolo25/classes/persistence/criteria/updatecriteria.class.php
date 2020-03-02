<?php

class UpdateCriteria extends DMLCriteria
{

    private $columnAttributes = array();

    public function getSqlStatement()
    {
        $statement = new MSQL();
        $statement->setTables($this->classMap->getUpdateSql());

        if (count($this->columnAttributes))
        {
            $i = 0;
            $columns = '';

            foreach ($this->columnAttributes as $column)
            {
                if ($i++)
                    $columns .= ',';

                $columns .= $this->getOperand($column)->getSqlName();
            }
            $statement->setColumns($columns);
        }

        // Add 'WHERE' clause to update statement
        if (($whereCondition = $this->whereCondition->getSql()) != '')
        {
            $statement->setWhere($whereCondition);
        }
        return $statement;
    }

    public function addColumnAttribute($attribute)
    {
        $this->columnAttributes[] = $attribute;
    }

    public function update($parameters = null)
    {
        return $this->manager->processCriteriaUpdate($this, $parameters);
    }
}
?>