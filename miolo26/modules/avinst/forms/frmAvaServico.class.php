<?php

/**
 * Formulário para inserir, editar e remover registros da tabela ava_servico.
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
class frmAvaServico extends AManagementForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaServico';
        parent::__construct(_M('Serviço', MIOLO::getCurrentModule()));
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        if ( MIOLO::_REQUEST('function')  ==  'edit' )
        {
            $fields[] = new MTextField('idServico', '', _M('Código do serviço', $module), 10, null, null, true);
            $validators[] = new MIntegerValidator('idServico', '', 'required');
        }

        $fields[] = new MTextField('descricao', '', _M('Descrição', $module), 70);
        $fields[] = new MMultilineField('localizacao', '', _M('Localização', $module), 70, 5, 70);
        $fields[] = new MMultilineField('metodo', '', _M('Método', $module), 70, 5, 70);
        $fields[] = new MMultilineField('parametros', '', _M('Parâmetros', $module), 70, 5, 70);
        $label = new MLabel(_M('Atributos', $module).':');
        $label->setClass('mCaption');
        $sFields[] = new MSpan(null,$label,'label');
        $sFields[] = new MButton('btnServiceTest',_M('Importar atributos do serviço', $module));
        $sFields[] = new MSpan('divServiceTest',null,null,'style="margin-left:1%"');
        $fields[] = new MDiv(null, $sFields, 'mFormRow');        
        unset($sFields);
        $sFields[] = new MSpan(null,'&nbsp','label');
        $MIOLO->uses('classes/aservice.class.php',$module);
        // Campos da subdetail
        $sdtFields[] = new MTextField('atributoServico', NULL, 'Atributo do serviço', 20);
        $sdtFields[] = new MSelection('atributoSistema', null, _M('Atributo do sistema', $module), AService::getSystemAttributes());
        $sdtFields[] = new MLabel('<br>');
        // Colunas da grid da Subdetail
        $sdtFieldsColumns[] = new MGridColumn('Atributo do serviço', 'left', false, '50%', true, 'atributoServico');
        $sdtFieldsColumns[] = new MGridColumn('Atributo do sistema', 'left', false, '50%', true, 'atributoSistema', AService::getSystemAttributes());
        $sFields[] = $sdt = new MSubDetail('atributos', _M('Atributos do serviço X Atributos do sistema'), $sdtFieldsColumns, $sdtFields, array('edit'));
        $sdt->setAttribute('style','width: 70%');
        $this->page->onLoad('dojo.byId("clearDataatributos").style.display = "none"');
        $this->page->onLoad('dojo.byId("addDataatributos").style.display = "none"');
        if( MUtil::isFirstAccessToForm() )
        {
            MSubDetail::clearData('atributos');
        }
        $fields[] = new MDiv(null, $sFields, 'mFormRow');
        $fields[] = new MLabel('<br>');
        $fields[] = $this->getButtons();
        $this->addFields($fields);        
        $validators[] = new MRequiredValidator('descricao');
        $validators[] = new MRequiredValidator('localizacao');
        $validators[] = new MRequiredValidator('metodo');
        $this->setValidators($validators);
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
        $data->atributos = serialize(MSubDetail::getData('atributos'));
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
                        new MMessageSuccess(_M(MSG_RECORD_INSERTED,avinst,$link->generate()));
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
    
	/**
     * Ação do botão editar.
     */
    public function editButton_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $type = new $this->target();
        $type->__set($type->getPrimaryKeyAttribute(),MUtil::getAjaxActionArgs()->item);
        $type->populate();
        $type->__set('atributos',unserialize($type->__get('atributos')));
        
        // Pega os atributos(public,protected) de acordo com o nome do type (target)
        $reflectionClass = new ReflectionClass($this->target);
        foreach ($reflectionClass->getProperties() as $attribute)
        {
            if( is_object($this->{$attribute->name}) ) // Se for um objeto (componente do miolo), seta o valor
            {
                if( $this->{$attribute->name} instanceof MSubDetail )
                {
                    if( MUtil::isFirstAccessToForm() )
                    {
                        MSubDetail::setData($type->__get($attribute->name), $attribute->name);
                    }
                }
                else
                {
                    // FIXME: Adicionar setValue no ARadioButtonGroup para setar o checked e não o value
                    // Mas $this->obrigatorio se refere ao campo com id "obrigatorio".
                    // Nesse caso há duas instâncias de MRadioButton com esse id. 
                    // E o atributo value de um input do tipo "radio" não deve ser alterado.
                    // Ao invés disso, deve ser verificado qual dos inputs tem o valor desejado e adicionar o atributo checked nele.
                    // Recomendo implementar um setValue no componente ARadioButtonGroup e fazer mais checagens antes de chamar o setValue do campo.
                    $this->{$attribute->name}->setValue($type->__get($attribute->name));
                }   
            }
        }                        
    }
    
	/*
     * Função que importa os atributos de retorno dos serviços
     */
    public function btnServiceTest_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $args = MUtil::getAjaxActionArgs();
        $avaServico = new avaServico();
        $avaServico->__set('localizacao',$args->localizacao);
        $avaServico->__set('metodo',$args->metodo);
        $avaServico->__set('parametros',$args->parametros);
        $serviceData = $avaServico->chamaServico();
        
        if( is_array($serviceData) && count($serviceData) > 0 )
        {
            $data = array_shift($serviceData);
            
            if ( is_object($data) )
            {
                foreach ( $data as $attribute => $value )
                {
                    $obj = new stdClass();
                    $obj->atributoServico = $attribute;
                    $obj->atributoSistema = null; 
                    $attributes[] = $obj;                                
                }
                $return = _M('Array de objetos',$module);
            }
            else
            {
                foreach ( $serviceData as $attribute => $value )
                {
                    $obj = new stdClass();
                    $obj->atributoServico = $attribute;
                    $obj->atributoSistema = null; 
                    $attributes[] = $obj;                                
                }
                $return = _M('Array',$module);
            }
        }
        elseif ( is_object($var) )
        {
            foreach ( $serviceData as $attribute => $value )
            {
                $obj = new stdClass();
                $obj->atributoServico = $attribute;
                $obj->atributoSistema = null;
                $attributes[] = $obj;
            }
            $return = _M('Objeto',$module);
        }
        else
        {
            $return = _M('String',$module);
        }
        
        MSubDetail::clearData('atributos');

        if( ! is_null($attributes) )
        {
            MSubDetail::setData($attributes,'atributos');
        }
        
        $status = _M('Retorno: @1', $module, $return);
        $MIOLO->page->onLoad("dojo.byId('divServiceTest').innerHTML = '$status';");
        //$this->setResponse(array($link),array('divServiceTest'));        
    }
    
    public function editFromTable()
    {
        $this->page->onLoad('dojo.byId("addDataatributos").style.display = ""');
        MSubDetail::editFromTable(MUtil::getAjaxActionArgs());
    }
}


?>