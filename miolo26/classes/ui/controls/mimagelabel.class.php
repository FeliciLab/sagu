<?php

/**
 * Class MImageLabel.
 * This component is similar to MImageButtonLabel, but without action.
 *
 * @author Daniel Afonso Heisler
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2005/08/04
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solções Livres \n
 * The MIOLO2 AND SAGU2 Development Team
 *
 * \b Copyright: \n
 * Copyright (c) 2005 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */

class MImageLabel extends MImage
{
    public function generateInner()
    {
        parent::generateInner();

        $image = new MDiv('', $this->inner, 'mImageCentered');
        $text = new MSpan('', $this->label, 'mImageLabel');
        $this->inner = $image->generate() . $text->generate();
    }
}

?>