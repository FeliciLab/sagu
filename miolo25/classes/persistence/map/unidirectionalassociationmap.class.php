<?php

class UniDirectionalAssociationMap
{
    private $targetName;
    private $databaseMap;
    private $forClass;
    private $associativeClass;
    private $cardinality;
    private $deleteAutomatic = FALSE;
    private $retrieveAutomatic = FALSE;
    private $saveAutomatic = FALSE;
    private $joinAutomatic = FALSE;
    private $inverse = FALSE;
    private $entries = array();
    private $direction = array();
    private $orderAttributes = NULL;
    private $indexAttribute;

    public function __construct()
    {
        $this->inverse = FALSE;
    }

    public function setForClass($classMap)
    {
        $this->forClass = $classMap;
    }

    public function getForClass()
    {
        return $this->forClass;
    }

    public function setAssociativeClass($classMap)
    {
        $this->associativeClass = $classMap;
    }

    public function getAssociativeClass()
    {
        return $this->associativeClass;
    }

    public function setTargetName($name)
    {
        $this->targetName = $name;
    }

    public function getTargetName()
    {
        return $this->targetName;
    }

    public function setTarget($attributeMap)
    {
        $this->target = $attributeMap;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setOrderAttributes($orderAttributes)
    {
        $this->orderAttributes = $orderAttributes;
    }

    public function getOrderAttributes()
    {
        return $this->orderAttributes;
    }

    public function setIndexAttribute($indexAttribute)
    {
        $this->indexAttribute = $indexAttribute;
    }

    public function getIndexAttribute()
    {
        return $this->indexAttribute;
    }

    public function setDeleteAutomatic($value = NULL)
    {
        if ($value === NULL)
            $value = FALSE;

        $this->deleteAutomatic = ($value == 'true');
    }

    public function setRetrieveAutomatic($value = NULL)
    {
        if ($value === NULL)
            $value = FALSE;

        $this->retrieveAutomatic = ($value == 'true');
    }

    public function setSaveAutomatic($value = NULL)
    {
        if ($value === NULL)
            $value = FALSE;

        $this->saveAutomatic = ($value == 'true');
    }

    public function setJoinAutomatic($value = NULL)
    {
        if ($value === NULL)
            $value = FALSE;

        if ($value === TRUE)
            $value = 'inner';

        $this->joinAutomatic = $value;
    }

    public function setInverse($value = NULL)
    {
        if ($value === NULL)
            $value = FALSE;

        $this->inverse = ($value == 'true') || ($value === TRUE);
    }

    public function isDeleteAutomatic()
    {
        return $this->deleteAutomatic;
    }

    public function isRetrieveAutomatic()
    {
        return $this->retrieveAutomatic;
    }

    public function isSaveAutomatic()
    {
        return $this->saveAutomatic;
    }

    public function isJoinAutomatic()
    {
        return $this->joinAutomatic;
    }

    public function getJoinAutomatic()
    {
        return $this->joinAutomatic;
    }

    public function isInverse()
    {
        return $this->inverse;
    }

    public function setCardinality($value = NULL)
    {
        if ($value === NULL)
            $value = 'oneToOne';

        $this->cardinality = $value;
    }

    public function getCardinality()
    {
        return $this->cardinality;
    }

    public function addEntry(&$udaEntry)
    {
        $this->entries[] = $udaEntry;
    }

    public function getEntry($index)
    {
        return $this->entries[$index];
    }

    public function addDirection($direction)
    {
        $this->direction[] = $direction;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function getSize()
    {
        return count($this->entries);
    }

    public function getCriteria($orderAttrs, $manager)
    {
        $criteria = new RetrieveCriteria($this->forClass, $manager);


        if ($this->cardinality == 'manyToMany')
        {
            $criteria->setAlias($this->direction[1],$this->forClass);
            $criteria->addAssociationMap($this->associativeClass, $this->direction[0], TRUE);
            $criteria->addAssociationMap($this->associativeClass, $this->direction[1], FALSE);
            $aMap = $this->associativeClass->getAssociationMap($this->direction[0]);
            $n = $aMap->getSize();
            for ($i = 0; $i < $n; $i++)
            {
                $criteria->addCriteria($aMap->getEntry($i)->getFrom(), '=', '?');
            }
        }
        else
        {
            $n = $this->getSize();
            if ($this->isInverse())
            {
                for ($i = 0; $i < $n; $i++)
                {
                    $criteria->addCriteria($this->getEntry($i)->getFrom()->getName(), '=', '?');
                }
            }
            else
            {
                for ($i = 0; $i < $n; $i++)
                {
                    $criteria->addCriteria($this->getEntry($i)->getTo()->getName(), '=', '?');
                }
            }
        }

        if (count($this->orderAttributes))
        {
            foreach ($this->orderAttributes as $order)
            {
                $criteria->addOrderEntry($order);
            }
        }

        return $criteria;
    }

    public function getCriteriaParameters($object)
    {
        $criteriaParameters = array();
        if ($this->cardinality == 'manyToMany')
        {
            $aMap = $this->associativeClass->getAssociationMap($this->direction[0]);
            $n = $aMap->getSize();
            for ($i = 0; $i < $n; $i++)
            {
                $criteriaParameters[] = $aMap->getEntry($i)->getTo()->getValue($object);
            }
        }
        else
        {
            $n = $this->getSize();
            if ($this->isInverse())
            {
                for ($i = 0; $i < $n; $i++)
                {
                    $criteriaParameters[] = $this->getEntry($i)->getTo()->getValue($object);
                }
            }
            else
            {
                for ($i = 0; $i < $n; $i++)
                {
                    $criteriaParameters[] = $this->getEntry($i)->getFrom()->getValue($object);
                }
            }
        }
        return $criteriaParameters;
    }
}
?>
