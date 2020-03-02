<?php

/**
 * Class MInvertDate.
 *
 * @author Ely Edison Matos [ely.matos@ufjf.edu.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2006/03/08
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2006-2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */

class MInvertDate
{
    public $separator = '/';
    public $date;

    public function __construct($date=null)
    {
        $date = strstr($date, '-') ? str_replace('-', $this->separator, $date) : str_replace('.', $this->separator, $date);
        $this->date = $date;
        $this->formatDate();
    }

    public function formatDate()
    {
        list($obj1, $obj2, $obj3) = split($this->separator, $this->date, 3);
        $this->date = $obj3 . $this->separator . $obj2 . $this->separator . $obj1;

        if ( ( $this->date == ($this->separator . $this->separator) ) )
        {
            $this->date = 'Invalid Date!';
        }

        return $this->date;
    }
}

?>