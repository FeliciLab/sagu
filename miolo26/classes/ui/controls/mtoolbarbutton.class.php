<?php

/**
 * Class MToolBarButton.
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

class MToolBarButton extends MFormControl
{
    public $name;
    public $caption;
    public $enabled;
    public $visible = true;
    protected $url;
    protected $enabledImage;
    protected $disabledImage;
    protected $type;

    /**
     * This is the constructor of the MToolbar class.
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
    public function __construct($name, $caption, $url, $jsHint, $enabled, $enabledImage, $disabledImage, $method='', $type)
    {
        parent::__construct($name);

        $this->name = $name;
        $this->caption = $caption;
        $this->jsHint = $jsHint;
        $this->enabled = $enabled;
        $this->enabledImage = $enabledImage;
        $this->disabledImage = $disabledImage;
        $this->url = $url;
        $this->type = $type;
    }

    /**
     * Set button type.
     * 
     * @param string $type Button type: MToolBar::TYPE_ICON_ONLY, MToolBar::TYPE_ICON_TEXT or MToolBar::TYPE_TEXT_ONLY
     */
    public function setType($type=MToolBar::TYPE_ICON_ONLY)
    {
        $this->type = $type;
    }

    /**
     * Show button.
     */
    public function show()
    {
        $this->visible = true;
    }

    /**
     * Hide Button.
     */
    public function hide()
    {
        $this->visible = false;
    }

    /**
     * Enable button.
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Disable button.
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Generate inner content.
     */
    public function generateInner()
    {
        if ( $this->visible )
        {
            if ( $this->enabled )
            {
                $image = $this->enabledImage;

                if ( $this->type == MToolBar::TYPE_ICON_ONLY )
                {
                    $button = new MImageButton($this->name, $this->caption, $this->url, $image);
                }
                elseif ( $this->type == MToolBar::TYPE_ICON_TEXT )
                {
                    $button = new MImageButtonLabel($this->name, $this->caption, $this->url, $image);
                }
                elseif ( $this->type == MToolBar::TYPE_TEXT_ONLY )
                {
                    $button = new MLink($this->name, $this->caption, $this->url);
                }
            }
            else
            {
                $image = $this->disabledImage;

                if ( $this->type == MToolBar::TYPE_ICON_ONLY )
                {
                    $button = new MImage($this->name, $this->caption, $image);
                }
                elseif ( $this->type == MToolBar::TYPE_ICON_TEXT )
                {
                    $button = new MImageLabel($this->name, $this->caption, $image);
                }
                elseif ( $this->type == MToolBar::TYPE_TEXT_ONLY )
                {
                    $button = new MLabel($this->caption);
                }
            }

            if ( $this->enabled )
            {
                $this->inner = new MDiv('', $button, 'mToolbarButton');
            }
            else
            {
                $this->inner = new MDiv('', $button, 'mToolbarButtonDisabled');
            }
        }
    }
}

?>