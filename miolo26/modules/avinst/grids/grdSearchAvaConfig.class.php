<?php

/**
 * Grid da tabela ava_config.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 23/11/2011
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class grdSearchAvaConfig extends MGrid
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $columns[] = new MGridColumn(_M('Chave', $module), 'left', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn(_M('Valor', $module), 'left', true, NULL, true, NULL, true);
        $primaryKeys = array('chave_'=>'%0%', );
        $url = $MIOLO->getActionUrl($module, $action);
        parent::__construct(NULL, $columns, $url);
        $args = array('event'=>'editButton:click', 'function'=>'edit', );
        $hrefUpdate = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $args = array(MUtil::getDefaultEvent()=>'deleteButton:click', 'function'=>'search', );
        $hrefDelete = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $this->addActionUpdate($hrefUpdate);
        $this->addActionDelete($hrefDelete);
    }
}


?>
