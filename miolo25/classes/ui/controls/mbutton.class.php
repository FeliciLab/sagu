<?php

class MButton extends MActionControl
{
    public $action;
    public $target = '_self';
    public $type;
    public $image;
    public $text;
    public $onclickdisable;

    public function __construct( $name = '', $label = '', $action = NULL, $image = NULL, $onclickdisable = FALSE )
    {
        parent::__construct( $name, '', $label );

        $this->action  = $action ? $action : 'submit';
        $this->image   = $image;
        $this->onclickdisable = $onclickdisable;
        $this->setClass('mButton');
    }

    public function setImage( $location )
    {
        $this->image = $location;
    }


    public function setAction( $action )
    {
        $this->action = $action;
    }

    public function generateButton()
    {
        if ( $this->label == '' )
        {
            $this->label = _M('Send');
        }

        if ( $this->value == '' )
        {
            $this->value = $this->label;
        }

        $this->text = $this->label;
        $this->label = '';

        $action = $this->action;
        $this->type = ( strtoupper($action) == 'RESET' ) ? 'reset' : 'button';

        // if it has a event registered, it's not necessary calculate $onclick
        if ($this->hasEvent('click'))
        {
            return;
        }

        if ($this->type == 'reset')
        {
            return;
        }

        $onclick = $this->getOnClick('', $action, '');

        if ( !$this->onclickdisable )
        {
            if ( $onclick != '' && (!$this->getAttribute('onclick')) )
            {
                $this->addAttribute('onclick', $onclick);
            }
        }
        else
        {
            if ( $this->action == 'submit' )
            {
                $this->type = 'submit';
            }
        }
    }

    public function generateInner()
    {
        if ( $this->visible )
        {
            $this->generateButton();
            $this->inner = $this->generateLabel() . $this->getRender('button');
        }
    }
}
?>
