<?php

class MDiv extends MControl
{
    public function __construct( $name = NULL, $content = '&nbsp;', $class = NULL, $attributes = NULL )
    {
        parent::__construct( $name );
        $this->setAttributes($attributes);
        $this->setInner($content);
        $this->setClass($class);
    }

    public function generate()
    {
        $this->generateInner();
        $this->generateEvent();
        return $this->getRender('div');
    }
}

?>