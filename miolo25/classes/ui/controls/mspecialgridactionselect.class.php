<?php

/**
 * MSpecialGrid component.
 * A MGrid component with some extra features, like row selection by clicking
 * and JS hiding columns.
 *
 * @author Armando Taffarel Neto [taffarel@solis.coop.br]
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/08/17
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2010-2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */

class MSpecialGridActionSelect extends MGridActionSelect
{
    public function __construct($grid)
    {
        parent::__construct($grid);
    }

    public function generate()
    {
        $i = $this->grid->currentRow;
        $row = $this->grid->data[$i];

        foreach ( $this->grid->arguments as $key => $argument )
        {
            if ( strpos($argument, '%') === 0 )
            {
                // gets the column number expressed between %%
                $value = $row[substr($argument, 1, strpos($argument, '%', 1) - 1)];
            }
            else
            {
                $value = $argument;
            }

            $index .= "{$key}|{$value}&";
        }

        $control = new MCheckBox("select" . $this->grid->name . "[$i]", $index, '');
        $control->addAttribute('onclick', "miolo.grid.check(this,'" . $this->grid->name . "[$i]" . "');", false);

        if ( $this->grid->selectsData[$i] )
        {
            $control->addAttribute('checked');
        }

        return $control;
    }
}

?>