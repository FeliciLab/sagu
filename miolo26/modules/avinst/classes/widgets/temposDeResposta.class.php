<?php

/**
 * Grupo de botões para selecionar
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/11/13
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2008 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

$MIOLO = MIOLO::getInstance();
$MIOLO->uses('classes/awidget.class.php', 'avinst');

class temposDeResposta extends AWidget
{
    function __construct( $parameters )
    {
        parent::__construct('temposDeResposta'.$parameters->idAvaliacao.'_'.$parameters->idFormulario);
        $this->elementName = __CLASS__;
        $this->description = 'Tempos de resposta';
        $this->version = '0.1';
        $this->parameters = $parameters;
    }

    //
    // Retorna uma div com todos os componentes
    //
    public function generate()
    { 
        $fields = array();

        $fields[] = new MDiv(null, $this->description, 'widgetTitleStatistics');
	$fields[] = new MDiv(null, 'Aqui vai a estatística');
        $vct['cont'] = new MVContainer('vct'.$this->param->idAvaliacao.'_'.$this->params->idFormulario, $fields);
        $vct['cont']->addAttribute('align', 'center');
        $this->setInner($vct);
        return parent::generate();
    }
}

?>

