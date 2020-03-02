<?php

class MSpan extends MControl
{
    public function __construct( $name = NULL, $content = '&nbsp;', $class = NULL, $attributes = NULL )
    {
        parent::__construct( $name );

        $this->setInner( $content );
        $this->setClass( $class );
        $this->setAttributes( $attributes );
    }


    public function generate()
    {
        return $this->getRender('span');
    }
    
}


class MDiv extends MControl
{
    public function __construct( $name = NULL, $content = '&nbsp;', $class = NULL, $attributes = NULL )
    {
        parent::__construct( $name );

        $this->setBoxAttributes($attributes);
        $this->setInner($content);
        $this->setBoxClass($class);
    }

    public function addAttribute($attr,$value)
    {
        $this->addBoxAttribute($attr,$value);
    }

    public function generate()
    {
        $this->setBoxId( $this->getId() );
        return parent::generate();
    }
}


class MSpacer extends MDiv
{
    public function __construct($space = NULL)
    {
        parent::__construct('', '&nbsp;', 'm-spacer');

        if ( ! is_null($space) )
        {
            $this->addBoxStyle('line-height',$space);
        } 
    }
}


class MHr extends MDiv
{
    public function __construct()
    {
        parent::__construct('', '', 'm-hr');
    }
}

?>
