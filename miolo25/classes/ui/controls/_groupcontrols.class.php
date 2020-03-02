<?php

class MBaseGroup extends MContainer
{
    public $borderType;
    public $scrollable;
    public $scrollHeight;

    public function __construct( $name = '', $caption = '', $controls = '', $disposition = 'none', $border = 'css', $formMode = MControl::FORM_MODE_SHOW_ABOVE )
    {
        parent::__construct( $name, $controls, $disposition );

        $this->scrollable   = false;
        $this->scrollHeight = '';
        $this->borderType   = $border;
        $this->caption      = $caption;
        $this->formMode     = $formMode;
    }


    public function setScrollHeight( $height )
    {
        $this->scrollable   = true;
        $this->scrollHeight = $height;
    }


    public function setBorder( $border )
    {
        $this->borderType = $border;
    }


    public function generateInner()
    {
        switch ( $this->borderType )
        {
            case 'none':
            case '':
                $this->border = '0';

                break;

            case 'css': break;

            default: $this->addStyle('border', $this->border);
        }

        $attrs = $this->getAttributes();

        parent::generateInner();

        $html = $this->getInnerToString();

        if ( $this->scrollable )
        {
            $f[]  = new MDiv( '', $this->caption, 'm-scrollable-label' );
            $this->setClass('field');
            $f[]  = $div = new MDiv( '', $html, 'm-scrollable-field' );
            $div->height = $this->scrollHeight;
            $this->inner = new MDiv( '', $f, '' );
        }
        else
        {
            $this->width = MUtil::NVL( $this->width, '98%' );
            $this->inner = $this->getRender( 'fieldset' );
        }
    }
}


class MCheckBoxGroup extends MBaseGroup
{
    // options:
    //    - a simple array of values
    //    - a array of key/value pairs
    //    - an array of Option objects
    //    - an array of CheckBox objects
    public function __construct( $name = '', $label = '', $options = '', $hint = '', $disposition = 'horizontal', $border = 'none' )
    {
        $controls = array();

        if ( ! is_array( $options ) )
        {
            $options = array($options);
        }

        $n = count($options);

        for ( $i = 0; $i < $n; $i++ )
        {
            // we will accept an array of CheckBox ...
            if ( $options[$i] instanceof MCheckBox )
            {
                $controls[] = clone $options[$i];
            }
            else
            {
                // we will accept an array of Options ...
                if ( $options[$i] instanceof MOption )
                {
                    $oName    = $name . '_' . $options[$i]->name;
                    $oLabel   = $options[$i]->label;
                    $oValue   = $options[$i]->value;
                    $oChecked = $options[$i]->checked || ( $oValue == MIOLO::_REQUEST($oName) );
                }
                // or an array of label/value pairs ...
                elseif ( is_array( $options[$i] ) )
                {
                    $oName    = $name . '_' . $i;
                    $oLabel   = $options[$i][0];
                    $oValue   = $options[$i][1];
                    $oChecked = $oValue == MIOLO::_REQUEST($oName);
                }
                // or a simple array of values
                else
                {
                    $oName    = $name . '_' . $i;
                    $oLabel   = $oValue = $options[$i];
                    $oChecked = $oValue == MIOLO::_REQUEST($oName);
                }

                $option = new MCheckBox( $oName, $oValue, $oLabel, $oChecked, $oLabel );
                if ( $options[$i] instanceof MOption )
                {
                    $option->attrs = $options[$i]->attrs;
                }
                $controls[] = $option;
            }
        }

        parent::__construct($name, $label, $controls, $disposition, $border);
        $this->setShowChildLabel( false, true );
    }


    public function getValue()
    {
        $value    = array();
        $controls = $this->getControls();

        foreach( $controls as $control )
        {
            $value[$control->getName()] = $control->checked ? $control->getValue() : NULL;
        }

        return $value;
    }

}


class MRadioButtonGroup extends MBaseGroup
{
    public $default;
    public $options;
    // options:
    //    - a simple array of values
    //    - a array of key/value pairs
    //    - an array of Option objects
    //    - an array of RadioButton objects

    public function setValue( $v )
    {
        if ( $v ) 
        {
            $this->_setValue( $v );
        }
    }

    private function _setValue( $v )
    {
        foreach( $this->getControls( ) as $option )
        {
            $option->checked = false;
        }
        foreach( $this->getControls( ) as $option )
        {
            if ( $option->value )
            {
                if ( $v == $option->value )
                {
                    $option->checked = true;
                    $this->value = $v;
                }
            }
        }
    }

    public function __construct( $name = '', $label = '', $options = '', $default = false, $hint = '', $disposition = 'vertical', $border = 'none' )
    {
        $controls = array();

        if ( ! is_array( $options ) )
        {
            $options = array( $options );
        }
        $this->options = $options;

        $n = count( $options );

        for ( $i = 0; $i < $n; $i++ )
        {
            // we will accept an array of RadioButton ... 
            if ( $options[$i] instanceof MRadioButton )
            {
                $options[$i]->setName( $name );
                $options[$i]->setId( $name . '_' . $i );
                $options[$i]->checked = ( $options[$i]->checked || ( $options[$i]->value == $default ) );
                $controls[] = clone $options[$i];
            }
            else
            {
                $oName = $name;

                // we will accept an array of Options ... 
                if ( $options[$i] instanceof MOption )
                {
                    $oName    = $name . '_' . $options[$i]->name;
                    $oLabel   = $options[$i]->label;
                    $oValue   = $options[$i]->value;
                    $oChecked = ( $oValue == $default ) || $options[$i]->checked || ( $oValue == MIOLO::_REQUEST($oName) );
                }
                // or an array of label/value pairs ... 
                elseif ( is_array( $options[$i] ) )
                {
                    $oName    = $name . '_' . $i;
                    $oLabel   = $options[$i][0];
                    $oValue   = $options[$i][1];
                    $oChecked = ( $oValue == $default ) || ( $oValue == MIOLO::_REQUEST($oName) );
                }
                // or a simple array of values
                else
                {
                    $oName    = $name . '_' . $i;
                    $oLabel   = $oValue = $options[$i];
                    $oChecked = ( $oValue == $default ) || ( $oValue == MIOLO::_REQUEST($oName) );
                }

                $control = new MRadioButton( $oName, $oValue, $oLabel, $oChecked, $oLabel, $hint );
                $control->setName( $name );
                if ( $options[$i] instanceof MOption )
                {
                    $control->attrs = $options[$i]->attrs;
                }

                $controls[] = $control;
            }
        }

        parent::__construct( $name, $label, $controls, $disposition, $border );
        $this->value = $default;
        $this->setShowChildLabel( false, true );
    }
}


class MLinkButtonGroup extends MBaseGroup
{
    // options: array of LinkButton objects
    public function __construct( $name = '', $label = '', $options = '', $disposition = 'horizontal', $border = 'css' )
    {
        parent::__construct( $name, $label, $options, $disposition, $border );
        $this->setShowChildLabel( false, true );
    }
}

?>
