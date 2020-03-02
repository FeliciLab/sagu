<?php
class MOptionGroup extends MControl
{
    public $label;
    public $name;
    public $options; // array of option objects
    public $content;


    public function __construct( $name, $label = '', $options = NULL )
    {
        parent::__construct();

        $this->label   = $label;
        $this->name    = $name;
        $this->options = $options;
    }


    public function generate()
    {
        foreach ( $this->options as $o )
        {
            $this->content .= $o->generate();
        }

        $this->setClass( 'mCombo' );

        return $this->getRender( 'optiongroup' );
    }
}
?>