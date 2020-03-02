<?php

class PersistentObject
{
    private $isPersistent;
    private $isProxy;
    private $timeStamp;

    /**
     * @var PersistentManager Manager instance.
     */
    private $manager;

    private $factory;

    public function __construct()
    {
        $miolo = MIOLO::getInstance();
        $this->factory = $miolo->persistence;
        $this->manager = $this->factory->getPersistentManager();
    }

    public function getClassMap()
    {
        return $this->factory->getClassMap($this);
    }

    public function setPersistent($value)
    {
        $this->isPersistent = $value;
    }

    public function isPersistent()
    {
        return $this->isPersistent;
    }

    public function setProxy($value)
    {
        $this->isProxy = $value;
    }

    public function isProxy()
    {
        return $this->isProxy;
    }

    public function retrieve()
    {
        $this->manager->retrieveObject($this);
    }

    public function retrieveFromQuery(MQuery $query)
    {
        $this->manager->retrieveObjectFromQuery($this, $query);
    }

    public function retrieveFromCriteria(PersistentCriteria $criteria, $parameters=NULL)
    {
        $this->manager->retrieveObjectFromCriteria($this, $criteria, $parameters);
    }

    public function retrieveAssociation($target, $orderAttributes = null)
    {
        $this->manager->retrieveAssociation($this, $target);
    }

    public function retrieveAssociationAsCursor($target, $orderAttributes = null)
    {
        $this->manager->retrieveAssociationAsCursor($this, $target);
    }

    public function retrieveAsProxy()
    {
        $this->manager->retrieveObjectAsProxy($this);
    }

    /**
     * @return RetrieveCriteria Criteria instance.
     */
    public function getCriteria()
    {
        return $this->manager->getRetrieveCriteria($this);
    }

    public function getDeleteCriteria()
    {
        return $this->manager->getDeleteCriteria($this);
    }

    public function getUpdateCriteria()
    {
        return $this->manager->getUpdateCriteria($this);
    }

    public function update()
    {
        $this->manager->saveObjectRaw($this);
    }

    public function save()
    {
        $this->manager->saveObject($this);
    }

    public function saveAssociation($target)
    {
        $this->manager->saveAssociation($this, $target);
    }

    public function delete()
    {
        $this->manager->deleteObject($this);
    }

    public function deleteAssociation($target, $object)
    {
        $this->manager->deleteAssociation($this, $target, $object);
    }

    public function deleteAssociationObject($target)
    {
        $this->manager->deleteAssociationObject($this, $target);
    }

    public function handleLOBAttribute($attribute, $value, $operation)
    {
        $this->manager->handleLOBAttribute($this, $attribute, $value, $operation);
    }

}
?>