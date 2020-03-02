<?php

class TrivialConverter implements IConverter
{
    public function trivialConverter()
    {
    }

    public function init($properties)
    {
    }

    public function convertFrom($value,$object)
    {
        return $value;
    }

    public function convertTo($value,$object)
    {
        return $value;
    }

    public function convertColumn($value,$object)
    {
        return $value;
    }

    public function convertWhere($value,$object)
    {
        return $value;
    }
}
?>