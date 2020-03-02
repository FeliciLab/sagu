<?php

class AssociationCriteria
{
    private $name;
    private $classMap;
    private $associationMap;
    private $joinType;
    private $alias;

    public function __construct($name, $classMap, $aMap, $alias, $joinType)
    {
        $this->name = $name;
        $this->classMap = $classMap;
        $this->associationMap = $aMap;
        $this->alias = $alias;
        $this->joinType = $joinType;
    }

    public function getAssociationMap()
    {
        return $this->associationMap; 
    }

    public function getName()
    {
        return $this->name; 
    }

    public function getAlias()
    {
        return $this->alias; 
    }

    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    public function getJoinType()
    {
        return $this->joinType; 
    }

    public function setJoinType($joinType)
    {
        $this->joinType = $joinType; 
    }

    public function getClassMap()
    {
        return $this->classMap; 
    }
}

class PersistentCriteria
{
    protected $classMap = NULL;
    protected $tables = array();
    public $aliasTable = array();
    public $associations = array();
    protected $whereCondition = NULL;

    /**
     * @var PersistentManager Manager instance.
     */
    protected $manager = NULL;

    protected $alias = '';

    public function __construct($classMap, $manager)
    {
        $this->classMap = $classMap;
        $this->manager = $manager;
        // Fill tables with tableMaps
        $cm = $this->classMap;

        do
        {
            $this->tables['table'][] = $cm->getTable();
            $this->tables['alias'][] = '';
            $cm = $cm->getSuperClass();
        } while ($cm != NULL);

        // Create condition for the WHERE part of this criteria
        $this->whereCondition = $this->getNewCondition();
    }

    public function getManager()
    {
        return $this->manager;
    }

    public function getNewCondition()
    {
        return new CriteriaCondition();
    }

    public function getWhereCondition()
    {
        return $this->whereCondition;
    }

    public function getClassMap()
    {
        return $this->classMap;
    }

    public function addTable($tableMap, $alias = '')
    {
        if (($i = array_search($tableMap, $this->tables['table'])) === FALSE)
        {
            $this->tables['table'][] = $tableMap;
            $this->tables['alias'][] = $alias;
        }
        else
        {
            if ($alias != '')
            {
                $a = $this->tables['alias'][$i];

                if ($a != '')
                {
                    $this->tables['table'][] = $tableMap;
                    $this->tables['alias'][] = $alias;
                }
                else
                {
                    $this->tables['alias'][$i] = $alias;
                }
            }
        }
    }

    public function getTableAlias($tableMap)
    {
        if (($i = array_search($tableMap, $this->tables['table'])) === FALSE)
        {
            return '';
        }
        else
        {
            return $this->tables['alias'][$i];
        }
    }

    public function addCriteriaTables($tables)
    {
        $n = count($tables['table']);

        for ($i = 0; $i < $n; $i++)
        {
            $this->addTable($tables['table'][$i], $tables['alias'][$i]);
        }
    }

    public function getTables()
    {
        return $this->tables;
    }

    public function getAlias($classMap = NULL)
    {
        if ($className == NULL)
            return $this->alias;
        else
        {
            $alias = array_search($classMap, $this->aliasTable);
            return ($alias) ? $alias : '';
        }
    }

    public function setAlias($alias, $classMap = NULL)
    {
        if ($classMap == NULL)
        {
            $this->alias = $alias;
            $classMap = $this->classMap;
        }

        $this->aliasTable[$alias] = $classMap;
        $this->addTable($classMap->getTable(), $alias);
    }

    public function isAlias($name)
    {
        return isset($this->aliasTable[$name]);
    }

    public function setAssociationAlias($associationName, $alias)
    {
        $association = $this->getAssociation($associationName);

        if ($association == NULL)
        {
            $this->addAssociationMap($this->classMap, $associationName);
            $association = $this->associations[$associationName];
        }

        $association->setAlias($alias);
        $cm = $association->getAssociationMap()->getForClass();
        $this->setAlias($alias, $cm);
    }

    public function setAssociationType($associationName, $joinType)
    {
        $association = $this->getAssociation($associationName);

        if ($association == NULL)
        {
            $this->addAssociationMap($this->classMap, $associationName);
            $association = $this->associations[$associationName];
        }

        $association->setJoinType($joinType);
    }

    public function setAutoAssociationAlias($alias0, $alias1)
    {
        $this->setAlias($alias0, $this->classMap);
        $this->setAlias($alias1, $this->classMap);
    }

