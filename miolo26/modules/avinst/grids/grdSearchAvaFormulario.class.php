<?php

/**
 * Grid da tabela ava_formulario.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 21/11/2011
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
class grdSearchAvaFormulario extends AGrid
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $columns[] = new MGridColumn('Código do formulário', 'right', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn('Cód. avaliação', 'right', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn('Avaliação', 'left', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn('Perfil', 'right', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn('Nome', 'left', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn(null);
        $columns[] = new MGridColumn('Serviço', 'left', true, NULL, true, NULL, true);
        $primaryKeys = array('idFormulario'=>'%0%', );
        $url = $MIOLO->getActionUrl($module, $action);
        parent::__construct(__CLASS__, NULL, $columns, $url);
        $args = array('event'=>'editButton:click', 'function'=>'edit', );
        $hrefUpdate = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $args = array(MUtil::getDefaultEvent()=>'deleteButton:click', 'function'=>'search', );
        $hrefDelete = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $this->addActionUpdate($hrefUpdate);
        $this->addActionDelete($hrefDelete);
    }
    
    //
    // Passa linha a linha e executa o procedimento, bloqueando a exclusão caso algum usuário já tenha acessado o formulário
    //
    public function myRowMethod($i, $row, $actions, $columns)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaFormLog.class.php', 'avinst');
        $data = new stdClass();
        $data->refFormulario = $row[0];
        $avaFormLog = new avaFormLog($data);
        $tentativas = $avaFormLog->contaTentativasPorFormulario();
        if ($tentativas>0)
        {
            // $actions[0]->disable(); // desabilita a ação editar - TODO: Verificar se deve ser desabilitado ou não
            $actions[1]->disable(); // desabilita a ação excluir               
        }
        else
        {
            $actions[1]->enable();
        }
    }
}


?>
