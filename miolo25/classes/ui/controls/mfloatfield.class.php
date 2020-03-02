<?php

/**
 * Input field for float numbers
 * TODO: obter últimas alterações do MFloatField do MIOLO 2.0
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2011/02/07
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

class MFloatField extends MTextField
{
    private $precision;

    public function __construct($name='', $value='', $label='', $size=10, $hint='', $validator=NULL, $isReadOnly=false, $precision=2)
    {
        parent::__construct($name, $value, $label, $size, $hint, $validator, $isReadOnly);

        // remove non float
        $this->addAttribute('onkeyup', 'return miolo.floatfield.validate(this)');
        // prevent paste
        $this->addAttribute('onchange', 'return miolo.floatfield.validate(this)');

        $this->setPrecision($precision);

        $this->page->addScript( 'm_floatfield.js' );
    }

    public function setPrecision($precision)
    {
        $this->precision = $precision;
    }

    public function getPrecision()
    {
        return $this->precision;
    }

    public function generate()
    {
        if ( $this->precision )
        {
            $this->addAttribute('onblur', "miolo.floatfield.fixPrecision(this, '{$this->precision}')");
        }

        return parent::generate();
    }
}
?>
