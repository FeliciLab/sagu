<?php

/**
 * Grid da tabela ava_perfil.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 18/11/2011
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
class grdSearchAvaPerfil extends AGrid
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $columns[] = new MGridColumn('Código do perfil', 'right', true, null, true, NULL, true);
        $columns[] = new MGridColumn('Descrição', 'left', true, null, true, NULL, true);
        $columns[] = new MGridColumn('Tipo', 'left', true, null, true, NULL, true);
        $columns[] = new MGridColumn('É avaliável?', 'left', true, null, true, AVinst::listYesNo(AVinst::RETURN_TYPE_SINGLE_ARRAY), true);
        $columns[] = new MGridColumn('Posição', 'right', true, null, true);
        $primaryKeys = array('idPerfil'=>'%0%', );
        $url = $MIOLO->getActionUrl($module, $action);
        parent::__construct(__CLASS__, NULL, $columns, $url);
        $args = array('event'=>'editButton:click', 'function'=>'edit', );
        $hrefUpdate = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $args = array(MUtil::getDefaultEvent()=>'deleteButton:click', 'function'=>'search', );
        $hrefDelete = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $this->addActionUpdate($hrefUpdate);
        $this->addActionDelete($hrefDelete);
    }
}


?>
