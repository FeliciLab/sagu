<?php

/**
 * Regular expression validator.
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

class AvinstCheckBoxValidator extends AvinstValidator
{
    public $regexp;

    public function __construct($field, $min, $max, $msgerr = '')
    {
        parent::__construct();
        $this->id = 'checkBox';
        $this->field = $field;
        $this->label = $label;
        $this->min = $min>=0 ? $min : 0;
        $this->max = $max;
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
        if (is_array($value))
        {
            if ((count($value)>$this->max) || (count($value)<$this->min))
            {
                $valid = false;
            }
        }
        else
        {
            $valid = false;
        }
        return $valid;
    }
}

?>
