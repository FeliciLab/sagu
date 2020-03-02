<?php

/**
 * Float validator.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2011/08/02
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

class MFloatValidator extends MRegExpValidator
{
    public function __construct($field, $label='', $separator='.', $precision=2, $type='optional', $msgerr='')
    {
        $regexp = '^[+-]?[0-9]{1,}(\\' . $separator . '[0-9]{1,' . $precision . '})?$';
        parent::__construct($field, $label, $regexp, $type, $msgerr);
        $this->chars = '0123456789+-' . $separator;
    }
}

?>