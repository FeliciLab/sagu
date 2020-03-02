<?php

/**
 * Formulário de busca da tabela ava_widget.
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 09/03/2012
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class frmSearchAvaWidget extends ASearchForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaWidget';
        parent::__construct(_M('Busca de componentes', MIOLO::getCurrentModule()));
        MSubDetail::clearData('opcoesPadrao');
        MSubDetail::clearData('perfilWidget');
    }

    /**
     * Criar os campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $module = MIOLO::getCurrentModule();
        $fields[] = new MTextField('idWidget', '', _M('Código', $module), 20);
        $fields[] = new MTextField('versao', '', _M('Versao', $module), 20);
        $fields[] = new MTextField('nome', '', _M('Nome', $module), 20);
        $fields[] = $this->getButtons();
        
        $MIOLO = MIOLO::getInstance();
        $this->grid = $this->manager->getUI()->getGrid($module, 'grdSearchAvaWidget');
        $this->grid->setData($this->searchButton_click((Object)array('return'=>true)));
        $fields[] = new MDiv('divGrid', $this->grid->generate());
        $this->addFields($fields);
    }
}


?>