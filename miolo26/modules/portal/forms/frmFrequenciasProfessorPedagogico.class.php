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
$MIOLO->uses('forms/frmFrequenciasProfessor.class.php', $module);
$MIOLO->uses('classes/prtCommonFormPedagogico.class.php', $module);
$MIOLO->uses('types/AcpOcorrenciaHorarioOferta.class', 'pedagogico');
$MIOLO->uses('types/AcpOfertaComponenteCurricular.class.php', 'pedagogico');
$MIOLO->uses('types/AcpComponenteCurricularMatriz.class.php', 'pedagogico');

class frmFrequenciasProfessorPedagogico extends  frmFrequenciasProfessor
{
    public $permiteMeiaPresenca = false;
    
    public function __construct()
    {
        //Trocar nome dinamicamente, se vier do portal deixa padrão, senão muda
        if ( MIOLO::_REQUEST('modulo') == 'academic' )
        {
            $MIOLO = MIOLO::getInstance();
            $JS = 'document.title = "Sagu";';
            $MIOLO->page->addJsCode($JS);
        }
        
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid');
        $matrizid = AcpOfertaComponenteCurricular::obterComponenteCurricularMatrizId($ofertacomponentecurricularid);
        $curricularid = AcpOfertaComponenteCurricular::obterComponenteCurricularId($matrizid[0]);
        $componentecurricularnome = AcpOfertaComponenteCurricular::obterComponenteCurricularNome($curricularid[0]);
        
        $this->permiteMeiaPresenca = $_SESSION['permiteMeiaPresenca'];

        parent::__construct($componentecurricularnome[0][0]);
    }

    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        
        $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
        $disciplinas = new PrtDisciplinasPedagogico();
        
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid');
        $ofertacomponentecurricular = new AcpOfertaComponenteCurricular($ofertacomponentecurricularid);

        $ocorrenciacursoid = $ofertacomponentecurricular->ofertaturma->ofertacurso->ocorrenciacursoid;
        $ocorrenciacurso = new AcpOcorrenciaCurso($ocorrenciacursoid);
        
        $isClosed = ( strlen($ofertacomponentecurricular->datafechamento) > 0 );
        
        // obtem informacoes do controle de frequencia
        $modeloDeAvaliacaoId = $ocorrenciacurso->curso->perfilcurso->modelodeavaliacaogeral;

        if ( strlen($modeloDeAvaliacaoId) > 0 )
        {
            $controleFrequencia = AcpControleDeFrequencia::obterPeloModelo($modeloDeAvaliacaoId);
            $this->permiteMeiaPresenca = $controleFrequencia->permiteMeiaPresenca == DB_TRUE;

            $_SESSION['permiteMeiaPresenca'] = $this->permiteMeiaPresenca;
        }
        
        $habilitarBiometria = ($ofertacomponentecurricular->ofertaturma->ofertacurso->ocorrenciacurso->curso->perfilcurso->permiteregistrarfrequenciabiometria == DB_TRUE);
        
