<?php

/**
 * Formulário de busca da tabela ava_questoes.
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
class frmSearchAvaQuestoes extends ASearchForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/adynamicfields.class.php', $module);
        $this->target = 'avaQuestoes';
        parent::__construct('Buscar questões');
    }

    /**
     * Criar os campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $fields[] = new MIntegerField('idQuestoes', '', 'Código da questão', 10);
        $fields[] = new MMultilineField('descricao', '', 'Enunciado', 70, 5, 70);
        $fields[] = new MSelection('tipo', '', 'Tipo da questão', ADynamicFields::getQuestionTypes());
        $fields[] = new MDiv('divQuestionOptions', null);
        $fields[] = $this->getButtons();
        $this->grid = $this->manager->getUI()->getGrid($module, 'grdSearchAvaQuestoes');
        $this->grid->setData($this->searchButton_click((Object)array('return'=>true)));
        $fields[] = new MDiv('divGrid', $this->grid->generate());
        $this->addFields($fields);
    }
}
?>