<?php

class AdminForm extends MForm
{

    function __construct($title)
    {
        parent::__construct( $title );
        $this->eventHandler();
    }

    function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $toolbar = new MToolBar( 'toolbar', $MIOLO->getActionURL( $this->module, $this->handler ), MToolBar::TYPE_ICON_TEXT );
        $toolbar->disableButtons( MToolBar::BUTTON_NEW );
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

    protected function tbBtnSave_click()
    {
        $this->save_click();
    }

    protected function tbBtnDelete_click()
    {
        $this->delete_click();
    }

    protected function tbBtnNew_click()
    {
        $this->new_click();
    }

    public function save_click($object)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $function = MIOLO::_request( 'function' );
        
        $object = $MIOLO->getBusiness( $module, $object );
        
        if ( $object )
        {
            $data = $this->getData();
            $object->setData( $data );
            
            if ( $function == 'update' )
            {
                $object->setPersistent( true );
            }
            
            $object->save();
            
            if ( $function == 'insert' )
            {
                $actionYes = $MIOLO->getActionURL( $module, $action, '', array( 
                        'event' => 'new:click', 
                        'function' => 'insert' 
                ) );
                $actionNo = $MIOLO->getActionURL( $module, $action, '' );
                $MIOLO->question( _M( 'Record successfully added. Do you want to insert a new record?', $module ), $actionYes, $actionNo );
            }
            else
            {
                $action = $MIOLO->getActionURL( $module, $action, '' );
                new MMessageSuccess(_M('Record succesfully updated.', $module), false);
                $MIOLO->page->redirect($action);
            }
        }
        
        return $object->getId();
    }

    public function delete_click($object, $objectId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        
        $object = $MIOLO->getBusiness( $module, $object );
        $object->getById( $objectId );
        
        $confirm = MIOLO::_request( 'confirm' );
        if ( $confirm == 'yes' )
        {
            $object->delete();
            
            $action = $MIOLO->getActionURL( $module, $action, '' );
            $MIOLO->information( _M( 'Record successfully deleted.', $module ), $action );
        }
        else
        {
            $actionYes = $MIOLO->getActionURL( $module, $action, $objectId, array(
                    'function' => MIOLO::_REQUEST('function'),
                    'event' => 'delete:click', 
                    'confirm' => 'yes' 
            ) );
            $actionNo = $MIOLO->getActionURL( $module, $action, '' );
            $MIOLO->question( _M( 'About to remove record. Are you sure?', $module ), $actionYes, $actionNo );
        }
    }

    public function new_click()
    {
    
    }
}

?>