        if ( $isClosed == DB_TRUE )
        {
            $fields[] = MMessageInformation::getStaticMessage('msgInfo', _M('Esta disciplina está encerrada.'), MMessage::TYPE_INFORMATION);
        }
        elseif( $ocorrenciacurso->curso->perfilcurso->permiteregistrarfrequenciaportal == DB_FALSE && MIOLO::_REQUEST('isAdmin') != DB_TRUE )
        {
            $fields[] = MMessageInformation::getStaticMessage('msgInfo', _M('Não é permitido o registro de frequências para o perfil de curso da disciplina selecionada.'), MMessage::TYPE_INFORMATION);
        }
        else
        {
            $personId = MIOLO::_REQUEST('isAdmin') == DB_TRUE ? null : $this->personid;
            $ocorrenciashorariooferta = $disciplinas->obterDiasDeAula($ofertacomponentecurricularid, $personId);
            
            foreach($ocorrenciashorariooferta as $ocorrenciahorariooferta)
            {
                $options[$ocorrenciahorariooferta[1]] = array($ocorrenciahorariooferta[1], $ocorrenciahorariooferta[1]);
            }
            $componenteCurricular = new AcpComponenteCurricular();
            $tipoEad = $componenteCurricular->obterTipoComponenteCurricular($ofertacomponentecurricularid); 
        
            if ( count($ocorrenciashorariooferta) == 0 && $tipoEad == 'E')
            {
                $fields[] = new MDiv('divSemEncontro',$this->frequenciasSemEncontro());
            }
            else
            {
                $ocorrenciahorariooferta = $disciplinas->obterUltimoDiaCronograma($ofertacomponentecurricularid);
                $dataInicial = $ocorrenciashorariooferta[0][1];

                $selection = new MSelection('dataaula', $dataInicial, '', $options);
                $selection->setAttribute('onchange', MUtil::getAjaxAction('trocarDia'));

                $bgFields[] = $selection;
                $bgFields[] = new MSpacer();
                $bgFields[] = new MDiv('divCronograma', prtCommonFormPedagogico::cronograma($dataInicial, $habilitarBiometria));

                $bgNotas = new MDiv('', new MBaseGroup('', _M('Data'), $bgFields));
                $bgNotas->addStyle('margin', '0 0 0 0');
                $bgNotas->addStyle('width', '100%');
                $fields[] = $bgNotas;

                $fields[] = new MSpacer();
                $fields[] = new MDiv('divPresencas', $this->presencas($dataInicial));
            }
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
            $label = $horario[2] . ' - ' . $horario[3];
            $horarioId = $horario[0];
            $id = 'marcarOuDesmarcarTudo_' . $horarioId;
            $stateId = $id . '_state';

            $fields[] = $button = new btnFrequencia($id, $label);
            $fields[] = new MHiddenField($stateId, 1);
            
            $button->addStyle('background-color', 'green');
            
            if ( isset($_SESSION['presenca'][$horario[0]]) )
            {
                if ( $_SESSION['presenca'][$horario[0]] == AcpFrequencia::FREQUENCIA_PRESENTE )
                {
                    $button->addStyle('background-color', 'red');
                }
                else if ( $this->permiteMeiaPresenca && ( $_SESSION['presenca'][$horario[0]] == AcpFrequencia::FREQUENCIA_AUSENTE ) )
                {
                    $button->addStyle('background-color', 'orange');
                }
            }
                
            $button->addAttribute('onclick', MUtil::getAjaxAction('salvar', "{$horario[0]}||{$horario[0]}"));
        }
        
        return $fields;
    }
    
    public function trocarDia($args)
    {
        $args = $this->getAjaxData();
        
        $this->setResponse($this->presencas($args->dataaula), 'divPresencas');
        
        $ofertacomponentecurricular = new AcpOfertaComponenteCurricular(MIOLO::_REQUEST('ofertacomponentecurricularid'));
        $habilitarBiometria = ($ofertacomponentecurricular->ofertaturma->ofertacurso->ocorrenciacurso->curso->perfilcurso->permiteregistrarfrequenciabiometria == DB_TRUE);
        
        $this->setResponse(prtCommonFormPedagogico::cronograma($args->dataaula, $habilitarBiometria), 'divCronograma');
    }
    
    public function frequenciasSemEncontro()
    {
        $disciplinas = new PrtDisciplinasPedagogico();
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid');

        $matriculas = $disciplinas->obterMatriculasOtimizado($ofertacomponentecurricularid, false);

        foreach ( $matriculas as $matricula )
        {   
            $div = null;
            $photoFileId = null;
            if ( strlen($matricula['personid']) > 0 )
            {
                $busPhysicalPerson = new BusinessBasicBusPhysicalPerson();
                $dataPerson = $busPhysicalPerson->getPhysicalPerson($matricula['personid']);
                $photoFileId = $dataPerson->photoId;
            }

            $freq = new MTextField('freqField_'.$matricula['matriculaid'], $matricula['frequencia'],'',5);
            $percent = new MLabel('&nbsp;&nbsp;%');
            $percent->addStyle('height', '54px');
            $freq->addAttribute('onBlur',MUtil::getAjaxAction('saveFreqEAD', 'divResposta_'.$matricula['matriculaid'], false));
            $fields[] = new MDiv('divResposta');
            
            $mhCont = new MHContainer('mhcn',array($freq,$percent));

            $nomeAluno = new MLabel($matricula['_pessoa']);
            $nomeAluno->setClass('label-nome-aluno');
            $divAluno = new MDiv('', $nomeAluno);
            $divAluno->addStyle('width', '20%');

            $fieldsAluno = new MBaseGroup('', '', array($divAluno, $mhCont));
            $fieldsAluno->addStyle('height', '54px');
            $fields[] = $fieldsAluno;
        }

        
        return $fields;
        
    }
    // Salvar frequencia dos aluno da disciplina tipo EAD = 'E'
    public function saveFreqEAD($args)
    {
        $MIOLO = MIOLO::getInstance();
        $args = explode('_', $args);
        
        $matricula = new AcpMatricula($args[1]);
        $matricula->frequencia = str_replace(',','.',MIOLO::_REQUEST('freqField_'.$args[1]));
        $freq = str_replace(',','.',MIOLO::_REQUEST('freqField_'.$args[1]));
        
        if($freq >= 0 && $freq < 101 && is_numeric($freq) 
        && MIOLO::_REQUEST('freqField_'.$args[1]) != null )
        {
            if($matricula->save())
            {
                return new MMessageSuccess('Dados salvos com sucesso.');
            }
        }
        else if($freq != null)
        {
            $MIOLO->page->addJsCode("document.getElementById('freqField_{$args[1]}').value = '';");
            return new MMessageWarning('Frequência precisa ser um número de 0-100.');
        }
    }
    
    public function presencas($dataaula)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
