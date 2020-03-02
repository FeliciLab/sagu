<?php

class ConverterBirth implements IConverter
{

    public function init($properties)
    {
    }

    public function convertFrom($value,$object)
    {
        $db = $object->getDb();
        $v = $value == '' ? ':NULL' : strtoupper($db->charToDate(trim((string)$value)));
        return $v;
    }

    public function convertTo($value,$object)
    {
        return $value;
    }

    public function convertColumn($value,$object)
    {
        $db = $object->getDb();
        return strtoupper($db->dateToChar(trim((string)$value)));
    }

    public function convertWhere($value,$object)
    {
        $db = $object->getDb();
        return strtoupper($db->dateToChar(trim((string)$value), 'YYYY/MM/DD'));
    }

}
?>
