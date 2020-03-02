<?php

class dateconverter implements IConverter
{
    private $db;

    public function dateconverter()
    {
    }

    public function init($properties)
    {
        $MIOLO = MIOLO::getInstance();
        $this->db = $MIOLO->getDatabase($properties['database']);
    }

    public function convertFrom($value,$object)
    {
        return strtoupper($this->db->charToDate(trim((string)$value)));
    }

    public function convertTo($value,$object)
    {
        return $value;
    }

    public function convertColumn($value,$object)
    {
        return strtoupper($this->db->dateToChar(trim((string)$value)));
    }

    public function convertWhere($value,$object)
    {
       return strtoupper($this->db->dateToChar(trim((string)$value), 'YYYY/MM/DD'));
    }

}
?>
