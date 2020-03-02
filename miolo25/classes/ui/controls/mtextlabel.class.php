<?php

class MTextLabel extends MText
{
    public function __construct( $name = '', $text = null, $label = '', $color = '', $bold = false )
    {
        parent::__construct( $name, $text, $color, $bold );

        $this->label = $label;
        $this->setClass( 'mLabelText' );
//        $this->formMode = 1;
    }

    public function generateInner()
    {
        if ( $this->getClass() == '' )
        {
            $this->setClass( 'mText' );
        }

        $this->inner =  $this->generateLabel() . $this->getRender( 'text' );
    }
}

?>