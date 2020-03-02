<?php

class ConverterUpperCase implements IConverter
{
   private $case;

   public function init($properties)
   {
      $this->case = $properties['case'];
   } 

   public function convertFrom($value,$object)
   {
       return strtoupper((string)$value);
   }

   public function convertTo($value,$object)
   {
       return strtoupper((string)$value);
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