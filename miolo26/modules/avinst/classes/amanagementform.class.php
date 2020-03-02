<?php
/**
 *  Formulário herdado pelos formulários de inserção e edição na avaliação institucional
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/11/16
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

/*
 * Class AManagementForm
 *
 */
class AManagementForm extends AForm
{
    public function __construct($title)
    {
        $function = MIOLO::_REQUEST('function') == 'edit' ? _M('Atualizar') : _M('Adicionar');
        //parent::__construct($function . ' ' . mb_strtolower($title));
        parent::__construct(null);
    }
    
    public function createFields()
    {
        parent::createFields();

        if( MIOLO::_REQUEST('function') == 'insert' )
        {
            $this->toolbar->hideButtons(MToolBar::BUTTON_DELETE);
        }
        else
        {
            $this->checkDeleteButtton();
            $this->toolbar->hideButtons(MToolBar::BUTTON_RESET);
        }
        
        $this->toolbar->hideButtons(MToolBar::BUTTON_EXIT);
        $this->setJsValidationEnabled(false);
        if( AVinst::isFirstAccessToForm() )
        {
            $this->page->onLoad('setFocus();'); // Coloca o foco sempre no primeiro campo da tela
        }
    }
    
    public function getButtons()
    {
        $module = MIOLO::getCurrentModule();
        $buttons[] = new MButton('backButton', _M('Voltar', $module));
        $buttons[] = new MButton('saveButton', _M('Salvar', $module));
        return new MDiv(NULL, $buttons, NULL, 'align=center');                            
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
     * Ação do botão salvar da toolbar
     *
     * @param array $args Request arguments
     */
    public function tbBtnSave_click()
    {
        $this->saveButton_click();
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
        
        // Pega os atributos(public,protected) de acordo com o nome do type (target)
        $reflectionClass = new ReflectionClass($this->target);
        foreach ($reflectionClass->getProperties() as $attribute)
        {
            if( is_object($this->{$attribute->name}) ) // Se for um objeto (componente do miolo), seta o valor
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
    
    public static function addToTable($data)
    {
        MSubDetail::addToTable($data);
    }
}
?>
