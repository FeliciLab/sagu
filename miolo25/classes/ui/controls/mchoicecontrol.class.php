<?php

class MChoiceControl extends MInputControl
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
?>