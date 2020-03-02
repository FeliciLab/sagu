<?php
interface IConverter
{
    public function init($properties);
    public function convertFrom($value,$object);
    public function convertTo($value,$object);
    public function convertColumn($value,$object);
    public function convertWhere($value,$object);
}
?>