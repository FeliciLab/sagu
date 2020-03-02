<?php

class MImage extends MFormControl
{
    public $location;

    public function __construct($name = NULL, $label = NULL, $location = NULL, $attrs = NULL)
    {
        parent::__construct( $name, '', $label );
        
        $this->location = $location;
        $this->setAttributes($attrs);
        $this->addAttribute('border', '0');
    }


    public function generateInner()
    {
        $this->inner = $this->getRender('image');
    }
}
?>