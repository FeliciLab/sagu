<?php

/**
 * Class MToolBar.
 *
 * @author Daniel Afonso Heisler
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2005/08/04
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solções Livres \n
 * The MIOLO2 AND SAGU2 Development Team
 *
 * \b Copyright: \n
 * Copyright (c) 2005 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */

class MToolBar extends MBaseGroup
{
    /**
     * Button's name constants
     *
     */
    const BUTTON_NEW = 'tbBtnNew';
    const BUTTON_SAVE = 'tbBtnSave';
    const BUTTON_DELETE = 'tbBtnDelete';
    const BUTTON_SEARCH = 'tbBtnSearch';
    const BUTTON_PRINT = 'tbBtnPrint';
    const BUTTON_RESET = 'tbBtnReset';
    const BUTTON_EXIT = 'tbBtnExit';

    /**
     * Toolbar button types
     */
    const TYPE_ICON_ONLY = 'icon-only';
    const TYPE_TEXT_ONLY = 'text-only';
    const TYPE_ICON_TEXT = 'icon-text';

    protected $toolBarButtons;
    protected $width;

    /**
     * MToolbar constructor
     * 
     * @param string $name Toolbar name
     * @param string $url Default URL
     * @param string $type Buttons type: MToolBar::TYPE_ICON_ONLY, MToolBar::TYPE_ICON_TEXT or MToolBar::TYPE_TEXT_ONLY
     * 
     */
    public function __construct($name='toolbar', $url='', $type=MToolbar::TYPE_ICON_ONLY)
    {
        parent::__construct($name, '');

        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        $this->name = $name;

        if ( !$url )
        {
            $url = $MIOLO->getActionURL($module, $action);
        }

        $enabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-new.png');
        $disabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-new-disabled.png');
        $event = $url . "&{$MIOLO->page->getFormId()}__EVENTTARGETVALUE=" . MToolBar::BUTTON_NEW . ':click';
        $eventURL = $event . '&function=insert';
        $this->toolBarButtons[MToolBar::BUTTON_NEW] = new MToolBarButton(MToolBar::BUTTON_NEW, _M('New'), $eventURL, _M('Click to insert new record'), true, $enabledImage, $disabledImage, NULL, $type);

        $enabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-save.png');
        $disabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-save-disabled.png');
        $event = MToolBar::BUTTON_SAVE . ':click';
        $newUrl = "javascript:miolo.doPostBack('$event','','{$MIOLO->page->getFormId()}');";
        $this->toolBarButtons[MToolBar::BUTTON_SAVE] = new MToolBarButton(MToolBar::BUTTON_SAVE, _M('Save'), $newUrl, _M('Click to save this record'), true, $enabledImage, $disabledImage, NULL, $type);

        $enabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-delete.png');
        $disabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-delete-disabled.png');
        $event = MToolBar::BUTTON_DELETE . ':click';
        $newUrl = "javascript:miolo.doAjax('$event','','{$MIOLO->page->getFormId()}');";
        $this->toolBarButtons[MToolBar::BUTTON_DELETE] = new MToolBarButton(MToolBar::BUTTON_DELETE, _M('Delete'), $newUrl, _M('Click to delete this record'), true, $enabledImage, $disabledImage, NULL, $type);

        $enabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-search.png');
        $disabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-search-disabled.png');
        $event = $url . "&{$MIOLO->page->getFormId()}__EVENTTARGETVALUE=" . MToolBar::BUTTON_SEARCH . ':click';
        $eventURL = $event . '&function=search';
        $this->toolBarButtons[MToolBar::BUTTON_SEARCH] = new MToolBarButton(MToolBar::BUTTON_SEARCH, _M('Search'), $eventURL, _M('Click to go to search page'), true, $enabledImage, $disabledImage, NULL, $type);

        $enabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-print.png');
        $disabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-print-disabled.png');
        $event = MToolBar::BUTTON_PRINT . ':click';
        $newUrl = "javascript:miolo.doAjax('$event','','{$MIOLO->page->getFormId()}');";
        $this->toolBarButtons[MToolBar::BUTTON_PRINT] = new MToolBarButton(MToolBar::BUTTON_PRINT, _M('Print'), $newUrl, _M('Click to print'), true, $enabledImage, $disabledImage, NULL, $type);

        $enabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-reset.png');
        $disabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-reset-disabled.png');
        $newUrl = 'document.forms[0].reset();';
        $this->toolBarButtons[MToolBar::BUTTON_RESET] = new MToolBarButton(MToolBar::BUTTON_RESET, _M('Reset'), $newUrl, _M('Click to reset the form'), true, $enabledImage, $disabledImage, NULL, $type);

        $enabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-exit.png');
        $disabledImage = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'toolbar-exit-disabled.png');
        $eventURL = $MIOLO->getConf('home.url') . '/' . $MIOLO->getPreviousUrl();
        $this->toolBarButtons[MToolBar::BUTTON_EXIT] = new MToolBarButton(MToolBar::BUTTON_EXIT, _M('Exit'), $eventURL, _M('Click to exit this form'), true, $enabledImage, $disabledImage, NULL, $type);

