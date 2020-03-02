<?php

class MSeparator extends MDiv
{
    private $margin;
    private $lineHeight;
    private $text;
    private $color;

    public function __construct( $text = NULL, $margin = '', $color = '', $lineHeight = '1px' )
    {
        parent::__construct();
        $this->text = $text;
        $this->color = $color;
        $this->margin = $margin;
        $this->lineHeight = $lineHeight;
    }

    public function generateInner()
    {
        if ($this->margin)
        {
            $this->addStyle( 'margin-top', "{$this->margin}" );
        }
        if ( trim($this->text) != '' )
        {
            $this->setClass( 'mSeparator' );
            $text = new MLabel($this->text, $color);
            $this->inner = $text->generate() . $this->getRender('hr');
        }
        else
        {
            $this->setClass( 'mHr', false );
        }
    }
}


?>