<?php

class ClassMap
{
    private $name;
    private $className;
    private $database;
    private $superClassName;
    private $attributeMaps = array();
    private $hashedAttributeMaps = array();
    private $keyAttributeMaps = array();
    private $proxyAttributeMaps = array();
    private $updateAttributeMaps = array();
    private $referenceAttributeMaps = array();
    private $associationMaps = array();
    private $inverseAssociationMaps = array();
    private $straightAssociationMaps = array();
    private $joinAssociationMaps = array();
    private $tables = array();
    private $mapObjectClass = NULL;
    private $isInited = FALSE;
    private $superClass = NULL;
    private $selectStatement;
    private $updateStatement;
    private $insertStatement;
    private $deleteStatement;
    private $broker;
    private $hasTypedAttribute = FALSE;

    public function __construct($name, $database, $broker)
    {
        $this->name = $name;
        $this->database = $database;
        $this->broker = $broker;
        $this->hasTypedAttribute = FALSE;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function getDb()
    {
        return $this->broker->getPersistentManager()->getConnection($this->database);
    }

    public function getTables()
    {
        return $this->tables;
    }

    public function getTable()
    {
        reset ($this->tables);
        return pos($this->tables);
    }

    public function addTable($tableMap)
    {
        $name = $tableMap->getName();
        $this->tables[$name] = $tableMap;
    }

    public function setHasTypedAttribute($has)
    {
        $this->hasTypedAttribute = $has;
    }

    public function getHasTypedAttribute()
    {
        return $this->hasTypedAttribute;
    }

    public function getObject()
    {
        $MIOLO = MIOLO::getInstance();
        $className = 'business' . $this->getName();
        $module    = $MIOLO->usesBusiness[$className]['module'];
        $name      = $MIOLO->usesBusiness[$className]['name'];
        if (($module != '') && ($name != ''))
        {
            $object    = $MIOLO->getBusiness($module, $name);
        }
        else
        {
            $object = new PersistentObject();
        }

        for ($i = 0; $i < $this->getSize(); $i++)
        {
            $this->getAttributeMap($i)->setValue($object, NULL);
        }
        return $object;
    }

    public function setSuperClass($superClassName, $broker)
    {
        $MIOLO = MIOLO::getInstance();
        $sc = 'business' . $superClassName;

        if (($sc != 'business') && ($sc != 'persistentobject'))
        {
            $module = $MIOLO->usesBusiness[$sc]['module'];
            $name   = $MIOLO->usesBusiness[$sc]['name'];
            $superClassMap = $broker->getClassMap($module, $name);

            if ($superClassMap != NULL)
            {
                $this->superClass = $superClassMap;
            }
        }
    }

    public function getSuperClass()
    {
        return $this->superClass;
    }

    public function addAttributeMap($attributeMap)
    {
        $this->hashedAttributeMaps[$attributeMap->getName()] = $attributeMap;

        if (($cm = $attributeMap->getColumnMap()) != NULL)
        {
            $this->attributeMaps[] = $attributeMap;

            if ($cm->getKeyType() != 'none')
                $this->keyAttributeMaps[] = $attributeMap;
            else
                $this->updateAttributeMaps[] = $attributeMap;

            if ($attributeMap->getReference() != NULL)
                $this->referenceAttributeMaps[] = $attributeMap;

            // Add attributeMap table to the table map collection
            $tableMap = $cm->getTableMap();
            $this->addTable($tableMap);

            if ($attributeMap->isProxy() || ($cm->getKeyType() != 'none'))
            {
                $this->proxyAttributeMaps[] = $attributeMap;
            }
        }
    }

    public function getAttributeMap($name, $areSuperClassesIncluded = FALSE)
    {
        $am = NULL;
        $cm = $this;

        if (gettype($name) == 'string')
        {
            do
            {
                $am = $cm->hashedAttributeMaps[$name];
                $cm = $cm->superClass;
            } while ($areSuperClassesIncluded && ($am == NULL) && ($cm != NULL));
        }
        else
        {
            $am = $cm->attributeMaps[$name];
        }
        return $am;
    }

    public function getKeyAttributeMap($index)
    {
        return $this->keyAttributeMaps[$index];
    }

    public function getProxyAttributeMap($index)
    {
        return $this->proxyAttributeMaps[$index];
    }

    public function getUpdateAttributeMap($index)
    {
        return $this->updateAttributeMaps[$index];
    }

    public function getReferenceAttributeMap($index)
    {
        return $this->referenceAttributeMaps[$index];
    }

    public function getAssociationMap($name)
    {
//        return $this->associationMaps[$name];
        $am = NULL;
        $cm = $this;
        do
        {
            $am = $cm->associationMaps[$name];
            $cm = $cm->superClass;
        } while (($am == NULL) && ($cm != NULL));
        return $am;
    }

    public function getJoinAssociationMap($index)
    {
        return $this->joinAssociationMaps[$index];
    }

    public function putAssociationMap($associationMap)
    {
        $this->associationMaps[$associationMap->getTargetName()] = $associationMap;
        if ($associationMap->isInverse())
        {
            $this->inverseAssociationMaps[] = $associationMap;
        }
        else
        {
            $this->straightAssociationMaps[] = $associationMap;
        }
        if ($associationMap->isJoinAutomatic())
        {
            $this->joinAssociationMaps[] = $associationMap;
        }
    }

    public function getAssociationMaps()
    {
        return $this->associationMaps;
    }

    public function getJoinAssociationMaps()
    {
        return $this->joinAssociationMaps;
    }

    public function getStraightAssociationMaps()
    {
        return $this->straightAssociationMaps;
    }

    public function getInverseAssociationMaps()
    {
        return $this->inverseAssociationMaps;
    }

    public function getProxySize()
    {
        return count($this->proxyAttributeMaps);
    }

    public function getSize()
    {
        return count($this->attributeMaps);
    }

    public function getReferenceSize()
    {
        return count($this->referenceAttributeMaps);
    }

    public function getAssociationSize()
    {
        return count($this->associationMaps);
    }

    public function getJoinAssociationSize()
    {
        return count($this->joinAssociationMaps);
    }

    public function getKeySize()
    {
        return count($this->keyAttributeMaps);
    }

    public function getUpdateSize()
    {
        return count($this->updateAttributeMaps);
    }

    public function retrieveObject($object, $query)
    {
        $index = 0;
        $classMap = $this;

        do
        {
            for ($i = 0; $i < $classMap->getSize(); $i++)
            {
                $am = $classMap->getAttributeMap($i);
                if ($cm = $am->getColumnMap())
                {
                    $value = $query->getValue($cm->getName());
                    $am->setValue($object, $cm->getValueTo($value,$this));
                }
            }
            for ($i = 0; $i < $this->getSize(); $i++)
            {
                $am = $this->getAttributeMap($i);
                if ($type = $am->getColumnType())
                {
                    $object->handleTypedAttribute($am->getName(), 'select' , $type);
                }
            }

            $classMap = $classMap->superClass;
        } while ($classMap != NULL);

        $object->setPersistent(TRUE);
        $object->setProxy(FALSE);

        for ($i = 0; $i < $this->getJoinAssociationSize(); $i++)
        {
            $aMap = $this->getJoinAssociationMap($i);
            $classMap = $aMap->getForClass();
            $nestedObject = $classMap->getObject();
            for ($i = 0; $i < $classMap->getSize(); $i++)
            {
                $am = $classMap->getAttributeMap($i);
                if ($cm = $am->getColumnMap())
                {
                    $value = $query->getValue($cm->getName());
                    $am->setValue($nestedObject, $cm->getValueTo($value,$this));
                }
            }
            $value = $nestedObject;
            $target = $aMap->getTarget();
            $target->setValue($object, $value);
        }
    }

    public function retrieveProxyObject($object, $query)
    {
        $index = 0;
        $classMap = $this;

        do
        {
            for ($i = 0; $i < $classMap->getProxySize(); $i++)
            {
                $am = $classMap->getProxyAttributeMap($i);
                if ($cm = $am->getColumnMap())
                {
                    $value = $query->getValue($cm->getName());
                    $am->setValue($object, $cm->getValueTo($value,$this));
                }
            }

            $classMap = $classMap->superClass;
        } while ($classMap != NULL);

        $object->setPersistent(TRUE);
        $object->setProxy(TRUE);

        for ($i = 0; $i < $this->getJoinAssociationSize(); $i++)
        {
            $aMap = $this->getJoinAssociationMap($i);
            $classMap = $aMap->getForClass();
            $nestedObject = $classMap->getObject();
            for ($i = 0; $i < $classMap->getSize(); $i++)
            {
                $value = $result[$index++];
                $am = $classMap->getAttributeMap($i);
                if ($cm = $am->getColumnMap())
                {
                    $am->setValue($nestedObject, $cm->getValueTo($value,$this));
                }
            }
            $value = $nestedObject;
            $target = $aMap->getTarget();
            $target->setValue($object, $value);
        }
    }

    public function getSelectSqlFor($object)
    {
        $statement = $this->getSelectStatement();

        // Fill statement with values
        for ($i = 0; $i < $this->getKeySize(); $i++)
        {
            $am = $this->getKeyAttributeMap($i);
            $value = $am->getColumnMap()->getValueFrom($am->getValue($object),$this);
            $statement->addParameter($value);
        }
        return $statement;
    }

    public function getSelectProxySqlFor($object)
    {
        $statement = $this->getSelectProxyStatement();

        // Fill statement with values
        for ($i = 0; $i < $this->getKeySize(); $i++)
        {
            $am = $this->getKeyAttributeMap($i);
            $value = $am->getColumnMap()->getValueFrom($am->getValue($object),$this);
            $statement->addParameter($value);
        }
        return $statement;
    }

    public function getSelectSql($alias = '')
    {
        $isFirst = TRUE;
        $classMap = $this;

        do
        {
            for ($i = 0; $i < $classMap->getSize(); $i++)
            {
                $am = $classMap->getAttributeMap($i);

                if ($cm = $am->getColumnMap())
                {
                    $column = $cm->getColumnName($alias, TRUE, $this);
                    $columns .= ($isFirst ? "" : ", ") . $column;
                }
                $isFirst = FALSE;
            }

            for ($i = 0; $i < $classMap->getJoinAssociationSize(); $i++)
            {
                $am = $classMap->getJoinAssociationMap($i);
                
                $cm = $am->getForClass();

                for ($j = 0; $j < $cm->getSize(); $j++)
                {
                    $atrm = $cm->getAttributeMap($j);
                    if ($colm = $atrm->getColumnMap())
                    {
                        $column = $colm->getColumnName($alias, TRUE, $this);
                        $columns .= ($isFirst ? "" : ", ") . $column;
                    }
                    $isFirst = FALSE;
                }
            }

            $classMap = $classMap->superClass;
        } while ($classMap != NULL);

        return $columns;
    }

    public function getSelectProxySql($alias = '')
    {
        $isFirst = TRUE;
        $classMap = $this;

        do
        {
            for ($i = 0; $i < $classMap->getProxySize(); $i++)
            {
                $am = $classMap->getProxyAttributeMap($i);

                if ($cm = $am->getColumnMap())
                {
                    $column = $cm->getColumnName($alias, TRUE, $this);
                    $columns .= ($isFirst ? "" : ", ") . $column;
                }

                $isFirst = FALSE;
            }

            for ($i = 0; $i < $classMap->getJoinAssociationSize(); $i++)
            {
                $am = $classMap->getJoinAssociationMap($i);
                
                $cm = $am->getForClass();

                for ($j = 0; $j < $cm->getSize(); $j++)
                {
                    $atrm = $cm->getAttributeMap($j);
                    if ($colm = $atrm->getColumnMap())
                    {
                        $column = $colm->getColumnName($alias, TRUE, $this);
                        $columns .= ($isFirst ? "" : ", ") . $column;
                    }
                    $isFirst = FALSE;
                }
            }

            $classMap = $classMap->superClass;
        } while ($classMap != NULL);

        return $columns;
    }

    public function getFromSql()
    {
        $isFirst = TRUE;
        $classMap = $this;

        do
        {
            $table = $classMap->getAttributeMap(0)->getColumnMap()->getTableMap()->getName();
            $tables .= ($isFirst ? "" : ", ") . $table;
            $isFirst = FALSE;
            $classMap = $classMap->superClass;
        } while ($classMap != NULL);

        for ($i = 0; $i < $this->getJoinAssociationSize(); $i++)
        {
            $classMap = $this->getJoinAssociationMap($i)->getForClass();
            foreach($classMap->getTables() as $tm)
            {
                $table = $tm->getName();
                $tables .= ($isFirst ? "" : ", ") . $table;
                $isFirst = FALSE;
            }
        }

        return $tables;
    }

    public function getWhereSql()
    {
        $conditions = '';
        $inheritanceAssociations = $this->getInheritanceAssociations();

        if (($this->getKeySize() > 0) || ($inheritanceAssociations != ''))
        {
            $isFirst = TRUE;
            $classMap = $this;

            for ($i = 0; $i < $classMap->getKeySize(); $i++)
            {
                $am = $classMap->getKeyAttributeMap($i);

                if ($cm = $am->getColumnMap())
                {
                    $column = $cm->getFullyQualifiedName($alias);
                    $conditions .= ($isFirst ? " " : " AND ") . $column . " = ?";
                }

                $isFirst = FALSE;
            }

            if ($inheritanceAssociations != '')
            {
                $conditions .= (($classMap->getKeySize() > 0) ? " AND " : "") . $inheritanceAssociations;
            }
        }

        return $conditions;
    }

    public function getInheritanceAssociations()
    {
        $result = '';
        $isFirst = TRUE;
        $classMap = $this;

        do
        {
            for ($i = 0; $i < $classMap->getReferenceSize(); $i++)
            {
                $am = $classMap->getReferenceAttributeMap($i);
                if ($cm = $am->getColumnMap())
                {
                    $columnLeft = $cm->getFullyQualifiedName();
                    $columnRight = $am->getReference()->getColumnMap()->getFullyQualifiedName();
                    $result .= ($isFirst ? " " : " AND ") . $columnLeft . " = " . $columnRight;
                }
                $isFirst = FALSE;
            }
            $classMap = $classMap->superClass;
        } while ($classMap != NULL);

        return $result;
    }

    public function getJoinAssociations()
    {
        $join = array();
        for ($i = 0; $i < $this->getJoinAssociationSize(); $i++)
        {
            $aMap = $this->getJoinAssociationMap($i);
            $type = $aMap->getJoinAutomatic();
            $k = $aMap->getSize();
            for ($j = 0; $j < $k; $j++)
            {
                $entry = $aMap->getEntry($j);
                $t1 = $entry->getFrom()->getColumnMap()->getTableMap();
                $t1Name = $t1->getName();
                $t2 = $entry->getTo()->getColumnMap()->getTableMap();
                $t2Name = $t2->getName();
                if ($aMap->isInverse())
                {
                    $condition = $entry->getTo()->getColumnMap()->getFullyQualifiedName() . "=" . $entry->getFrom()->getColumnMap()->getFullyQualifiedName();
                    $join[] = array($t2Name,$t1Name,$condition,$type);
                }
                else
                {
                    $condition = $entry->getFrom()->getColumnMap()->getFullyQualifiedName() . "=" . $entry->getTo()->getColumnMap()->getFullyQualifiedName();
                    $join[] = array($t1Name,$t2Name,$condition,$type);
                }
            }
        }
        return (count($join) ? $join : NULL);
    }

    public function getUpdateSqlFor($object)
    {
        $statement = $this->getUpdateStatement();

        // Fill statement with values
        for ($i = 0; $i < $this->getUpdateSize(); $i++)
        {
            $am = $this->getUpdateAttributeMap($i);
            $value = $am->getColumnMap()->getValueFrom($am->getValue($object),$this);
            $statement->addParameter($value);
        }

        for ($i = 0; $i < $this->getKeySize(); $i++)
        {
            $am = $this->getKeyAttributeMap($i);
            $value = $am->getColumnMap()->getValueFrom($am->getValue($object),$this);
            $statement->addParameter($value);
        }
        return $statement;
    }

    public function getUpdateSql()
    {
        return $this->getAttributeMap(0)->getColumnMap()->getTableMap()->getName();
    }

    public function getUpdateSetSql()
    {
        $isFirst = TRUE;
        $classMap = $this;

        for ($i = 0; $i < $classMap->getUpdateSize(); $i++)
        {
            $am = $classMap->getUpdateAttributeMap($i);

            if ($cm = $am->getColumnMap())
            {
                $column = $cm->getName();
                $columns .= ($isFirst ? "" : ", ") . $column;
            }

            $isFirst = FALSE;
        }
        return $columns;
    }

    public function getUpdateWhereSql()
    {
        $conditions = '';
        $isFirst = TRUE;
        $classMap = $this;

        for ($i = 0; $i < $classMap->getKeySize(); $i++)
        {
            $am = $classMap->getKeyAttributeMap($i);

            if ($cm = $am->getColumnMap())
            {
                $column = $cm->getFullyQualifiedName($alias);
                $conditions .= ($isFirst ? " " : " AND ") . $column . " = ?";
            }

            $isFirst = FALSE;
        }
        return $conditions;
    }

    public function getInsertSqlFor($object)
    {
        $statement = $this->getInsertStatement();

        // Fill statement with values
        for ($i = 0; $i < $this->getSize(); $i++)
        {
            $am = $this->getAttributeMap($i);
            $value = $am->getColumnMap()->getValueFrom($am->getValue($object),$this);
            $statement->addParameter($value);
        }
        return $statement;
    }

    public function getInsertSql()
    {
        return $this->getAttributeMap(0)->getColumnMap()->getTableMap()->getName();
    }

    public function getInsertValuesSql()
    {
        $isFirst = TRUE;
        $classMap = $this;

        for ($i = 0; $i < $classMap->getSize(); $i++)
        {
            $am = $classMap->getAttributeMap($i);

            if ($cm = $am->getColumnMap())
            {
                $column = $cm->getName();
                $columns .= ($isFirst ? "" : ", ") . $column;
            }

            $isFirst = FALSE;
        }

        return $columns;
    }

    public function getDeleteSqlFor($object)
    {
        $statement = $this->getDeleteStatement();

        // Fill statement with values
        for ($i = 0; $i < $this->getKeySize(); $i++)
        {
            $am = $this->getKeyAttributeMap($i);
            $value = $am->getColumnMap()->getValueFrom($am->getValue($object),$this);
            $statement->addParameter($value);
        }
        return $statement;
    }

    public function getDeleteSql()
    {
        return $this->getAttributeMap(0)->getColumnMap()->getTableMap()->getName();
    }

    public function getDeleteWhereSql()
    {
        $conditions = '';
        $isFirst = TRUE;
        $classMap = $this;

        for ($i = 0; $i < $classMap->getKeySize(); $i++)
        {
            $am = $classMap->getKeyAttributeMap($i);

            if ($cm = $am->getColumnMap())
            {
                $column = $cm->getName();
                $conditions .= ($isFirst ? " " : " AND ") . $column . " = ?";
            }

            $isFirst = FALSE;
        }
        return $conditions;
    }

    public function getSelectStatement()
    {
        $this->selectStatement = new MSQL();
        $this->selectStatement->setColumns($this->getSelectSql());
        if ($join = $this->getJoinAssociations())
        {
            $this->selectStatement->join = $join;
        }
        else
        {
            $this->selectStatement->setTables($this->getFromSql());
        }
        $this->selectStatement->setWhere($this->getWhereSql());
        return $this->selectStatement;
    }

    public function getSelectProxyStatement()
    {
        $this->selectProxyStatement = new MSQL();
        $this->selectProxyStatement->setColumns($this->getSelectProxySql());
        if ($join = $this->getJoinAssociations())
        {
            $this->selectProxyStatement->join = $join;
        }
        else
        {
            $this->selectProxyStatement->setTables($this->getFromSql());
        }
        $this->selectProxyStatement->setWhere($this->getWhereSql());
        return $this->selectProxyStatement;
    }

    public function getUpdateStatement()
    {
        $this->updateStatement = new MSQL();
        $this->updateStatement->setColumns($this->getUpdateSetSql());
        $this->updateStatement->setTables($this->getUpdateSql());
        $this->updateStatement->setWhere($this->getUpdateWhereSql());
        return $this->updateStatement;
    }

    public function getInsertStatement()
    {
        $this->insertStatement = new MSQL();
        $this->insertStatement->setColumns($this->getInsertValuesSql());
        $this->insertStatement->setTables($this->getInsertSql());
        return $this->insertStatement;
    }

    public function getDeleteStatement()
    {
        $this->deleteStatement = new MSQL();
        $this->deleteStatement->setTables($this->getDeleteSql());
        $this->deleteStatement->setWhere($this->getDeleteWhereSql());
        return $this->deleteStatement;
    }

    public function handleTypedAttribute($object, $operation)
    {
        for ($i = 0; $i < $this->getSize(); $i++)
        {
            $am = $this->getAttributeMap($i);
            if ($type = $am->getColumnType())
            {
                if ($cm = $am->getColumnMap())
                {
                    $cmd[] = array($object, $am->getName(), $operation , $type);
                }
            }
        }

        return $cmd;   
    }
}
?>
