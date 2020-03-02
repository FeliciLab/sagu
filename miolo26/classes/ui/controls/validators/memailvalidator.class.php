<?php

/**
 * Email validator.
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

class MEmailValidator extends MValidator
{
    public function __construct($field, $label='', $type='optional', $msgerr='')
    {
        parent::__construct();
        $this->id = 'email';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 0;
        $this->max = 99;
        $this->chars = 'ALL';
        $this->mask = '';
        $this->checker = 'EMAIL';
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

        if ( filter_var($value, FILTER_VALIDATE_EMAIL) === false )
        {
            $valid = false;
            $this->error = _M('The email is not valid');
        }

        return $valid ? parent::validate($value) : $valid;
    }
}

?>