<?php

class MOutputControl extends MFormControl
{
    public function __construct( $name, $label = '', $color = '', $hint = '' )
    {
        parent::__construct( $name, $label, NULL,  $color, $hint );
    }
}

?>