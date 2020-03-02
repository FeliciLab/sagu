<?php

/**
 * MBackButton - A simple button with a 'history.back();' javascript action
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
 * Creation date 2010/08/26
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
class MBackButton extends MButton
{
    public function __construct($name = 'btnBack', $label = '', $image = NULL)
    {
        if ( !$label )
        {
            $label = _M('Back');
        }
        parent::__construct($name, $label, 'javascript:history.back();', $image);
    }
}
?>
