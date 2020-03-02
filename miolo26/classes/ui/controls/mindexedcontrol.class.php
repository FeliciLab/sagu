<?php

class MIndexedControl extends MInputControl
{
    public $index;
    public $container;

    public function __construct( $name = '', $label = '', $controls = NULL )
    {
        $this->container = new MVContainer( $name );

        parent::__construct( $name, '', $label );

        $this->index = 0;

        if ( $controls != NULL )
        {
            foreach ( $controls as $c )
            {                 
                $this->addControl( $c );
            }
        }
    }


    public function setIndex( $control, $index )
    {
        $control->setId( $this->name . '_' . $index );
        $control->setName( $this->name . '[' . $index . ']' );
    }


    public function addControl( $control, $index = NULL )
    {
        if ( $index == NULL )
        {
            $index = $this->index++;
        }

        if ( is_array( $control ) )
        {
            foreach ( $control as $c )
            {
                $this->addControl( $c, $index );
            }
        }
        else
        {
            $this->container->insertControl( $control, $index );
            $this->setIndex( $control, $index );
        }
    }


    public function setValue( $value )
    {
        $controls = $this->container->getControls();

        foreach ( $controls as $k => $c )
        {
            $c->setValue( $value[$k] );
        }

        $this->value = $value;
    }


    public function setDisposition( $disposition )
    {
        $this->container->setDisposition( $disposition );
    }


    public function generateInner()
    {
        $t = array();

        $controls = $this->container->getControls();

        foreach ( $controls as $control )
        {
            $a = array(new MLabel($control->label != '' ? $control->label . ':&nbsp;' : ''), $control); 
            $t[] = new MDiv('',$a);
        }

        $this->inner = $t;
    }
}

?>