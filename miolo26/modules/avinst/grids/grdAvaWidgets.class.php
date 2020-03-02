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
class grdAvaWidgets extends MSpecialGrid
{
    public function __construct($data)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaAvaliacao.class.php', 'avinst');
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $columns[] = new MGridColumn('idWidget', null, true, null, false);
        $columns[] = new MGridColumn('Nome', 'left', true, NULL, false, NULL, true);
        $columns[] = new MGridColumn('Versão', 'right', true, NULL, false, NULL, true);
        $columns[] = new MGridColumn('Descrição', 'left', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn('Tela', 'center', true, null, true, null, true);

        $url = $MIOLO->getActionUrl($module, $action);
        parent::__construct($data, $columns, null, 0, true, array('idWidget'=>'%0%'));
    }
}

?>
