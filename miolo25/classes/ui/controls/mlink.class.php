<?php

class MLink extends MActionControl
{
    const TARGET_BLANK = '_blank';
    const TARGET_PARENT = '_parent';
    const TARGET_SELF = '_self';
    const TARGET_TOP = '_top';

    public $target = '_self';
    public $href;
    public $generateOnClick;

    public function __construct( $name = NULL, $label = NULL, $href = NULL, $text = NULL, $target = self::TARGET_SELF, $generateOnClick = true )
    {
        parent::__construct( $name, $href, $label );

        $this->caption = $text;
        $this->href    = $href;
        $this->target  = $target;
        $this->generateOnClick = $generateOnClick;
    }

    public function setText( $text )
    {
        $this->caption = $text;
    }

    public function setTarget( $target )
    {
        $this->target = $target;
    }

    public function setHREF( $href )
    {
        $this->href = $href;
    }

    public function setAction( $module = '', $action = '', $item = null, $args = null )
    {
        $this->href = $this->manager->getActionURL( $module, $action, $item, $args );
//        $this->addEvent('click', "miolo.doLink('{$goto}','{$this->formId}');");
    }

    public function setGenerateOnClick($generateOnClick)
    {
        $this->generateOnClick = $generateOnClick;
    }

    public function getGenerateOnClick()
    {
        return $this->generateOnClick;
    }

    /* deprecated */
    public function setOnClick($code)
    {
        $this->href = $code;
    }

    public function generateLink()
    {
        if ( $this->generateOnClick && $this->href != '' )
        {
            $onclick = $this->getOnClick($this->href,'','href');
            $this->addAttribute('onclick',$onclick);
        }
    }

    public function generateInner()
    {
        $this->generateLink();

        if ( $this->readOnly )
        {
            $this->inner = MHtmlPainter::span( 'mReadOnly', $this->name, $this->caption );

            return;
        }

        if ( $this->getClass() == '' )
        {
            $this->setClass( 'mLink' );
        }

        if ( $this->target != self::TARGET_SELF )
        {
            $this->addAttribute( 'target', $this->target );
        }

        if ( $this->caption == '' )
        {
            $this->caption = $this->label;
        }

        $this->inner = $this->generateLabel() . $this->getRender('anchor');
    }
}

?>