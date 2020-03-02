<style>
    
    .ui-btn-corner-all {
        border-bottom-left-radius: 0em;
        border-bottom-right-radius: 0em;
        border-top-left-radius: 0em;
        border-top-right-radius: 0em;
    }
    
</style>

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
$MIOLO->uses('classes/prtUsuario.class.php', $module);

class frmFrequenciasProfessor extends frmMobile
{
    public $autoSave = false;
    
    public function __construct($titulo = null)
    {
        
        self::$fazerEventHandler = FALSE;
        $titulo = strlen($titulo) > 0 ? 'FREQUÊNCIAS ['.$titulo.']' : 'Frequências '; 
        parent::__construct(_M($titulo, MIOLO::getCurrentModule()));
        $this->setShowPostButton(FALSE);
    }

    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $busLearningPeriod = $MIOLO->getBusiness('academic', 'BusLearningPeriod');
        $busSchedule = $MIOLO->getBusiness('academic', 'BusSchedule');
        $busPhysicalPerson = $MIOLO->getBusiness('basic', 'BusPhysicalPerson');
        
        $groupId = MIOLO::_REQUEST('groupid');
        $groupData = $busGroup->getGroup($groupId);
        
        //Obtém todos os professores da oferecida
        $professores = $busSchedule->getGroupProfessors($groupId);
        foreach( $professores as $personId => $prof )
        {
            $professoresDaOferecida[] = $personId;
        }

        //Verifica se o professor logado é professor na disciplina oferecida
        if( !in_array($this->personid, $professoresDaOferecida) && !(prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR) )
        {
            //Bloqueia o acesso, pois o professor não é professor da disciplina oferecida
            $MIOLO->error(_M('Apenas professores da disciplina podem ter acesso a esta tela.'));
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
            $dias = $disciplinas->obterDiasDeAula($groupId, $this->personid);
            
            foreach($dias as $dia)
            {
                $options[] = array($dia[0] . '_' . $dia[1], $dia[0]);
            }
            
            $fields[] = $divInfo = new MDiv('divInfo');
            
            $dia = $disciplinas->obterUltimoDiaCronograma(MIOLO::_REQUEST('groupid'), $this->personid, true);
            
            $diaSchedule = split('_', $dia); 
            
            //$disciplinas->obterUltimoDiaCronograma(MIOLO::_REQUEST('groupid'))
            $selection = new MSelection('dia', $dia, '', $options);
            $selection->setAttribute('onchange', MUtil::getAjaxAction('trocarDia'));

            $bgFields[] = $selection;
            $bgFields[] = new MSpacer();
            $bgFields[] = new MDiv('divCronograma', prtCommonForm::cronograma($diaSchedule[0], $diaSchedule[1], $this->personid));

            $bgNotas = new MDiv('', new MBaseGroup('', _M('Data'), $bgFields));
            $bgNotas->addStyle('margin', '0 0 0 0');
            $bgNotas->addStyle('width', '100%');
            $fields[] = $bgNotas;

            $fields[] = new MSpacer();
            $fields[] = new MDiv('divPresencas', $this->presencas($diaSchedule[0], $horarios, $this->personid, $diaSchedule[1]));
            $fields[] = new SHiddenField('professor', $this->personid);
        }

