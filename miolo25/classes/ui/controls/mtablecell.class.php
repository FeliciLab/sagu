<?php

class TableCell extends MControl
{
    public $content;
    public $attributes;
    public $separator;
    public $contentCount;

    public function __construct()
    {
       parent::__construct();
       $this->attributes = array(); 
       $this->content = array(); 
       $this->contentCount = 0;  
       $this->cssclass = 'ThemeTableCell';
       $this->separator = '<br>';
    }

    public function setAttributes($attr)
    {
       foreach($attr as $a=>$v) $this->attributes[$a] = $v;
    }

    public function setAttribute($attr,$value)
    {
       $this->attributes[$attr] = $value;
    }

    public function setContent($content)
    {
       $this->content = array($content);
       $this->contentCount = 1;
    }

    public function getContent($pos=0)
    {
       if ($pos < $this->contentCount) {
          return $this->content[$pos];
       }
       return null;
    }

    public function clearContent()
    {
       $this->content = array();
       $this->contentCount = 0;
    }

    public function addContent($content)
    {
       $this->content[] = $content;
       $this->contentCount++;
    }
}
?>