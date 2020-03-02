<?php

/**
 * Grid da tabela ava_widget.
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 09/03/2012
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
class grdSearchAvaWidget extends AGrid
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $MIOLO->uses('classes/awidgetcontrol.class.php', 'avinst');
        $MIOLO->uses('types/avaPerfilWidget.class.php', 'avinst');
        $columns[] = new MGridColumn(_M('Código', $module), 'left', true, '15%', true, NULL, true);
        $columns[] = new MGridColumn(_M('Versao', $module), 'left', true, '15%', true, NULL, true);
        $columns[] = new MGridColumn(_M('Nome', $module), 'left', true, '60%', true, NULL, true);
        $columns[] = new MGridColumn(_M('Estado', $module), 'center', true, '10%', true, NULL, true);
        $primaryKeys = array('idWidget'=>'%0%', );
        $url = $MIOLO->getActionUrl($module, $action);
        parent::__construct(__CLASS__, NULL, $columns, $url);
        $args = array('event'=>'editButton:click', 'function'=>'edit', );
        $hrefUpdate = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $args = array('event'=>'deleteButton:click', 'function'=>'search', );
        $hrefDelete = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $this->addActionUpdate($hrefUpdate);
        $this->addActionDelete($hrefDelete);
    }
    
    public function myRowMethod($i, $row, $actions, $columns)
    {
        $MIOLO = MIOLO::getInstance();
        
        //
        // Verifica se há avaliações relacionadas com o widget. Se existir, então "bloqueia" a opção de exclusão
        //
        $filter = new stdClass();
        $filter->refWidget = $row[0];
        $avaPerfilWidget = new avaPerfilWidget($filter);
        $result = $avaPerfilWidget->search(ADatabase::RETURN_TYPE, true);
        
        if (count($result[0]->avaliacaoPerfilWidgets)>0)
        {
            $actions[1]->disable();
        }
        else
        {
            $actions[1]->enable();
        }
        
        //
        // Verifica se a classe existe no banco de dados
        //
        if( AWidgetControl::existsWidgetClass($row[0]) )
        {
            $columns[3]->control[$i] = new MTextLabel(null,_M('Funcional'),null,'green'); 
        }
        else
        {
            $columns[3]->control[$i] = new MTextLabel(null,'Classe não encontrada',null,'red');
        }       
    }
}


?>
