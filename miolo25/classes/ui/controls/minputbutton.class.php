<?php

class MInputButton extends MButton
{
    public function generateInner()
    {
        if ( $this->visible )
        {
            parent::generateButton();
            $this->value = $this->text = '';
            $this->inner = $this->generateLabel() . $this->getRender('button');
        }
    }
}

?>