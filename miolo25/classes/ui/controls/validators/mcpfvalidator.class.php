<?php

/**
 * CPF validator.
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

class MCPFValidator extends MMaskValidator
{
    public function __construct($field, $label='', $type = 'optional', $msgerr='')
    {
        parent::__construct($field);
        $this->id = 'cpf';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 14;
        $this->max = 14;
        $this->chars = '.-0123456789';
        $this->mask = '999.999.999-99';
        $this->checker = 'CPF';
        $this->msgerr = $msgerr;
    }

    /**
     * Check if the given CPF is valid.
     *
     * @param string $cpf CPF to verify.
     * @return boolean Whether the CPF is valid.
     */
    public function validateCPF($cpf)
    {
        $invalids = array(
            '000.000.000-00',
            '111.111.111-11',
            '222.222.222-22',
            '333.333.333-33',
            '444.444.444-44',
            '555.555.555-55',
            '666.666.666-66',
            '777.777.777-77',
            '888.888.888-88',
            '999.999.999-99',
        );

        if ( in_array($cpf, $invalids) )
        {
            return false;
        }

        $cpf = str_replace('.', '', $cpf);
        $cpf = explode('-', $cpf);

        $number = str_split($cpf[0]);
        $digit = str_split($cpf[1]);

        return $this->modulus11($number, $digit);
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

        if ( !$this->validateCPF($value) )
        {
            $this->error = _M('This is not a valid CPF');
            $valid = false;
        }

        return $valid ? parent::validate($value) : $valid;
    }
}

?>