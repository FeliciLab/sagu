<?php

/**
 * CNPJ validator.
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

class MCNPJValidator extends MMaskValidator
{
    public function __construct($field, $label='', $type = 'optional', $msgerr='')
    {
        parent::__construct($field);
        $this->id = 'cnpj';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 18;
        $this->max = 18;
        $this->chars = '/.-0123456789';
        $this->mask = '99.999.999/9999-99';
        $this->checker = 'CNPJ';
        $this->msgerr = $msgerr;
    }

    /**
     * Check if the given CNPJ is valid.
     *
     * @param string $cnpj The CNPJ to verify.
     * @return boolean Whether the CNPJ is valid.
     */
    public function validateCNPJ($cnpj)
    {
        $cnpj = str_replace('.', '', $cnpj);
        $cnpj = str_replace('/', '', $cnpj);
        $cnpj = explode('-', $cnpj);

        $number = str_split($cnpj[0]);
        $digits = str_split($cnpj[1]);

        return $this->modulus11($number, $digits, true);
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

        if ( !$this->validateCNPJ($value) )
        {
            $this->error = _M('This is not a valid CNPJ');
            $valid = false;
        }

        return $valid ? parent::validate($value) : $valid;
    }
}

?>