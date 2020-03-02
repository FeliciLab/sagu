<?php

/**
 * Formulário para inserir, editar e remover registros da tabela ava_questoes.
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
class frmAvaQuestoes extends AManagementForm
{
    public $data;
    
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaQuestoes';
        parent::__construct('Questões');
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        if ( MIOLO::_REQUEST('function') == 'edit')
        {
            $this->getQuestionData();
            $tipo = $this->data->__get('tipo');
            $fields[] = new MTextField('idQuestoes', '', 'Código da questão', 10, null, null, true);
            $validators[] = new MIntegerValidator('idQuestoes', '', 'required');
        }
        $fields[] = new MMultilineField('descricao', '', 'Enunciado', 70, 5, 70);
        $fields['tipo'] = new MSelection('tipo', '', 'Tipo da questão', ADynamicFields::getQuestionTypes());
        $fields['tipo']->addAttribute('onChange', MUtil::getAjaxAction('changeQuestionType'));
        $fields[] = new MSpan('divOpcoes', ADynamicFields::returnQuestionFields($this->getFormValue('tipo') ? $this->getFormValue('tipo') : $tipo));
        $fields[] =  $this->getButtons();
        $fields[] = new MDiv('importDiv');
        $this->addFields($fields);
        
        $validators[] = new MRequiredValidator('descricao');
        $validators[] = new MRequiredValidator('tipo');
        $this->setValidators($validators);
    }
    
    /*
     * Função que atualiza as informações relativas ao tipo de questão
     */
    public function changeQuestionType()
    {
        $MIOLO = MIOLO::getInstance();
        $args = MUtil::getAjaxActionArgs();
        $fields = ADynamicFields::returnQuestionFields($args->tipo);
        MSubDetail::clearData('opcoes');
        $this->setResponse($fields, 'divOpcoes');
    }
    
	/*
     * Função que importa as opções de uma questão ja existente para a subDetail de opções
     */
    public function opcoes_btnImportar_click()
    {
        $MIOLO = MIOLO::getInstance();
        $args = MUtil::getAjaxActionArgs();
        if (strlen($args->opcoes_importarOpcoesLkp)>0)
        {
            $MIOLO->uses( "types/avaQuestoes.class.php", $MIOLO->getCurrentModule() );
            $questoes = new avaQuestoes();
            $questoes->__set('idQuestoes',$args->opcoes_importarOpcoesLkp);
            $questoes->populate();
            if ($this->getFormValue('tipo') == $questoes->tipo)
            {
                $opcoes = unserialize($questoes->__get('opcoes'));
                // Verifica se as opções estão dentro de um atributo
                if( is_array($opcoes->opcoes) )
                {
                    $opcoes = $opcoes->opcoes;
                }
                if( count($opcoes) > 0 )
                {
                    MSubDetail::clearData('opcoes');
                    MSubDetail::setData($opcoes,'opcoes');
                }
                else
                {
                    new MMessage('Por favor, selecione outra questão, esta questão não tem opções válidas'); 
                }
            }
            else
            {
                new MMessage('A questão selecionada para importar os dados não é do mesmo tipo da questão a ser registrada/atualizada. Por favor, selecione uma questão a importar do mesmo tipo');
            }
        }
        else
        {
            new MMessage('Por favor, selecione uma questão para executar a importação');
        }
    }
    
    /**
     * Ação do botão salvar.
     */
    public function saveButton_click()
    {
        if( ! $this->validate() )
        {
            new MMessageWarning('Verifique os dados informados');
        }
        else
        {
            $MIOLO = MIOLO::getInstance();
            $module = MIOLO::getCurrentModule();
            $action = MIOLO::getCurrentAction();
            $data = MUtil::getAjaxActionArgs();
            
            if (!is_null($data->tipo))
            {
                $options = ADynamicFields::parseFields($data);
            }
            
            $data->opcoes = serialize($options);
            
            // Ticket #29671. Quando o tipo de questão é multi resposta, o objeto é diferente, foi deixado assim para não ter impacto no restante do sistema.
            if ( $data->tipo == ADynamicFields::TIPO_QUESTAO_MULTIPLA_ESCOLHA_MULTI_RESPOSTA ||  $data->tipo == ADynamicFields::TIPO_QUESTAO_MULTIPLA_ESCOLHA_SELECAO )
            {
                $options = $options->opcoes;
            }
            
            $data->opcoesUnserialize = $options;
            
            $type = new $this->target($data);
            $pk = $type->getPrimaryKeyAttribute();
            switch ( MIOLO::_REQUEST('function') )
            {
                case 'insert':
                    try
                    {
                        if ( $type->insert() )
                        {
                            $linkOpts[$pk] = $type->__get($pk);
                            $linkOpts['function'] = 'search';
                            $link = new MLinkButton(null,"($linkOpts[$pk])",$MIOLO->getActionUrl($module, $action, null, $linkOpts));
                            new MMessageSuccess(_M(MSG_RECORD_INSERTED, $module, $link->generate()));
                        }
                    }
                    catch ( Exception $e )
                    {
                        new MMessageError(MSG_RECORD_INSERT_ERROR . ' ' . $e);
                    }
                    break;
                case 'edit':
                    if ( $type->update() )
                    {
                        $linkOpts[$pk] = $type->__get($pk);
                        $linkOpts['function'] = 'search';

                        $link = new MLinkButton(null,'('.$linkOpts[$pk].')',$MIOLO->getActionUrl($module, $action, null, $linkOpts));
                        new MMessageSuccess(_M(MSG_RECORD_UPDATED, $module, $link->generate()));
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
            $opcoes = unserialize($this->data->__get('opcoes'));
         
            // TODO: Integrar melhor com a ADynamicFields, 
            // Está funcional, porém, fora do padrão (a ideia é que todos os
            // elementos dos tipos sejam controlados pela ADynamicFields)
            if( $this->data->__get('tipo') == ADynamicFields::TIPO_QUESTAO_ABERTA )
            {
                $this->page->setElementValue('size', $opcoes->size);
                $this->page->setElementValue('height', $opcoes->height);            
                $this->page->setElementValue('charLimit', $opcoes->charLimit);
            }
            if( $this->data->__get('tipo') == ADynamicFields::TIPO_QUESTAO_MULTIPLA_ESCOLHA )
            {
                MSubDetail::clearData('opcoes');
                MSubDetail::setData($opcoes, 'opcoes');
            }
            if( $this->data->__get('tipo') == ADynamicFields::TIPO_QUESTAO_MULTIPLA_ESCOLHA_SELECAO )
            {
                MSubDetail::clearData('opcoes');
                MSubDetail::setData($opcoes->opcoes, 'opcoes');
            }            
            if ($this->data->__get('tipo') == ADynamicFields::TIPO_QUESTAO_MULTIPLA_ESCOLHA_MULTI_RESPOSTA)
            {
                MSubDetail::clearData('opcoes');
                MSubDetail::setData($opcoes->opcoes, 'opcoes');
                $this->page->setElementValue('maxSelected', $opcoes->maxSelected);
            }
            
            // Pega os atributos(public,protected) de acordo com o nome do type (target)
            $reflectionClass = new ReflectionClass($this->target);
            foreach ($reflectionClass->getProperties() as $attribute)
            {
                if( is_object($this->{$attribute->name}) ) // Se for um objeto (componente do miolo), seta o valor
                {
                    $this->{$attribute->name}->setValue($this->data->__get($attribute->name));   
                }
            }
        }                        
    }
    
    /**
     * Ação do botão editar.
     */
    public function getQuestionData()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $type = new $this->target();
        $type->__set($type->getPrimaryKeyAttribute(), MUtil::getAjaxActionArgs()->item);
        $type->populate();
        $this->data = $type;
    }
}
?>
