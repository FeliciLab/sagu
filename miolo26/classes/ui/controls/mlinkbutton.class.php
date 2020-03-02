<?php

class MLinkButton extends MLink
{
    public function __construct( $name = '', $label = '', $action = '', $text = NULL )
    {
        parent::__construct( $name, $label, $action, $text );
    }

    public function generateLink()
    {
        $action = $this->href;
        if ( $action == '' )
        {
            $action = 'submit';
        }

        $onclick = $this->getOnClick('', $action, '');

        if ($onclick != '')
        {
            $this->addAttribute('onclick', $onclick);
        }
    }
}
?>