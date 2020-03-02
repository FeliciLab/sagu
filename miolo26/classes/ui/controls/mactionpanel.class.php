<?php

class MActionPanel extends MPanel
{
    public $iconType     = 'large';
    
    public function __construct($name = '', $caption = '', $controls = NULL, $close = '', $icon = '', $iconType='large')
    {
        parent::__construct($name, $caption, $controls, $close, $icon);

        $this->setIconType($iconType);
    }

    public function setIconType($type = 'large')
    {
        $this->iconType = $type;
    }

    public function _getControl($label, $image, $actionURL, $target = NULL)
    {
        $control = new MImageLinkLabel('', $label, $actionURL, $image);

        if( $target != NULL)
        {
            $control->setTarget($target);
        }
        if( $this->iconType != 'large')
        {
            $control->setImageType('icon');
        }
        return $control;
    }


    public function addAction($label, $image, $module = 'main', $action = '', $item = NULL, $args = NULL)
    {
        $actionURL = $this->manager->getActionURL($module, $action, $item, $args);
        $control = $this->_getControl($label, $image, $actionURL);
        $class = 'mPanelCell'.ucfirst($this->iconType);
        $this->addControl($control,'','left',$class);
    }

    public function addLink($label, $image, $link, $target=NULL)
    {
        $actionURL = ($link instanceof MLink) ? $link->href : $link;
        $control = $this->_getControl($label, $image, $actionURL, $target);
        $class = 'mPanelCell'.ucfirst($this->iconType);
        $this->addControl($control,'','left',$class);
    }

    public function insertAction($pos, $label, $image, $module = 'main', $action = '', $item = NULL, $args = NULL)
    {
        $actionURL = $this->manager->getActionURL($module, $action, $item, $args);
        $control = $this->_getControl($label, $image, $actionURL);
        $class = 'mPanelCell'.ucfirst($this->iconType);
        $this->insertControl($pos, $control,'','left',$class);
    }


    public function addUserAction($transaction, $access, $label, $image, $module = 'main', $action = '', $item = '', $args = NULL)
    {
        if ( $this->manager->perms->checkAccess($transaction, $access) )
        {
            $this->addAction($label, $image, $module, $action, $item, $args);
        }
    }


    public function insertUserAction($pos, $transaction, $access, $label, $image, $module = 'main', $action = '', $item = '', $args = NULL)
    {
        if ( $this->manager->checkAccess($transaction, $access) )
        {
            $this->insertAction($pos, $label, $image, $module, $action, $item, $args);
        }
    }

    public function addGroupAction($transaction, $access, $label, $image, $module = 'main', $action = '', $item = '', $args = NULL)
    {
        if ( $this->manager->perms->checkAccess($transaction, $access, false, true) )
        {
            $this->addAction($label, $image, $module, $action, $item, $args);
        }
    }


    public function insertGroupAction($pos, $transaction, $access, $label, $image, $module = 'main', $action = '', $item = '', $args = NULL)
    {
        if ( $this->manager->checkAccess($transaction, $access, false, true) )
        {
            $this->insertAction($pos, $label, $image, $module, $action, $item, $args);
        }
    }

    public function addBreak()
    {
        $this->addControl( new MSpacer(), '0', 'clear' );
    }
}

?>
