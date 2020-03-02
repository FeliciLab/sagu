<?php

class Cursor
{
    private $position;

    private $rows;
    private $classMap;
    private $proxy;
    private $size;
    private $manager;
    private $baseObject;
    private $query;

    public function __construct($query, Classmap $classMap, $proxy = FALSE, PersistentManager $manager)
    {
        $this->position = 0;
        $this->query = $query;
        $this->query->moveFirst();
        $this->rows = $query->result;
        $this->size = (is_array($query->result)) ? count($query->result) : 0;
        $this->classMap = $classMap;
        $this->baseObject = $this->classMap->getObject();
        $this->proxy = $proxy;
        $this->manager = $manager;
    }

    public function getQuery()
    {
        return $this->query;
    }
 
    public function getRows()
    {
        return $this->rows;
    }

    public function getRow()
    {
        $row = NULL;
        if (!$this->query->eof())
        {
            $row = $this->query->getRowValues();
            $this->query->moveNext();
        }
        return $row;
    }

    public function retrieveObject($object)
    {
        if ($this->proxy)
            $this->classMap->retrieveProxyObject($object, $this->query);
        else
            $this->classMap->retrieveObject($object, $this->query);

        // Associations
        if ($this->classMap->getAssociationSize() > 0)
        {
            $db = $this->manager->getConnection($this->classMap->getDatabase());
            $this->manager->_retrieveAssociations($object, $this->classMap, $db);
        }
    }

    public function getObject()
    {
        $object = NULL;
        if (!$this->query->eof())
        {
            if ($this->baseObject == NULL)
            {
                $object = $this->getRow();
            }
            else
            {
                $object = clone $this->baseObject;
                $this->retrieveObject($object);
            }
            $this->query->moveNext();
        }
        return $object;
    }

    public function getObjects()
    {
        $array = array();
        $this->query->moveFirst();
        while (!$this->query->eof())
        {
            $object = clone $this->baseObject;
            $this->retrieveObject($object);
            $array[] = $object;
            $this->query->moveNext();
        }
        return $array;
    }

    public function getSize()
    {
        return $this->size;
    }
}
?>