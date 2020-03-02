<?php

/**
 * Phone validator.
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

class MPhoneValidator extends MValidator
{
    public function __construct($field, $label='', $type = 'optional', $msgerr='')
    {
        parent::__construct();
        $this->id = 'phone';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 8;
        $this->max = 13;
        $this->chars = '() 01234-56789';
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

        if ( strlen($value) > 0 )
        {
            $validChars = str_split($this->chars);
            $valueChars = str_split($value);

            foreach ( $valueChars as $char )
            {
                if ( !in_array($char, $validChars) )
                {
                    $this->error = _M('Must have only the following characters @1', 'miolo', $this->chars);
                    $valid = false;
                    break;
                }
            }
        }

        return $valid ? parent::validate($value) : $valid;
    }
}

?>