<?php

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

?>