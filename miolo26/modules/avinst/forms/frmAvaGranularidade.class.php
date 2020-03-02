<?php

/**
 * Formulário para inserir, editar e remover registros da tabela ava_granularidade.
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
class frmAvaGranularidade extends AManagementForm
{
    public $data;
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/agranularity.class.php', 'avinst');
        $this->target = 'avaGranularidade';
        parent::__construct('Granularidade');
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        if ( MIOLO::_REQUEST('function')  ==  'edit' && MUtil::isFirstAccessToForm() )
        {
            $fields[] = new MTextField('idGranularidade', '', 'Código da granularidade', 10, null, null, true);
            $validators[] = new MIntegerValidator('idGranularidade', '', 'required');
            $this->getGranularityData();
            $tipo = $this->data->tipo;
        }
        else
        {
            $tipo = $this->getFormValue('tipo');
        }
        $fields[] = new MTextField('descricao', '', 'Descrição', 74);
        $fields[] = new MLookupContainer('refServico', null, 'Serviço', $module, 'Servico');
        $fields['sel'] = new MSelection('tipo', $tipo, 'Tipo de retorno', AGranularity::getGranularityReturn());
        $fields['sel']->addAttribute('onChange', MUtil::getAjaxAction('changeGranularityData', null));
        $fields[] = new MDiv('divOpcoes', AGranularity::returnGranularityReturnFields($tipo));
        $fields[] = new MSelection('tipoGranularidade', NULL, _M('Tipo de granularidade', $module), AGranularity::getGranularityTypes(), FALSE, NULL, FALSE, FALSE);
        $fields[] = $this->getButtons();
        $this->addFields($fields);
        $validators[] = new MRequiredValidator('descricao');
        $validators[] = new MRequiredValidator('refServico');
        $validators[] = new MRequiredValidator('tipo');
        $this->setValidators($validators);
    }
    
    /**
     * Obtém os campos conforme o tipo de retorno
     */
    public function changeGranularityData()
    {
        $MIOLO = MIOLO::getInstance();
        $args = MUtil::getAjaxActionArgs();
        $fields = AGranularity::returnGranularityReturnFields($args->tipo);
        MSubDetail::clearData('opcoesFormulario');
        MSubDetail::clearData('opcoesEstatisticas');
        MSubDetail::clearData('opcoesEmail');
        $this->setResponse($fields, 'divOpcoes');
    }
    
    /**
     * Ação do botão salvar.
     */
    public function saveButton_click()
    {
        if( ! $this->validate() )
        {
            new MMessageWarning('Verifique os dados informados');
            return;
        }
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $data = MUtil::getAjaxActionArgs();
        if (!is_null($data->tipo))
        {
            $options = AGranularity::parseFields($data);
        }
        $data->opcoes = serialize($options);
        $type = new $this->target($data);
        $pk = $type->getPrimaryKeyAttribute();
        switch ( MIOLO::_REQUEST('function') )
        {
            case 'insert':
            {
                try
                {
                    if ( $type->insert() )
                    {
                        $linkOpts[$pk] = $type->__get($pk);
                        $linkOpts['function'] = 'search';
                        $link = new MLinkButton(null,"($linkOpts[$pk])",$MIOLO->getActionUrl($module, $action, null, $linkOpts));
                        new MMessageSuccess(_M(MSG_RECORD_INSERTED,avinst,$link->generate()));
                    }
                }
                catch ( Exception $e )
                {
                    new MMessageError(MSG_RECORD_INSERT_ERROR . ' ' . $e);
                }
                break;
            }
            case 'edit':
            {
                if ( $type->update() )
                {
                    $linkOpts[$pk] = $type->__get($pk);
                    $linkOpts['function'] = 'search';
                    $link = new MLinkButton(null,"($linkOpts[$pk])",$MIOLO->getActionUrl($module, $action, null, $linkOpts));
                    new MMessageSuccess(_M(MSG_RECORD_UPDATED,avinst,$link->generate()));
                }
                else
                {
                    new MMessageError(MSG_RECORD_UPDATE_ERROR . ' ' . $e);
                }
                break;
            }
        }
    }
    
   /**
     * Ação do botão editar.
     */
    public function editButton_click()
    {

        if( MUtil::isFirstAccessToForm() )
        {
            
            $opcoes = unserialize($this->data->opcoes);

            if( $this->data->tipo == AGranularity::GRANULARITY_RETURN_ARRAY_OF_OBJECTS )
            {
                MSubDetail::clearData('opcoesFormulario');     
                MSubDetail::setData($opcoes->opcoesFormulario, 'opcoesFormulario');
                
                MSubDetail::clearData('opcoesEstatisticas');
                MSubDetail::setData($opcoes->opcoesEstatisticas, 'opcoesEstatisticas');
                
                MSubDetail::clearData('opcoesEmail');
                MSubDetail::setData($opcoes->opcoesEmail, 'opcoesEmail');
            }
            // Pega os atributos(public,protected) de acordo com o nome do type (target)
            $reflectionClass = new ReflectionClass($this->target);

            foreach ($reflectionClass->getProperties() as $attribute)
            {
                if( is_object($this->{$attribute->name}) && (!$this->{$attribute->name} instanceof MSubDetail )) // Se for um objeto (componente do miolo), seta o valor
                {
                    $this->{$attribute->name}->setValue($this->data->__get($attribute->name));   
                }
            }
        }                        
    }
    
    /**
     * Função para obter os dados para edição
     */
    public function getGranularityData()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $type = new $this->target();
        $type->__set($type->getPrimaryKeyAttribute(), MUtil::getAjaxActionArgs()->item);
        $type->populate();
        $this->data = $type;
    }
}
?>