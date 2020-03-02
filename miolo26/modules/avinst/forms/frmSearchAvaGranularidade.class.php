<?php

/**
 * Formulário de busca da tabela ava_granularidade.
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

$MIOLO->uses('classes/agranularity.class.php', $module);

class frmSearchAvaGranularidade extends ASearchForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaGranularidade';
        parent::__construct('Buscar granularidades');
    }

    /**
     * Criar os campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $module = MIOLO::getCurrentModule();
        $fields[] = new MIntegerField('idGranularidade', '', 'Código da granularidade', 10);
        $fields[] = new MTextField('descricao', '', 'Descrição', 74);
        $fields[] = new MLookupContainer('refServico', null, 'Serviço', $module, 'Servico');
        $fields[] = new MSelection('tipoGranularidade', NULL, _M('Tipo de granularidade', $module), AGranularity::getGranularityTypes());
        $fields[] = $this->getButtons();
        $MIOLO = MIOLO::getInstance();
        $this->grid = $this->manager->getUI()->getGrid($module, 'grdSearchAvaGranularidade');
        $this->grid->setData($this->searchButton_click((Object)array('return'=>true)));
        $fields[] = new MDiv('divGrid', $this->grid->generate());
        $this->addFields($fields);
    }
}


?>