<?php

/**
 * Grupo de botões para selecionar
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/11/13
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

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('abuttonselectgroup.js','avinst');

class AButtonSelectGroup extends MDiv
{
    function __construct( $id , $options, $value = null )
    {
        $MIOLO = MIOLO::getInstance();
        
        if( $options )
        {
            foreach ( $options as $index => $option )
            {
                if( strlen($option->color) > 0 ) // Se a cor foi setada
                {
                    $colors[] = $option->color;                    
                }                 
            }
            
            $colors = implode(',', $colors);
            
            foreach ( $options as $index => $option )
            {
                if( strlen($option->label) == 0 )
                {
                    $option->label = '&nbsp';
                }
                $fields[] = $btn = new MDiv("{$id}_{$option->id}",$option->label);
                $btn->addAttribute('onClick',"abuttonselectgroup.select('{$id}',this,'$option->id','$colors')");
                if( $value == $option->id )
                {
                    $btn->setClass('aButtonSelectGroupSelected');
                    $btn->addAttribute('style',"background-color: #FF8C00");
                }
                else
                {
                    $btn->setClass('aButtonSelectGroup');
                    $btn->addAttribute('style',"background-color: $option->color");
                }                                
                //$btn->addAttribute('title',$option->tooltipMessage); // Tooltip nativo da tag div
                if ( strlen($option->tooltipMessage) > 0 )
                {
                    $MIOLO->page->onload("abuttonselectgroup.setTooltip('{$id}_{$option->id}','$option->tooltipMessage')");
                }
            }
            $fields[] = new MHiddenField($id, $value);
        }        
        parent::__construct( 'bsgDiv_'.$id, $fields);                
    }
    
    public static function setColorButton($id,$color)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onLoad("abuttonselectgroup.setColorButton('$id','$color')");
    }
}

?>
