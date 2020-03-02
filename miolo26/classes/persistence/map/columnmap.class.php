<?php

class ColumnMap
{
    private $name;
    private $database;
    private $keyType;
    private $tableMap;
    private $converter;
    private $idGenerator;

    public function __construct($name, $tableMap, $converter)
    {
        $this->name = $name;
        $this->tableMap = $tableMap;
        $this->converter = $converter;
    }

    public function setKeyType($type)
    {
        $this->keyType = $type;
    }

    public function getKeyType()
    {
        return $this->keyType;
    }

    public function setIdGenerator($idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function getIdGenerator()
    {
        return $this->idGenerator;
    }

    public function getTableMap()
    {
        return $this->tableMap;
    }

    public function setTableMap($tableMap)
    {
        $this->tableMap = $tableMap;
    }

    public function getConverter()
    {
        return $this->converter;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getFullyQualifiedName($alias = '')
    {
        $tableMap = $this->tableMap;

        if ($alias != '')
            $name = $alias . '.' . $this->name;
        else
            $name = $tableMap->getName() . '.' . $this->name;

        return $name;
    }

    public function getValueTo($value, $object)
    {
        return $this->converter->convertTo($value,$object);
    }

    public function getValueFrom($value, $object)
    {
        return $this->converter->convertFrom($value,$object);
    }

    public function getColumnName($criteriaAlias = '', $as = TRUE, $object)
    {
        $fullyName =  $this->getFullyQualifiedName($criteriaAlias);
        $name = $this->converter->convertColumn($fullyName,$object);
        if ($as && ($name != $fullyName)) // need a "as" clause
        {
            $name .= ' AS ' . $this->name;
        }
        return $name;
    }

    public function getColumnWhereName($criteriaAlias = '', $object)
    {
        $fullyName =  $this->getFullyQualifiedName($criteriaAlias);
        $name = $this->converter->convertWhere($fullyName,$object);
        return $name;
    }

}
?>
