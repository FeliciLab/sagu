<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Bruno Edgar Fuhr [bruno@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 08/05/2013
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
$MIOLO->uses('classes/prtCommonForm.class.php', $module);
$MIOLO->uses('classes/prtDisciplinas.class.php', $module);
$MIOLO->uses('classes/prtTableRaw.class.php', $module);
$MIOLO->uses('classes/prtUsuario.class.php', $module);

class frmResultadoFinal extends frmMobile
{
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Resultado final', MIOLO::getCurrentModule()));

        $this->setShowPostButton(FALSE);
    }

    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $userPerms = $MIOLO->getLogin()->rights["frmresultadofinalprofessor"];
        
        // Obter os business necessários.
        $busProfessorFrequency = $MIOLO->getBusiness('services', 'BusProfessorFrequency');
        $busDegree = $MIOLO->getBusiness('academic', 'BusDegree');
        $busEnroll = $MIOLO->getBusiness('academic', 'BusEnroll');
        $busEnrollStatus = $MIOLO->getBusiness('academic', 'BusEnrollStatus');
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $busSchedule = $MIOLO->getBusiness('academic', 'BusSchedule');
        $busPhysicalPerson = $MIOLO->getBusiness('basic', 'BusPhysicalPerson');
        
        $disciplinas = new PrtDisciplinas();
        $commonForm = new prtCommonForm();
        $groupId = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');
        $groupData = $busGroup->getGroup($groupId);
        
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
        
        $isEstadoDetalhado = $disciplinas->isEstadoDetalhado();
        $tiposDeNota = $busDegree->getEnrollDegree(MIOLO::_REQUEST('groupid'), true);
        
        $colunasDaTabela[] = _M('Nome do aluno');
        
        if ( $groupData->evaluationTypeId == BusinessAcademicBusEvaluationType::POR_FREQUENCIA || 
             $groupData->evaluationTypeId == BusinessAcademicBusEvaluationType::POR_NOTA_E_FREQUENCIA )
        {
            $colunasDaTabela[] = _M('Freq.');
        }
        
        if ( $groupData->evaluationTypeId == BusinessAcademicBusEvaluationType::POR_NOTA || 
             $groupData->evaluationTypeId == BusinessAcademicBusEvaluationType::POR_NOTA_E_FREQUENCIA )
        {
            foreach($tiposDeNota as $tipoNota)
            {
                $degree = $busDegree->getDegreeCache($tipoNota[0]);
                $colunasDaTabela[] = $degree->description;
            }
        }
        
        // Se estiver aberta
        $estado          = ($groupData->isClosed == DB_FALSE) ? 'Estado previsto' : 'Estado';
        $estadoDetalhado = ($groupData->isClosed == DB_FALSE) ? 'Estado detalhado previsto' : 'Estado detalhado';
            
        $colunasDaTabela[] = _M($estado);
        if ( $isEstadoDetalhado )
        {
            $colunasDaTabela[] = _M($estadoDetalhado);
        }
        
        $alunos = $busProfessorFrequency->listGroupPupilsEnrolled($groupId);
        $linhasDaTabela = array();
        foreach( $alunos as $key => $aluno )
        {
            $nomeAluno = new MLabel($aluno[0], '', true);
            $linhasDaTabela[$key][] = $nomeAluno;
            
            $matricula = $aluno[2];
            
            if ( $groupData->evaluationTypeId == BusinessAcademicBusEvaluationType::POR_FREQUENCIA || 
             $groupData->evaluationTypeId == BusinessAcademicBusEvaluationType::POR_NOTA_E_FREQUENCIA )
            {
                $linhasDaTabela[$key][] = $lblFreq = new MLabel($busEnroll->obterPercentualDeFrequencia($matricula), '', true);
                $lblFreq->setClass('lblResultado');
            }
            
            if ( $groupData->evaluationTypeId == BusinessAcademicBusEvaluationType::POR_NOTA || 
                 $groupData->evaluationTypeId == BusinessAcademicBusEvaluationType::POR_NOTA_E_FREQUENCIA )
            {
                $notaExame = false;
                $notas = $disciplinas->obterNotas($groupId, $matricula);
                foreach($notas as $nota)
                {
                    if ( $nota['exame'] == DB_TRUE )
                    {
                        $notaExame = $nota['nota'] ? true : false;
                    }

                    $linhasDaTabela[$key][] = $lblNota = new MLabel($nota['nota'], '', true);
                    $lblNota->setClass('lblResultado');
                }
            }
            
            $linhasDaTabela[$key][] = $estado = $commonForm->obterEstadoMatricula($matricula);
            $estado->setClass('lblResultado');
            
            if ( $isEstadoDetalhado )
            {
                $filters = new stdClass();
                $estadoDaMatricula = $commonForm->obterEstadoDaMatriculaId($matricula);
                $filters->parentStatus = $estadoDaMatricula;
                $estadosDetalhados = AcdDetailedEnrollStatus::listRecords($filters);                
                
                $futureStatus = $busEnrollStatus->getEnrollStatus($estadoDaMatricula);
                
                // Posição já selecionada:
                // - se for APROVADO: Selecionar por padrão APROVADO POR MÉDIA
                // - se for APROVADO e tiver nota de exame: Selecionar por padrão APROVADO POR EXAME
                // - se for REPROVADO: Selecionar por padrão REPROVADO POR MÉDIA
                // - se for REPROVADO e tiver nota de exame: Selecionar por padrão REPROVADO POR EXAME
                
                if ( $estadoDaMatricula == SAGU::getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED') )
                {
                    if ( $notaExame )
                    {
                        foreach ( $futureStatus->detailEnrollStatus as $fkey => $futureState )
                        {
                            if ( $futureState->isexam == DB_TRUE )
                            {
                                $selectedPosition = $futureStatus->detailEnrollStatus[$fkey]->detailEnrollStatusId;
                                break;
                            }
                        }
                    }
                    else
                    {
                        $count = 0;
                        $chave = NULL;
                        foreach ( $futureStatus->detailEnrollStatus as $fkey => $futureState )
                        {
                            if ( $futureState->isexam == DB_FALSE )
                            {
                                $count++;
                                $chave = $futureStatus->detailEnrollStatus[$fkey]->detailEnrollStatusId;
                            }
                        }

                        if ( $count == 1 )
                        {
                            $selectedPosition = $chave;
                        }
                    }
                }
                elseif ( $estadoDaMatricula == SAGU::getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED') )
                {
                    if ( $notaExame )
                    {
                        foreach ( $futureStatus->detailEnrollStatus as $fkey => $futureState )
                        {
                            if ( $futureState->isexam == DB_TRUE )
                            {
                                $selectedPosition = $futureStatus->detailEnrollStatus[$fkey]->detailEnrollStatusId;
                                break;
                            }
                        }
                    }
                    else
                    {
                        $count = 0;
                        $chave = NULL;
                        foreach ( $futureStatus->detailEnrollStatus as $fkey => $futureState )
                        {
                            if ( $futureState->isexam == DB_FALSE )
                            {
                                $count++;
                                $chave = $futureStatus->detailEnrollStatus[$fkey]->detailEnrollStatusId;
                            }
                        }

                        if ( $count == 1 )
                        {
                            $selectedPosition = $chave;
                        }
                    }
                }
                else
                {
                    $selectedPosition = $futureStatus->detailEnrollStatus[0]->detailEnrollStatusId;
                }       
                
                if ( ( in_array(A_INSERT, $userPerms) || 
                       in_array(A_UPDATE, $userPerms) ) &&
                     ( $groupData->isClosed == DB_FALSE ) )
                {
                    $linhasDaTabela[$key][] = new MSelection('selEstadoDetalhado_' . $matricula, $selectedPosition, '', $estadosDetalhados);
                }
                else
                {
                    $linhasDaTabela[$key][] = new MText('selEstadoDetalhado_' . $matricula, $futureStatus->detailEnrollStatus[0]->description, null, true);
                }
            }
        }
        
        if ( $groupData->isClosed == DB_TRUE )
        {
            $fields[] = MMessageInformation::getStaticMessage('msgInfo', _M('Esta disciplina está encerrada.'), MMessage::TYPE_INFORMATION);
        }
        
        $tabelaResultados = new prtTableRaw(_M('Resultados'), $linhasDaTabela, $colunasDaTabela);
        foreach ( $linhasDaTabela as $key => $linha )
        {
            $tabelaResultados->addCellAttributes($key, 0, array('align' => 'left'));
            $tabelaResultados->addCellAttributes($key, 1, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 2, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 3, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 4, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 5, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 6, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 7, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 8, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 9, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 10, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 11, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 12, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 13, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 14, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 15, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 16, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 17, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 18, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 19, array('align' => 'center'));
            $tabelaResultados->addCellAttributes($key, 20, array('align' => 'center'));
        }
        $tabelaResultados->setWidth('100%');
        $fields[] = $tabelaResultados;
        
        $professor = $this->retornaPersonIdPessoaLogada();
        $isProfessorResponsible = $this->verificaProfessorResponsavel(MIOLO::_REQUEST('groupid'), $professor);
        
        $ok = FALSE;
        $msg = '';
        
        if ( $busGroup->checkGradesTyping($groupId) && $groupData->isClosed == DB_FALSE )
        {
            if ( in_array(A_INSERT, $userPerms) || in_array(A_UPDATE, $userPerms) )
            {
                if ( $isProfessorResponsible == DB_TRUE )
                {
                    $ok = TRUE;
                }
                else
                {
                    $msg .= _M(' - Apenas o professor responsável pela disciplina pode encerrá-la.');
                }
            }
            else
            {
                $msg .= _M(' - Você não tem as permissões necessárias para encerrar a disciplina.');
            }
        }
        else
        {
            try
            {
                SDatabase::beginTransaction();
                $ok = $busGroup->closeGroup($groupId);
                SDatabase::rollback();
            }
            catch (Exception $e)
            {
                SDatabase::rollback();
                
                foreach ( $busGroup->getErrors() as $error )
                {
                    $msg .= ' - ' . $error . '<br>';
                }
            }
        }
        
        if ( !$ok )
        {
            $fixedMsg = _M('Esta disciplina não pode ser encerrada pelos seguintes motivos:<br><br>');
            $txt = new MLabel($fixedMsg . $msg, 'white', true);
            $div = new MDiv('divMsgCloseGroup', array($txt));

            $div->addStyle('border', '1px solid');
            $div->addStyle('padding', '8px');
            $div->addStyle('background-color', '#268CEB');

            $fields[] = new MSeparator();
            $fields[] = $div;
        }
        else if ( $ok && $groupData->isClosed == DB_FALSE )
        {
            $buttons[] = new MButton('btnEncerrarDisciplina', _M('Encerrar disciplina'));
            $fields[] = MUtil::centralizedDiv($buttons, 'divButtons');
        }

	parent::addFields($fields);
    }
    
    public function btnEncerrarDisciplina_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $groupId = MIOLO::_REQUEST('groupid');
        
        try
        {
            if ( $busGroup->closeGroup($groupId) )
            {
                new MMessageSuccess(_M('Disciplina encerrada com sucesso!'));
                
                $this->setResponse(NULL, 'divButtons');
            }
            else
            {
                new MMessageWarning(_M('A disciplina não pôde ser encerrada.'));
            }
        }
        catch( Exception $e )
        {
            new MMessage($busGroup->getErrors());
        }
    }
    
    /*
     * Verifica se a pessoa logada � o professor respons�vel da disciplina.
     * Est� verifica��o acontece apenas se o par�metro est� habilitado e existe um professor cadastrado com respons�vel
     * caso contr�rio, mant�m a funcionalidade original.
     */
    public function verificaProfessorResponsavel($groupId, $personId)
    {
        if(SAGU::getParameter('ACADEMIC', 'SOMENTE_PROFESSOR_RESPONSAVEL') == DB_FALSE)
        {
            return DB_TRUE;
        }
        else
        {
            $busGroup = new BusinessAcademicBusGroup();
            $grupo = $busGroup->getGroup($groupId);
            
            if( $grupo->professorResponsible == $personId )
            {
                return DB_TRUE;
            }
            else
            {
                return DB_FALSE;
            }
        }
    }
    
    /*
     * Retorna o personid da pessoa logada
     */
    public function retornaPersonIdPessoaLogada()
    {
        $MIOLO = MIOLO::getInstance();
        
        $user = $MIOLO->getLogin();
        $filters->mioloUserName = $user->id;
        $busPhysicalPerson = new BusinessBasicBusPhysicalPerson();
        $professor = $busPhysicalPerson->searchPhysicalPerson($filters);
        
        return $professor[0][0];
    }
}

?>
