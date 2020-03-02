<?php

/**
 * Colorpicker
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/12/22
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
$MIOLO->page->addScript('cP_v0.91/colorPicker.js','avinst');

class AColorPicker extends MTextField
{
    function __construct( $id , $value, $label )
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onLoad('colorPicker.size = 1;');
        parent::__construct( $id, $value, $label );
        $this->addAttribute('onClick', "colorPicker(event,'RGB','1')");                        
    }
}
?>
