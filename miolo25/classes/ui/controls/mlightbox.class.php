<?php

/**
 * MLightBox
 *
 * @author Armando Taffarel Neto [taffarel@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/07/13
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

class MLightBox extends MLink
{

    public function __construct($name, $label, $href)
    {
        parent::__construct($name, $label, $href);

        $this->page->addDojoRequire( 'dojox.image.Lightbox' );
        $this->addAttribute('dojoType','dojox.image.Lightbox');
    }

    public function generateInner()
    {
        if ( $this->name )
        {
           $jsCode = "if ( dijit.byId('{$this->name}') ) { dijit.byId('{$this->name}').destroy(); }";
           $this->page->addJsCode($jsCode);
        }

        if ( $this->caption == '' )
        {
            $this->caption = $this->label;
        }

        $this->inner = $this->getRender('anchor');
    }
}

class MLightBoxImage extends MLightBox
{
    public function __construct($name, $urlImage, $width = '', $height = '')
    {
        $img = new MImage("{$name}Image", '', $urlImage);

        if ( $width )
        {
            $img->addStyle('width', $width);
        }

        if ( $height )
        {
            $img->addStyle('height', $height);
        }

        parent::__construct('', $img->generate(), $urlImage);
    }
}

class MLightBoxIcon extends MLightBox
{
    public function __construct($name, $urlImage, $urlIcon = NULL)
    {
        if ( !$urlIcon )
        {
            $MIOLO = MIOLO::getInstance();
            $urlIcon = $MIOLO->getUI()->getImageTheme( $MIOLO->getTheme()->getId(), 'photoEnable.png' );
        }

        $img = new MImage("{$name}Image", '', $urlIcon);
        parent::__construct($name, $img->generate(), $urlImage);
    }
}

class MLightBoxButton extends MButton
{
    public function __construct($id, $label, $urlImage, $themeImage = 'button_noselect.png')
    {
        $MIOLO = MIOLO::getInstance();
        $image = $MIOLO->getUI()->getImageTheme($MIOLO->getTheme()->getId(), $themeImage);

        parent::__construct($id, $label, ' ', $image);

        $this->page->addDojoRequire('dojox.image.Lightbox');
        $this->addAttribute('dojoType', 'dojox.image.Lightbox');
        $this->addAttribute('href', $urlImage);
    }
}
?>
