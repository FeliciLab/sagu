<?php

class MImageButton extends MImageLink
{
    public function generateLink()
    {
        parent::generateLink();

        $this->target = '';
        $action = $this->href;
        if ( $action == '' )
        {
            $action = 'submit';
        }

        if ( preg_match('/^https?:\/\//i', $action) )
        {
            $onclick = $this->getOnClick($action, '', 'href');
        }
        else
        {
            $onclick = $this->getOnClick('', $action, '');
        }

        if ($onclick != '')
        {
            $this->addAttribute('onclick', $onclick);
        }
    }
}

?>