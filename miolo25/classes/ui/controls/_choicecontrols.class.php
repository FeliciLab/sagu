<?php

class MChoiceControl extends MFormControl
{
    public $checked;
    public $text;
    public $type;

    public function __construct( $name = '', $value = '', $label = '', $checked = false, $text = NULL, $hint = '' )
    {
        parent::__construct( $name, $value, $label, '', $hint );

        $this->checked  = $checked;
        $this->text     = $text;
        $this->formMode = 1;
    }

    public function setChecked( $checked )
    {
        if ( ! is_bool( $checked ) )
        {
            throw new Exception( _M('This method expects an boolean as parameter!') );
        }

        $this->checked = $checked;
    }

}


class MCheckBox extends MChoiceControl
{
    public function generateInner()
    {
        if ( $this->readonly )
        {
            return $this->text;
        }

        if ( $this->autoPostBack )
        {
            $this->addAttribute( 'onclick', "miolo.doPostBack('{$this->name}:click',''); miolo.getForm().submit();" );
        }

        $this->setClass( 'm-checkbox-group' );
        $this->type  = 'checkbox';
        $this->inner = $this->generateLabel() . $this->getRender( 'inputcheck' );
    }
}


class MRadioButton extends MChoiceControl
{
    public function generateInner()
    {
        if ( $this->readonly )
        {
            return $this->text;
        }

        if ( $this->autoPostBack )
        {
            $this->addAttribute( 'onclick', "miolo.getForm().submit();" );
        }

        $this->setClass( 'm-radiobutton-group' );
        $this->type  = 'radio';
        $this->inner = $this->generateLabel() . $this->getRender( 'inputcheck' );
    }
}

?>