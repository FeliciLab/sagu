<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/10/23
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


class frmNotasProfessor extends frmMobile
{
    public function __construct($titulo = null)
    {
      
        self::$fazerEventHandler = FALSE;
        $titulo = strlen($titulo) > 0 ? 'NOTAS ['.$titulo.']' : 'Notas '; 
        parent::__construct(_M($titulo, MIOLO::getCurrentModule()));
        
        $this->setShowPostButton(FALSE);
    }

    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busDegree = $MIOLO->getBusiness('academic', 'BusDegree');
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $busLearningPeriod = $MIOLO->getBusiness('academic', 'BusLearningPeriod');
        $busSchedule = $MIOLO->getBusiness('academic', 'BusSchedule');
        $busPhysicalPerson = $MIOLO->getBusiness('basic', 'BusPhysicalPerson');
        
        $groupId = MIOLO::_REQUEST('groupid');
        $dgs = $busDegree->getEnrollDegree($groupId, true);
        $groupData = $busGroup->getGroup($groupId);
                
        $user = $MIOLO->getLogin();
        $filters->mioloUserName = $user->id;        
        $pessoaLoaga = $busPhysicalPerson->searchPhysicalPerson($filters);
        
        //Obtém todos os professores da oferecida
        $professores = $busSchedule->getGroupProfessors($groupId);
        
        foreach( $professores as $personId => $professor )
        {
            $professoresDaOferecida[] = $personId;
        }
        
        // Verifica permissão na tela de digitação de notas e frequência, devido ao sistema redirecionar para o portal
        $checkAccess = $MIOLO->checkAccess('FrmGradesTyping', A_ADMIN, false);
                
        //Verifica se o professor logado é professor na disciplina oferecida
        if( !in_array($pessoaLoaga[0][0],$professoresDaOferecida) && !(prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR) )
        {
            if ( $checkAccess == false )
            {
                //Bloqueia o acesso, pois o professor não é professor da disciplina oferecida
                $MIOLO->error(_M('Apenas professores da disciplina podem cadastrar notas.'));
            }
        }
        
        // Exibe conceito/nota final
        $exibeConceitoFinal = true;
        
        //Verifica se a preferência está habilitada
        if ( SAGU::getParameter('ACADEMIC', 'SOMENTE_PROFESSOR_RESPONSAVEL') == DB_TRUE )
        {
            if ( $groupData->professorResponsible != $pessoaLoaga[0][0] && strlen($groupData->professorResponsible) > 0 )
            {
                $busEvaluation = new BusinessAcademicBusEvaluation();
                $filters = new stdClass();
                $filters->groupId = $groupId;
                $filters->professorId = $pessoaLoaga[0][0];

                $avalicao = $busEvaluation->searchEvaluation($filters);
                
                if ( count($avalicao) > 0 )
                {
                    $exibeConceitoFinal = false;
                }
                
                //Bloqueia o acesso, caso o professor logado não seja o responsável
                //$MIOLO->error(_M('Apenas o professor responsável pode cadastrar notas.'));
            }
        }
        
        if ( $groupData->isClosed == DB_TRUE )
        {
            $fields[] = MMessageInformation::getStaticMessage('msgInfo', _M('Esta disciplina está encerrada.'), MMessage::TYPE_INFORMATION);
        }
        elseif ( !$busLearningPeriod->permiteRegistrarNotaOuFrequencia($groupId) )
        {
            $fields[] = MMessageInformation::getStaticMessage('msgInfo', $busLearningPeriod->obterMensagemDigitacaoBloqueada($groupId), MMessage::TYPE_INFORMATION);
        }
        else
        {
            foreach($dgs as $d)
            {
                $degree = $busDegree->getDegreeCache($d[0]);
                $options[] = array($degree->degreeId, $degree->description);
            }

            // Hidden que indica qual matricula esta sendo alterada. Utilizado para identificar na funcao salvar AJAX.
            $fields[] = new MHiddenField('alterandoMatricula');
                             
            if ( $options[0][0] )
            {
                $selection = new MSelection('degreeId', $options[0][0], '', $options);
                $selection->setAttribute('onchange', MUtil::getAjaxAction('trocarNota'));

                $bgNotas = new MDiv('', new MBaseGroup('', _M('Nota'), array($selection)));
                $bgNotas->addStyle('margin', '0 0 0 0');
                $bgNotas->addStyle('width', '100%');

                $fields[] = $bgNotas;
                $fields[] = new MSpacer();
                $fields[] = new MDiv('divAvaliacoes', $this->avaliacoes($options[0][0], $exibeConceitoFinal));
            }
            else
            {
                $fields[] = MMessageInformation::getStaticMessage('msgInfo', _M('Não há nenhuma nota cadastrada para esta disciplina.'), MMessage::TYPE_INFORMATION);
            }
        }
        