    public function setReferenceAlias($alias)
    {
        $this->aliasTable[$alias] = $this->classMap;
    }

    public function getAssociationsJoin()
    {
        // Build a join array to sql statement
        $join = array();

        // Inheritance associations
        $classMap = $this->classMap;

        do
        {
            for ($i = 0; $i < $classMap->getReferenceSize(); $i++)
            {
                $am = $classMap->getReferenceAttributeMap($i);

                if ($cm = $am->getColumnMap())
                {
                    $crm = $am->getReference()->getColumnMap();
                    $t1 = $cm->getTableMap();
                    $t1Alias = $this->getTableAlias($t1);
                    $t1Name = $t1->getName() . ' ' . $t1Alias;
                    $t2 = $crm->getTableMap();
                    $t2Alias = $this->getTableAlias($t2);
                    $t2Name = $t2->getName() . ' ' . $t2Alias;
                    $condition = $cm->getFullyQualifiedName($t1Alias) . "=" . $crm->getFullyQualifiedName($t2Alias);
                    $join[] = array($t1Name, $t2Name, $condition, 'INNER');
                }
            }

            $classMap = $classMap->getSuperClass();
        } while ($classMap != NULL);

        // if this classMap has joinAssociations, add to $this->associations
        if ($this->classMap->getJoinAssociationSize())
        {
            foreach($this->classMap->getJoinAssociationMaps() as $aMap)
            {
                $this->_addAssociation($aMap->getTargetName(),$this->classMap, $aMap, '', $aMap->getJoinAutomatic());
            }
        }

        // Associations
        $n = count($this->associations);

        if ($n > 0)
        {
            foreach ($this->associations as $association)
            {
                $aMap = $association->getAssociationMap();
                $type = strtoupper($association->getJoinType());
                $k = $aMap->getSize();

                for ($j = 0; $j < $k; $j++)
                {
                    $entry = $aMap->getEntry($j);
                    $t1 = $entry->getFrom()->getColumnMap()->getTableMap();
                    $t1Alias = ($aMap->isInverse() ? $association->getAlias() : $this->getTableAlias($t1));
                    $t1Name = $t1->getName() . ' ' . $t1Alias;
                    $t2 = $entry->getTo()->getColumnMap()->getTableMap();
                    $t2Alias = (!$aMap->isInverse() ? $association->getAlias() : $this->getTableAlias($t2));
                    $t2Name = $t2->getName() . ' ' . $t2Alias;

                    if ($aMap->isInverse())
                    {
                        $condition = $entry->getTo()->getColumnMap()->getFullyQualifiedName($t2Alias) . "=" . $entry->getFrom()->getColumnMap()->getFullyQualifiedName($t1Alias);
                        $join[] = array($t2Name, $t1Name, $condition, $type);
                    }
                    else
                    {
                        $condition = $entry->getFrom()->getColumnMap()->getFullyQualifiedName($t1Alias) . "=" . $entry->getTo()->getColumnMap()->getFullyQualifiedName($t2Alias);
                        $join[] = array($t1Name, $t2Name, $condition, $type);
                    }
                }
            }
        }

        return (count($join) ? $join : NULL);
    }

    public function getAttributeMap(&$attribute)
    {
        $map = NULL;
        $cm = $this->classMap;

        if (strpos($attribute, '.'))
        {
            $tok = strtok($attribute, ".");

            while ($tok)
            {
                $nameSequence[] = $tok;
                $tok = strtok(".");
            }

            for ($i = 0; $i < count($nameSequence) - 1; $i++)
            {
                $name = $nameSequence[$i];
                $isAlias = $this->isAlias($name);

                if ($isAlias)
                {
                    $cm = $this->aliasTable[$name];
                    break;
                }
                else
                {
                    $am = $this->getAssociationMap($cm, $name);

                    // If association map is NULL something wrong with names
                    if ($am == NULL)
                        break;

                    $cm = $am->getForClass();
                }
            }

            if ($cm != NULL)
            {
                $attribute = $nameSequence[count($nameSequence) - 1];
                $map = $cm->getAttributeMap($attribute, TRUE);

                if (($map == NULL) || ($isAlias))
                    $attribute = $name . '.' . $attribute;
            }
        }
        else
        {
            $map = $cm->getAttributeMap($attribute, TRUE);

            if (($map != NULL) && ($this->alias != ''))
                $attribute = $this->alias . '.' . $attribute;
        }

        return $map;
    }

