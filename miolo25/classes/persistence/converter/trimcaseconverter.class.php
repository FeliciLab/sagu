<?php

class trimcaseconverter implements IConverter
{
   private $case;

   public function trimcaseconverter()
   {
   }

   public function init($properties)
   {
      $this->case = $properties['case'];
   } 

   public function convertFrom($value,$object)
   {
       switch ($this->case)
       {
           case 'upper': $o = strtoupper(trim((string)$value)); break;
           case 'lower': $o = strtolower(trim((string)$value)); break;
       } 
       return $o;
   }

   public function convertTo($value,$object)
   {
       return strtoupper(trim((string)$value));
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