        $fields[] = MDialog::getDefaultContainer();

	parent::addFields($fields);
    }
    
    public function trocarNota($args)
    {
        $args = $this->getAjaxData();
        
        if ( strlen($args->degreeId) > 0 )
        {
            $this->setResponse($this->avaliacoes($args->degreeId), 'divAvaliacoes');
        }
        else
        {
            $this->setNullResponseDiv();
        }
    }
    
    /**
     * @return array
     */
    public function obterAvaliacoes($degreeId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $groupId = MIOLO::_REQUEST('groupid');
        
        $busEvaluation = $MIOLO->getBusiness('academic', 'BusEvaluation');
        
        // Avaliacoes
        $filter = new stdClass();
        $filter->degreeId = $degreeId;
        $filter->groupId = $groupId;
        
        return (array) $busEvaluation->searchEvaluationAssoc($filter);
    }
    
    /**
     * @return array 
     */
    public function obterAlunos()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busProfessorFrequency = $MIOLO->getBusiness('services', 'BusProfessorFrequency');

        return (array) $busProfessorFrequency->listGroupPupilsEnrolled(MIOLO::_REQUEST('groupid'), true);
    }
    
    public function avaliacoes($degreeId, $exibeConceitoFinal = true)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busDegree = $MIOLO->getBusiness('academic', 'BusDegree');
        $busGradeTyping = $MIOLO->getBusiness('academic', 'BusGradeTyping');

        // Objeto nota
        $degree = $busDegree->getDegreeCache($degreeId);
        
        $avaliacoes = $this->obterAvaliacoes($degreeId);
        
        foreach ( $this->obterAlunos() as $aluno)
        {
            if ( $degree->isExam == DB_TRUE )
            {
                if ( $busGradeTyping->deveFazerExame($degreeId, $aluno['enrollId']) )
                {
                    $fields[] = $this->criaBaseGroupAluno($degreeId, $aluno, $degree, $avaliacoes, $exibeConceitoFinal);
                }
            }
            else
            {
                $fields[] = $this->criaBaseGroupAluno($degreeId, $aluno, $degree, $avaliacoes, $exibeConceitoFinal);
            }
        }

        return $fields;
    }
        
    public function criaBaseGroupAluno($degreeId, $aluno, $degree, $avaliacoes, $exibeConceitoFinal = true)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $nomeAluno = new MLabel($aluno['personName']);
        $nomeAluno->setClass('label-nome-aluno');
        $divAluno = new MDiv('', $nomeAluno);
        $divAluno->addStyle('width', '30%');
        $divAluno->addStyle('padding-top', '9px');
        
        $enrollId = $aluno['enrollId'];
        $div = array();
        
        /*$divEstado = new MDiv('divEstado_' . $enrollId, prtCommonForm::obterEstadoMatricula($enrollId));
        $divEstado->addStyle('margin', '0px');
        $div[] = $divEstado;*/
        
        foreach ( $avaliacoes as $avaliacao )
        {
            $div[] = $this->criaFieldsAvaliacao($avaliacao, $enrollId);
        }

        if ( $exibeConceitoFinal == true )
        {
            $nota = $this->obterCampoNota($degreeId, $enrollId, $avaliacoes, $degree);
        }
        $div[] = $nota;

        if ( !MUtil::getBooleanValue(SAGU::getParameter('PORTAL', 'DESABILITA_EXIBICAO_FOTO_ALUNO')) )
        {
            $divFoto = prtCommonForm::obterFoto($aluno['photoId'], '64', '80');
        }
        
        $divInfo = new MDiv('', $div);
        $divInfo->addStyle('width', '50%');
        $divInfo->addStyle('float', 'left');
        //$divInfo->addStyle('margin', '15px');
        
        $bgr = new MBaseGroup('bsgAluno', '', array($divFoto, $divAluno, $divInfo));
        
        return $bgr;
    }
    
    public function criaFieldsAvaliacao($avaliacao, $enrollId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $prtDisciplinas = new PrtDisciplinas();        
        $groupId = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');
        $usaConceito = MUtil::getBooleanValue($prtDisciplinas->usaConceito($groupId));
        
        $busEvaluationEnroll = $MIOLO->getBusiness('academic', 'BusEvaluationEnroll');
        $evaluationId = $avaliacao['evaluationId'];
        
        $usuario = SAGU::getUsuarioLogado();
        
        $busEvaluation = new BusinessAcademicBusEvaluation();
        $infoEvaluation = $busEvaluation->getEvaluation($evaluationId);
        
        $verificaProfessor = true;
        
        // Verifica se o campo 'pode digitar' está com 'P'(apenas o professor informado/apenas eu) e bloquea o processo
        // de digitação para a avaliação, permitindo apenas o professor que cadastrou a avaliação 
        if ( $infoEvaluation->podeDigitar == BusinessServicesBusProfessor::APENAS_O_PROFESSOR_INFORMADO )
        {
            if ( $usuario->personId != $infoEvaluation->professorId )
            {
                $verificaProfessor = false;
            }
        }
        
        if ( $verificaProfessor == true )
        {
            $valor = $busEvaluationEnroll->getEvaluationEnrollCurrentGrade($evaluationId, $enrollId, $usaConceito);
            $id = 'avaliacao_'.$evaluationId.'_'.$enrollId;

            if ( $usaConceito )
            {
                $conceitos = $prtDisciplinas->obterConceitos($groupId);
                $campo = new MSelection($id, $valor, $avaliacao['description'], $conceitos);
            }
            else
            {
                $campo = new MFloatField($id, $valor, $avaliacao['description']);
            }

            $args = new stdClass();
            $args->enrollId = $enrollId;
            $args->degreeId = $avaliacao['degreeId'];
            $args->evaluationId = $evaluationId;

            $campo->addAttribute('onchange', MUtil::getAjaxAction('salvar', $args));        
            //$campo->addAttribute('onchange', $this->eventoJsNota($enrollId));

            //$div[] = new MLabel($avaliacao['description']);        
            $div = new MFormContainer('', array($campo));
        }
        
        return $div;
    }
    
    /**
     * @return array
     */
    public function obterCampoNota($degreeId, $enrollId, $avaliacoes, $degree)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $prtDisciplinas = new PrtDisciplinas();        
        $groupId = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');
        $usaConceito = MUtil::getBooleanValue($prtDisciplinas->usaConceito($groupId));
        
        $busDegreeEnroll = $MIOLO->getBusiness('academic', 'BusDegreeEnroll');
        $busGradeTyping = $MIOLO->getBusiness('academic', 'BusGradeTyping');
        $busDegree = $MIOLO->getBusiness('academic', 'BusDegree');
                
        $id = 'nota_' . $degreeId . '_' . $enrollId;
        $notaAtual = $busDegreeEnroll->getDegreeEnrollCurrentGrade($degreeId, $enrollId);
                
        if ( MUtil::getBooleanValue($usaConceito) )
        {
            $conceitos = $prtDisciplinas->obterConceitos($groupId);
            $nota = new MSelection($id, $notaAtual, $degree->description, $conceitos);
        }
        else
        {
            $label = new MLabel($degree->description . ':');
            $nota = new MFloatField($id, $notaAtual, null, SAGU::getParameter('BASIC', 'FIELD_ID_SIZE'), null, null, false, SAGU::getParameter('BASIC', 'GRADE_ROUND_VALUE'));
        }
        
        if ( MUtil::getBooleanValue(SAGU::getParameter('PORTAL', 'HABILITAR_JUSTIFICATIVA_NOTA')) && strlen($notaAtual) > 0 )
        {
            $justificativa = $this->obterCampoJustificativa($degreeId, $enrollId, FALSE, FALSE);
            
            $nota->setReadOnly(TRUE);
        }
        elseif ( MUtil::getBooleanValue(SAGU::getParameter('PORTAL', 'HABILITAR_JUSTIFICATIVA_NOTA')) )
        {
            $contId = 'contJustificativa_' . $degreeId . '_' . $enrollId;
            $justificativa = new MDiv($contId);
            $justificativa->addStyle('margin-top', '-18px');
        }
        
        $args = new stdClass();
        $args->enrollId = $enrollId;
        $args->degreeId = $degree->degreeId;
        
        $nota->addAttribute('onchange', MUtil::getAjaxAction('salvar', $args));
        //$nota->addAttribute('onchange', $this->eventoJsNota($enrollId));
        
        $infoDegree = $busDegree->getDegree($degreeId);
        
        // Verifica se exite data limite para o cadastrado da nota
        $limiteDigitacao = strlen($infoDegree->limitDate) > 0 ? ( (SAGU::dateDiff(SAGU::getDateNow(), $infoDegree->limitDate)) > 0 ) : false;
                
        if ( MUtil::getBooleanValue($usaConceito) )
        {
            $finalDegree = NULL;
            $notaDigitada = TRUE;
                                    
            $dgs = $busDegree->getEnrollDegree($groupId, true);
            foreach( $dgs as $dg )
            {
                if ( $dg[4] == NULL )
                {
                    $finalDegree = $dg[0];
                }
                elseif ( !MUtil::getBooleanValue($dg[5]) && $notaDigitada )
                {
                    $degreeGrade = $busGradeTyping->getEnrollDegreeIdNote($dg[0], $enrollId, TRUE);                    
                    $notaDigitada = strlen($degreeGrade) > 0;
                }
            }
            
            // Define read-only quando:
            // Houverem outras notas e não foram todas digitadas.
            if ( ($degree->degreeId == $finalDegree && !$notaDigitada) || $limiteDigitacao )
            {
                $nota->setReadOnly(true);
            }
        }
        else
        {
            // Define read-only quando:
            // Quando houver avaliacoes OU
            // Quando for nota tipo MEDIA/NOTA FINAL, nao pode digitar a nota
            // Quando nao pegou exame
            if ( count($avaliacoes) > 0 || $busGradeTyping->eNotaEspecial($degree->degreeId) || !$busGradeTyping->deveFazerExame($degreeId, $enrollId) || $limiteDigitacao )
            {
                $nota->setReadOnly(true);
            }
        }
        
        $linkHistorico = $this->button('hist_' . $degreeId . '_' . $enrollId, NULL, NULL, MUtil::getAjaxAction('mostraHistorico', "$degreeId|$enrollId"), $MIOLO->getUI()->getImageTheme('portal', 'bf-explorar-on.png'));
                
        if ( !MUtil::getBooleanValue($usaConceito) )
        {
            $nota = new MHContainer('', array($label, $nota));
        }
        
        $div = new MFormContainer('', array($justificativa, $nota));
                
        return new MHContainer('divNota_' . $degreeId . '_' . $enrollId, array($div, $linkHistorico));
    }
    
    public function mostraHistorico($args)
    {
        $groupId = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');
        
        $args = explode('|', $args);
        $degreeId = $args[0];
        $enrollId = $args[1];
        
        $prtDisciplinas = new PrtDisciplinas();
        $usaConceito = $prtDisciplinas->usaConceito($groupId);
        $historico = $prtDisciplinas->obterHistorico($enrollId, $degreeId, $usaConceito);
        
        $table = new prtTableRaw(_M('Histórico de notas'), $historico, array('Data do registro', 'Usuário', 'Nota', 'Descrição/Justificativa'));
        foreach($historico as $key => $linha)
        {
            $table->addCellAttributes($key, 0, array('align' => 'center'));
            $table->addCellAttributes($key, 2, array('align' => 'center'));
        }
        
        $table->setWidth('100%');
        
        $botao = $this->button('botaoFechar', _M('Fechar', $this->modulo), NULL, "dijit.byId('dlgHistorico').hide();");
        
        $dialog = new MDialog('dlgHistorico', _M('Histórico'), array($table, $botao));
        $dialog->show();
        
        $this->setNullResponseDiv();
    }
        
    /**
     * @return string
     */
    public function eventoJsNota($enrollId)
    {
        return " $('#alterandoMatricula').val('{$enrollId}') ";
    }
        
    public function salvar()
    {        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $args = MUtil::getAjaxActionArgs();

        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
                
        $groupId = $args->groupid;
        $enrollId = $args->enrollId;
        $degreeId = $args->degreeId;        
        $avaliacoes = $this->obterAvaliacoes($degreeId);
        $usaConceito = $disciplinas->usaConceito($groupId);
        
        if ( strlen($enrollId) == 0 )
        {
            $this->setNullResponseDiv();
            return;
        }
        
        if ( MUtil::getBooleanValue($usaConceito) )
        {
            // Notas por avaliacao
            if ( count($avaliacoes) > 0 )
            {
                if ( strlen($args->evaluationId) > 0 )
                {
                    foreach ( $avaliacoes as $key => $avaliacao )
                    {
                        $ultima = !isset($avaliacoes[$key + 1]);
                        $this->processaAvaliacao($disciplinas, $avaliacao, $args, $ultima, $enrollId, $degreeId);
                    }
                }
                else
                {
                    $this->processaNota($disciplinas, $args, $enrollId, $degreeId);
                }
            }
            else // Notas da forma tradicional (sem avaliacao)
            {
                $this->processaNota($disciplinas, $args, $enrollId, $degreeId);
            }
        }
        else
        {
            // Notas por avaliacao
            if ( count($avaliacoes) > 0 )
            {
                foreach ( $avaliacoes as $key => $avaliacao )
                {
                    $ultima = !isset($avaliacoes[$key + 1]);
                    $this->processaAvaliacao($disciplinas, $avaliacao, $args, $ultima, $enrollId, $degreeId);
                }
            }
            else // Notas da forma tradicional (sem avaliacao)
            {
                $this->processaNota($disciplinas, $args, $enrollId, $degreeId);
            }
        }
        
        // Exibe o estado final para o usuario (APROVADO/REPROVADO)...
        // Utiliza funcoes padrao de calculo de nota final/media, e depois de obtencao de estado (APROVADO, REPROVADO...), o mesmo utilizado no fechamento de disciplina
        $busEnroll = $MIOLO->getBusiness('academic', 'BusEnroll');
        $busEnroll->calculaNota($enrollId); // Calculando a nota sera possivel calcular o estado do aluno
        
        $divEstado = 'divEstado_' . $enrollId;
        
        $prtCommonForm = new prtCommonForm();
        $this->setResponse($prtCommonForm->obterEstadoMatricula($enrollId), $divEstado);

        $this->setNullResponseDiv();
    }
    
    public function processaNota(PrtDisciplinas $disciplinas, $args, $enrollId, $degreeId)
    {
        $MIOLO = MIOLO::getInstance();
        
        $id = 'nota_' . $degreeId . '_' . $enrollId;
        $nota = $args->$id;
        
        $justificativaId = 'just_' . $degreeId . '_' . $enrollId;
        $justificativa = $args->$justificativaId;
        
        $prtDisciplinas = new PrtDisciplinas();
        $groupId = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');
        $usaConceito = $prtDisciplinas->usaConceito($groupId);
        $descricao = _M('NOTA INSERIDA PELO USUÁRIO');
        
        if ( MUtil::getBooleanValue($usaConceito) )
        {
            // Salva nota de forma simples
            try
            {
                $disciplinas->salvarConceito($enrollId, $degreeId, $nota);
            }
            catch(Exception $e)
            {
                new MMessageWarning(_M('Não foi possível salvar este conceito.'));
            }
        }
        else
        {
            // Verifica se a nota digitada é maior que a nota máxima permitida.
            $notaMaxima = $prtDisciplinas->obterNotaMaximaDaDisciplina($groupId);
            if ( $notaMaxima )
            {
                if ( $nota > $notaMaxima )
                {
                    $jsCode = "document.getElementById('$id').value = ''";
                    $this->manager->page->onload($jsCode);
                    new MMessageWarning(_M("A nota digitada é maior do que a nota máxima permitida ($notaMaxima)."));
                    return false;
                }
            }
            
            // Verifica se a nota digitada é menor do que a nota registrada ( se o parâmetro estiver habilitado )
            if ( SAGU::getParameter('ACADEMIC', 'CONSIDER_HIGHER_PUNCTUATION_DEGREE') == DB_TRUE )
            {
                // Se a nota estiver em branco, significa que foi excluída.
                if ( strlen($nota) > 0 )
                {
                    $busDegreeEnroll = $MIOLO->getBusiness('academic', 'BusDegreeEnroll');
                    $notaMaiorAtual = $busDegreeEnroll->getDegreeEnrollCurrentGrade($degreeId, $enrollId);

                    if ( ( strlen($notaMaiorAtual) > 0 ) && ( is_numeric($notaMaiorAtual) || is_double($notaMaiorAtual) ) )
                    {
                        $isMaior = SAGU::calcNumber(" {$notaMaiorAtual} > {$nota} ") == DB_TRUE;
                        if ( $isMaior )
                        {
                            $jsCode = "document.getElementById('$id').value = '$notaMaiorAtual'";
                            $this->manager->page->onload($jsCode);
                            new MMessageWarning(_M('Esta nota é inferior à nota existente e não será substituída.'));
                            return false;
                        }
                    }
                }
            }

            // Salva nota de forma simples
            try
            {
                if ( strlen($nota) <= 0 )
                {
                    if ( $MIOLO->checkAccess('FrmGradesTyping', A_DELETE) || $MIOLO->checkAccess('FrmProfessorGradesTyping', A_DELETE) )
                    {
                        if ( strlen($justificativa) > 0 )
                        {
                            $descricao = _M('NOTA EXCLUÍDA. MOTIVO: ') . strtoupper($justificativa);
                        }
                        else
                        {
                            $descricao = _M('NOTA EXCLUÍDA');
                        }
                        $disciplinas->salvarNota($enrollId, $degreeId, $nota, $descricao);
                        
                        if ( MUtil::getBooleanValue(SAGU::getParameter('PORTAL', 'HABILITAR_JUSTIFICATIVA_NOTA')) )
                        {
                            $jsCode = "
                                document.getElementById('$id').readOnly = true;
                                document.getElementById('$id').style.color = '#666';
                                document.getElementById('$id').style.opacity = '0.6';
                            ";
                            $this->manager->page->onload($jsCode);

                            $campoJustificativa = $this->obterCampoJustificativa($degreeId, $enrollId);

                            $contId = 'contJustificativa_' . $degreeId . '_' . $enrollId;
                            $this->setResponse($campoJustificativa, $contId);
                        }
                    }
                    else
                    {
                        $busDegreeEnroll = $MIOLO->getBusiness('academic', 'BusDegreeEnroll');
                        $notaAtual = $busDegreeEnroll->getDegreeEnrollCurrentGrade($degreeId, $enrollId);
                        
                        $jsCode = "document.getElementById('$id').value = '$notaAtual'";
                        $this->manager->page->onload($jsCode);
                        
                        new MMessageWarning(_M('Você não tem permissão para excluir esta nota.'));
                    }
                }
                else
                {
                    if ( strlen($justificativa) > 0 )
                    {
                        $descricao = strtoupper($justificativa);
                    }
                    $disciplinas->salvarNota($enrollId, $degreeId, $nota, $descricao);
                    
                    if ( MUtil::getBooleanValue(SAGU::getParameter('PORTAL', 'HABILITAR_JUSTIFICATIVA_NOTA')) )
                    {
                        $jsCode = "
                            document.getElementById('$id').readOnly = true;
                            document.getElementById('$id').style.color = '#666';
                            document.getElementById('$id').style.opacity = '0.6';
                        ";
                        $this->manager->page->onload($jsCode);
                        
                        $campoJustificativa = $this->obterCampoJustificativa($degreeId, $enrollId);
                        
                        $contId = 'contJustificativa_' . $degreeId . '_' . $enrollId;
                        $this->setResponse($campoJustificativa, $contId);
                    }
                }
                
            }
            catch(Exception $e)
            {
                new MMessageWarning(_M('Não foi possível salvar esta nota.'));
            }
        }
        
        $this->setNullResponseDiv();
    }
    
    public function processaAvaliacao(PrtDisciplinas $disciplinas, $avaliacao, $args, $ultima, $enrollId, $degreeId)
    {
        $evaluationId = $avaliacao['evaluationId'];
        
        $id = 'avaliacao_' . $evaluationId . '_' . $enrollId;
        $nota = $args->$id;
        
        $prtDisciplinas = new PrtDisciplinas();
        $groupId = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');
        $usaConceito = MUtil::getBooleanValue($prtDisciplinas->usaConceito($groupId));
        
        if ( $usaConceito )
        {
            $disciplinas->salvarNotaAvaliacao($evaluationId, $enrollId, $nota, $usaConceito);
        }
        else
        {
            $notaMaxima = $prtDisciplinas->obterNotaMaximaDaDisciplina($groupId);

            if ( $notaMaxima )
            {
                if ( $nota > $notaMaxima )
                {
                    $jsCode = "document.getElementById('$id').value = '0.00'";
                    $this->manager->page->onload($jsCode);
                    new MMessageWarning(_M("A nota digitada é maior do que a nota máxima permitida ($notaMaxima)."));
                    return false;
                }
            }

            if ( strlen($nota) > 0 )
            {
                // Salva avaliacao
                $disciplinas->salvarNotaAvaliacao($evaluationId, $enrollId, $nota);

                // Quando for o ultimo item, salva a nota baseando-se na media gerada pelas avaliacoes
                if ( $ultima )
                {
                    $this->salvaNotaPorAvaliacao($disciplinas, $enrollId, $degreeId);
                }
            }
        }
    }

    public function salvaNotaPorAvaliacao(PrtDisciplinas $disciplinas, $enrollId, $degreeId)
    {
        $nota = $disciplinas->calcularMediaAvaliacoes($enrollId, $degreeId);

        if ( strlen($nota) > 0 )
        {
            $disciplinas->salvarNota($enrollId, $degreeId, $nota);

            $this->addJsCode("$('#nota_{$degreeId}_{$enrollId}').val('{$nota}');");
        }
    }
    
    private function obterCampoJustificativa($degreeId, $enrollId, $hidden = FALSE, $ajax = TRUE)
    {
        $notaId = 'nota_' . $degreeId . '_' . $enrollId;
        
        $justificativaId = 'just_' . $degreeId . '_' . $enrollId;
        $justificativa = new MMultiLineField($justificativaId, _M('Informe a justificativa da alteração antes de digitar a nota.'), NULL, 30, 2, 30);
        $justificativa->setClass('mReadOnly');
        $justificativa->addAttribute('onclick', "
            if ( document.getElementById('$notaId').readOnly == true )
            {
                this.value = '';
                this.style.color = '#000000';
                this.style.opacity = 'inherit';
            }
        ");
        $justificativa->addAttribute('onchange', "
            if ( this.value.trim() != '' )
            {
                document.getElementById('$notaId').readOnly = false;
                document.getElementById('$notaId').style.color = '#000000';
                document.getElementById('$notaId').style.opacity = 'inherit';
                document.getElementById('$notaId').focus();
            }
            else
            {
                document.getElementById('$notaId').readOnly = true;
                document.getElementById('$notaId').style.color = '#666';
                document.getElementById('$notaId').style.opacity = '0.6';
            }
        ");
        
        $justificativaLabel = new MLabel(_M('Justificativa:'));
        
        $cont = new MHContainer('contJustificativa_' . $degreeId . '_' . $enrollId, array($justificativaLabel, $justificativa));
        if ( $ajax )
        {
            $cont->addStyle('margin-left', '0px');
        }
        else
        {
            $cont->addStyle('padding-left', '8px');
        }        
        
        $cont->setVisibility(!$hidden);
        
        return $cont;
    }
}

?>
