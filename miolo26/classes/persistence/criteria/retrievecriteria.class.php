<?php

class RetrieveCriteria extends PersistentCriteria
{
    private $havingCondition;
    private $distinct = FALSE;
    private $range = NULL; 
    private $columnAttributes = array();
    private $columnAlias = array();
    private $orderAttributes = array();
    private $groupAttributes = array();
    private $setOperation = array();

    public function __construct($classMap, $manager)
    {
        parent::__construct($classMap, $manager);
        // Create condition for the HAVING part of this criteria
        $this->havingCondition = $this->getNewCondition();
    }

    public function getSqlStatement($forProxy = FALSE)
    {
        $statement = new MSQL();
        if (count($this->columnAttributes))
        {
            $i = 0;
            $columns = '';

            foreach ($this->columnAttributes as $column)
            {
                if ($i++)
                    $columns .= ',';

                $columns .= $this->getOperand($column)->getSql();
                $columns .= ($alias = $this->columnAlias[$column]) != '' ? " as $alias" : "";
            }
        }
        else
        {
            $classMap = $this->getClassMap();
            $alias = $this->getAlias();
            if ($forProxy)
            {
                $columns = $classMap->getSelectProxySql($alias);
            }
            else
            {
                $columns = $classMap->getSelectSql($alias);
            }
        }

        $statement->setColumns($columns, $this->distinct);

        // Add 'FROM' clause to the select statement

        if ($join = $this->getAssociationsJoin())
        {
            $statement->join = $join;
        }
        else
        {
            $aTables = $this->getTables();
            $n = count($aTables['table']);

            for ($i = 0; $i < $n; $i++)
            {
                $tables .= (($i > 0 ? ", " : "") . $aTables['table'][$i]->getName() . ' ' . $aTables['alias'][$i]);
            }

            $statement->setTables($tables);
        }

        // Add 'WHERE' clause to the select statement
        //        $statement->setWhere($this->getWhereSql());

        if (($whereCondition = $this->whereCondition->getSql()) != '')
        {
            $statement->setWhere($whereCondition);
        }

        // Add 'GROUP BY' clause to the select statement
        if (count($this->groupAttributes))
        {
            $i = 0;
            $groupby = '';

            foreach ($this->groupAttributes as $group)
            {
                $groupby .= (($i++ > 0) ? ',' : '') . $this->getOperand($group)->getSql();
            }

            $statement->setGroupBy($groupby);
        }

        // Add 'HAVING' clause to the select statement
        $statement->setHaving($this->havingCondition->getSql());

        // Add 'ORDER BY' clause to the select statement
        if (count($this->orderAttributes))
        {
            $i = 0;
            $orderby = '';

            foreach ($this->orderAttributes as $entry)
            {
                $orderby .= (($i++ > 0) ? ',' : '') . $entry->getStatement($this);
            }

            $statement->setOrderBy($orderby);
        }

        // Add a range clause to the select statement
        if (!is_null($this->range))
        {
            $statement->setRange($this->range);
        } 

        // Add Set Operations
        if (count($this->setOperation))
        {
            foreach ($this->setOperation as $s)
            {
                $statement->setSetOperation($s[0], $s[1]->getSqlStatement());
            }
        }

        return $statement;
    }

    public function setDistinct($distinct = FALSE)
    {
        $this->distinct = $distinct;
    }

    public function setRange($range)
    {
        $this->range = $range;
    }

    public function addGroupAttribute($attribute)
    {
        $this->groupAttributes[] = $attribute;
    }

    public function addOrderAttribute($attribute, $ascend = TRUE)
    {
        $this->orderAttributes[] = new OrderEntry($attribute, $ascend);
    }

    public function addOrderEntry($orderEntry)
    {
        $this->orderAttributes[] = $orderEntry;
    }

    public function addColumnAttribute($attribute, $alias = '')
    {
        if ($attribute == '*')
        {
            $classMap = $this->classMap;
            for ($i = 0; $i < $classMap->getSize(); $i++)
            {
                $am = $classMap->getAttributeMap($i);
                $this->addColumnAttribute($am->getName());
            }
        }
        else
        {
            $this->columnAttributes[] = $attribute;
            $this->columnAlias[$attribute] = $alias;
        }
    }

    public function addHavingCriteria($op1, $operator, $op2)
    {
        $this->havingCondition->addCriteria($this->getCriteria($op1, $operator, $op2));
    }

    public function addOrHavingCriteria($op1, $operator, $op2)
    {
        $this->havingCondition->addOrCriteria($this->getCriteria($op1, $operator, $op2));
    }

    public function addJoinCriteria($criteria)
    {
        $this->havingCondition->addCondition($criteria->havingCondition);
        $this->columnAttributes = array_merge($this->columnAttributes, $criteria->columnAttributes);
        $this->orderAttributes = array_merge($this->orderAttributes, $criteria->orderAttributes);
        $this->groupAttributes = array_merge($this->groupAttributes, $criteria->groupAttributes);
        $this->aliasTable = array_merge($this->aliasTable, $criteria->aliasTable);
        parent::addJoinCriteria($criteria);
    }

    public function addSetOperation($operation,$criteria)
    {
        $this->setOperation[] = array($operation, $criteria);
    }

    public function retrieveAsQuery($parameters = null)
    {
        return $this->manager->processCriteriaAsQuery($this, $parameters);
    }

    public function retrieveAsCursor($parameters = null)
    {
        return $this->manager->processCriteriaAsCursor($this, $parameters);
    }

    public function retrieveAsProxyQuery($parameters = null)
    {
        return $this->manager->processCriteriaAsProxyQuery($this, $parameters);
    }

    public function retrieveAsProxyCursor($parameters = null)
    {
        return $this->manager->processCriteriaAsProxyCursor($this, $parameters);
    }
}
?>
