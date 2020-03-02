<?php

/**
 * Formulário para inserir, editar e remover registros da tabela ava_config.
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
class frmAvaConfig extends AManagementForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaConfig';
        parent::__construct('Configuração');
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $module = MIOLO::getCurrentModule();
        
        $fields['chave_'] = new MMultilineField('chave_', '', _M('Chave', $module), 70, 5, 70);

        if ( MIOLO::_REQUEST('function')  ==  'edit' )
        {
            $fields['chave_']->setReadOnly(true);        
        }

        $fields[] = new MMultilineField('valor', '', _M('Valor', $module), 70, 5, 70);
        $fields[] = $this->getButtons();
        $this->addFields($fields);
        $validators[] = new MRequiredValidator('chave_');
        $this->setValidators($validators);
    }
}


?>