<?php
/**
 *  Formulário herdado pelos formulários de pesquisa na avaliação institucional
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
 * Class AForm
 *
 */
class AForm extends MForm
{
    // Variável com o objeto da barra de ferramentas
    // Essa variável deve ser usada para desabilitar botões e para definir os links dos botões
    //public $toolbarField;
    
    // indica a classe do objeto em questão
    public $target;
    
    // verificacao para ativar o eventHandler
    public static $doEventHandler;
    
    public function __construct($title)
    {
        $MIOLO = MIOLO::getInstance();
        
        if( isset($this->target) )
        {
            $MIOLO->uses( "types/$this->target.class.php", $MIOLO->getCurrentModule() );
        }

        parent::__construct( '<font size="2" style="vertical-align: 50%">'.$title.'</font>' );

        unset($this->formBox->boxTitle); // Tira o box de título das janelas 

        if ( !self::$doEventHandler )
        {
            $this->eventHandler();
            self::$doEventHandler = true;
        }
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
                $MIOLO->page->redirect( $MIOLO->getActionURL( $module, $MIOLO->getCurrentAction(), '', array('function'=>'search') ) );
            }
        }
    }
}
?>