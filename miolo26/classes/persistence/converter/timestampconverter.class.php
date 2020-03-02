<?php

class timestampconverter implements IConverter
{
    private $db;

    public function timestampconverter()
    {
    }

    public function init($properties)
    {
        $MIOLO = MIOLO::getInstance();
        $this->db = $MIOLO->getDatabase($properties['database']);
    }

    public function convertFrom($value,$object)
    {
        return strtoupper($this->db->charToTimestamp(trim((string)$value)));
    }

    public function convertTo($value,$object)
    {
        return $value;
    }

    public function convertColumn($value,$object)
    {
        return strtoupper($this->db->timestampToChar(trim((string)$value)));
    }

    public function convertWhere($value,$object)
    {
        return $value;
    }

}
?>