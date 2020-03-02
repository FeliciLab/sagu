<?php

class MFieldLabel extends MBaseLabel
{
    public function __construct( $id, $text = NULL )
    {
        parent::__construct( NULL, $text );

        $this->setId( $id );
    }


    public function generateInner()
    {
        if ( $this->getClass() == '' )
        {
            $this->setClass( 'mLabel' );
        }

        $this->inner = ( trim($this->value) != '' ) ? $this->getRender('label') : '';
    }
}

?>