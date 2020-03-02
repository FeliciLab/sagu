<?php

class MButtonImage extends MButton
{
    public function generateInner()
    {
        if ( $this->visible )
        {
            parent::generateButton();
            $this->value = $this->text = '';
            $this->setClass('mButtonImage');
            $this->_addStyle('background-image',"url({$this->image})");
            $this->inner = $this->generateLabel() . $this->getRender('inputButton');
        } 
    }
}

?>