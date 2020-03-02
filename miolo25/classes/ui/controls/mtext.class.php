<?php


class MText extends MBaseLabel
{
    public function __construct( $name = '', $text = NULL, $color = '', $bold = false )
    {
        parent::__construct( $name, $text, $color, NULL, $bold );

        $this->formMode = 1;
    }


    public function generateInner()
    {
        if ( $this->getClass() == '' )
        {
            $this->setClass( 'mText' );
        }

        $this->inner = $this->getRender( 'text' );
    }
}

?>