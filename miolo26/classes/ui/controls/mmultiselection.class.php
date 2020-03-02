<?php
class MMultiSelection extends MListControl
{       
    public $size;

    public function __construct($name='', $values=Array('1','2','3'), 
               $label='&nbsp;', $options=Array('Option1','Option2','Option3'),
               $showValues=false, $hint='', $size='3')
    {
        parent::__construct($name,$label,$options,$showValues,$hint);
        $this->size       = $size;
        $this->value      = $values; // is this still necessary?
    }

    public function getName()
    {
        return (strpos($this->name, '[]') !== false ? $this->name : $this->name.'[]');
    }

    public function generateInner()
    {       
        if ( !is_array($this->value) )
        {
            echo $this->painter->div(new MDiv('',_M("Values must be an single-dimensional array"),'alert'));
        }
        if ( !is_array($this->options) )
        {
            echo $this->painter->div(new MDiv('',_M("Options must be an {single,multi}-dimensional array"),'alert'));
        }
        $this->content = $this->generateOptions();
        $this->addAttribute('multiple','');
        $this->addAttribute('size', $this->size);
        if ($this->getClass() == '')
        {
            $this->setClass('mSelection');
        }
        $this->inner = $this->generateLabel() . $this->getRender('select');
    }
}

?>