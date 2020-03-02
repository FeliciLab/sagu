<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/09/11
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

class frmDocumentosProfessor extends frmDocumentosPai
{
    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        $busTransaction = new BusinessAdminTransaction();
        $busGroup = new BusinessAcademicBusGroup();
        $busSchedule = $MIOLO->getBusiness('academic', 'BusSchedule');
        $busPhysicalPerson = $MIOLO->getBusiness('basic', 'BusPhysicalPerson');
        $busPhysicalPersonProfessor = $MIOLO->getBusiness('basic', 'BusPhysicalPersonProfessor');
        $professor = $busPhysicalPersonProfessor->getPhysicalPersonProfessor($this->personid);
        $eTemporario = MUtil::getBooleanValue($professor->eTemporario);
        $prtDocumentos = new prtDocumentos();
        
        $fields[] = MMessage::getStaticMessage('msgInfo', _M('Se o documento não for gerado, verifique se o seu navegador não está bloqueando popups.'), MMessage::TYPE_WARNING);
        
        if ( MIOLO::_REQUEST('attendenceError') == DB_TRUE )
        {
            // Caso ocorrer erro ao gerar o documento de atas de avaliação
            $fields[] = MMessage::getStaticMessage('msgError', _M('Não foi possível gerar o documento. Verifique se o professor e o horário da disciplina estão definidos.'), MMessage::TYPE_ERROR);
        }
                
        $groupId = MIOLO::_REQUEST('groupid');
        $isTcc = $busGroup->isFinalExaminationGroup($groupId);
        
        $user = $MIOLO->getLogin();
        $filters->mioloUserName = $user->id;
        $professor = $busPhysicalPerson->searchPhysicalPerson($filters);
        
        //Obtém todos os professores da oferecida
        $professores = $busSchedule->getGroupProfessors($groupId);
        foreach( $professores as $personId => $prof )
        {
            $professoresDaOferecida[] = $personId;
        }

        if ( $groupId )
        {
            //Verifica se o professor logado é professor na disciplina oferecida
            if( !in_array($professor[0][0], $professoresDaOferecida) && !(prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR) )
            {
                //Bloqueia o acesso, pois o professor não é professor da disciplina oferecida
                $MIOLO->error(_M('Apenas professores da disciplina podem ter acesso a esta tela.'));
            }
        }
	
        $panel = new mobilePanel('panel');

