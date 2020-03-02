<?php

/**
 * Grid da tabela ava_granularidade.
 *
 * @author Jader Fiegenbaum [jader@solis.com.br]
 *
 * \b Maintainers: \n
 * Jader Fiegenbaum [jader@solis.com.br]
 *
 * @since
 * Creation date 09/07/2014
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2014 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class grdAvaDefinirTipoDeGranularidade extends AGrid
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $columns[] = new MGridColumn('Código', 'right', true, '5%', true, NULL, true);
        $columns[] = new MGridColumn('Descrição', 'left', true, '65%', true, NULL, true);
        $columns[] = new MGridColumn('Tipo atual', 'left', true, '15%', true, NULL, true);
        $columns[] = new MGridColumn(_M('Novo tipo', $module), 'left', true, '15%', false, NULL, true);
        $columns[] = new MGridColumn(_M('Novo tipo', $module), 'left', true, '15%', true, NULL, true);
        parent::__construct(__CLASS__, NULL, $columns);
    }
}


?>
