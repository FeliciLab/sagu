<?php

/**
 * Range validator.
 * Validate if value is between a given range, which can be of dates, strings or
 * numbers.
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

class AvinstLengthValidator extends AvinstValidator
{
    public function __construct($field, $min = 0, $max, $type = 'optional', $msgerr = '')
    {
        parent::__construct();
        $this->id = 'range';
        $this->field = $field;
        $this->label = $label;
        $this->min = strlen($min)<0 ? 0 : $min;
        $this->max = $max;
        $this->chars = 'ALL';
        $this->mask = '';
        $this->type = $type;
        $this->msgerr = $msgerr;
    }
    
    public function validate($value)
    {
        $valid = true;
        // Check min and max
        if ( ($this->min != '') && ($this->max != '') )
        {
            $valueLength = mb_strlen($value);
            if ( $valueLength < $this->min || $valueLength > $this->max )
            {
                $valid = false;
                $this->error = _M('The value length must be between @1 and @2', 'miolo', $this->min, $this->max);
            }
        }
        // Check required
        $valid = $valid && parent::validate($value);
        return $valid;
    }
}
?>
