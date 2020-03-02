<?php

/**
 * Component to group fields in tabs
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/07/20
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2010-2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('m_tabbedbasegroup.js');

class MTabbedBaseGroup extends MBaseGroup
{
    private $tabs;

    /**
     * @var boolean Define if must animate when changing between tabs.
     */
    private $animate;
    
    /**
     * @var boolean Sets whether navigable. 
     */
    private $navigable;

    /**
     * MTabbedBaseGroup constructor
     *
     * @param string $name Id and name of the component
     * @param array $tabs Array of MTab objects
     * @param boolean $animate Set if the tabs must be animated.
     * @param boolean $navigable Sets whether navigable.
     */
    public function __construct($name, $tabs=array(), $animate=TRUE, $navigable=FALSE)
    {
        parent::__construct($name);
        $this->tabs = $tabs;

        if ( $this->manager->checkMobile() )
        {
            $animate = FALSE;
            $navigable = TRUE;
        }
        else
        {
            if ( $animate )
            {
                $this->manager->page->addDojoRequire('dojo.fx');
            }

            // FIXME: Navigable is currently not supported by non mobile devices
            $navigable = FALSE;
        }

        $this->animate = $animate;
        $this->navigable = $navigable;
        $this->setClass('mTabbedBaseGroup');
    }

    /**
     * Creates a tab and adds it to the container.
     *
     * @param string $id Id of the tab.
     * @param string $label Label of the tab.
     * @param array $controls Fields of the tab.
     * @param string $ajaxAction An ajax action to be called when the tab is selected.
     * @param boolean $disabled Informs whether the tab is disabled or not.
     * @param boolean $isInitial Defines if the tab must be the initial.
     */
    public function createTab($id, $label, $controls=array(), $ajaxAction=NULL, $disabled=FALSE, $isInitial=FALSE)
    {
        $tab = new MTab($id, $label, $controls, $ajaxAction, $disabled, $isInitial);
        $tab->setWidth('100%');
        $tab->addStyle('display', 'none');
        $this->tabs[$id] = $tab;
    }

    /**
     * Statically creates a tab and adds it to the container
     * Usefull for ajax requests
     *
     * Note: Use it only if you really need it. If not in an ajax request,
     * you should use the createTab method instead.
     *
     * @param string $tabbedBaseGroupId Id of the MTabbedBaseGroup
     * @param string $id Id of the tab
     * @param string $label Label of the tab
     * @param array $controls Fields of the tab
     * @param string $ajaxAction An ajax action to be called when the tab is selected
     * @param boolean $disabled Informs whether the tab is disabled or not
     */
    public static function createStaticTab($tabbedBaseGroupId, $id, $label, $controls = array(), $ajaxAction = NULL, $disabled = false)
    {
        $MIOLO = MIOLO::getInstance();

        $tab = new MTab($id, $label, $controls, $ajaxAction, $disabled);
        $tab->setWidth('100%');
        $tab->addStyle('display', 'none');

        $button = self::getTabButton($tab, $tabbedBaseGroupId);
        $bGenerate = $button->generate();
        $bGenerate = str_replace("\n", '\n', $bGenerate);
        $bGenerate = str_replace("'", "\'", $bGenerate);

        $dGenerate = $tab->generate();
        $dGenerate = str_replace("\n", '\n', $dGenerate);
        $dGenerate = str_replace("'", "\'", $dGenerate);

        $js = "mtabbedbasegroup.createTab('$id', '$tabbedBaseGroupId', '$bGenerate', '$dGenerate', '$ajaxAction');";

        $MIOLO->page->onload($js);
        $MIOLO->ajax->setResponse(NULL, "{$tabbedBaseGroupId}ResponseDiv");
    }

    /**
     * @return array Array of MTab objects
     */
    public function getTabs()
    {
        return $this->tabs;
    }

    /**
     * @return array Array of MTab objects.
     */
    public function getControls()
    {
        return $this->tabs;
    }

    /**
     * @param array Array of MTab objects
     */
    public function setTabs($tabs = array())
    {
        $this->tabs = $tabs;
    }

    /**
     * @param MTab Tab to be added on tabs array
     */
    public function addTab($tab)
    {
        $this->tabs[$tab->id] = $tab;
    }

    /**
     * @param string Id of the tab to be getted from tabs array
     */
    public function getTab($tabId)
    {
        return $this->tabs[$tabId];
    }

    /**
     * @param string $id Set the id of the intitial tab.
     */
    public function setInitialTab($id)
    {
        foreach ( $this->tabs as $tab )
        {
            $tab->isInitial = FALSE;
        }

        $this->tabs[$id]->isInitial = TRUE;
    }

    /**
     * Generates the tab buttons
     *
     * @return object MHContainer
     */
    protected static function getTabButton($tab, $tabbedBaseGroupId, $selected=FALSE)
    {
        $MIOLO = MIOLO::getInstance();

        $tempDiv = new MDiv($tab->id . 'Button', $tab->label);

        //controla se é por ajax ou aba estatica, ou desabilita
        if ( !$tab->disabled )
        {
            $tempDiv->setClass('mTab mTabIdle');

            if ( !$tab->ajaxAction )
            {
                $tempDiv->addAttribute('onclick', "mtabbedbasegroup.changeTab('{$tab->id}', '$tabbedBaseGroupId');");
            }
            else
            {
                $url = $MIOLO->getUI()->getAjax($tab->ajaxAction);
                $tempDiv->addAttribute('onclick', "$url; mtabbedbasegroup.changeTab('{$tab->id}','$tabbedBaseGroupId');");
                $MIOLO->page->onload("mtabbedbasegroup.ajaxTabs.$tab->id = true;");
            }

            if ( $selected )
            {
                $tab->addStyle('display', 'block');
                $MIOLO->page->onload("if ( !document.mtabbedbasegroup_lastTab ) { miolo.getElementById('{$tab->id}Button').onclick(); }");
            }
            else
            {
                $MIOLO->page->onload("mtabbedbasegroup.changeToLastTab(document.mtabbedbasegroup_lastTab, '$tabbedBaseGroupId');");
            }
        }
        else
        {
            $tempDiv->setClass('mTab mTabDisabled');

            if ( $tab->ajaxAction )
            {
                $url = $MIOLO->getUI()->getAjax($tab->ajaxAction);
                $tempDiv->addAttribute('onclick', "return false;$url");
            }
        }
        
        if ( $tab->isInitial )
        {
            $MIOLO->page->onload("mtabbedbasegroup.changeToLastTab('$tab->id', '$tabbedBaseGroupId');");
        }

        return $tempDiv;
    }

    /**
     * @param boolean $animate Set if the tabs must be animated.
     */
    public function setAnimate($animate)
    {
        $this->animate = $animate;
    }

    /**
     * @return boolean Get if the tabs must be animated.
     */
    public function getAnimate()
    {
        return $this->animate;
    }
    
    /**
     * @param boolean $navigable Sets whether navigable.
     */
    public function setNavigable($navigable)
    {
        // FIXME: Navigable is currently not supported by non mobile devices
        if ( $this->manager->checkMobile() )
        {
            $this->navigable = $navigable;
        }
    }
    
    /**
     * @return booean Get if the tabs must be navigable. 
     */
    public function getNavigable()
    {
        return $this->navigable;
    }

    /**
     * Generates the tabs container
     *
     * @return object MHContainer
     */
    public function generate()
    {
        $MIOLO = MIOLO::getInstance();
        $ui = $MIOLO->getUI();

        $jsCode = "{$this->name}Tabs = new Array(); \n";
        $index = 0;

        $tabButtons = array();

        if ( $this->navigable )
        {
            $tabButtons[] = $action = new MImageLink('', '', '', $ui->getImageTheme(NULL, 'back_16x16.png'));
            $action->setOnClick("javascript:mtabbedbasegroup.moveLeft('$this->name'); return false;");
            $action->addStyle('float', 'left');
            $action->addStyle('padding-right', '4px');
            $action->setClass('mTabbedBaseGroupLeftArrow');
        }

        foreach ( $this->tabs as $tab )
        {
            $tabButtons[$tab->id] = self::getTabButton($tab, $this->name, $index == 0 ? true : false);

            $this->addControl($tab);
            $jsCode .= "{$this->name}Tabs[{$index}] = '{$tab->id}';\n";

            if ( $tab->ajaxAction )
            {
                $jsCode .= "mtabbedbasegroup.ajaxTabs.$tab->id = true;\n";
            }

            $index++;
        }
        
        if ( $this->navigable )
        {
            $tabButtons[] = $action =  new MImageLink('', '', '', $ui->getImageTheme(NULL, 'next_16x16.png'));
            $action->setOnClick("javascript:mtabbedbasegroup.moveRight('$this->name'); return false;");
            $action->addStyle('position', 'relative');
            $action->addStyle('right', '0');
            $action->addStyle('padding-left', '4px');
            $action->setClass('mTabbedBaseGroupRightArrow');
        }

        if ( !$this->animate )
        {
            $jsCode .= "mtabbedbasegroup.animate = false;\n";
        }

        $divButtons = new MDiv("buttons{$this->name}", $tabButtons);
        $divButtons->setClass('mTabButtons');

        // Shows only the current tab
        $MIOLO->page->addJsCode($jsCode);

        $buttonContainer = new MHContainer("tabButtonContainer{$this->name}", array($divButtons));

        // Div for AJAX responses
        $responseDiv = new MDiv("{$this->name}ResponseDiv", NULL);

        return $responseDiv->generate() . $buttonContainer->generate() . parent::generate();
    }

    /* Static methods to use on ajax requests */

    /**
     * Enable tab.
     *
     * @param string $tabId
     * @param string $tabbedBaseGroupId
     */
    public static function enableTab($tabId, $tabbedBaseGroupId)
    {
        $jsCode = "mtabbedbasegroup.enableTab('{$tabId}', '{$tabbedBaseGroupId}');";

        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onload($jsCode);
        $MIOLO->ajax->setResponse(NULL, "{$tabbedBaseGroupId}ResponseDiv");
    }

    /**
     * Disable tab.
     *
     * @param string $tabId
     * @param string $tabbedBaseGroupId
     */
    public static function disableTab($tabId, $tabbedBaseGroupId)
    {
        $jsCode = "mtabbedbasegroup.disableTab('{$tabId}', '{$tabbedBaseGroupId}');";

        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onload($jsCode);
        $MIOLO->ajax->setResponse(NULL, "{$tabbedBaseGroupId}ResponseDiv");
    }

    /**
     * Removes a tab from the tab list
     *
     * @param string $tabId
     * @param string $tabbedBaseGroupId
     */
    public static function removeTab($tabId, $tabbedBaseGroupId)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onload("mtabbedbasegroup.removeTab('{$tabId}','{$tabbedBaseGroupId}');");
        $MIOLO->ajax->setResponse(NULL, "{$tabbedBaseGroupId}ResponseDiv");
    }

    /**
     * Updates tab content through ajax
     *
     * @param string $tabId Id of the tab to put content
     * @param array $controls Fields to put in the tab
     */
    public static function updateTab($tabId, $controls)
    {
        $MIOLO = MIOLO::getInstance();
        $container = new MVContainer("ajaxFields$tabId", $controls, MFormControl::FORM_MODE_SHOW_SIDE);

        $MIOLO->ajax->setResponse( $container, $tabId );
    }
}

/**
 * Tab class used in the 
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/07/20
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2010 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */
class MTab extends MVContainer
{
    public $label;
    public $ajaxAction;
    public $disabled;
    public $isInitial;

    /**
     * MTab constructor
     *
     * @param string $id Id of the tab
     * @param string $label Label of the tab
     * @param array $controls Fields of the tab
     * @param string $ajaxAction An ajax action to be called when the tab is selected
     * @param boolean $disabled Informs whether the tab is disabled or not
     * @param boolean $isInitial Informs whether the tab is the initial one.
     */
    public function __construct($id, $label, $controls, $ajaxAction=NULL, $disabled=FALSE, $isInitial=FALSE)
    {
        parent::__construct($id, $controls, MFormControl::FORM_MODE_SHOW_SIDE);
        $this->label = $label;
        $this->ajaxAction = $ajaxAction;
        $this->disabled = $disabled;
        $this->isInitial = $isInitial;
    }

    /**
     * @param boolean Sets disabled to false
     */
    public function setEnabled()
    {
        $this->disabled = false;
    }

    /**
     * @param boolean Sets disabled to true
     */
    public function setDisabled()
    {
        $this->disabled = true;
    }
}
?>
