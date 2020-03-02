<?php

/**
 * Formulário de busca da tabela ava_avaliacao.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 17/11/2011
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
class frmSearchAvaAvaliacao extends ASearchForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaAvaliacao';
        parent::__construct('Buscar avaliações');
    }

    /**
     * Criar os campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $module = MIOLO::getCurrentModule();
        $fields[] = new MIntegerField('idAvaliacao', '', 'Código da avaliação', 10);
        $fields[] = new MTextField('nome', '', 'Nome', 60);
        $fields[] = new MCalendarMobileField('dtInicio', '', 'Data inicial', 10);
        $fields[] = new MCalendarMobileField('dtFim', '', 'Data final', 10);
        $fields[] = new MSelection('tipoProcesso', null, 'Tipo de processo', avaAvaliacao::obtemTiposProcesso());
        $fields[] = $this->getButtons();
        $MIOLO = MIOLO::getInstance();
        $this->grid = $this->manager->getUI()->getGrid($module, 'grdSearchAvaAvaliacao');
        $this->grid->setData($this->searchButton_click((Object)array('return'=>true)));
        $fields[] = new MDiv('divGrid', $this->grid->generate());
        $this->addFields($fields);
    }

    /**
     * Ação do botão limpar.
     */
    public function clearButton_click()
    {
        $this->getField('idAvaliacao')->setValue('');
        $this->getField('nome')->setValue('');
        $this->getField('dtInicio')->setValue('');
        $this->getField('dtFim')->setValue('');
        $this->getField('tipoProcesso')->setValue('');
    }
}


?>