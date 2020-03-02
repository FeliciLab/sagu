<?php

/**
 * A MIOLO Validator to validate captcha fields
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
 * Copyright (c) 2010-2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */

class MCaptchaValidator extends MValidator
{
    public function __construct($field, $label=NULL, $msgerr=NULL)
    {
        parent::__construct();
        $this->id = 'required';
        $this->field = $field;
        $this->label = $label;
        $this->mask = '';
        $this->type = 'required';
        $this->checker = 'required';
        $this->chars = 'ALL';
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

        // FIXME: Looks like only work one captcha per page.
        if ( !MCaptchaField::validate($value) )
        {
            $valid = false;
            $this->error = _M('The captcha is not valid');
        }

        return $valid ? parent::validate($value) : $valid;
    }
}

?>