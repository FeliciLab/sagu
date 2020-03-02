<?php

/**
 * Class MTreeArray.
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

class MTreeArray
{
    public $tree;

    public function __construct($array, $group, $node)
    {
        $this->tree = array();
        if ( $rs = $array )
        {
            $node = explode(',', $node);
            $group = explode(',', $group);
            foreach ( $rs as $row )
            {
                $aNode = array();
                foreach ( $node as $n ) $aNode[] = $row[$n];
                $s = '';
                foreach ( $group as $g ) $s .= '[$row[' . $g . ']]';
                eval("\$this->tree$s" . "[] = \$aNode;");
            }
        }
    }
}

?>