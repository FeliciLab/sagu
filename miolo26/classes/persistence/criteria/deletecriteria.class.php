<?php

class DeleteCriteria extends DMLCriteria
{
    public function getSqlStatement()
    {
        $statement = new MSQL();
        $statement->setTables($this->classMap->getDeleteSql());

        // Add 'WHERE' clause to the select statement
        if (($whereCondition = $this->whereCondition->getSql()) != '')
        {
            $statement->setWhere($whereCondition);
        }
        return $statement;
    }

    public function delete($parameters = null)
    {
        return $this->manager->processCriteriaDelete($this, $parameters);
    }
}
?>