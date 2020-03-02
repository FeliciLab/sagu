<?php
/**
 *  Formulário herdado pelos formulários de processos na avaliação institucional
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/11/29
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
 * Class AProcessForm
 *
 */
class AProcessForm extends AForm
{
    public $grid;
    
    public function __construct($title)
    {
        parent::__construct($title);
    }
    
    public function createFields()
    {
        parent::createFields();
        $this->toolbar->hideButtons(MToolBar::BUTTON_NEW);
        $this->toolbar->hideButtons(MToolBar::BUTTON_SEARCH);
        $this->toolbar->hideButtons(MToolBar::BUTTON_DELETE);
        $this->toolbar->hideButtons(MToolBar::BUTTON_PRINT);
        $this->setJsValidationEnabled(false);
        $this->page->onLoad('setFocus();'); // Coloca o foco sempre no primeiro campo da tela
    }
    
    public function getButtons()
    {
        $module = MIOLO::getCurrentModule();
        $buttons[] = new MButton('backButton', _M('Voltar', $module));
        $buttons[] = new MButton('saveButton', _M('Salvar', $module));
        return new MDiv(NULL, $buttons, NULL, 'align=center');                            
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
     * Ação padrão para o botão buscar
     *
     * @param array $args Request arguments
     */
    public function searchButton_click()
    {
        $targetType = new $this->target(MUtil::getAjaxActionArgs());
        $data = $targetType->search();
        
        if(  MUtil::getDefaultEventValue() != 'searchButton:click' )
        {
            return $data;
        }
        else
        {
            $this->grid->setData($data);
            $this->setResponse( $this->grid, 'divGrid' );
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
     * Ação do botão limpar ou redefinir.
     */
    public function resetValues()
    {
        foreach (parent::getFieldList() as $field)
        {
            if( is_object($this->{$field->id}) ) // Se for um objeto (componente do miolo), seta o valor
            {
                if( $this->{$field->id} instanceof MEditor )
                {
                    $this->page->onLoad("miolo.getElementById('{$field->id}').innerHTML = '';");
                }
                elseif ( $this->{$field->id} instanceof MTimestampField )
                {
                    $this->page->setElementValue($field->id.'Date', null);
                    $this->page->setElementValue($field->id.'Time', null);
                }
                else
                {
                    $this->page->setElementValue($field->id, null);
                }                
            }
        }        
    }
}
?>