        // Senão estiver dentro de uma pasta
        if ( MIOLO::_REQUEST('pasta') != DB_TRUE )
        {
            if ( $groupId )
            {
                if ( $MIOLO->checkAccess('DocPlanoDeEnsino', A_ACCESS, FALSE) )
                {
                    $panel->addActionAJAX($busTransaction->getTransactionName('DocPlanoDeEnsino'), $ui->getImageTheme($module, 'notas.png'), 'planoDeEnsino');
                }

                if ( $MIOLO->checkAccess('DocDiarioDeClasse', A_ACCESS, FALSE) )
                {
                    $panel->addAction($busTransaction->getTransactionName('DocDiarioDeClasse'), $ui->getImageTheme($module, 'notas.png'), $module, 'main:diarioDeClasse', null, array('professorId' => $this->personid, 'groupId' => $groupId));
                }

                if ( $MIOLO->checkAccess('DocAtasDeAvaliacao', A_ACCESS, FALSE) )
                {
                    $panel->addAction($busTransaction->getTransactionName('DocAtasDeAvaliacao'), $ui->getImageTheme($module, 'notas.png'), 'academic', 'main:document:examinationAct', null, array('professorId' => $this->personid, 'groupId' => $groupId, 'generateOption' => 'pdf', 'emissionDate' => date(SAGU::getParameter('BASIC', 'MASK_DATE_PHP')),'event' => 'examinationAct'));
                }

                if ( $MIOLO->checkAccess('DocFolhaDeConteudos', A_ACCESS, FALSE) )
                {
                    $panel->addAction($busTransaction->getTransactionName('DocFolhaDeConteudos'), $ui->getImageTheme($module, 'notas.png'), 'academic', 'main:document:attachedLeaf', null, array('function'=>'print', 'event'=>'btnGerar_click', 'professorId' => $this->personid, 'groupId' => $groupId, 'generateOption' => 'pdf', 'emissionDate' => date(SAGU::getParameter('BASIC', 'MASK_DATE_PHP'))));        
                }

                if ( $MIOLO->checkAccess('DocResultadoFinal', A_ACCESS, FALSE) )
                {
                    $url = $MIOLO->getActionURL($module, 'main:documentosProfessor', null, array('event' => 'resultadoFinal', 'function' => 'print', 'groupId' => $groupId, 'disableAjax' => 1));
                    $url = str_replace('&amp;', '&', $url);

                    $panel->addAction($busTransaction->getTransactionName('DocResultadoFinal'), $ui->getImageTheme($module, 'notas.png'), null, null, null, null, 'window.location = \''.$url.'\'; return false;');
                }

                if ( $MIOLO->checkAccess('DocContatoDosAlunos', A_ACCESS, FALSE) )
                {
                    $panel->addAction($busTransaction->getTransactionName('DocContatoDosAlunos'), $ui->getImageTheme($module, 'notas.png'), $module, 'main:contatoAlunos', null, array('groupId' => $groupId));
                }

                if ( $isTcc )
                {
                    $panel->addAction(_M('Declaração de orientação de TCC'), $ui->getImageTheme($module, 'notas.png'), $module, 'main:declaracaoTcc', null, array('groupId' => $groupId));
                }

                $documentos = $prtDocumentos->getDocumentosDisciplina();
                foreach( $documentos as $documento )
                {
                    $documento instanceof BReport;
                    $docInfo = $documento->getReportInfo($documento->getReportFile());

                    $show = TRUE;
                    if ( $docInfo['type'] == 'contratoPrestacaoServico' && !$eTemporario )
                    {
                        $show = FALSE;
                    }

                    if ( $show )
                    {
                        $panel->addActionAJAX($docInfo['title'], $ui->getImageTheme($module, 'notas.png'), 'gerarDocumento', $docInfo['filepath']);
                    }
                }
            }
            else
            {
                $panel->addAction(_M('Declaração de participação em banca'), $ui->getImageTheme($module, 'notas.png'), $module, 'main:declaracaoParticipacaoBanca');

                $documentos = $prtDocumentos->getDocumentos('professor');

                foreach( $documentos as $documento )
                {
                    $documento instanceof BReport;
                    $docInfo = $documento->getReportInfo($documento->getReportFile());

                    if ( SAGU::getParameter('PORTAL', 'HABILITA_ATESTADO_PROFESSOR_PORTAL') == DB_TRUE )
                    {
                        if ( strlen($docInfo['title']) > 0 )
                        {
                            $panel->addActionAJAX($docInfo['title'], $ui->getImageTheme($module, 'notas.png'), 'gerarDocumento', $docInfo['filepath']);
                        }
                    }
                    else
                    {    
                        if ( preg_match("/cliente/", $docInfo['filepath']) && strlen($docInfo['title']) > 0 )
                        {
                            $panel->addActionAJAX($docInfo['title'], $ui->getImageTheme($module, 'notas.png'), 'gerarDocumento', $docInfo['filepath']);
                        }
                    }
                }
            }
        }
        
        $finalPanel = $this->obterDocumentosPortal($panel);
        
        $fields[] = $finalPanel;
        
	parent::addFields($fields);
    }
    
    public function resultadoFinal()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busEnroll = $MIOLO->getBusiness('academic', 'BusEnroll');
        $groupId = SAGU::NVL(MIOLO::_REQUEST('groupid'), MIOLO::_REQUEST('groupId'));
        
        $professor = $busEnroll->getGroupProfessor($groupId);
        
        $parameters = array();
        $parameters['GROUP_ID'] = (int) $groupId;

        $report = new SReport(array(
            'module' => 'academic',
            'reportName' => 'notesAndFrequencies',
            'parameters' => $parameters
        ));
        
        if ( !$report->generate() )
        {
            $this->addError(_M('Não foi possível gerar o documento.', $module ));
        }
    }
    
    public function planoDeEnsino()
    {
        $MIOLO = MIOLO::getInstance();
        $groupId = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');
        
        $professorData = prtUsuario::obterDadosPerfil($this->personid);
        
        $report = new SReport(array(
            'module' => 'academic',
            'reportName' => 'gradebook',
            'parameters' => array(
                'int_groupid' => $groupId,
                'professorName' => $professorData->nome
                )
        ));

        if ( !$report->generate() )
        {
            new MMessageWarning(_M('Não foi possível gerar o documento.'));
        }
    }
    
}
?>