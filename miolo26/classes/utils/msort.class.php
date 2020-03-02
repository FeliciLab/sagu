<?php

/**
 * Sort class to use with PHP's usort function.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2012/05/07
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class MSort
{
    const MASK_DATE_EN = '%m/%d/%Y';
    const MASK_DATE_BR = '%d/%m/%Y';
    const MASK_DATETIME_EN = '%m/%d/%Y %H:%M:%S';
    const MASK_DATETIME_BR = '%d/%m/%Y %H:%M:%S';
    const MASK_TIME = '%H:%M:%S';

    /**
     * @var string Sort order. Possible values are ASC and DESC.
     */
    private $order;

    /**
     * @var string Date and/or time mask. Use MASK_* constants or any of the formats described at http://www.php.net/manual/en/function.strftime.php.
     */
    private $dateTimeMask;

    /**
     * @var mixed Index of the column that must be sorted. Not necessary for single dimensional arrays.
     */
    private $key;

    /**
     * MSort constructor.
     *
     * @param string $order Sort order. Possible values are ASC and DESC.
     * @param string $dateTimeMask Date and/or time mask.
     */
    public function __construct($order, $key=NULL, $dateTimeMask='')
    {
        $this->order = strtoupper($order);
        $this->key = $key;
        $this->dateTimeMask = $dateTimeMask;
    }

    /**
     * Compare dates/times in the format specified by datetimeMask attribute.
     *
     * @param string $d1 Date/time.
     * @param string $d2 Date/time.
     * @return integer Returns -1 if the second parameter must be above the first. Returns 1 to the opposite behavior and 0 for keeping positions.
     */
    public function compareDate($d1, $d2)
    {
        if ( $this->dateTimeMask )
        {
            if ( $this->key !== NULL )
            {
                $d1 = strptime($d1[$this->key], $this->dateTimeMask);
                $d2 = strptime($d2[$this->key], $this->dateTimeMask);
            }
            else
            {
                $d1 = strptime($d1, $this->dateTimeMask);
                $d2 = strptime($d2, $this->dateTimeMask);
            }

            $t1 = mktime($d1['tm_hour'], $d1['tm_min'], $d1['tm_sec'], $d1['tm_mon'] + 1, $d1['tm_mday'], $d1['tm_year'] + 1900);
            $t2 = mktime($d2['tm_hour'], $d2['tm_min'], $d2['tm_sec'], $d2['tm_mon'] + 1, $d2['tm_mday'], $d2['tm_year'] + 1900);

            if ( $t1 == $t2 )
            {
                $result = 0;
            }
            elseif ( $this->order == 'DESC' )
            {
                $result = ($t1 < $t2) ? 1 : -1;
            }
            else
            {
                $result = ($t1 > $t2) ? 1 : -1;
            }
        }
        else
        {
            $result = $this->compare($d1[$this->key], $d2[$this->key]);
        }

        return $result;
    }

    /**
     * Compare two values.
     *
     * @param mixed $v1 First value.
     * @param mixed $v2 Second value.
     * @return integer Returns -1 if the second parameter must be above the first. Returns 1 to the opposite behavior and 0 for keeping positions.
     */
    public function compare($v1, $v2)
    {
        if ( $this->key !== NULL )
        {
            $v1 = $v1[$this->key];
            $v2 = $v2[$this->key];
        }

        if ( $v1 == $v2 )
        {
            $result = 0;
        }
        elseif ( $this->order == 'DESC' )
        {
            $result = ($v1 < $v2) ? 1 : -1;
        }
        else
        {
            $result = ($v1 > $v2) ? 1 : -1;
        }
    }
}

?>