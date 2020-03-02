<?php

/**
 * Formulário de busca da tabela ava_formulario.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 21/11/2011
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
class frmSearchAvaFormulario extends ASearchForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaFormulario';
        parent::__construct('Buscar formulários');
    }

    /**
     * Criar os campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $module = MIOLO::getCurrentModule();
        $fields[] = new MIntegerField('idFormulario', '', 'Código do formulário');
        $fields[] = new MLookupContainer('refAvaliacao', '', 'Avaliação', $module, 'Avaliacao');
        $fields[] = new MLookupContainer('refPerfil', null, 'Perfil', $module, 'Perfil');
        $fields[] = new MTextField('nome', '', 'Nome', 50);
        $fields[] = new MLookupContainer('refServico', null, 'Serviço', $module, 'Servico');
        $fields[] = $this->getButtons();
        $MIOLO = MIOLO::getInstance();
        $this->grid = $this->manager->getUI()->getGrid($module, 'grdSearchAvaFormulario');
        $this->grid->setData($this->searchButton_click((Object)array('return'=>true)));
        $fields[] = new MDiv('divGrid', $this->grid->generate());
        $this->addFields($fields);
    }
}


?>