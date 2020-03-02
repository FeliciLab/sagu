<?php

/**
 * Formulário para inserir, editar e remover registros da tabela ava_widget.
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 09/03/2012
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
$MIOLO->uses('classes/awidgetcontrol.class.php',$module);
class frmAvaWidget extends AManagementForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaWidget';
        parent::__construct(_M('Widget', MIOLO::getCurrentModule()));                
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $MIOLO = MIOLO::getInstance();

        if ( MIOLO::_REQUEST('function') == 'edit' )
        {
            $fields['idWidget'] = new MTextField('idWidget', '', 'Código', 60);
            $fields['idWidget']->setReadOnly(true);
                        
        }
        else
        {
            $widgetObj = new AWidgetControl($params, null, null);
            $widgetsClasses = $widgetObj->listWidgetFiles();
            $avaWidget = new avaWidget();

            foreach ($widgetsClasses as $widgetClass)
            {
                $avaWidget->idWidget = $widgetClass['idWidget'];
                $result = $avaWidget->search();
                if( is_null($avaWidget->search()) )
                {
                    $widgets[$widgetClass['idWidget']] = $widgetClass['name'];
                }            
            }
            
            
            if( is_null($widgets) )
            {
                $fields['idWidget'] = new MDiv('divAviso', 'Todos os widgets estão cadastrados, por favor, adicione novos widgets para permitir seu registro');
            }
            else
            {
                $fields['idWidget'] = new MSelection('idWidget', null, _M('Código'), $widgets);
                $fields['idWidget']->addAttribute('onChange', MUtil::getAjaxAction('widgetLoadInfo', null));
            }
        }

        $fields[] = new MTextField('versao', '', 'Versão', 20, null, null, true);
        $fields[] = new MTextField('nome', '', 'Nome', 60, null, null, true);
        
        // Campos da subdetail perfis
        unset($sdtFields);
        unset($sdtFieldsColumns);
        $sFields[] = new MSpan(null,'&nbsp','label');
        $sdtFields['refPerfil'] = new MLookupContainer('refPerfil', null, 'Perfil', 'avinst', 'Perfil');
        $sdtFields['refPerfil']->setContext('avinst', 'Perfil', 'refPerfil,refPerfil_lookupDescription', array('profilesNotEvaluable'=>'profilesNotEvaluable'));
        $sdtFields['idPerfilWidget'] = new MTextfield('idPerfilWidget', null);
        $sdtFields['idPerfilWidget']->addStyle('display', 'none');
        
        // Colunas da grid da Subdetail
        $sdtFieldsColumns[] = new MGridColumn('idPerfilWidget', null, null, null, null, 'idPerfilWidget');
        $sdtFieldsColumns[] = new MGridColumn('Código', 'left', false, '50%', true, 'refPerfil');
        $sdtFieldsColumns[] = new MGridColumn('Descrição', 'left', false, '50%', true, 'refPerfil_lookupDescription');
        $sFields[] = $sdt = new MSubDetail('perfisWidget', _M('Perfis'), $sdtFieldsColumns, $sdtFields, array('remove'));
        $sdt->setAttribute('style','width: 70%');
        $fields[] = new MDiv(null, $sFields, 'mFormRow');
        $fields[] = $this->getButtons();
        $fields[] = new MHiddenField('profilesNotEvaluable',true);
        $this->addFields($fields);
        if( AVinst::isFirstAccessToForm() )
        {
            MSubDetail::clearData('opcoesPadrao');
            MSubDetail::clearData('perfisWidget');
            $MIOLO->getSession()->setValue('opcoesPadrao', null);
            $MIOLO->getSession()->setValue('perfisWidget', null);
        }
        $validators[] = new MRequiredValidator('idWidget');
        $validators[] = new MRequiredValidator('versao');
        $validators[] = new MRequiredValidator('nome');
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
        $data->opcoesPadrao = serialize(MSubDetail::getData('opcoesPadrao'));
        $data->perfisWidget = MSubDetail::getData('perfisWidget');
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
        $type->__set('opcoesPadrao',unserialize($type->__get('opcoesPadrao')));
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
                        if($attribute->name == 'perfisWidget') // Se for a subDetail perfisWidget
                        {
                            $MIOLO->uses('types/avaPerfil.class.php',$module);
                            $avaPerfil = new avaPerfil();
                            // Monta um objeto com código e descrição para cada perfil
                            foreach ( $type->__get($attribute->name) as $key => $perfil )
                            {
                                $perfis[$key]->idPerfilWidget = $perfil->idPerfilWidget;
                                $avaPerfil->idPerfil = $perfil->refPerfil;
                                $avaPerfil->populate();
                                $perfis[$key]->refPerfil = $avaPerfil->idPerfil;
                                $perfis[$key]->refPerfil_lookupDescription = $avaPerfil->descricao; 
                            }                            
                            $type->perfisWidget = $perfis;
                        }
                        
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

        //
        // Verifica se há avaliações relacionadas com o widget. Se existir, então "bloqueia" a opção de exclusão na toolbar
        //
        $filter = new stdClass();
        $filter->refWidget = MUtil::getAjaxActionArgs()->item;
        $avaPerfilWidget = new avaPerfilWidget($filter);
        $result = $avaPerfilWidget->search(ADatabase::RETURN_TYPE, true);
        
        if (count($result[0]->avaliacaoPerfilWidgets)>0)
        {
            $this->toolbar->hideButtons(MToolBar::BUTTON_DELETE);            
        }                
    }
    
    /**
     * Ação do selection "idWidget".
     */
    public function widgetLoadInfo()
    {
        $widgetName = MUtil::getAjaxActionArgs()->idWidget;
        
        if( AWidgetControl::existsWidgetClass($widgetName) )
        {
            $obj = new $widgetName(null);
            $this->nome->setValue($obj->description);
            $this->versao->setValue($obj->version);               
        }
    }
}

?>