<?php

class TableRow extends MControl
{
    public $cell;  // array of Cell object
    public $attributes;
    public $cellCount;

    public function __construct()
    {
       parent::__construct();
       $this->cell = array();
       $this->attributes = array (
          'align'=>'',
          'valign'=>''
       );
       $this->cssclass = 'ThemeTableRow';
       $this->cellCount = 0;
    }
  
    public function setAttributes($attr)
    {
       foreach($attr as $a=>$v) $this->attributes[$a] = $v;
    }

    public function setAttribute($attr,$value)
    {
       $this->attributes[$attr] = $value;
    }

    public function addCell()
    {
       $this->cell[$c = $this->cellCount++] = new TableCell();
       return $c;
    }

    public function removeCell($cell)
    {
       if (($this->cellCount > 0) && ($this->cellCount > $cell))
       {
          array_splice($this->cell,$cell, 1);
          $this->cellCount--;
       }
    }

    public function getCell($cell)
    {
       if ($this->ncells > $cell)
       {
          return $this->cell[$cell];
       }
       return null;
    }
}

?>