	parent::addFields($fields);
    }
    
    /**
     * @return array
     */
    public function marcDesmarcTodos($horarios = array())
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        foreach ( $horarios as $horario )
        {
            $label = $horario['beginhour'] . ' - ' . $horario['endhour'];
            $horarioId = $horario['horarioidunique'];
            $id = 'marcarOuDesmarcarTudo_' . $horarioId;
            $stateId = $id . '_state';

            $fields[] = $button = new btnFrequencia($id, $label);
            $fields[] = new MHiddenField($stateId, 1);
            
            $button->addStyle('background-color', 'green');
            
            if ( isset($_SESSION['presenca'][$horario['horarioidunique']]) )
            {
                if ( $_SESSION['presenca'][$horario['horarioidunique']] == 1 )
                {
                    $button->addStyle('background-color', 'red');
                }
            }
            
            $button->addAttribute('onclick', MUtil::getAjaxAction('salvar', "{$horario['timeid']}||{$horario['horarioidunique']}"));
        }
        
        return $fields;
    }
    
    public function trocarDia($args)
    {
        $MIOLO = MIOLO::getInstance();
                
        $diaSchedule = split('_', $args->dia);
        
        $args = $this->getAjaxData();
        $this->setResponse($this->presencas($diaSchedule[0], null, $this->personid, $diaSchedule[1]), 'divPresencas');
        $this->setResponse(prtCommonForm::cronograma($diaSchedule[0], $diaSchedule[1], $this->personid), 'divCronograma');

        //Esconder mensagem de informacao dos alunos;
        $jsCode = "try{document.getElementById('msgInfo').style.display = 'none';}catch(err){}";
        $MIOLO->page->addJsCode($jsCode);
    }
    
    public function presencas($dia, $horarios, $professor, $scheduleId)
    {
        if(!$dia)
	{
            return null;
	}
        
        if(SAGU::getParameter('SERVICES', 'LOCK_FUTURE_FREQUENCY') == DB_TRUE)
        {
            $now = SAGU::getDateNow();

            if ( SAGU::compareTimestamp($now, '<', $dia) )
            {
                $fields[] = MPrompt::information(_M('O sistema está configurado para não lançar presenças futuras.'),'NONE',_M('Information'));
                return $fields;
            }
        }
                
        $professor = strlen($professor) > 0 ? $professor : MIOLO::_REQUEST('professor');
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();

        $descricao = $disciplinas->obterCronogramaDescricao(MIOLO::_REQUEST('groupid'), $dia, $scheduleId);

        $horarios2 = $disciplinas->obterHorarioDeAula2(MIOLO::_REQUEST('groupid'), $dia, $professor, $scheduleId);
        
        $habilitarMeiaPresenca = MUtil::getBooleanValue(SAGU::getParameter('BASIC', 'HALF_PRESENCE'));
        
        if ( !$habilitarMeiaPresenca )
        {
            // marcar/desmarcar todos
            $bgMarcTud = new MDiv('', new MBaseGroup('', _M('Marcar/desmarcar todos'), $this->marcDesmarcTodos( $horarios2 )));
            $bgMarcTud->addStyle('margin', '0 0 0 0');
            $bgMarcTud->addStyle('width', '100%');
            $fields[] = $bgMarcTud;

            $legendaVerde = new MLabel('<b><li type=square>Presença</li></b>');
            $legendaVerde->addStyle('color', 'green');
            $legendaVerde->addStyle('padding-left', '20px');
            $legendaVerde->addStyle('width', '100px');
            $legendaVermelho = new MLabel('<b><li type=square>Falta</li></b>');
            $legendaVermelho->addStyle('color', 'red');
            $legendaVermelho->addStyle('padding-left', '20px');
            $legendaVermelho->addStyle('width', '100px');
            $legendaCinza = new MLabel('<b><li type=square>Não marcada</li></b>');
            $legendaCinza->addStyle('color', 'gray');
            $legendaCinza->addStyle('padding-left', '20px');
            $legendaCinza->addStyle('width', '100px');
            

            $fields['divLegenda'] = new MHContainer('divLegenda', array(new MLabel('Legenda: '), $legendaVerde, $legendaVermelho, $legendaCinza));
            $fields['divLegenda']->addStyle('padding-left', '10px');
        }
        
        $horarios = $disciplinas->obterHorarioDeAula(MIOLO::_REQUEST('groupid'), $dia, $professor, $scheduleId);
        $busProfessorFrequency = $MIOLO->getBusiness('services', 'BusProfessorFrequency');
        
        //foreach por alunos
        $alunos = $busProfessorFrequency->listGroupPupilsEnrolled(MIOLO::_REQUEST('groupid'));

	$frequencia = array();       
 
        foreach($alunos as $aluno)
        {   
            $div = null;
            $photoFileId = $aluno[3];
            
            $nomeAluno = new MLabel($aluno[0]);
            $nomeAluno->setClass('label-nome-aluno');
            $divAluno = new MDiv('', $nomeAluno);
            $divAluno->addStyle('width', '20%');
            
            foreach($horarios as $k=>$horario)
            {
                $frequencia = $disciplinas->obterFrequencia($aluno[2], $dia, $horario[4]);
                
                // Verifica se aluno está fazendo disciplina em regime dimiciliar.
                $prtDisciplinas = new PrtDisciplinas();
                $filtros = new stdClass();
                $filtros->enrollId = $aluno[2];
                $filtros->frequencyDate = $horario[0];
                $regimeDomiciliar = $prtDisciplinas->obterRegimeDomiciliar($filtros);
                
                if ( $regimeDomiciliar[0][0] )
                {
                    // Insere ou atualiza a frequência justificada.
                    $filtros->frequency = 1;
                    $filtros->justification = $regimeDomiciliar[0][3];
                    $filtros->justifiedAbsense = DB_TRUE;
                    $filtros->scheduleId = $horario[3];
                    $filtros->timeId = $horario[4];
                    $prtDisciplinas->updateOrInsertFrequenceEnroll($filtros);
                    
                    $txt = $horario[1].' - '.$horario[2];
                    $justification = _M('Esta frequência não pode ser alterada pois o aluno está cursando esta disciplina em regime domiciliar. ') . '(' . $regimeDomiciliar[0][3] . ')';
                    $justification = str_replace("\r\n", ' ', $justification);
                    
                    $label = new MLabel('<b><li type=square title=\'' . $justification . '\'>' . $txt . '</li></b>');
                    $label->addStyle('color', 'gray');
                    $label->addStyle('padding-left', '20px');
                    $label->addStyle('width', '200px');
                    $label->addStyle('font-size', '20px');
                    
                    $div[] = $label;
                }
                else
                {
                    if ( $habilitarMeiaPresenca )
                    {
                        $options = array(
                            '1' => 'PRESEN&Ccedil;A',
                            '0.5' => 'MEIA PRESEN&Ccedil;A',
                            '0' => 'FALTA'
                        );
                        
                        $id = 'presenca_'.base64_encode($aluno[2].'_'.$horario[4].'_'.$dia);
                        $txt1 = new MLabel($horario[1].' - '.$horario[2]);
                        
                        $selection = new MSelection($id, $frequencia[0][3], NULL, $options);
                        $ajax = MUtil::getAjaxAction('registraFrequencia', "{$aluno[2]}_{$horario[4]}_{$dia};");
                        $selection->addAttribute('onchange', $ajax);
                        $div[] = new MVContainer(rand(), array($txt1, $selection));
                    }
                    else
                    {
                        if ( !(strlen($frequencia[0][3]) > 0) )
                        {
                            $presenca = NULL;
                        }
                        elseif($frequencia[0][3] == 1)
                        {
                            $presenca = DB_TRUE;
                        }
                        elseif ($frequencia[0][3] == 0)
                        {
                            $presenca = DB_FALSE;                            
                        }

                        $id = 'presenca_'.base64_encode($aluno[2].'_'.$horario[4].'_'.$dia);
                        $txt1 = $horario[1].' - '.$horario[2];
                        $class = 'horariopres_' . $horarios2[$k]['horarioidunique'];
                        
                        $div[] = new MHiddenField($id, $presenca);
                        
                        $btnFrequency = new btnFrequencia('btn_' . $id, $txt1, MUtil::getAjaxAction('registraFrequencia', "{$aluno[2]}_{$horario[4]}_{$dia};{$txt1}"), $img, FALSE, $this->obterJustificativa($id));
                        
                        if ( !(strlen($frequencia[0][3]) > 0) )
                        {
                            $btnFrequency->addStyle('background-color', 'gray');
                        }
                        elseif ( $presenca == DB_TRUE )
                        {
                            $btnFrequency->addStyle('background-color', 'green');
                        }
                        elseif ( $presenca == DB_FALSE )
                        {
                            $btnFrequency->addStyle('background-color', 'red');
                        }
                        
                        $div[] = $divFrequency = new MDiv('div_' . $id, array($btnFrequency));                        
                    }
                }
            }
            
            $divInfo = new MHContainer('',$div);
            $divInfo->addStyle('width', '75%');
            $divInfo->addStyle('float', 'left');

            if ( !MUtil::getBooleanValue(SAGU::getParameter('PORTAL', 'DESABILITA_EXIBICAO_FOTO_ALUNO')) )
            {
                $divFoto = prtCommonForm::obterFoto($photoFileId, '64', '64');
                
                $divInfo->addStyle('width', '70%');
            }
             
            
            $fieldsAluno = new MBaseGroup('', '', array($divFoto, $divAluno, $divInfo));
                        
            $fieldsAluno->addStyle('height', 'auto');
            
            $fields[] = $fieldsAluno;
        }
        
        if ( strlen($descricao) == 0 && count($frequencia)==0 )
        {
            return null;
        }
        
        return $fields;
    }
    
    public function registraFrequencia($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $groupId = MIOLO::_REQUEST('groupid');
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        $habilitarMeiaPresenca = MUtil::getBooleanValue(SAGU::getParameter('BASIC', 'HALF_PRESENCE'));
        
        //Clicou para regisrar frequencia também vamos esconder os que foram salvos automaticametne
        $jsCode = "try{document.getElementById('msgInfo').style.display = 'none';}catch(err){}";
        $MIOLO->page->addJsCode($jsCode);
        
        $_args = explode(';', $args);        
        $data = explode('_', $_args[0]);
        $txt1 = $_args[1];
                
        // Controle pela sessão porque o MIOLO passa 2 vezes pelas requisições Ajax.
        if ( !$_SESSION[$groupId][$data[0]][$data[1]][$data[2]] )
        {
            $scheduleid = $disciplinas->obterHorarioPelasChaves($groupId, $data[2], $data[1]);
            $frequencia = $disciplinas->obterFrequencia($data[0], $data[2], $data[1], $scheduleid);

            $id = 'presenca_'.base64_encode($data[0].'_'.$data[1].'_'.$data[2]);
            
            if ( $habilitarMeiaPresenca )
            {
                $presenca = $_REQUEST[$id];
            }
            else
            {
                // Se tiver presença, marca falta, e vice-versa.
                $presenca = $frequencia[0][3] == 1 ? 0 : 1;    
            }
            
            $ok = $disciplinas->salvarFrequencia($data[0], $data[2], $data[1], $scheduleid, $presenca);
            
            if ( $ok && !$habilitarMeiaPresenca )
            {
                $btnFrequency = new btnFrequencia('btn_' . $id, $txt1, MUtil::getAjaxAction('registraFrequencia', "{$data[0]}_{$data[1]}_{$data[2]};{$txt1}"), NULL, FALSE, $this->obterJustificativa($id));
                
                if ( $presenca )
                {
                    $btnFrequency->addStyle('background-color', 'green');
                }
                else
                {
                    $btnFrequency->addStyle('background-color', 'red');
                }
                
                $div[] = $btnFrequency;
                
                $divInfo = new MDiv('div_' . $id, $div);
                $divInfo->addStyle('width', '75%');
                $divInfo->addStyle('float', 'left');
                
                $this->setResponse($divInfo, 'div_' . $id);
            }
            
            $_SESSION[$groupId][$data[0]][$data[1]][$data[2]] = true;
        }
        else
        {
            $_SESSION[$groupId][$data[0]][$data[1]][$data[2]] = NULL;
        }
        
        $this->setNullResponseDiv();
    }
    
    public function salvarPeloBotao($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $groupId = MIOLO::_REQUEST('groupid');
        
        $diaSchedule = split('_', $args->dia);
        $args->dia = $diaSchedule[0];
        $args->scheduleId = $diaSchedule[1];
        
        try
        {
            if ( strlen($args->cronograma) > 0 )
            {
                $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
                $disciplinas = new PrtDisciplinas();

                bBaseDeDados::iniciarTransacao();

                //salvar cronograma
                if ( strlen($args->cronograma) > 0 && strlen($args->dia) > 0 )
                {
                    $disciplinas->salvarCronograma2($groupId, $args->cronograma, $args->dia, $args->scheduleId);
                }

                $args->salvarPeloBotao = true;
                $this->salvar($args);
            
                bBaseDeDados::finalizarTransacao();
            
                $this->setResponse($this->presencas($args->dia, null, $this->personid, $args->scheduleId), 'divPresencas');
            }
            else
            {
                $this->setNullResponseDiv();
            }
        }
        catch ( Exception $e )
        {
            new MMessage($e->getMessage(), MMessage::TYPE_ERROR, TRUE, MMessage::MSG_CONTAINER_ID, FALSE, 10000);
        
            bBaseDeDados::reverterTransacao();
        }
    }
    
    public function salvar($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $timeIdButton = NULL;
        if ( !is_object($args) )
        {
            $horarioArgs = explode('||', $args);
            $timeIdButton = $horarioArgs[0];
            $timeUnique = $horarioArgs[1];
        }
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $groupId = MIOLO::_REQUEST('groupid');
        $salvarPeloBotao = $args->salvarPeloBotao;
        
        $args = $this->getAjaxData();
        
        $scheduleId = $args->scheduleId;
        
        if ( strstr($args->dia, '_') )
        {
            $diaSchedule = split('_', $args->dia);
            $args->dia = $diaSchedule[0];
            $scheduleId = $diaSchedule[1];
        }
                
        $horarios = $disciplinas->obterHorarioDeAula(MIOLO::_REQUEST('groupid'), $args->dia, null, $scheduleId);
        $busProfessorFrequency = $MIOLO->getBusiness('services', 'BusProfessorFrequency');
        $alunos = $busProfessorFrequency->listGroupPupilsEnrolled($groupId);
        $totalHorarios = count($horarios) * count($alunos);

        $busSchedule = $MIOLO->getBusiness('academic', 'BusSchedule');

        // Verifica as frequências. Se não tiver, adiciona 'presente' por padrão. Se já existe a frequência, não mexe.
        $textoAlunos = '';
        foreach($alunos as $aluno)
        {   
            foreach($horarios as $k=>$horario)
            {
                if ( $salvarPeloBotao )
                {                    
                    /**
                     * Quando o usuário clica pelo botão Salvar, o sistema salva todas as frequências ainda não
                     * registradas na base como Frequência válida, vamos criar uma msg que pegue os alunos e mostre
                     * ao usuário quais que tiveram esse comportamento processado.
                     */
                    
                    $textoAlunos .= $disciplinas->verificaFrequencia($aluno[2], $args->dia, $horario[4], $groupId, false, $aluno, $horario);
                }
//                else
//                {
//                    $disciplinas->verificaFrequencia($aluno[2], $args->dia, $horario[4], $groupId, true);
//                }
            }
        }

        if ( !$salvarPeloBotao )
        {
            // Marcar ou desmarcar
            if ( isset($_SESSION['presenca'][$timeUnique]) )
            {
               $presenca = $_SESSION['presenca'][$timeUnique] == 1 ? 0 : 1;
               $_SESSION['presenca'][$timeUnique] = $presenca;
            }
            else
            {
                $_SESSION['presenca'][$timeUnique] = 1;
                $presenca = 1;
            }

            //salvar presencas       
            foreach((array)$args as $k=>$v)
            {
                if(substr($k,0,  strlen('presenca_'))=='presenca_')
                {
                    $data = explode('_', $k);
                    $data = base64_decode($data[1]);
                    $data = explode('_', $data);

                    list($enrollId, $timeId, $date) = $data;

                    if ( $timeId == $timeIdButton )
                    {
                        $scheduleid = $disciplinas->obterHorarioPelasChaves($groupId, $date, $timeId);
                        $disciplinas->salvarFrequencia($enrollId, $args->dia, $timeId, $scheduleid, $presenca);
                    }
                }
            }
        }

        if ( !($disciplinas->obterTotalDeFrequenciasRegistradas($groupId, $args->dia) >= $totalHorarios) )
        {
            $msg = _M('O número de frequências registradas não corresponde ao total de registros que deveriam ter sido salvos.<br>
            Por favor tente salvar novamente.');
            
            // Demanda solicitada pelo Brandão
            $recipient[] = 'luciano_brandao@solis.com.br';
            $subject = _M('Pendências no registro de frequência.');
            $body = $msg;
            $mail = new sendEmail($from, $fromName, $recipient, $subject, $body, array());

            $mail->sendEmail();
            
            throw new Exception($msg);
        }

        $this->setResponse($this->presencas($args->dia, null, $this->personid, $scheduleId), 'divPresencas');
  
        if ($salvarPeloBotao)
        {
            if ( strlen($textoAlunos) > 0 )
            {
                $texto = _M("As frequências para os seguintes alunos foram salvas automaticamente como <strong>PRESENÇA</strong>:</br>");
                $texto .= $textoAlunos;
                
                $this->setResponse(array(MMessage::getStaticMessage('msgInfo', $texto)), 'divInfo');
            }
        }
        
        $this->setResponse(NULL, 'responseDiv');
        
    }
    
    /**
     * Obtém justificativa do aluno.
     * Utilizada para obter o hint do botão de frequência.
     * 
     * @param string $idBotao
     * @return string
     */
    private function obterJustificativa($idBotao)
    {
        $dados = explode('_', $idBotao);
        $valores = explode('_', base64_decode($dados[1]));
        $dadosFreq = PrtDisciplinas::obterFrequencia($valores[0], $valores[2], $valores[1]);
        
        return $dadosFreq[0][4];
    }

}

class btnFrequencia extends MButton
{
    
    public function __construct($name = '', $label = '', $action = NULL, $image = NULL, $onclickdisable = FALSE, $hint = NULL)
    {
        parent::__construct($name, $label, $action, $image, $onclickdisable);
        
        // Ajustando hint dos botões - ticket #38699
        (strlen($hint) > 0) ? $this->addAttribute('title', _M($hint)) : null;
        
        $this->setClass('btnFrequencia');
    }
    
}

?>
