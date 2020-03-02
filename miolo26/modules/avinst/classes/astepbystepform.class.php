<?php
/**
 *  Formulário herdado pelos formulários de passo a passo na inserção e edição da avaliação institucional
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/11/22
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2008 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

//$MIOLO->uses( 'classes/sfields.class.php','scotty2' );
//$MIOLO->uses( 'classes/uvalidator.class.php','adminUnivates' );

/*
 * Class AStepByStepForm
 *
 */
class AStepByStepForm extends MStepByStepForm
{
    // indica a classe do objeto em questão
    public $target;
    
    public function __construct( $title, $steps = NULL, $step = NULL, $nextStep = NULL )
    {
        $function = MIOLO::_REQUEST('function') == 'edit' ? _M('Atualizar') : _M('Adicionar');
        parent::__construct( '<font size="2" style="vertical-align: 50%">'.$function.' '.mb_strtolower($title).'</font>', $steps, $step, $nextStep );
        unset($this->formBox->boxTitle); // Tira o box de título das janelas
    }
    
    public function createFields()
    {
        $fields['toolbar'] = new MToolBar('toolbar',null,MToolBar::TYPE_ICON_TEXT);
        $fields['toolbar']->hideButtons(array(MToolBar::BUTTON_PRINT));
        $fields[] = MMessage::getMessageContainer();
        $fields[] = MPopup::getPopupContainer();
        $fields[] = new MLabel('<br>'); // Espaço para os campos
        $this->addFields($fields);        
        $this->setShowPostButton( FALSE );
        
        if( MIOLO::_REQUEST('function') == 'insert' )
        {
            $this->toolbar->hideButtons(MToolBar::BUTTON_DELETE);
        }
        else
        {
            $this->checkDeleteButtton();
            $this->toolbar->hideButtons(MToolBar::BUTTON_RESET);            
        }
        
        $this->toolbar->hideButtons(MToolBar::BUTTON_SAVE);
        $this->toolbar->hideButtons(MToolBar::BUTTON_EXIT);
        $this->setJsValidationEnabled(false);
        $this->page->onLoad('setFocus();'); // Coloca o foco sempre no primeiro campo da tela
        parent::createFields();
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
        $type = new $this->target($this->getData());
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
                    new MMessageError(MSG_RECORD_INSERT_ERROR);
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
                    new MMessageError(MSG_RECORD_UPDATE_ERROR);
                }

                break;
        }
    }
    
	/**
     * Ação do botão salvar da toolbar
     *
     * @param array $args Request arguments
     */
    public function tbBtnSave_click()
    {
        $this->saveButton_click();
    }
    
	/**
     * Ação do botão deletar
     *
     * @param array $args Request arguments
     */
    public function deleteButton_click()
    {
        $module = MIOLO::getCurrentModule();
        $args = MUtil::getAjaxActionArgs();
        MPopup::confirm( _M(MSG_CONFIRM_RECORD_DELETE, $module, "<b>($args->item)</b>"), _M('Confirmação de exclusão', $module), 'mpopup.remove();'.MUtil::getAjaxAction('deleteButtonConfirm_click') );        
    }
    
	/**
     * Ação do botão deletar da toolbar
     *
     * @param array $args Request arguments
     */
    public function tbBtnDelete_click()
    {
        $this->deleteButton_click();
    }
    
	/**
     * Ação de confirmação do botão deletar
     *
     * @param array $args Request arguments
     */
    public function deleteButtonConfirm_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $data = MUtil::getAjaxActionArgs();
        $MIOLO->uses( "types/$this->target.class.php", $module );
        $type = new $this->target();
        $type->__set($type->getPrimaryKeyAttribute(),$data->item);
        
        if ( $type->delete() )
        {
            if( $data->function == 'search' )
            {
                $this->grid->setData($type->search());
                $this->setResponse($this->grid->generate(), 'divGrid');
                MPopup::remove();
                new MMessageSuccess(_M(MSG_RECORD_DELETED, $module));
            }
            else
            {
                new MMessageSuccess(_M(MSG_RECORD_DELETED, $module),false);
                $MIOLO->page->redirect( $MIOLO->getActionURL( $module, $MIOLO->getCurrentAction() ) );                
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
            $MIOLO = MIOLO::getInstance();
            $module = MIOLO::getCurrentModule();
            $action = MIOLO::getCurrentAction();
            $MIOLO->uses( "types/$this->target.class.php", $MIOLO->getCurrentModule() );
            $type = new $this->target();
            $type->__set($type->getPrimaryKeyAttribute(),MUtil::getAjaxActionArgs()->item);
            $type->populate();
            
            // Grava os dados carregados na sessão 
            $this->setEditData($type);
            
            // Pega os atributos(public,protected) de acordo com o nome do type (target)
            $reflectionClass = new ReflectionClass($this->target);
            foreach ($reflectionClass->getProperties() as $attribute)
            {
                if( is_object($this->{$attribute->name}) ) // Se for um objeto (componente do miolo), seta o valor
                {
                    $this->{$attribute->name}->setValue($type->__get($attribute->name));   
                }
            }
        }                        
    }
    
	/**
     * Seta os dados carregados para a função editar na sessão.
     */
    public function setEditData($type)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getSession()->setValue('editData', serialize($type));
    }
    
    /**
     * Pega os dados carregados na função editar.
     */
    public function getEditData()
    {
        $MIOLO = MIOLO::getInstance();
        return unserialize($MIOLO->getSession()->getValue('editData'));
    }
    
    /**
     * Ação do botão voltar.
     */
    public function backButton_click()
    {
        $MIOLO = MIOLO::getInstance();
        $url = $MIOLO->getActionURL($MIOLO->getCurrentModule(), $MIOLO->getCurrentAction(), '', array('function'=>'search'));
        $MIOLO->page->redirect($url);
    }
    
	/**
     * Função que checa dependências para habilitar/desabilitar o botão excluir.
     */
    public function checkDeleteButtton()
    {
        $table = DB_PREFIX_TABLE . '_' . substr(strtolower($this->target), strlen(DB_PREFIX_TABLE));
            
        if( AVinst::checkTableDependencies($table,MUtil::getAjaxActionArgs()->item) )
        {
            $this->toolbar->hideButtons(MToolBar::BUTTON_DELETE);
        }
    }
}
?>
