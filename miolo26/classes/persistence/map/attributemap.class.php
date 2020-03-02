<?php

class AttributeMap
{
    private $columnMap = NULL;
    private $classMap;
    private $name;
    private $proxy;
    private $reference;
    private $index = NULL;
    private $type = NULL;

    public function __construct($name, $classMap)
    {
        $this->name = $name;
        $this->classMap = $classMap;
        $this->columnMap = NULL;
    }

    public function getClassMap()
    {
        return $this->classMap;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }

    public function setValue($object, $value)
    {
       if (($pos = strpos($this->name, '.')) !== FALSE)
       {
           $nested = substr($this->name,0,$pos);
           if (is_null($object->{$nested}))
           {
               $attribute    = substr($this->name,$pos+1);
               $classMap = $object->getClassMap(); 
               $aMap = $classMap->getAssociationMap($nested);
               $cm = $aMap->getForClass();
               $nestedObject = $cm->getObject(); 
               $object->{$nested} = $nestedObject;
           }
           else
           {
               $nestedObject = $object->{$nested};
           }
           $nestedObject->{$attribute} = $value;
       }
       elseif ($this->index)
       {
          $object->{$this->name}{$this->index} = $value;
       }
       else
       {
          $object->{$this->name} = $value;
       }
    }

    public function getValue($object)
    {
       if ($this->index)
       {
          $value = $object->{$this->name}{$this->index};
       }
       else
       {
          $value = $object->{$this->name};
       }
       return $value;
    }

    public function setProxy($value=NULL)
    {
        if ($value === NULL) $value = false;
        $this->proxy = ($value == 'true');
    }

    public function isProxy()
    {
        return $this->proxy;
    }

    public function setColumnMap($columnMap)
    {
        $this->columnMap = $columnMap;
    }

    public function setColumnType($columnType)
    {
        $this->type = (string)$columnType;
    }

    public function setReference($attributeMap)
    {
        $this->reference = $attributeMap;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getColumnMap()
    {
        return $this->columnMap;
    }

    public function getColumnType()
    {
        return $this->type;
    }
}
?>