        $this->setShowChildLabel(false);
    }

    /**
     * Adds a custom button.
     *
     * @param string $name MToolbarButton name.
     * @param string $caption Caption description.
     * @param string $url Button action.
     * @param string $jsHint Button Javascript hint.
     * @param boolean $enable Button status.
     * @param string $enabledImage Complete image URL.
     * @param string $disabledImage Complete image URL.
     * @param string $method @deprecated
     * @param string $type Button type: MToolBar::TYPE_ICON_ONLY, MToolBar::TYPE_ICON_TEXT or MToolBar::TYPE_TEXT_ONLY.
     * 
     */
    public function addButton($name, $caption, $url, $jsHint, $enabled, $enabledImage, $disabledImage, $type=MToolBar::TYPE_ICON_ONLY)
    {
        $this->toolBarButtons[$name] = new MToolBarButton($name, $caption, $url, $jsHint, $enabled, $enabledImage, $disabledImage, NULL, $type);
    }

    /**
     * Shows one or more buttons
     *
     * @param $name (string or array) Button's name
     */
    public function showButtons($name)
    {
        if ( is_array($name) )
        {
            foreach ( $name as $n )
            {
                $this->toolBarButtons[$n]->show();
            }
        }
        else
        {
            $this->toolBarButtons[$name]->show();
        }
    }

    /**
     * Shows one or more buttons
     *
     * @deprecated use showButtons instead
     * 
     * @param mixed $name Button's name (string or array).
     */
    public function showButton($name)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->logMessage('[DEPRECATED] Call method MToolbar::showButton() is deprecated -- use MToolbar::showButtons() instead!');

        $this->showButtons($name);
    }

    /**
     * Hides one or more buttons
     *
     * @param mixed $name Button's name (string or array).
     */
    public function hideButtons($name)
    {
        if ( is_array($name) )
        {
            foreach ( $name as $n )
            {
                $this->toolBarButtons[$n]->hide();
            }
        }
        else
        {
            $this->toolBarButtons[$name]->hide();
        }
    }

    /**
     * Hides one or more buttons
     *
     * @deprecated use hideButtons instead
     * 
     * @param mixed $name Button's name (string or array).
     */
    public function hideButton($name)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->logMessage('[DEPRECATED] Call method MToolbar::hideButton() is deprecated -- use MToolbar::hideButtons() instead!');

        $this->hideButtons($name);
    }

    /**
     * Enables one or more buttons
     *
     * @param mixed $name Button's name (string or array).
     */
    public function enableButtons($name)
    {
        if ( is_array($name) )
        {
            foreach ( $name as $n )
            {
                $this->toolBarButtons[$n]->enable();
            }
        }
        else
        {
            $this->toolBarButtons[$name]->enable();
        }
    }

    /**
     * Enables one or more buttons
     *
     * @deprecated use enableButtons instead
     * 
     * @param mixed $name Button's name (string or array).
     */
    public function enableButton($name)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->logMessage('[DEPRECATED] Call method MToolbar::enableButton() is deprecated -- use MToolbar::enableButtons() instead!');

        $this->enableButtons($name);
    }

    /**
     * Disables one or more buttons
     *
     * @param mixed $name Button's name (string or array).
     */
    public function disableButtons($name)
    {
        if ( is_array($name) )
        {
            foreach ( $name as $n )
            {
                $this->toolBarButtons[$n]->disable();
            }
        }
        else
        {
            $this->toolBarButtons[$name]->disable();
        }
    }

    /**
     * Disables one or more buttons
     *
     * @deprecated use disablesButtons instead
     * 
     * @param mixed $name Button's name (string or array).
     */
    public function disableButton($name)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->logMessage('[DEPRECATED] Call method MToolbar::disableButton() is deprecated -- use MToolbar::disableButtons() instead!');

        $this->disableButtons($name);
    }

    /**
     * Set button's type
     * 
     * @param string $type Button type: MToolBar::TYPE_ICON_ONLY, MToolBar::TYPE_ICON_TEXT or MToolBar::TYPE_TEXT_ONLY
     * 
     */
    public function setType($type=MToolBar::TYPE_ICON_ONLY)
    {
        foreach ( $this->toolBarButtons as $tbb )
        {
            $tbb->setType($type);
        }
    }

    /**
     * Set toolbar width
     *
     * @param string $width Width size
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Add custom control to toolbar
     *
     * @param object $control MControl instance.
     * @param string $name Control name.
     */
    public function addControl($control, $name=NULL)
    {
        parent::addControl($control);

        if ( $name )
        {
            $this->toolBarButtons[$name] = $control;
        }
    }

    /**
     * Generate inner content.
     */
    public function generateInner()
    {
        parent::__construct($this->name, '', $this->toolBarButtons);

        parent::setWidth($this->width);

        parent::generateInner();
    }
}

?>