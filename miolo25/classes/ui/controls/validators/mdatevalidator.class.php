<?php

/**
 * Date validators.
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

class MDateValidator extends MValidator
{
    /**
     * Validate value according to validator rules.
     *
     * @param mixed $value Field value.
     * @return boolean Whether field value is valid.
     */
    public function validate($value)
    {
        $valid = true;

        if ( $value )
        {
            switch ( $this->checker )
            {
                case 'DATETimeDMY':
                    list($date, $time) = explode(' ', $value);
                    list($hours, $mins) = explode(':', $time);

                    if ( $hours > 23 || $mins > 59 )
                    {
                        $valid = false;
                        $this->error = _M('The time is invalid');
                        break;
                    }

                    // Validate if only date or time was informed
                    if ( (!$date && $time) || ($date && !$time) )
                    {
                        $valid = false;
                        $this->error = _M('Both date and time must be informed');
                        break;
                    }

                    // Continue to validate the date
                    $value = $date;

                case 'DATEDMY':
                    list($userDay, $userMonth, $userYear) = explode('/', $value);
                    break;

                case 'DATEYMD':
                    list($userYear, $userMonth, $userDay) = explode('/', $value);
                    break;
            }

            if ( $valid && strtotime("$userYear-$userMonth-$userDay") === false )
            {
                $valid = false;
                $this->error = _M('The date is invalid');
            }
        }

        return $valid ? parent::validate($value) : $valid;
    }
}

class MDateDMYValidator extends MDateValidator
{
    public function __construct($field, $label='', $type = 'optional', $msgerr='')
    {
        parent::__construct();
        $this->id = 'datedmy';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 10;
        $this->max = 10;
        $this->chars = '/0123456789';
        $this->mask = '99/99/9999';
        $this->checker = 'DATEDMY';
        $this->msgerr = $msgerr;
    }
}

class MDateYMDValidator extends MDateValidator
{
    public function __construct($field, $label='', $type = 'optional', $msgerr='')
    {
        parent::__construct();
        $this->id = 'dateymd';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 10;
        $this->max = 10;
        $this->chars = '/0123456789';
        $this->mask = '9999/99/99';
        $this->checker = 'DATEYMD';
        $this->msgerr = $msgerr;
    }
}

class MDateTimeDMYValidator extends MDateValidator
{
    public function __construct($field, $label='', $type='optional', $msgerr='')
    {
        parent::__construct();
        $this->id = 'datetimedmy';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 10;
        $this->max = 16;
        $this->chars = ':/0123456789 ';
        $this->mask = '99/99/9999 99:99';
        $this->checker = 'DATETimeDMY';
        $this->msgerr = $msgerr;
    }
}

?>