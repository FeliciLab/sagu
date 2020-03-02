<?php

class MPageComment extends MBaseLabel
{
    public function __construct( $text = NULL )
    {
        parent::__construct( NULL, $text );
    }


    public function generateInner()
    {
        $this->inner = $this->getRender( 'comment' );
    }


    public function generate()
    {
        $this->generateInner();

        return $this->getInner();
    }
}

?>