<?php

class caseconverter implements IConverter
{
   private $case;

   public function caseconverter()
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
           case 'upper': $o = strtoupper((string)$value); break;
           case 'lower': $o = strtolower((string)$value); break;
       } 
       return $o;
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