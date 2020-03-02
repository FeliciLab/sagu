<?php

/**
 * Class which represents an item of MStaticMenu or MContextMenu.
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

class MMenuItem extends MDiv
{
    /**
     * MMenuItem constructor.
     *
     * @param string $label Item label.
     * @param string $onClick On click action.
     * @param string $iconClass Icon CSS class.
     */
    public function __construct($label, $onClick, $iconClass)
    {
        parent::__construct('', $label);
        $this->setAttribute('dojoType', 'dijit.MMenuItem');
        $this->setAttribute('onClick', $onClick);
        $this->setAttribute('iconClass', $iconClass);
    }

    /**
     * @return string Return the string format of the instance.
     */
    public function __toString()
    {
        return $this->generate();
    }

    /**
     * @param string $label Set the item label.
     */
    public function setLabel($label)
    {
        $this->inner = $label;
    }

    /**
     * @return string Get the item label.
     */
    public function getLabel()
    {
        return $this->inner;
    }
}

?>