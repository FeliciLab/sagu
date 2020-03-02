<?php

/**
 * Mask validator.
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

class AvinstMaskValidator extends AvinstValidator
{
    public function __construct($field, $label='', $mask='', $type = 'ignore', $msgerr='')
    {
        parent::__construct();
        $this->id = 'mask';
        $this->field = $field;
        $this->label = $label;
        $this->mask = $mask;
        $this->type = $type;
        $this->min = 0;
        $this->max = strlen($mask);
        $this->msgerr = $msgerr;
        $this->chars = 'ALL';
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
        $valueChars = str_split($value);
        $valueLength = strlen($value);
        $mask = str_split($this->mask);
        $errorMessage = _M('The value must respect the mask @1', 'miolo', $this->mask);

        if ( $this->type != 'required' && $valueLength != 0 && $valueLength != count($mask) )
        {
            $this->error = $errorMessage;
            $valid = false;
        }
        elseif ( $valueLength > 0 )
        {
            foreach ( $valueChars as $index => $char )
            {
                // Validate numbers and letters
                if ( ( $mask[$index] == '9' && !is_numeric($char) ) ||
                     ( $mask[$index] == 'a' && is_numeric($char) ) )
                {
                    $this->error = $errorMessage;
                    $valid = false;
                    break;
                }
                // Validate other chars
                elseif ( ( $mask[$index] != 'a' && $mask[$index] != '9' ) &&
                         ( $mask[$index] != $char ) )
                {
                    $this->error = $errorMessage;
                    $valid = false;
                    break;
                }
            }
        }

        return $valid ? parent::validate($value) : $valid;
    }

    /**
     * Check if the given number and digits are in modulus 11.
     *
     * @param array $number Number to check.
     * @param array $digits Digits to validate.
     * @param boolean $cnpj Indicates whether is a CNPJ testing.
     * @return boolean Returns whether the number is in modulus 11.
     */
    protected function modulus11($number, $digits, $cnpj=false)
    {
        if ( is_array($digits) )
        {
            // Iterate through the verifying digits and test them
            $lastDigit = null;

            foreach ( $digits as $digit )
            {
                if ( $lastDigit != null )
                {
                    $number = array_merge($number, array( $lastDigit ));
                }

                if ( !$this->modulus11($number, $digit, $cnpj) )
                {
                    return false;
                }

                $lastDigit = $digit;
            }

            return true;
        }
        else
        {
            // Test if the digit corresponds to modulus 11 logic
            $sum = 0;

            for ( $i = count($number) - 1, $factor = 9; $i >= 0; $i--, $factor-- )
            {
                if ( $cnpj && $factor == 1 )
                {
                    $factor = 9;
                }

                $sum += $number[$i] * $factor;
            }

            $digit = $sum % 11;

            if ( $digit > 9 )
            {
                $digit = 0;
            }

            return $digit == $digits;
        }
    }
}

?>
