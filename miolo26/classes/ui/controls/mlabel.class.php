<?php

class MBaseLabel extends MOutputControl
{
    public function __construct( $name = NULL, $label = NULL, $color = '', $hint = '', $bold = false )
    {
        parent::__construct( $name, $label, $color, $hint );
        $this->setBold($bold);
    }

    public function setBold( $value = true )
    {
        if ($value) $this->setClass('mLabelBold');
    }
}

class MLabel extends MBaseLabel
{
    public function __construct( $text = NULL, $color = '', $bold = false )
    {
        parent::__construct( NULL, $text, $color, NULL, $bold );
    }

    public function generateInner()
    {
        if ( $this->getClass() == '' )
        {
            $this->setClass( 'mLabel' );
        }
        $this->inner = ( trim($this->value) != '' ) ? $this->getRender( 'text' ) : '';
    }
}

?>