    public function getOperand($operand)
    {
        if ($operand == NULL)
        {
            $o = new OperandNull($this, $operand);
        }
        elseif (is_object($operand))
        {
            if ($operand instanceof AttributeMap)
            {
                $o = new OperandAttributeMap($this, $operand, $operand->getName());
            }
            elseif ($operand instanceof RetrieveCriteria)
            {
                $o = new OperandCriteria($this, $operand);
            }
            else
            {
                $o = new OperandObject($this, $operand);
            }
        }
        elseif (is_array($operand))
        {
            $o = new OperandArray($this, $operand);
        }
        elseif (strpos($operand, '(') === FALSE)
        {
            $op = $operand;
            $am = $this->getAttributeMap($operand);

            if ($am == NULL)
            {
                $o = new OperandValue($this, $op);
            }
            else
            {
                $o = new OperandAttributeMap($this, $am, $operand);
            }
        }
        else
        {
            $o = new OperandFunction($this, $operand);
        }

        return $o;
    }

    public function getCriteria($op1, $operator = '', $op2 = NULL)
    {
        $operand1 = $this->getOperand($op1);
        $operand2 = $this->getOperand($op2);
        $criteria = new BaseCriteria($operand1, $operator, $operand2);
        return $criteria;
    }

    public function addCriteria($op1, $operator = '', $op2 = NULL)
    {
        $this->whereCondition->addCriteria($this->getCriteria($op1, $operator, $op2));
    }

    public function addOrCriteria($op1, $operator = '', $op2 = NULL)
    {
        $this->whereCondition->addOrCriteria($this->getCriteria($op1, $operator, $op2));
    }

    private function convertMultiCriteria($condition, &$criteriaCondition)
    {
        if (is_array($condition))
        {
            foreach($condition as $c)
            {
                if (is_array($c[1]))
                {
                    $cc = new CriteriaCondition;
                    $this->convertMultiCriteria($c[1],$cc);
                    $criteriaCondition->addCriteria($cc,$c[0]);
                }
                else
                {
                    $criteria = $this->getCriteria($c[1], $c[2], $c[3]);
                    $criteriaCondition->addCriteria($criteria, $c[0]);           
                }    
            }
        }
    }

    public function addMultiCriteria($condition)
    {
        $this->convertMultiCriteria($condition,$this->whereCondition);
    }

    public function getAssociation($associationName)
    {
        $association = NULL;

        foreach ($this->associations as $a)
        {
            if (($a->getName() == $associationName) || ($a->getAlias() == $associationName))
                $association = $a;
        }

        return $association;
    }

    public function getAssociationMap($classMap, $associationName)
    {
        $association = $this->getAssociation($associationName);

        if ($association == NULL)
        {
            if ($this->addAssociationMap($classMap, $associationName) != NULL)
                $association = $this->associations[$associationName];
            else
                return NULL;
        }

        return $association->getAssociationMap();
    }

    public function addAssociationMap($classMap, $associationName, $inverse = NULL, $alias = '')
    {
        $am = $classMap->getAssociationMap($associationName);
        if ($inverse !== NULL)
        {
            $am->setInverse($inverse);
        }

        if ($am != NULL)
        {
            $cardinality = $am->getCardinality();

            if ($cardinality == 'manyToMany')
            {
                $direction = $am->getDirection();
                $this->addAssociationMap($am->getAssociativeClass(), $direction[0], TRUE);
                $this->addAssociationMap($am->getAssociativeClass(), $direction[1], FALSE);
            }
            else
            {
                $this->_addAssociation($associationName, $classMap, $am, $alias);
            }

            $this->addTable($classMap->getTable());

            if (($cm = $am->getForClass()) != NULL)
            {
                $this->addTable($cm->getTable());
            }
        }

        return $am;
    }

    private function _addAssociation($name, $classMap, $associationMap, $alias = '', $joinType = 'INNER')
    {
        $this->associations[$name] = new AssociationCriteria($name, $classMap, $associationMap, $alias, $joinType);
    }

    public function addJoinCriteria($criteria)
    {
        $this->addCriteriaTables($criteria->getTables());
//        $this->associations = array_merge($this->associations, $criteria->associations);
        foreach($criteria->associations as $key=>&$value){
           $k = ($alias = $value->getAlias()) != '' ? $alias : $key; 
           $this->associations[$k] = $value;
        }
        $this->whereCondition->addCondition($criteria->getWhereCondition());
    }
}
?>