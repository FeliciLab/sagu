<?php

/**
 * Grupo de radio buttons
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/11/21
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2008 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

class ARadioButtonGroup extends MDiv
{
    function __construct( $id , $label, $options, $value )
    {
        if( ! is_null($label) )
        {
            $label = new MLabel($label.':');
            $label->setClass( 'mCaption' );
            $fields[] = new MSpan(null,$label,'label');
        }
        
        if ( ! is_array( $options ) )
        {
            $options = array( $options );
        }
        
        foreach ( $options as $i => $option )
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
                $oName = $id;

                // we will accept an array of Options ... 
                if ( $options[$i] instanceof MOption )
                {
                    $oName    = $id . '_' . $options[$i]->name;
                    $oLabel   = $options[$i]->label;
                    $oValue   = $options[$i]->value;
                    $oChecked = $options[$i]['checked'] || MIOLO::_REQUEST($oName);
                }
                // or an array of label/value pairs ... 
                elseif ( is_array( $options[$i] ) )
                {
                    $oName    = $id . '_' . $i;
                    $oLabel   = $options[$i][0];
                    $oValue   = $options[$i][1];
                    $oChecked = $options[$i]['checked'] || MIOLO::_REQUEST($oName);
                }
                // or a simple array of values
                else
                {
                    $oName    = $id . '_' . $i;
                    $oLabel   = $oValue = $options[$i];
                    $oChecked = $options[$i]['checked'] || MIOLO::_REQUEST($oName);
                }

                $control = new MRadioButton( $id, $oValue, null, $oChecked, $oLabel);
                $control->setName( $id );
                if ( $options[$i] instanceof MOption )
                {
                    $control->attrs = $options[$i]->attrs;
                }

                $controls[] = $control;
            }
        }
        
        $fields[] = new MHContainer(null,$controls);        
        parent::__construct( $id.'Div', $fields);                
    }
    
    public function setValue($value)
    {
    }
}

?>
