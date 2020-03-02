<?php

/**
 * Required validator.
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

class AvinstRequiredValidator extends AvinstValidator
{
    public function __construct($field, $label='', $max=0, $msgerr='')
    {
        parent::__construct();
        $this->id = 'required';
        $this->field = $field;
        $this->label = $label;
        $this->mask = '';
        $this->type = 'required';
        $this->min = 0;
        $this->max = $max;
        $this->chars = 'ALL';
        $this->msgerr = $msgerr;
    }
}

?>
