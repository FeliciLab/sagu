<?php

class MOptionListItem extends MControl
{
    public $type;
    public $control;


    public function __construct( $type, $control, $cssClass )
    {
        parent::__construct();
        $this->type    = $type;
        $this->control = $control;
        $this->setClass($cssClass);
    }


    public function generateMenu()
    {
        return new MDiv( '', $this->control, $this->getClass() );
    }


    public function generateLink()
    {
        $this->control->setClass( $this->getClass() );

        return $this->control->generate();
    }


    public function generateOption()
    {
        $this->control->setClass( $this->getClass() );

        return $this->control->generate();
    }


    public function generateText()
    {
        $this->control->setClass( $this->getClass() );

        return $this->control;
    }


    public function generateSeparator()
    {
        return $this->control->generate();
    }

    public function generateControl()
    {
        return $this->control->generate();
    }

    public function generate()
    {
        $method = 'Generate' . ucfirst( $this->type );

        return $this->$method();
    }
}

?>