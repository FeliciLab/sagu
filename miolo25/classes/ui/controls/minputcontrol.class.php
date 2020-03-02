<?php

class MInputControl extends MFormControl
{
    public $autoPostBack;
    public $validator;

    public function __construct( $name, $value, $label = '', $color = '', $hint = '' )
    {
        parent::__construct( $name, $value, $label, $color, $hint );
        $this->autoPostBack = false;
    }

    public function setAutoPostBack( $value )
    {
        $this->autoPostBack = $value;
    }

    public function generateLabel()
    {
        $label = '';
        $this->showLabel = ( $this->formMode >= MFormControl::FORM_MODE_SHOW_ABOVE );

        if ( ( $this->showLabel ) && ( $this->label != '' ) )
        {
            $span  = new MSpan( '', $this->label, 'mCaption' );

/*
            if( ! $this->validator && method_exists($this->form,'getFieldValidator') )
            {
                $this->validator = $this->form->getFieldValidator($this->name);
            }
*/
            $r = $this->attrs->items['required'] || ($this->validator && $this->validator->type == 'required');

            if( $r && trim(MUtil::removeSpaceChars($this->label)) )
            {
                $span->setClass('mCaptionRequired');
            }

            $label = $this->generateLabelMode($this->painter->span( $span ));

        }

        return $label;
    }

    public function getOnClick($action, $attr)
    {
        if ( substr($action, 0,11) == 'javascript:' )
        {
            return $action;
        }
        else
        {
            return "javascript:miolo.doLink(this.{$attr},'{$this->formId}'); return false;";
        }



    }
}
?>