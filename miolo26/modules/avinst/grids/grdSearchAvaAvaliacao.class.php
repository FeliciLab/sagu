<?php

/**
 * Grid da tabela ava_avaliacao.
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
class grdSearchAvaAvaliacao extends AGrid
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaAvaliacao.class.php', 'avinst');
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $columns[] = new MGridColumn('Código da avaliação', 'right', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn('Nome', 'left', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn(null);
        $columns[] = new MGridColumn('Data inicial', 'left', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn('Data final', 'left', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn('Tipo de processo', 'left', true, NULL, true, avaAvaliacao::obtemTiposProcesso(), true);
        $primaryKeys = array('idAvaliacao'=>'%0%', );
        $url = $MIOLO->getActionUrl($module, $action);
        parent::__construct(__CLASS__, NULL, $columns, $url);
        $args = array('event'=>'editButton:click', 'function'=>'edit');
        $hrefUpdate = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $args = array(MUtil::getDefaultEvent()=>'deleteButton:click', 'function'=>'search');
        $hrefDelete = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $this->addActionUpdate($hrefUpdate);
        $this->addActionDelete($hrefDelete);
    }
}


?>
