<?php

/**
 * CEP validator.
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

class MCEPValidator extends MMaskValidator
{
    public function __construct($field, $label='', $type = 'optional', $msgerr='')
    {
        parent::__construct($field);
        $this->id = 'cep';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 9;
        $this->max = 9;
        $this->chars = '0123456789-';
        $this->mask = '99999-999';
        $this->msgerr = $msgerr;
    }
}

?>