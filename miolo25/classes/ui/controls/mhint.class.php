<?php

class MHint extends MSpan
{
    public function __construct( $content = '&nbsp;')
    {
        parent::__construct( '', $name );

        $this->setInner( $content );
        $this->setClass( 'mHint' );
    }
}

?>