<?php

/**
 * Class MVarDump.
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

class MVarDump
{
    public $var;

    public function varDump(&$var)
    {
        $this->var =& $var;
    }
    
    public function generate()
    {
        echo "<b>Variable Dump:</b><br><br>\n";
        echo "<blockquote>\n";
        echo "<pre>\n";
        var_dump($this->var);
        echo "</pre>\n";
        echo "</blockquote>\n";
    }
}

?>