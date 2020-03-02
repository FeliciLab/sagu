<?php

class ConverterLOB implements IConverter
{

    public function init($properties)
    {
    }

    public function convertFrom($value,$object)
    {
        $db = $object->getDb();
        $v = $db->LOBSave($value);
        return $v;
    }

    public function convertTo($value,$object)
    {
        return $value;
    }

    public function convertColumn($value,$object)
    {
        $db = $object->getDb();
        return $db->LOBLoad($value);
    }

    public function convertWhere($value,$object)
    {
        return $value;
    }

}
?>
