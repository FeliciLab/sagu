<?php

/**
 * Component to create a context menu.
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

class MContextMenu extends MStaticMenu
{
    /**
     * Possible types of opening menu.
     */
    const TYPE_JS = 'js';
    const TYPE_AJAX = 'ajax';

    /**
     * @var string Type by which the menu must be opened. TYPE_JS or TYPE_AJAX.
     */
    public $type;

    /**
     * @var string Coordinates where the menu must be opened.
     * X and Y points separated by ':'. E.g. '100:120'.
     */
    public $coords;

    /**
     * @var objcet MControl instance where the menu must be opened.
     */
    public $targetControl;

    /**
     * @var boolean Define which click must open the menu. Default is right.
     */
    public $leftClickOpen = false;

    /**
     * MContextMenu constructor.
     *
     * @param string $id Menu id.
     * @param string $type Type of opening. Use constants TYPE_JS or TYPE_AJAX.
     * @param array $itens Array of MMenuItem instances.
     * @param boolean $isSubMenu Whether menu is a sub menu.
     */
    public function __construct($id, $type=self::TYPE_JS, $itens=array(), $isSubMenu=false)
    {
        parent::__construct($id, $itens, $isSubMenu);
        $this->type = $type;
    }

    /**
     * @param boolean $leftClickOpen Set to open menu via left click.
     */
    public function setLeftClickOpen($leftClickOpen)
    {
        $this->leftClickOpen = $leftClickOpen;
    }

    /**
     * Coordinates where the menu must be opened.
     *
     * @param string $coords X and Y points separated by ':'. E.g. '100:50'.
     */
    public function setCoords($coords)
    {
        $this->coords = $coords;
    }

    /**
     * Define the menu target.
     *
     * @param object $targetControl MControl instance.
     */
    public function setTarget($targetControl)
    {
        $this->targetControl = $targetControl;

        $jsCode = "if ( dojo.byId('{$this->id}') ) dojo.parser.parse(dojo.byId('{$this->id}').parentNode);";

        if ( $targetControl instanceof MGrid )
        {
            $jsCode .= "if ( dojo.byId('$targetControl->id') ) dijit.byId('$this->id').setGridTarget('$targetControl->id');";
        }

        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onload($jsCode);
    }

    /**
     * Configure menu via JavaScript.
     */
    public function configureContextMenu()
    {
        $jsCode = "dojo.parser.parse(dojo.byId('{$this->id}').parentNode);";
        $jsCode .= "dijit.byId('{$this->id}').show('{$this->coords}');";
        $jsCode .= "dijit.focus(dojo.query('tr', dojo.byId('{$this->id}'))[0]);";
        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onload($jsCode);
    }

    /**
     * @return string Return the context menu generated.
     */
    public function generate()
    {
        $attributes = 'dojoType="dijit.MContextMenu" style="display:none"';

        // AJAX
        if ( $this->type == self::TYPE_AJAX )
        {
            $this->configureContextMenu();
        }
        // JavaScript
        else
        {
            $attributes .= " targetNodeIds=\"{$this->targetControl->id}\"";

            if ( $this->leftClickOpen )
            {
                $attributes .= " leftClicktoOpen=\"true\"";
            }
        }

        $this->destroy();

        $div = new MDiv('', new MDiv($this->id, $this->itens, NULL, $attributes));
        return $div->generate();
    }
}

?>