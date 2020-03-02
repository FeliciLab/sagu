<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Bruno Edgar Fuhr [bruno@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2013/10/28
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */
$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/prtTableRaw.class.php', $module);
$MIOLO->uses('classes/prtDisciplinas.class.php', $module);
$MIOLO->uses('classes/prtUsuario.class.php', $module);

class frmAvaliacaoBusca extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        $this->autoSave = false;
        
        parent::__construct(_M('Cadastro de avaliações', MIOLO::getCurrentModule()));
    }
    
    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $prtDisciplinas = new PrtDisciplinas();        
        $groupId = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');
        $usaConceito = MUtil::getBooleanValue($prtDisciplinas->usaConceito($groupId));
        
        $busEvaluation = new BusinessAcademicBusEvaluation();
        $busSchedule = $MIOLO->getBusiness('academic', 'BusSchedule');
        $busPhysicalPerson = $MIOLO->getBusiness('basic', 'BusPhysicalPerson');
        
        $user = $MIOLO->getLogin();
        $filters->mioloUserName = $user->id;
        $professor = $busPhysicalPerson->searchPhysicalPerson($filters);
        
        //Obtém todos os professores da oferecida
        $professores = $busSchedule->getGroupProfessors($groupId);
        foreach( $professores as $personId => $prof )
        {
            $professoresDaOferecida[] = $personId;
        }

        //Verifica se o professor logado é professor na disciplina oferecida
        if( !in_array($professor[0][0], $professoresDaOferecida) && !(prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR) )
        {
            //Bloqueia o acesso, pois o professor não é professor da disciplina oferecida
            $MIOLO->error(_M('Apenas professores da disciplina podem ter acesso a esta tela.'));
        }

        $fields[] = $this->button('btnAddAvaliacao', _M('Adicionar avaliação'), NULL, MUtil::getAjaxAction('novaAvaliacao'));

        if ( $usaConceito )
        {
            $colunas = array('Ação', 'Grau acadêmico', 'Descrição', 'Data');
        }
        else
        {
            $colunas = array('Ação', 'Grau acadêmico', 'Descrição', 'Data', 'Peso');
        }
        $imgCancelar = $MIOLO->getUI()->getImageTheme($module, 'cancel.png');
        $imgEdit = $MIOLO->getUI()->getImageTheme($module, 'edit_32x32.png');
        
        $filtros = new stdClass();
        $filtros->groupId = MIOLO::_REQUEST('groupid');
        $filtros->professorId = $this->personid;
        $avaliacoes = $busEvaluation->searchEvaluation($filtros);
        
        $row = NULL;
        foreach( $avaliacoes as $key => $avaliacao)
        {
            $cancelarAction = MUtil::getAjaxAction('cancelar', $avaliacao[0]);
            $linkCancelar = new MImageLink('lnkCancelar_' . $key, _M('Excluir'), NULL, $imgCancelar);
            $linkCancelar->addEvent('click', $cancelarAction);
            
            $editAction = MUtil::getAjaxAction('editar', $avaliacao[0]);
            $linkEdit = new MImageLink('lnkEdit_' . $key, _M('Editar'), NULL, $imgEdit);
            $linkEdit->addEvent('click', $editAction);
            
            $row[$key][] = $linkCancelar . '&nbsp;&nbsp;&nbsp;' . $linkEdit;
            $row[$key][] = new MLabel($avaliacao[4], '', true);
            $row[$key][] = new MLabel($avaliacao[2], '', true);
            $row[$key][] = new MLabel($avaliacao[12], '', true);
            if ( !$usaConceito )
            {
                $row[$key][] = new MLabel($avaliacao[5], '', true);
            }
        }
        
        $tableAvaliacoes = new prtTableRaw('', $row, $colunas);
        foreach ( $row as $key => $line )
        {
            $tableAvaliacoes->addCellAttributes($key, 0, array('align' => 'center', 'width' => '15%'));
            $tableAvaliacoes->addCellAttributes($key, 1, array('align' => 'center', 'width' => '25%'));
            $tableAvaliacoes->addCellAttributes($key, 2, array('align' => 'center', 'width' => '45%'));
            $tableAvaliacoes->addCellAttributes($key, 3, array('align' => 'center', 'width' => '10%'));
            if ( !$usaConceito )
            {
                $tableAvaliacoes->addCellAttributes($key, 4, array('align' => 'center', 'width' => '5%'));
            }
        }
        $tableAvaliacoes->addStyle('width', '100%');
        $divAvaliacoes = new MDiv('divAvaliacoes', $tableAvaliacoes);
        $divAvaliacoes->addStyle('width', '100%');
        $fields[] = new MBaseGroup('grpAvaliacoes', _M('Avaliações'), array($divAvaliacoes));
        
	parent::addFields($fields);
    }
    
    public function cancelar($avaliacao)
    {
        if ( $avaliacao )
        {            
            $infoText = new MText('txtAvaliacao', _M('Deseja excluir esta avaliação?'));
            $infoText->addStyle('font-weight', 'bold');
            $infoText->addStyle('font-size', '18px');
            $dlgFields[] = MUtil::centralizedDiv($infoText);
            $dlgFields[] = $avaField = new MTextField('avaliacaoId', $avaliacao);
            $avaField->setVisibility(false);
            
            $buttons[] = new MButton('btnSim', _M('Sim'));
            $buttons[] = new MButton('btnNao', _M('Não'));
            $dlgFields[] = MUtil::centralizedDiv($buttons);
            
            $dialog = new MDialog('dlgConfirmaExclusao', 'Confirme a exclusão', $dlgFields);
            $dialog->setWidth('40%');
            $dialog->show();
        }
    }
    
    public function editar($avaliacao)
    {
        if ( $avaliacao )
        {
            $MIOLO = MIOLO::getInstance();
            
            $module = MIOLO::getCurrentModule();
        
            $this->page->redirect($MIOLO->getActionURL($module, 'main:avaliacoes', NULL, array('edit' => 1, 'evaluationid' => $avaliacao, 'groupid' => MIOLO::_REQUEST('groupid'))));
        }
    }
    
    public function btnSim_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busEvaluation = new BusinessAcademicBusEvaluation();
        $avaliacao = $args->avaliacaoId;
        
        MDialog::close('dlgConfirmaExclusao');
        if ( $busEvaluation->countEvaluations($avaliacao) > 0 )
        {            
            new MMessageInformation(_M('Não é possível remover esta avaliação por já existirem notas.'));
            $this->setNullResponseDiv();
        }
        else
        {
            if ( $busEvaluation->deleteEvaluation($avaliacao) )
            {
                new MMessageSuccess(_M('Avaliação removida com sucesso.'));
            }
            else
            {
                new MMessageError(_M('Não foi possível remover esta avaliação.'));
            }
            
            $url = $MIOLO->getActionURL($module, 'main:avaliacoes', NULL, array('groupid' => MIOLO::_REQUEST('groupid')));
            $this->page->redirect($url);
        }
    }
    
    public function btnNao_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $url = $MIOLO->getActionURL($module, 'main:avaliacoes', NULL, array('groupid' => MIOLO::_REQUEST('groupid')));
        $this->page->redirect($url);
    }

    public function novaAvaliacao($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->page->redirect($MIOLO->getActionURL($module, 'main:avaliacoes', NULL, array('new' => 1, 'groupid' => MIOLO::_REQUEST('groupid'))));
    }
    
}

?>
