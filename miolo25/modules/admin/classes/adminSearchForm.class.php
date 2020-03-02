<?php

class AdminSearchForm extends MForm
{

    public function __construct($title)
    {
        parent::__construct( $title );
        $this->eventHandler();
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $toolbar = new MToolBar( 'toolbar', $MIOLO->getActionURL( $this->module, $this->handler ), MToolBar::TYPE_ICON_TEXT );
        $toolbar->disableButtons( MToolBar::BUTTON_SEARCH );
        $toolbar->hideButtons( array( 
                MToolBar::BUTTON_SAVE, 
                MToolBar::BUTTON_DELETE, 
                MToolBar::BUTTON_PRINT 
        ) );
        
        $fields[] = $toolbar;
        $fields[] = MMessage::getMessageContainer();

        $this->setFields( $fields );
        $this->setShowPostButton( FALSE );
    }

    protected function btnSearch_click()
    {
        $this->search_click();
    }

}

?>
