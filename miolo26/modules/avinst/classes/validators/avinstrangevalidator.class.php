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

class AvinstRangeValidator extends AvinstValidator
{
    const DATATYPE_INTEGER = 'i';
    const DATATYPE_STRING = 's';
    const DATATYPE_DATE = 'd';

    public $minvalue;
    public $maxvalue;

    /**
     * @var char Can be DATATYPE_INTEGER, DATATYPE_STRING or DATATYPE_DATE.
     */
    public $datatype;

    public function __construct($field, $label='', $min, $max, $datatype=self::DATATYPE_STRING, $type='optional', $msgerr='')
    {
        parent::__construct();
        $this->id = 'range';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 0;
        $this->max = 255;
        $this->chars = 'ALL';
        $this->mask = '';
        $this->checker = 'RANGE';
        $this->minvalue = $min;
        $this->maxvalue = $max;
        $this->datatype = $datatype;
        $this->msgerr = $msgerr;
    }

    public function generate()
    {
        $this->html = "minvalue: '{$this->minvalue}', maxvalue: '{$this->maxvalue}', datatype: '{$this->datatype}',";
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
        switch ( $this->datatype )
        {
            case self::DATATYPE_INTEGER:
            case self::DATATYPE_STRING:
            default:
                if ( $value < $this->minvalue || $value > $this->maxvalue )
                {
                    $valid = false;
                    $this->error = _M('The input value must be between @1 and @2', 'miolo', $this->minvalue, $this->maxvalue);
                }
                break;

            case self::DATATYPE_DATE:
                list($userDay, $userMonth, $userYear) = explode('/', $value);
                list($minDay, $minMonth, $minYear) = explode('/', $this->minvalue);
                list($maxDay, $maxMonth, $maxYear) = explode('/', $this->maxvalue);

                $userTime = strtotime("$userYear-$userMonth-$userDay");
                $minTime = strtotime("$minYear-$minMonth-$minDay");
                $maxTime = strtotime("$maxYear-$maxMonth-$maxDay");
                
                if ( $userTime < $minTime || $userTime > $maxTime )
                {
                    $valid = false;
                    $this->error = _M('The date must be between @1 and @2', 'miolo', $this->minvalue, $this->maxvalue);
                }
                break;
        }

        return $valid ? parent::validate($value) : $valid;
    }
}

?>
