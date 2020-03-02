<?php

class MCheckBox extends MChoiceControl
{
    public function setSubmittedValue()
    {
        $value = $this->page->request($this->name);
        $this->checked = (isset($value) ? ($value == $this->value) : false);
    }

    public function generateInner()
    {
        if ( $this->manager->checkMobile() )
        {
            $dojoType = 'dojox.mobile.CheckBox';
            $this->page->addDojoRequire($dojoType);
            $this->addAttribute('dojoType', $dojoType);
        }

        if ( $this->readonly )
        {
            $this->addAttribute('readonly');
            $this->addAttribute('disabled');
        }

        if ( $this->autoPostBack )
        {
            $this->addEvent( 'click', "miolo.doPostBack('{$this->name}:click','','{$this->formId}'); " );
        }

        $this->setClass( 'mCheckboxGroup' );
        $this->type  = 'checkbox';
        $this->inner = $this->generateLabel() . $this->getRender( 'inputcheck' );
    }
}

?>
