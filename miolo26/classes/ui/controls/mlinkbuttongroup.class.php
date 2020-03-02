<?php

class MLinkButtonGroup extends MBaseGroup
{
    // options: array of LinkButton objects
    public function __construct( $name = '', $label = '', $options = '', $disposition = 'horizontal', $border = 'css' )
    {
        parent::__construct( $name, $label, $options, $disposition, $border );
        $this->setShowChildLabel( false, true );
    }
}

?>