//        $ui = $MIOLO->getUI();
        
        $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
        
	if(!$dataaula)
	{
            return null;
	}
        
        //FIXME verifica este trcho com parametro habilitado        
        if(SAGU::getParameter('SERVICES', 'LOCK_FUTURE_FREQUENCY') == DB_TRUE)
        {
            if ( SAGU::compareTimestamp(SAGU::getDateNow(), '<', $dataaula) )
            {
                $fields[] = MPrompt::information(_M('O sistema esta configurado para não lançar presenças futuras.'),'NONE',_M('Information'));
                return $fields;
            }
        }

        $disciplinas = new PrtDisciplinasPedagogico();
        
        $personId = MIOLO::_REQUEST('isAdmin') == DB_TRUE ? null : $this->personid;
        $descricao = $disciplinas->obterCronogramaPelaData(MIOLO::_REQUEST('ofertacomponentecurricularid'), $dataaula, $personId);
        $horarios = $disciplinas->obterHorariosPelaData(MIOLO::_REQUEST('ofertacomponentecurricularid'), $dataaula, $personId);
        
        // marcar/desmarcar todos
        $bgMarcTud = new MDiv('', new MBaseGroup('', _M('Marcar/desmarcar todos'), $this->marcDesmarcTodos($horarios)));
        $bgMarcTud->addStyle('margin', '0 0 0 0');
        $bgMarcTud->addStyle('width', '100%');
        $fields[] = $bgMarcTud;
        
        $legendas = array(new MLabel('Legenda: '));
        
        $legenda = $legendas[] = new MLabel('<b><li type=square>Presença</li></b>');
        $legenda->addStyle('color', 'green');
        $legenda->addStyle('padding-left', '20px');
        $legenda->addStyle('width', '100px');
        
        $legenda = $legendas[] = new MLabel('<b><li type=square>Falta</li></b>');
        $legenda->addStyle('color', 'red');
        $legenda->addStyle('padding-left', '20px');
        $legenda->addStyle('width', '100px');

        if ( $this->permiteMeiaPresenca )
        {
            $legenda = $legendas[] = new MLabel('<b><li type=square>Meia</li></b>');
            $legenda->addStyle('color', 'orange');
            $legenda->addStyle('padding-left', '20px');
            $legenda->addStyle('width', '100px');
        }
        
        $legenda = $legendas[] = new MLabel('<b><li type=square>Justificada</li></b>');
        $legenda->addStyle('color', 'royalblue');
        $legenda->addStyle('padding-left', '20px');
        $legenda->addStyle('width', '100px');
        
        $fields['divLegenda'] = new MHContainer('divLegenda', $legendas);
        $fields['divLegenda']->addStyle('padding-left', '10px');
        
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid');
        $matriculas = $disciplinas->obterMatriculasOtimizado($ofertacomponentecurricularid, false);
        
        SDatabase::beginTransaction();
        
	$frequencia = array();
        
        /**
         * Quando não há descrição preenchida, executa rollback e retorna nulo,
         * então não ha necessidade de executar todo este processo, para melhorar o desempenho da interface.
         */
        if ( strlen($descricao) > 0 )
        {
            foreach ( $matriculas as $matricula )
            {   
                $div = null;
                $photoFileId = null;
                if ( strlen($matricula['personid']) > 0 )
                {
                    $busPhysicalPerson = new BusinessBasicBusPhysicalPerson();
                    $dataPerson = $busPhysicalPerson->getPhysicalPerson($matricula['personid']);
                    $photoFileId = $dataPerson->photoId;
                }
                
                $nomeAluno = new MLabel($matricula['_pessoa']);
                $nomeAluno->setClass('label-nome-aluno');
                $divAluno = new MDiv('', $nomeAluno);
                $divAluno->addStyle('width', '20%');

                foreach($horarios as $k=>$horario)
                {
                    $frequencia = AcpFrequencia::obterFrequencia($matricula['matriculaid'], $horario[0]);
                    $semRegistro = ( strlen($frequencia->frequencia) == 0 );

                    if ( ( $frequencia->frequencia == AcpFrequencia::FREQUENCIA_PRESENTE ) || $semRegistro )
                    {
                        if ( $semRegistro )
                        {
                            $valor = AcpFrequencia::FREQUENCIA_PRESENTE;

                            $disciplinas = new PrtDisciplinasPedagogico();
                            $disciplinas->salvarFrequencia($matricula['matriculaid'], $horario[0], $valor);
                        }

                        $presenca = AcpFrequencia::FREQUENCIA_PRESENTE;
                    }
                    else if ( $frequencia->frequencia == AcpFrequencia::FREQUENCIA_MEIA )
                    {
                        $presenca = AcpFrequencia::FREQUENCIA_MEIA;
                    }
                    else if ( $frequencia->frequencia == AcpFrequencia::FREQUENCIA_JUSTIFICADA )
                    {
                        $presenca = AcpFrequencia::FREQUENCIA_JUSTIFICADA;
                    }
                    else
                    {
                        $presenca = AcpFrequencia::FREQUENCIA_AUSENTE;
                    }

                    $id = 'presenca_'.base64_encode($matricula['matriculaid'].'_'.$horario[0]);                
                    $txt1 = $horario[2].' - '.$horario[3];
                    $class = 'horariopres_' . $horarios[$k][0];

                    $matriculaid = $matricula['matriculaid'];
                    $btnFrequency = new btnFrequencia('btn_' . $id, $txt1, MUtil::getAjaxAction('registraFrequencia', "{$matriculaid}_{$horario[0]};{$txt1}"), $img);

                    $divControls = array($btnFrequency);

                    if ( $presenca == AcpFrequencia::FREQUENCIA_PRESENTE )
                    {
                        $btnFrequency->addStyle('background-color', 'green');
                    }
                    else if ( $presenca == AcpFrequencia::FREQUENCIA_MEIA )
                    {
                        $btnFrequency->addStyle('background-color', 'orange');
                    }
                    else if ( $presenca == AcpFrequencia::FREQUENCIA_JUSTIFICADA )
                    {
                        $btnFrequency->addStyle('background-color', 'royalblue');

                        if ( strlen($frequencia->justificativa) > 0 )
                        {
                            $btnFrequency->addAttribute('title', $frequencia->justificativa);
                        }

                        if ( MUtil::getBooleanValue(MIOLO::_REQUEST('isAdmin')) )
                        {
                            $divControls[] = $btnJust = $this->button('trocajust', NULL, NULL, MUtil::getAjaxAction('alterarJustificativa', "{$frequencia->frequenciaid}"), $MIOLO->getUI()->getImageTheme('portal', 'bf-explorar-on.png'));
                            $btnJust->addBoxStyle('margin', '0px');
                        }
                    }
                    else
                    {
                        $btnFrequency->addStyle('background-color', 'red');
                    }

                    $hct = new MHContainer('hct_'.rand(), $divControls);
                    $div[] = $divFrequency = new MyDiv('div_' . $id, $hct);

                    $div[] = new MHiddenField($id, $presenca);
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
                $fieldsAluno->addStyle('height', '54px');
                $fields[] = $fieldsAluno;
            }
        }
        
        if ( strlen($descricao) == 0 )
        {
            SDatabase::rollback();
            
            return null;
        }
        
        SDatabase::commit();
        
        return $fields;
    }
    
    public function registraFrequencia($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid');
        $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
        $disciplinas = new PrtDisciplinasPedagogico();

        $_args = explode(';', $args);        
        $data = explode('_', $_args[0]);
        $txt1 = $_args[1];

        // Controle pela sessão porque o MIOLO passa 2 vezes pelas requisições Ajax.
        if ( !$_SESSION[$ofertacomponentecurricularid][$data[0]][$data[1]] )
        {
            list( $matriculaid, $ocorrenciahorarioofertaid ) = $data;
            $frequencia = AcpFrequencia::obterFrequencia($matriculaid, $ocorrenciahorarioofertaid);
            
            $id = 'presenca_'.base64_encode($data[0].'_'.$data[1]);
            
            if( $frequencia->frequencia == AcpFrequencia::FREQUENCIA_PRESENTE )
            {
                $valor = AcpFrequencia::FREQUENCIA_AUSENTE;
            }
            else
            {
                $valor = AcpFrequencia::FREQUENCIA_PRESENTE;
            }
            
            // meia presenca
            if ( $this->permiteMeiaPresenca && ( $frequencia->frequencia == AcpFrequencia::FREQUENCIA_AUSENTE ) )
            {
                $valor = AcpFrequencia::FREQUENCIA_MEIA;
            }
            else if ( MUtil::getBooleanValue(MIOLO::_REQUEST('isAdmin')) && in_array($frequencia->frequencia, array(AcpFrequencia::FREQUENCIA_AUSENTE, AcpFrequencia::FREQUENCIA_MEIA)) )
            {
                // falta justificada
                $valor = AcpFrequencia::FREQUENCIA_JUSTIFICADA;
            }

            $ok = $disciplinas->salvarFrequencia($matriculaid, $ocorrenciahorarioofertaid, $valor);
            
            if ( $ok )
            {
                $btnFrequency = new btnFrequencia('btn_' . $id, $txt1, MUtil::getAjaxAction('registraFrequencia', "{$data[0]}_{$data[1]};{$txt1}"));
                $divControls[] = $btnFrequency;
                
                if ( $valor == AcpFrequencia::FREQUENCIA_PRESENTE )
                {
                    $btnFrequency->addStyle('background-color', 'green');
                }
                else if ( $valor == AcpFrequencia::FREQUENCIA_MEIA )
                {
                    $btnFrequency->addStyle('background-color', 'orange');
                }
                else if ( $valor == AcpFrequencia::FREQUENCIA_JUSTIFICADA )
                {
                    $btnFrequency->addStyle('background-color', 'royalblue');
                    
                    if ( MUtil::getBooleanValue(MIOLO::_REQUEST('isAdmin')) )
                    {
                        $divControls[] = $btnJust = $this->button('trocajust', NULL, NULL, MUtil::getAjaxAction('alterarJustificativa', "{$frequencia->frequenciaid}"), $MIOLO->getUI()->getImageTheme('portal', 'bf-explorar-on.png'));
                        $btnJust->addBoxStyle('margin', '0px');
                    }
                }
                else
                {
                    $btnFrequency->addStyle('background-color', 'red');
                }
                
                $hct = new MHContainer('hct_'.rand(), $divControls);
                $div[] = $hct;
                
                $divInfo = new MDiv('div_' . $id, $div);
//                $divInfo->addStyle('width', '75%');
//                $divInfo->addStyle('float', 'left');
                
                $this->setResponse($divInfo, 'div_' . $id);
            }
        }
        else
        {
            $_SESSION[$ofertacomponentecurricularid][$data[0]][$data[1]] = NULL;
        }
        
        $this->setNullResponseDiv();
    }
    
    public function salvarPeloBotao($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid');

        if ( strlen($args->cronograma) > 0 )
        {
            $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
            $disciplinas = new PrtDisciplinasPedagogico();
            
            //salvar cronograma
            $disciplinas->salvarCronograma($ofertacomponentecurricularid, $args->cronograma, $args->dataaula);

            $args->salvarPeloBotao = true;
            $this->salvar($args);
        }
        else
        {
            $this->setNullResponseDiv();
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
        
        $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
        $disciplinas = new PrtDisciplinasPedagogico();
        
        $groupId = MIOLO::_REQUEST('groupid');
        $salvarPeloBotao = $args->salvarPeloBotao;
        
        $args = $this->getAjaxData();
        $ofertacomponentecurricularid = MIOLO::_REQUEST('ofertacomponentecurricularid');
        $horarios = $disciplinas->obterHorariosPelaData($ofertacomponentecurricularid, $args->dataaula);

        $matriculas = $disciplinas->obterMatriculasOtimizado($ofertacomponentecurricularid, false);
        
        // Verifica as frequências. Se não tiver, adiciona 'presente' por padrão. Se já existe a frequência, não mexe.
        foreach($matriculas as $matricula)
        {   
            foreach( $horarios as $k=>$horario )
            {
                if ( $salvarPeloBotao )
                {
                    $disciplinas->verificaFrequencia($matricula['matriculaid'], $horario[0]);
                }
            }
        }
        
        if ( !$salvarPeloBotao )
        {
            // Marcar ou desmarcar
            if ( isset($_SESSION['presenca'][$timeUnique]) )
            {
                if ( $_SESSION['presenca'][$timeUnique] == AcpFrequencia::FREQUENCIA_PRESENTE )
                {
                    $presenca = AcpFrequencia::FREQUENCIA_AUSENTE;
                }
                else if ( $this->permiteMeiaPresenca && ( $_SESSION['presenca'][$timeUnique] == AcpFrequencia::FREQUENCIA_AUSENTE ) )
                {
                    $presenca = AcpFrequencia::FREQUENCIA_MEIA;
                }
                else if ( MUtil::getBooleanValue(MIOLO::_REQUEST('isAdmin')) && ( in_array($_SESSION['presenca'][$timeUnique], array(AcpFrequencia::FREQUENCIA_AUSENTE, AcpFrequencia::FREQUENCIA_JUSTIFICADA)) ) )
                {
                    $presenca = AcpFrequencia::FREQUENCIA_JUSTIFICADA;
                }
                else
                {
                    $presenca = AcpFrequencia::FREQUENCIA_PRESENTE;
                }
               
                $_SESSION['presenca'][$timeUnique] = $presenca;
            }
            else
            {
                $_SESSION['presenca'][$timeUnique] = AcpFrequencia::FREQUENCIA_AUSENTE;
                $presenca = AcpFrequencia::FREQUENCIA_AUSENTE;
            }
            
            //salvar presencas
            foreach((array)$args as $k=>$v)
            {
                if(substr($k,0,  strlen('presenca_'))=='presenca_')
                {
                    $data = explode('_', $k);
                    $data = base64_decode($data[1]);
                    $data = explode('_', $data);
                    
                    list($matriculaid, $ocorrenciahorarioofertaid) = $data;
                    
                    if ( $ocorrenciahorarioofertaid == $timeIdButton )
                    {
                        $disciplinas->salvarFrequencia($matriculaid, $ocorrenciahorarioofertaid, $presenca);
                    }
                }
            }
        }
        
        $this->setResponse($this->presencas($args->dataaula), 'divPresencas');
        $this->setResponse(NULL, 'responseDiv');
    }
    
    public function alterarJustificativa($args)
    {
        $args = explode('|', $args);
        $frequenciaId = $args[0];

        $frequencia = new AcpFrequencia($frequenciaId);

        $fields[] = new MMultiLineField('justificativa', $frequencia->justificativa, null, null, 4, 5);

        $botoes[] = $this->button('botaoSalvar', _M('Salvar', $this->modulo), NULL, MUtil::getAjaxAction('salvarJustificativa', "{$frequenciaId}"));
        $botoes[] = $this->button('botaoFechar', _M('Cancelar', $this->modulo), NULL, "dijit.byId('dlgJustificativa').hide();");
        $fields[] = new MHContainer('hctX', $botoes);

        $dialog = new MDialog('dlgJustificativa', _M('Alterar justificativa'), $fields);
        $dialog->setWidth('550px');
        $dialog->setHeight('230px');
        $dialog->show();

        $this->setNullResponseDiv();
    }
    
    public function salvarJustificativa($args)
    {
        $MIOLO = MIOLO::getInstance();
        
        $args = explode('|', $args);
        $frequenciaId = $args[0];
        
        $justificativa = MIOLO::_REQUEST('justificativa');
        
        $frequencia = new AcpFrequencia($frequenciaId);
        $frequencia->justificativa = SAGU::NVL($justificativa, SType::NULL_VALUE);
        $frequencia->save();
        
        $MIOLO->page->addJsCode("dijit.byId('dlgJustificativa').hide();");
        
        $this->setNullResponseDiv();
    }
}

class MyDiv extends MDiv
{
    public function generate()
    {
        $MIOLO = MIOLO::getInstance();
        
        $this->setClass('myclass', false);
        
        $this->addStyle('float', 'left');
        $this->addStyle('margin', '0px 0px 0px 0');
        $this->addStyle('display', 'block');
        
        return parent::generate();
    }
}
?>