<?php

/**
 * Comparison validator.
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

class MCompareValidator extends MValidator
{
    const DATATYPE_INTEGER = 'i';
    const DATATYPE_STRING = 's';
    
    public $operator;
    public $value;

    /**
     * @var char Possible values are DATATYPE_INTEGER or DATATYPE_STRING.
     */
    public $datatype;

    public function __construct($field, $label='', $operator='', $value='', $datatype=self::DATATYPE_STRING, $type='optional', $msgerr='')
    {
        parent::__construct();
        $this->id = 'compare';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 0;
        $this->max = 255;
        $this->chars = 'ALL';
        $this->mask = '';
        $this->checker = 'COMPARE';
        $this->operator = $operator;
        $this->value = $value;
        $this->datatype = strtolower($datatype);
        $this->msgerr = $msgerr;
    }

    public function generate()
    {
        $this->html = "operator: '{$this->operator}',  value: '{$this->value}', datatype: '{$this->datatype}',";
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
        $message = _M('The value must be ');

        switch ( $this->operator )
        {
            case '>':
                $valid = $value > $this->value;
                $message .= _M('greater than ');
                break;

            case '>=':
                $valid = $value >= $this->value;
                $message .= _M('greater than or equal to ');
                break;

            case '<':
                $valid = $value < $this->value;
                $message .= _M('less than ');
                break;

            case '<=':
                $valid = $value <= $this->value;
                $message .= _M('less than or equal to ');
                break;

            case '==':
            case '=':
                $valid = $value == $this->value;
                $message .= _M('equal to ');
                break;

            case '!=':
                $valid = $value != $this->value;
                $message .= _M('different than ');
                break;

            case '===':
                $valid = $value === $this->value;
                $message .= _M('identical to ');
                break;

            case '!==':
                $valid = $value !== $this->value;
                $message .= _M('not identical to ');
                break;

            case '<>':
                $valid = $value <> $this->value;
                $message .= _M('different than ');
                break;
        }

        if ( !$valid )
        {
            $this->error = $message . $this->value;
        }

        return $valid ? parent::validate($value) : $valid;
    }
}

?>