<?php

class MImageFormLabel extends MDiv
{
    public $image;
    public $label;

    public function __construct($name = NULL, $label = NULL, $location = NULL, $attrs = NULL)
    {
        parent::__construct();
        $this->image = new MImage($name, $label, $location, $attrs);
        $this->label = new MLabel($label);
        $this->setClass('mImageCentered');
    }

    public function generateInner()
    {
        $this->inner = $this->image->generate() . $this->painter->BR . $this->label->generate();
    }
}

?>