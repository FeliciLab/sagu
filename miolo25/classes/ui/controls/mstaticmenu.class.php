<?php

/**
 * Component to create a static menu.
 *
 * @author Armando Taffarel Neto [taffarel@solis.coop.br]
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2011/08/26
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addDojoRequire('dijit.Menu');
$MIOLO->page->addScript('m_menu.js');

class MStaticMenu extends MDiv
{
    /**
     * Constants for the icon CSS classes.There is more at dojo.css present on
     * the theme directory.
     */
    const ICON_CANCEL = 'dijitEditorIcon dijitEditorIconCancel';
    const ICON_COPY = 'dijitEditorIcon dijitEditorIconCopy';
    const ICON_CUT = 'dijitEditorIcon dijitEditorIconCut';
    const ICON_DELETE = 'dijitEditorIcon dijitEditorIconDelete';
    const ICON_DUPLICATE = 'mMenuIcon mMenuIconDuplicate';
    const ICON_EDIT = 'mMenuIcon mMenuIconEdit';
    const ICON_EXPORT = 'mMenuIcon mMenuIconExport';
    const ICON_EXPORT_HTML = 'mMenuIcon mMenuIconExportHTML';
    const ICON_EXPORT_PDF = 'mMenuIcon mMenuIconExportPDF';
    const ICON_EXPORT_CSV = 'mMenuIcon mMenuIconExportCSV';
    const ICON_INSERT = 'mMenuIcon mMenuIconInsert';
    const ICON_PASTE = 'dijitEditorIcon dijitEditorIconPaste';
    const ICON_RELATION = 'mMenuIcon mMenuIconRelation';
    const ICON_REMOVE = 'mMenuIcon mMenuIconRemove';
    const ICON_SAVE = 'dijitEditorIcon dijitEditorIconSave';
    const ICON_VIEW = 'mMenuIcon mMenuIconView';
    const ICON_WORKFLOW = 'mMenuIcon mMenuIconWorkflow';

    /**
     * @var boolean Whether is a sub menu.
     */
    public $isSubMenu;

    /**
     * @var array Menu itens. MMenuItem instances.
     */
    public $itens;

    /**
     * MStaticMenu constructor
     *
     * @param string $name Menu name.
     * @param array $itens Array of MMenuItem instances.
     * @param boolean $isSubMenu Whether menu is a sub menu.
     */
    public function __construct($name, $itens=array(), $isSubMenu=false)
    {
        parent::__construct($name);
        $this->itens = $itens;
        $this->isSubMenu = $isSubMenu;
    }

    /**
     * Add an item to menu.
     *
     * @param object $item MMenuItem instance.
     */
    public function addItem($item)
    {
        if ( $item instanceof MMenuItem )
        {
            $this->itens[] = $item;
        }
    }

    /**
     * Enable zebra menu.
     */
    public function enableZebra()
    {
        $this->setClass('dijitMenuZebra');
    }

    /**
     * Add custom CSS style.
     *
     * @param string $css CSS style.
     * @param string $cssClass New class name.
     */
    public function addCustomCSS($css, $cssClass)
    {
        $this->page->onLoad("dijit.byId('$this->name').addCustomCSS('$css', '$cssClass');");
    }

    /**
     * Add a custom item.
     *
     * @param string $label Item label.
     * @param string $onClick On click action.
     * @param string $iconClass Icon CSS class.
     */
    public function addCustomItem($label, $onClick, $iconClass)
    {
        if ( !$onClick )
        {
            $onClick = 'function() {}';
        }

        $this->itens[] = new MMenuItem($label, $onClick, $iconClass);
    }

    /**
     * Remove an item by Label.
     * The method compares that label of item is equal to passed label and remove it from list.
     * 
     * @param $label the label to remove
     */
    public function removeItemByLabel($label)
    {
        if ( is_array($itens = $this->itens) )
        {
            foreach ( $itens as $line => $item )
            {
                if ( method_exists($item, 'getLabel') )
                {
                    if ( $item->getLabel() == $label )
                    {
                        unset($this->itens[$line]);
                    }
                }
            }
        }
    }

    /**
     * Add a separator.
     */
    public function addSeparator()
    {
        $this->itens[] = new MDiv('', NULL, NULL, "dojoType=\"dijit.MenuSeparator\"");
    }

    /**
     * Add another MStaticMenu instance as a sub menu.
     *
     * @param object $contextMenu MStaticMenu instance
     * @param string $label Sub menu label.
     * @param string $onClick On click action.
     * @param string $iconClass Icon CSS class.
     */
    public function addSubMenu($contextMenu, $label, $onClick, $iconClass)
    {
        if ( $contextMenu instanceof MStaticMenu )
        {
            $id = $contextMenu->getId() . '_submenu';

            $span = new MSpan('', $label);

            $content = array( $span, $contextMenu );

            $div = new MDiv($id, $content, NULL, "dojoType=\"dijit.MPopupMenuItem\"");
            $div->setAttribute("iconClass", $iconClass);

            $this->itens[] = $div;

            if ( $onClick )
            {
                $jsCode = "dojo.byId('$id').onclick = function () { $onClick };";
                $this->page->onload($jsCode);
            }
        }
    }

    /**
     * Destroy current menu instance.
     */
    public function destroy()
    {
        $this->page->addJsCode("if ( dijit.byId('$this->name') ) dijit.byId('$this->name').destroyRecursive();");
    }

    /**
     * @return string The generated menu.
     */
    public function generate()
    {
        if ( $this->isSubMenu )
        {
            $this->setInner($this->itens);
            $this->setAttributes('dojoType="dijit.MStaticMenu"');
            $this->page->onload("dijit.byId('$this->name').setSubMenu();");
        }
        // Default
        else
        {
            $this->setInner(new MDiv($this->name, $this->itens, NULL, 'dojoType="dijit.MStaticMenu"'));
        }

        $this->destroy();

        return parent::generate();
    }
}

?>