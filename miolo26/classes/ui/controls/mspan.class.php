<?php

class MSpan extends MOutputControl
{
    public function __construct( $name = NULL, $content = '&nbsp;', $class = NULL, $attributes = NULL )
    {
        parent::__construct( $name );

        $this->setInner( $content );
        $this->setClass( $class != '' ? $class : 'mSpan' );
        $this->setAttributes( $attributes );
    }

    public function generate()
    {
        return $this->getRender('span');
    }
}

?>