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

class MRegExpValidator extends MValidator
{
    public $regexp;

    public function __construct($field, $label='', $regexp='', $type = 'optional', $msgerr='')
    {
        parent::__construct();
        $this->id = 'regexp';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 0;
        $this->max = 255;
        $this->chars = 'ALL';
        $this->mask = '';
        $this->checker = 'REGEXP';
        $this->regexp = $regexp;
        $this->msgerr = $msgerr;
    }

    public function generate()
    {
        $this->html = "regexp: '{$this->regexp}',";
        return parent::generate();
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

        if ( ($value != NULL) && (preg_match("/$this->regexp/", $value) == 0) )
        {
            $valid = false;
            $this->error = _M('The field value is invalid');
        }

        return $valid ? parent::validate($value) : $valid;
    }
}

?>