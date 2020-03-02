<?php

/**
 * Grid da tabela ava_servico.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 23/11/2011
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
class grdSearchAvaServico extends AGrid
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $MIOLO->uses('types/avaFormulario.class.php', 'avinst');
        $MIOLO->uses('types/avaGranularidade.class.php', 'avinst');
        $columns[] = new MGridColumn(_M('Código do serviço', $module), 'right', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn(_M('Descrição', $module), 'left', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn(_M('Localização', $module), 'left', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn(_M('Método', $module), 'left', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn(_M('Parâmetros', $module), 'left', true, NULL, true, NULL, true);
        $primaryKeys = array('idServico'=>'%0%', );
        $url = $MIOLO->getActionUrl($module, $action);
        parent::__construct(__CLASS__, NULL, $columns, $url);
        $args = array('event'=>'editButton:click', 'function'=>'edit', );
        $hrefUpdate = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $args = array(MUtil::getDefaultEvent()=>'deleteButton:click', 'function'=>'search', );
        $hrefDelete = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $this->addActionUpdate($hrefUpdate);
        $this->addActionDelete($hrefDelete);
        //$this->setRowMethod($this, 'myRowMethod');
    }
    
    /*public function myRowMethod($i, $row, $actions, $columns)
    {
        $MIOLO = MIOLO::getInstance();
        //
        // Verifica se há formulários e granularidades relacionadas com o serviço. Se existir, então "bloqueia" a opção de exclusão
        //
        $filter = new stdClass();
        $filter->refServico = $row[0];
        $avaFormulario = new avaFormulario($filter);
        $avaFormulario = $avaFormulario->search(ADatabase::RETURN_TYPE, true);
        $avaGranularidade = new avaGranularidade($filter);
        $avaGranularidade = $avaGranularidade->search(ADatabase::RETURN_TYPE, true);
        
        if ( count($avaFormulario) > 0 || count($avaGranularidade) > 0)
        {
            $actions[1]->disable();
        }
        else
        {
            $actions[1]->enable();
        }
    }*/
}


?>
