<?php

class TableMap
{
    private $name;
    private $databaseMap;
    private $alias = '';

    public function __construct()
    {
    }

    public function setDatabaseMap($databaseMap)
    {
        $this->databaseMap = $databaseMap;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getName()
    {
        return $this->name;
    }
}
?>