<?php


class MTextHeader extends MBaseLabel
{
    public $level;

    public function __construct( $name = '', $level = '1', $text = NULL, $color = '' )
    {
        parent::__construct( $name, $text, $color );

        $this->level = $level;
    }

    public function generateInner()
    {
        $this->inner = $this->getRender( 'header' );
    }
}


?>