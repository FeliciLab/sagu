<?php

#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# Table
#    Base Class for html tables
#    - Rows, Cols and Content are 0-based
#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

class MTable extends MControl
{
    public $body;
    public $head;
    public $foot;
    public $colgroup;
    public $attr;

    public function __construct($body=NULL, $tableAttr=NULL, $attr=NULL, $head=NULL, $foot=NULL, $colgroup=NULL)
    {
        parent::__construct();
        $this->body = $body;
        $this->head = $head;
        $this->foot = $foot;
        $this->colgroup = $colgroup;
        $this->setAttributes($tableAttr);
        $this->attr = $attr;
    }

    public function setBody($value)
    {
        $this->body = $value;
    }

    public function setHead($value)
    {
        $this->head = $value;
    }

    public function setFoot($value)
    {
        $this->foot = $value;
    }

    public function setColGroup($value)
    {
        $this->colgroup = $value;
    }

    public function setAttr($value)
    {
        $this->attr = $value;
    }


}

?>