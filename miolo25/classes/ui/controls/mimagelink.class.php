<?php

class MImageLink extends MLink
{
    public $location;
    public $image;

    public function __construct( $name = '', $label = '', $action = '', $location = '', $attrs = NULL )
    {
        parent::__construct( $name, $label, $action );

        $this->location = $location;
        $this->setAttributes( $attrs );

        $this->image = new MImage( $name, $label, $location, array('border' => '0') );
        $this->setClass( 'mImageLink' );
    }

    public function generateLink()
    {
        parent::generateLink();
        $this->caption = $this->image->generate();
    }
}

?>