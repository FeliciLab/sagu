<?php

class MRadioButton extends MChoiceControl
{
    public function setSubmittedValue()
    {
        $value = $this->page->request($this->name);
        $this->checked = (isset($value) ? ($value == $this->value) : false);
    }

    public function generateInner()
    {
        if ( $this->readonly )
        {
            return $this->text;
        }

        if ( $this->autoPostBack )
        {
            $this->addEvent('click', "miolo.submit();" );
        }

        $this->setClass( 'mRadiobuttonGroup' );
        $this->type  = 'radio';
        $this->inner = $this->generateLabel() . $this->getRender( 'inputcheck' );
    }
}

?>