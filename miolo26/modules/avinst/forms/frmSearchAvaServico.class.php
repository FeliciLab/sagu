<?php

/**
 * Formulário de busca da tabela ava_servico.
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
class frmSearchAvaServico extends ASearchForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaServico';
        parent::__construct(_M('Buscar Serviços', MIOLO::getCurrentModule()));
    }

    /**
     * Criar os campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $module = MIOLO::getCurrentModule();
        $fields[] = new MIntegerField('idServico', '', _M('Código do serviço', $module), 10);
        $fields[] = new MTextField('descricao', '', _M('Descrição', $module), 70);
        $fields[] = new MMultilineField('localizacao', '', _M('Localização', $module), 70, 2, 70);
        $fields[] = new MMultilineField('metodo', '', _M('Método', $module), 70, 5, 70);
        $fields[] = new MMultilineField('parametros', '', _M('Parâmetros', $module), 70, 5, 70);
        $fields[] = $this->getButtons();
        $MIOLO = MIOLO::getInstance();
        $this->grid = $this->manager->getUI()->getGrid($module, 'grdSearchAvaServico');
        $this->grid->setData($this->searchButton_click((Object)array('return'=>true)));
        $fields[] = new MDiv('divGrid', $this->grid->generate());
        $this->addFields($fields);
    }
}


?>