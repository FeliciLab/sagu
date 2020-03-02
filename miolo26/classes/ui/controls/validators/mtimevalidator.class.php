<?php

/**
 * Time validator.
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

class MTimeValidator extends MMaskValidator
{
    public function __construct($field, $label='', $type = 'optional', $msgerr='')
    {
        parent::__construct($field);
        $this->id = 'time';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 5;
        $this->max = 5;
        $this->chars = ':0123456789';
        $this->mask = '99:99';
        $this->checker = 'TIME';
        $this->msgerr = $msgerr;
    }

    /**
     * Validate value according to validator rules.
     *
     * @param mixed $value Field value.
     * @return boolean Whether field value is valid.
     */
    public function validate($value)
    {
        $valid = true;

        list($hours, $mins) = explode(':', $value);
        if ( $mins != NULL && $mins > 59 )
        {
            $valid = false;
            $this->error = _M('The informed time is invalid');
        }

        return $valid ? parent::validate($value) : $valid;
    }
}

?>