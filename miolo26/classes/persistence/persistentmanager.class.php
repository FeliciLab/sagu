<?php

class PersistentManager
{
    private $factory;
    private $active = FALSE;
    private $closed = FALSE;
    private $dbConnections = array();

    public function __construct(PersistentManagerFactory $factory)
    {
        $this->factory = $factory;
    }

    private function execute(MDatabase $db, $commands, $transaction = NULL)
    {
        $this->factory->miolo->profileEnter('PersistentManager::execute');
        if (!is_array($commands))
        {
            $commands = array($commands);
        } 
        if ($newTransaction = is_null($transaction))
        { 
            $transaction = $db->getTransaction();
        }
        $batch = $transaction->isBatch();
        foreach ($commands as $command)
        {
            $batch ? $transaction->addCommand($command) : $db->execute($command);
        }
        if ($newTransaction)
        { 
            $transaction->process();
        }
        $this->factory->miolo->profileExit('PersistentManager::execute');
    }

    public function retrieveObject(PersistentObject $object)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());
        $this->_retrieveObject($object, $classMap, $db);
    }

    public function retrieveObjectFromQuery(PersistentObject $object, MQuery $query)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());

        if (!$query->eof)
        {
            $classMap->retrieveObject($object, $query);
            $this->_retrieveAssociations($object, $classMap, $db);
        }
    }

    public function retrieveObjectFromCriteria(PersistentObject $object, PersistentCriteria $criteria, $parameters=NULL)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());
        $query = $this->processCriteriaQuery($criteria, $parameters, $db, FALSE);
        if (!$query->eof)
        {
            $classMap->retrieveObject($object, $query);
            $this->_retrieveAssociations($object, $classMap, $db);
        }
    }

    public function retrieveObjectAsProxy(PersistentObject $object)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());
        $this->_retrieveObjectAsProxy($object, $classMap, $db);
    }

    public function retrieveAssociations(PersistentObject $object)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());
        $this->_retrieveAssociations($object, $classMap, $db);
    }

    public function retrieveAssociation(PersistentObject $object, $target)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());
        $this->_retrieveAssociation($object, $target, $classMap, $db);
    }

    public function retrieveAssociationAsCursor(PersistentObject $object, $target)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());
        $this->_retrieveAssociationAsCursor($object, $target, $classMap, $db);
    }

    public function deleteAssociation(PersistentObject $object, $target, PersistentObject $assocObject)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());
        $commands = array();
        $this->_deleteAssociation($object, $target, $assocObject, $commands, $classMap, $db);
        $this->execute($db, $commands, $object->getTransaction());
    }

    public function deleteAssociationObject(PersistentObject $object, $target)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());
        $commands = array();
        $this->_deleteAssociationObject($object, $target, $commands, $classMap, $db);
        $this->execute($db, $commands, $object->getTransaction());
    }

    public function saveAssociation(PersistentObject $object, $target)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());
        $commands = array();
        $this->_saveAssociation($object, $target, $commands, $classMap, $db);
        $this->execute($db, $commands, $object->getTransaction());
    }

    public function saveObject(PersistentObject $object)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());
        $commands = array();
        $this->_saveObject($object, $classMap, $commands, $db);
        $this->execute($db, $commands, $object->getTransaction());
    }

    public function saveObjectRaw(PersistentObject $object)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());
        $commands = array();
        $this->_saveObjectRaw($object, $classMap, $commands, $db);
        $this->execute($db, $commands, $object->getTransaction());
    }

    public function deleteObject(PersistentObject $object)
    {
        $classMap = $this->factory->getClassMap($object);
        $db = $this->getConnection($classMap->getDatabase());
        $commands = array();
        $this->_deleteObject($object, $classMap, $commands, $db);
        $this->execute($db, $commands, $object->getTransaction());
    }

    private function _retrieveObject(PersistentObject $object, ClassMap $classMap, MDatabase $db, $isLock = FALSE)
    {
        $statement = $classMap->getSelectSqlFor($object);
        $query = $db->getQuery($statement);
        if (!$query->eof)
        {
            $classMap->retrieveObject($object, $query);
            $this->_retrieveAssociations($object, $classMap, $db);
        }
    }

    private function _retrieveObjectAsProxy(PersistentObject $object, ClassMap $classMap, MDatabase $db, $isLock = FALSE)
    {
        $statement = $classMap->getSelectProxySqlFor($object);
        $query = $db->getQuery($statement);

        if (!$query->eof)
        {
            $classMap->retrieveProxyObject($object, $query);
            $this->_retrieveAssociations($object, $classMap, $db);
        }
    }

    public function _retrieveAssociations(PersistentObject $object, ClassMap $classMap, MDatabase $db)
    {
        if ($classMap->getSuperClass() != NULL)
        { 
            $this->_retrieveAssociations($object, $classMap->getSuperClass(), $db);
        }
        $associations = $classMap->getAssociationMaps();
        foreach ($associations as $aMap)
        {
            if ($aMap->isRetrieveAutomatic() && !$aMap->isJoinAutomatic())
            {
                $this->__retrieveAssociation($object, $aMap, $classMap, $db);
            } 
        }
    }

    private function _retrieveAssociation(PersistentObject $object, $target, ClassMap $classMap, MDatabase $db)
    {
        $aMap = $classMap->getAssociationMap($target);
        if (is_null($aMap))
        {
            throw new EPersistentManagerException("Association name for target $target not found");
        } 
        if (is_null($aMap->getTarget()))
        {
            throw new EPersistentManagerException("Target attribute with name $target not found");
        }
        $this->__retrieveAssociation($object, $aMap, $classMap, $db);
    }

    private function _retrieveAssociationAsCursor(PersistentObject $object, $target, ClassMap $classMap, MDatabase $db)
    {
        $aMap = $classMap->getAssociationMap($target);
        if (is_null($aMap))
        {
            throw new EPersistentManagerException("Association name for target $target not found");
        } 
        if (is_null($aMap->getTarget()))
        {
            throw new EPersistentManagerException("Target attribute with name $target not found");
        }
        $orderAttributes = $aMap->getOrderAttributes();
        $criteria = $aMap->getCriteria($orderAttributes, $this);
        $criteriaParameters = $aMap->getCriteriaParameters($object);
        $cursor = $this->processCriteriaCursor($criteria, $criteriaParameters, $db, FALSE);
        $aMap->getTarget()->setValue($object, $cursor);
    }

    private function _deleteAssociation(PersistentObject $object, $target, PersistentObject $assocObject, &$commands, ClassMap $classMap, MDatabase $db)
    {
        $aMap = $classMap->getAssociationMap($target);
        if (is_null($aMap))
        {
            throw new EPersistentManagerException("Association name for target $target not found");
        } 
        if (is_null($aMap->getTarget()))
        {
            throw new EPersistentManagerException("Target attribute with name $target not found");
        }
        $this->__deleteAssociation($object, $aMap, $assocObject, $commands, $classMap, $db);
    }

    private function _deleteAssociationObject(PersistentObject $object, $target, &$commands, ClassMap $classMap, MDatabase $db)
    {
        $aMap = $classMap->getAssociationMap($target);
        if (is_null($aMap))
        {
            throw new EPersistentManagerException("Association name for target $target not found");
        } 
        if (is_null($aMap->getTarget()))
        {
            throw new EPersistentManagerException("Target attribute with name $target not found");
        }
        $this->__deleteAssociationObject($object, $aMap, $commands, $classMap, $db);
    }

    private function _saveAssociation(PersistentObject $object, $target, &$commands, ClassMap $classMap, MDatabase $db)
    {
        $aMap = $classMap->getAssociationMap($target);
        if (is_null($aMap))
        {
            throw new EPersistentManagerException("Association name for target $target not found");
        } 
        if (is_null($aMap->getTarget()))
        {
            throw new EPersistentManagerException("Target attribute with name $target not found");
        }
        $this->__saveAssociation($object, $aMap, $commands, $classMap, $db);
    }

    private function __retrieveAssociation(PersistentObject $object, UniDirectionalAssociationMap $aMap, ClassMap $classMap, MDatabase $db)
    {
        $orderAttributes = $aMap->getOrderAttributes();
        $criteria = $aMap->getCriteria($orderAttributes, $this);
        $criteriaParameters = $aMap->getCriteriaParameters($object);
        $cursor = $this->processCriteriaCursor($criteria, $criteriaParameters, $db, FALSE);

        if ($aMap->getCardinality() == 'oneToOne')
        {
            $value = $cursor->getObject();
            $target = $aMap->getTarget();
            $target->setValue($object, $value);
        }
        elseif (($aMap->getCardinality() == 'oneToMany') || ($aMap->getCardinality() == 'manyToMany'))
        {
            $target = $aMap->getTarget();
            $i = $aMap->getIndexAttribute();
            while ($o = $cursor->getObject())
            {
                if (!is_null($i))
                    $value[$o->$i] = $o;
                else
                    $value[] = $o;
            }
            $target->setValue($object, $value);
        }
    }

    private function __deleteAssociation(PersistentObject $object, UniDirectionalAssociationMap $aMap, PersistentObject $assocObject, &$commands, ClassMap $classMap, MDatabase $db)
    {
        if (($aMap->getCardinality() == 'oneToOne') || ($aMap->getCardinality() == 'oneToMany'))
        {
            if ($aMap->isInverse())
            {
                $classMap = $this->factory->getClassMap($assocObject);

                for ($i = 0; $i < $aMap->getSize(); $i++)
                {
                    $aMap->getEntry($i)->getFrom()->setValue($assocObject, ':NULL');
                }

                $statement = $classMap->getUpdateSqlFor($assocObject);
                $commands[] = $statement->update();
            }
            else
            {
                $target = $aMap->getTarget();
                $target->setValue($object, NULL);

                for ($i = 0; $i < $aMap->getSize(); $i++)
                {
                    $aMap->getEntry($i)->getFrom()->setValue($object, ':NULL');
                }

                $statement = $classMap->getUpdateSqlFor($object);
                $commands[] = $statement->update();
            }
        }
        elseif ($aMap->getCardinality() == 'manyToMany')
        {
            $associativeClassMap = $aMap->getAssociativeClass();
            $associativeObjectClassMap = $assocObject->getClassMap();
            $criteria = new DeleteCriteria($associativeClassMap, $this);
            $direction = $aMap->getDirection();
            $amA = $associativeClassMap->getAssociationMap($direction[0]);

            for ($i = 0; $i < $amA->getSize(); $i++)
            {
                $amTo = $amA->getEntry($i)->getTo();
                $keyValue = $amTo->getValue($object);
                $amFrom = $amA->getEntry($i)->getFrom();
                $criteria->addCriteria($amFrom, '=', $keyValue);
            }

            $amA = $associativeClassMap->getAssociationMap($direction[1]);

            for ($i = 0; $i < $amA->getSize(); $i++)
            {
                $amTo = $amA->getEntry($i)->getTo();
                $keyValue = $amTo->getValue($assocObject);
                $amFrom = $amA->getEntry($i)->getFrom();
                $criteria->addCriteria($amFrom, '=', $keyValue);
            }

            $commands[] = $criteria->getSqlStatement()->delete();
        }

        $this->__retrieveAssociation($object, $aMap, $classMap, $db);
    }

    private function __deleteAssociationObject(PersistentObject $object, UniDirectionalAssociationMap $aMap, &$commands, ClassMap $classMap, MDatabase $db)
    {
        $forClassMap = $aMap->getForClass();
        $forObject = $forClassMap->getObject();
        $criteria = new DeleteCriteria($forClassMap, $this);
        for ($i = 0; $i < $aMap->getSize(); $i++)
        {
            $am = $aMap->getEntry($i)->getFrom();
            $keyValue = $am->getValue($object);
            $criteria->addCriteria($am, '=', $keyValue);
        }
        $commands[] = $criteria->getSqlStatement()->delete();
        $this->__retrieveAssociation($object, $aMap, $classMap, $db);
    }

    private function __saveStraightAssociation(PersistentObject $object, UniDirectionalAssociationMap $aMap, &$commands, ClassMap $classMap, MDatabase $db)
    {
        if ($aMap->getCardinality() == 'oneToOne')
        {
            $value = $aMap->getTarget()->getValue($object);
            if ($value != NULL)
            {
                $this->_saveObject($value, $aMap->getForClass(), $commands, $db);
                for ($i = 0; $i < $aMap->getSize(); $i++)
                {
                    $aMap->getEntry($i)->getFrom()->setValue($object,  $aMap->getEntry($i)->getTo()->getValue($value));
                }
            }
        }
        elseif ($aMap->getCardinality() == 'oneToMany')
        {
            $collection = $aMap->getTarget()->getValue($object);
            if (count($collection) > 0)
            {
                foreach ($collection as $value)
                {
                    $this->_saveObject($value, $aMap->getForClass(), $commands, $db);
                    for ($i = 0; $i < $aMap->getSize(); $i++)
                    {
                        $aMap->getEntry($i)->getFrom()->setValue($value, $aMap->getEntry($i)->getTo()->getValue($object));
                    }
                }
            }
        }
        elseif ($aMap->getCardinality() == 'manyToMany')
        {
            $commands = array();
            $collection = $aMap->getTarget()->getValue($object);
            if (count($collection) > 0)
            {
                $associativeClassMap = $aMap->getAssociativeClass();
                $associativeObject = $associativeClassMap->getObject();
                $criteria = new DeleteCriteria($associativeClassMap, $this);
                $direction = $aMap->getDirection();
                $amA = $associativeClassMap->getAssociationMap($direction[0]);
                for ($i = 0; $i < $amA->getSize(); $i++)
                {
                    $pm = $amA->getEntry($i)->getFrom();
                    $am = $amA->getEntry($i)->getTo();
                    $keyValue = $am->getValue($object);
                    $criteria->addCriteria($pm, '=', $keyValue);
                }
                $commands[] = $criteria->getSqlStatement()->delete();
                foreach ($collection as $value)
                {
                    if (!$value)
                        continue;
                    $amA = $associativeClassMap->getAssociationMap($direction[0]);
                    for ($i = 0; $i < $amA->getSize(); $i++)
                    {
                        $pm = $amA->getEntry($i)->getFrom();
                        $am = $amA->getEntry($i)->getTo();
                        $pm->setValue($associativeObject, $am->getValue($object));
                    }
                    $pmA = $associativeClassMap->getAssociationMap($direction[1]);
                    for ($i = 0; $i < $pmA->getSize(); $i++)
                    {
                        $pm = $pmA->getEntry($i)->getFrom();
                        $qm = $pmA->getEntry($i)->getTo();
                        $pm->setValue($associativeObject, $qm->getValue($value));
                    }
                    $statement = $associativeClassMap->getInsertSqlFor($associativeObject);
                    $commands[] = $statement->insert();
                }
            }
        }
    }

    private function __saveInverseAssociation(PersistentObject $object, UniDirectionalAssociationMap $aMap, &$commands, ClassMap $classMap, MDatabase $db)
    {
        if ($aMap->getCardinality() == 'oneToOne')
        {
            $value = $aMap->getTarget()->getValue($object);
            if ($value != NULL)
            {
                for ($i = 0; $i < $aMap->getSize(); $i++)
                {
                    $aMap->getEntry($i)->getFrom()->setValue($value, $aMap->getEntry($i)->getTo()->getValue($value));
                }
                $this->_saveObject($value, $aMap->getForClass(), $commands, $db);
            }
        }
        elseif (($aMap->getCardinality() == 'oneToMany') || ($aMap->getCardinality() == 'manyToMany'))
        {
            $collection = $aMap->getTarget()->getValue($object);
            if (count($collection) > 0)
            {
                foreach ($collection as $value)
                {
                    for ($i = 0; $i < $aMap->getSize(); $i++)
                    {
                        $aMap->getEntry($i)->getFrom()->setValue($value, $aMap->getEntry($i)->getTo()->getValue($object));
                    }
                    $this->_saveObject($value, $aMap->getForClass(), $commands, $db);
                }
            }
        }
    }

    private function __saveAssociation(PersistentObject $object, UniDirectionalAssociationMap $aMap, &$commands, ClassMap $classMap, MDatabase $db)
    {
        if ($aMap->isInverse())
        {
            $this->__saveInverseAssociation($object, $aMap, $commands, $classMap, $db);
        }
        else
        {
            $this->__saveStraightAssociation($object, $aMap, $commands, $classMap, $db);
        }
    }

    private function _saveObject(PersistentObject $object, ClassMap $classMap, &$commands, MDatabase $db)
    {
        if ($classMap->getSuperClass() != NULL)
        {
            $isPersistent = $object->isPersistent();
            $this->_saveObject($object, $classMap->getSuperClass(), $commands, $db);
            $object->setPersistent($isPersistent);
        }

        if ($object->isPersistent())
        {
            $statement = $classMap->getUpdateSqlFor($object);
            $commands[] = $statement->update();
            if ($classMap->getHasTypedAttribute())
            {
                $commands[] = $classMap->handleTypedAttribute($object, 'update');
            }
        }
        else
        {
            for ($i = 0; $i < $classMap->getKeySize(); $i++)
            {
                $keyAttribute = $classMap->getKeyAttributeMap($i);

                if ($keyAttribute->getColumnMap()->getKeyType() != 'primary')
                    continue;
                else
                {
                    if ($keyAttribute->getColumnMap()->getIdGenerator() != NULL)
                        $value = $db->getNewId($keyAttribute->getColumnMap()->getIdGenerator());
                    else
                        $value = $keyAttribute->getValue($object);

                    $keyAttribute->setValue($object, $value);
                }
            }

            $statement = $classMap->getInsertSqlFor($object);
            $commands[] = $statement->insert();
            if ($classMap->getHasTypedAttribute())
            {
                $commands[] = $classMap->handleTypedAttribute($object, 'insert');
            }
        }
        $mmCmd = array();

        $associations = $classMap->getStraightAssociationMaps();
        foreach ($associations as $aMap)
        {
            if ($aMap->isSaveAutomatic())
            {
                $this->__saveStraightAssociation($object, $aMap, $mmCmd, $classMap, $db);
            }
        }

        $associations = $classMap->getInverseAssociationMaps();
        foreach ($associations as $aMap)
        {
            if ($aMap->isSaveAutomatic())
            { 
                $this->__saveInverseAssociation($object, $aMap, $mmCmd, $classMap, $db);
            }
        }

        if (count($mmCmd))
        {
            $commands = array_merge($commands, $mmCmd);
        } 
        $object->setPersistent(true);
    }

    private function _saveObjectRaw(PersistentObject $object, ClassMap $classMap, &$commands, MDatabase $db)
    {
        if ($object->isPersistent())
        {
            $statement = $classMap->getUpdateSqlFor($object);
            $commands[] = $statement->update();
        }
        else
        {
            for ($i = 0; $i < $classMap->getKeySize(); $i++)
            {
                $keyAttribute = $classMap->getKeyAttributeMap($i);

                if ($keyAttribute->getColumnMap()->getKeyType() != 'primary')
                    continue;
                else
                {
                    if ($keyAttribute->getColumnMap()->getIdGenerator() != NULL)
                        $value = $db->getNewId($keyAttribute->getColumnMap()->getIdGenerator());
                    else
                        $value = $keyAttribute->getValue($object);

                    $keyAttribute->setValue($object, $value);
                }
            }

            $statement = $classMap->getInsertSqlFor($object);
            $commands[] = $statement->insert();
        }

        $object->setPersistent(true);
    }

    private function _deleteObject(PersistentObject $object, ClassMap $classMap, &$commands, MDatabase $db)
    {
        $associations = $classMap->getStraightAssociationMaps();

        $mmCmd = array();
        foreach ($associations as $aMap)
        {
            if (!$aMap->isDeleteAutomatic())
                continue;

            if ($aMap->getCardinality() == 'oneToOne')
            {
                $value = $aMap->getTarget()->getValue($object);

                if ($value != NULL)
                {
                    $this->_deleteObject($value, $aMap->getForClass(), $commands, $db);

                    for ($i = 0; $i < $aMap->getSize(); $i++)
                    {
                        $aMap->getEntry($i)->getFrom()->setValue($object, NULL);
                    }
                }
            }
            elseif ($aMap->getCardinality() == 'oneToMany')
            {
                $collection = $aMap->getTarget()->getValue($object);

                if (count($collection) > 0)
                {
                    foreach ($collection as $value)
                    {
                        $this->_deleteObject($value, $aMap->getForClass(), $commands, $db);

                        for ($i = 0; $i < $aMap->getSize(); $i++)
                        {
                            $aMap->getEntry($i)->getFrom()->setValue($value, NULL);
                        }
                    }
                }
            }
            elseif ($aMap->getCardinality() == 'manyToMany')
            {
                $associativeClassMap = $aMap->getAssociativeClass();
                $associativeObject = $associativeClassMap->getObject();
                $criteria = new DeleteCriteria($associativeClassMap, $this);
                $direction = $aMap->getDirection();
                $am = $associativeClassMap->getAssociationMap($direction[0]);
                for ($i = 0; $i < $am->getSize(); $i++)
                {
                    $amTo = $am->getEntry($i)->getTo();
                    $keyValue = $amTo->getValue($object);
                    $amFrom = $am->getEntry($i)->getFrom();
                    $criteria->addCriteria($amFrom, '=', $keyValue);
                }
                $mmCmd[] = $criteria->getSqlStatement()->delete();
            }
        }

        $associations = $classMap->getInverseAssociationMaps();

        foreach ($associations as $aMap)
        {
            if (!$aMap->isDeleteAutomatic())
                continue;

            if ($aMap->getCardinality() == 'oneToOne')
            {
                $value = $aMap->getTarget()->getValue($object);

                if ($value != NULL)
                {
                    for ($i = 0; $i < $aMap->getSize(); $i++)
                    {
                        $aMap->getEntry($i)->getFrom()->setValue($value, NULL);
                    }

                    $this->_deleteObject($value, $aMap->getForClass(), $commands, $db);
                }
            }
            elseif (($aMap->getCardinality() == 'oneToMany') || ($aMap->getCardinality() == 'manyToMany'))
            {
                $collection = $aMap->getTarget()->getValue($object);

                if (count($collection) > 0)
                {
                    foreach ($collection as $value)
                    {
                        for ($i = 0; $i < $aMap->getSize(); $i++)
                        {
                            $aMap->getEntry($i)->getFrom()->setValue($value, NULL);
                        }

                        $this->_deleteObject($value, $aMap->getForClass(), $commands, $db);
                    }
                }
            }
        }

        $statement = $classMap->getDeleteSqlFor($object);
        $commands[] = $statement->delete();

        if (count($mmCmd))
            $commands = array_merge($mmCmd, $commands);

        if ($classMap->getSuperClass() != NULL)
        {
            $this->_deleteObject($object, $classMap->getSuperClass(), $commands, $db);
        }

        $object->setPersistent(FALSE);
    }

    private function processCriteriaQuery(PersistentCriteria $criteria, $parameters, MDatabase $db, $forProxy = FALSE)
    {
        $statement = $criteria->getSqlStatement($forProxy);
        $statement->setParameters($parameters);
        $query = $db->getQuery($statement);
        return $query;
    }

    private function processCriteriaCursor(PersistentCriteria $criteria, $parameters, MDatabase $db, $forProxy = FALSE)
    {
        $query = $this->processCriteriaQuery($criteria, $parameters, $db, $forProxy);
        $cursor = new Cursor($query, $criteria->getClassMap(), $forProxy, $this);
        return $cursor;
    }

    public function getRetrieveCriteria(PersistentObject $object)
    {
        $classMap = $this->factory->getClassMap($object);
        $criteria = new RetrieveCriteria($classMap, $this);
        return $criteria;
    }

    public function getDeleteCriteria(PersistentObject $object)
    {
        $classMap = $this->factory->getClassMap($object);
        $criteria = new DeleteCriteria($classMap, $this);
        $criteria->setTransaction($object->getTransaction());
        return $criteria;
    }

    public function getUpdateCriteria(PersistentObject $object)
    {
        $classMap = $this->factory->getClassMap($object);
        $criteria = new UpdateCriteria($classMap, $this);
        $criteria->setTransaction($object->getTransaction());
        return $criteria;
    }

    public function processCriteriaDelete(DeleteCriteria $criteria, $parameters)
    {
        $db = $this->getConnection($criteria->getClassMap()->getDatabase());
        $statement = $criteria->getSqlStatement();
        $statement->setParameters($parameters);
        $this->execute($db, $statement->delete(), $criteria->getTransaction());
    }

    public function processCriteriaUpdate(UpdateCriteria $criteria, $parameters)
    {
        $db = $this->getConnection($criteria->getClassMap()->getDatabase());
        $statement = $criteria->getSqlStatement();
        $statement->setParameters($parameters);
        $this->execute($db, $statement->update(), $criteria->getTransaction());
        $MIOLO = new Miolo;
    }

    public function processCriteriaAsQuery(PersistentCriteria $criteria, $parameters)
    {
        $db = $this->getConnection($criteria->getClassMap()->getDatabase());
        $query = $this->processCriteriaQuery($criteria, $parameters, $db, FALSE);
        return $query;
    }

    public function processCriteriaAsCursor(PersistentCriteria $criteria, $parameters)
    {
        $db = $this->getConnection($criteria->getClassMap()->getDatabase());
        $cursor = $this->processCriteriaCursor($criteria, $parameters, $db, FALSE);
        return $cursor;
    }

    public function processCriteriaAsProxyQuery(PersistentCriteria $criteria, $parameters)
    {
        $db = $this->getConnection($criteria->getClassMap()->getDatabase());
        $query = $this->processCriteriaQuery($criteria, $parameters, $db, true);
        return $query;
    }

    public function processCriteriaAsProxyCursor(PersistentCriteria $criteria, $parameters)
    {
        $db = $this->getConnection($criteria->getClassMap()->getDatabase());
        $cursor = $this->processCriteriaCursor($criteria, $parameters, $db, true);
        return $cursor;
    }

    public function handleLobAttribute($object, $attribute, $value, $operation)
    {
        $db = $object->getDB();
        $db->handleLOB($object, $attribute, $value, $operation);
    }

    public function getConnection($dbName)
    {
        if ($this->closed)
        {
            throw new EPersistenManagerException("Persistent Manager is closed!");
        }
        if ($this->active)
        {
            if (($conn = $this->dbConnections[$dbName]) == NULL)
            {
                $conn = $this->factory->miolo->getDatabase($dbName);
                $this->dbConnections[$dbName] = $conn;
            }
        }
        else
        {
            $conn = $this->factory->miolo->getDatabase($dbName);
        }

        return $conn;
    }
}
?>