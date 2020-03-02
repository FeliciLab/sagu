<?php

class MRadioButtonGroup extends MBaseGroup
{
    public $default;
    public $options;
    public $value;

    // options:
    //    - a simple array of values
    //    - a array of key/value pairs
    //    - an array of Option objects
    //    - an array of RadioButton objects

    public function getValue()
    {
        return $this->value;
    }

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
        $this->setValue($default);
        $this->setShowChildLabel( false, true );
        $this->setClass('mRadioButtonGroupDiv');
    }
}
?>