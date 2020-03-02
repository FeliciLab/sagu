<?php

/**
 * Formulário para inserir, editar e remover registros da tabela ava_perfil.
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
class frmAvaPerfil extends AManagementForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaPerfil';
        parent::__construct('Perfil');
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $module = MIOLO::getCurrentModule();

        if ( MIOLO::_REQUEST('function')  ==  'edit' )
        {
            $fields[] = new MTextField('idPerfil', '','Código do perfil', 10, null, null, true);
            $validators[] = new MIntegerValidator('idPerfil', '', 'required');
        }

        $fields[] = new MTextField('descricao', '', 'Descrição', 70);
        $fields[] = new MTextField('tipo', '', 'Tipo', 70);
        $fields[] = new MSelection('avaliavel', 't', 'É avaliável?', AVinst::listYesNo(AVinst::RETURN_TYPE_SINGLE_ARRAY), null, null, null, false);
        $fields[] = new MIntegerField('posicao', null, 'Posição', 5);
        $this->addFields($fields);
        $this->setButtons($this->getButtons());
        $validators[] = new MRequiredValidator('descricao');
        $validators[] = new MRequiredValidator('tipo');
        $validators[] = new MIntegerValidator('posicao');
        $this->setValidators($validators);
    }
}


?>