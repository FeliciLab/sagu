<?php

/**
 * Field set component.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2012/03/14
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */

class MFieldSet extends MContainer
{
    public function __construct($name='', $controls='', $class='')
    {
        parent::__construct($name, $controls);

        if ( $class )
        {
            $this->setClass($class, FALSE);
        }
    }

    public function generate()
    {
        $this->generateInner();

        return $this->getRender('fieldset');
    }
}

?>