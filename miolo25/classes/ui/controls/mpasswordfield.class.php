<?php

class MPasswordField extends MTextField
{
    public function __construct( $name='', $value='', $label='', $size=20, $hint='', $validator=null )
    {
        parent::__construct( $name, $value, $label, $size, $hint, $validator );

        $this->type = 'password';
    }
}

?>