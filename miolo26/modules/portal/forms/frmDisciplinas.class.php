<style>
    .mContainerHorizontal {
        overflow-y: auto !important;
        overflow-x: auto !important;
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
$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/breport.class.php', 'base');
$MIOLO->uses('classes/prtDisciplinas.class.php', $module);
$MIOLO->uses('classes/prtUsuario.class.php', $module);

ini_set('memory_limit', '10240M');
ini_set('max_execution_time', '0');

class frmDisciplinas extends frmMobile
{
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Disciplinas', MIOLO::getCurrentModule()));        
    }

    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $busLearningPeriod = $MIOLO->getBusiness('academic', 'BusLearningPeriod');
        $currentPeriod = $busLearningPeriod->obterPeriodoAtual();
	
        $fields = array();
        $sections = array();

        // disciplinas academico
        if ( SAGU::getParameter('BASIC', 'MODULE_ACADEMIC_INSTALLED') == 'YES' && 
             strlen(prtUsuario::obterContratoAtivo()) > 0 )
        {
            $periodos = $disciplinas->obterPeriodos($this->personid);
            $infoDisciplina = true;
            $arrayPeriodos = array();
            foreach($periodos as $periodo)
            {
                if ( !in_array($periodo[1], $arrayPeriodos) )
                {
                    $opts = array();

                    if($infoDisciplina)
                    {
                        if($periodo[1])
                        {
                            $opts[] = $this->disciplina($periodo[1]);
                        }
                    }

                    $sections[] = new jCollapsibleSection($periodo[2].' - '.$periodo[5], $opts, $periodo[1] == $currentPeriod);
                    //$infoDisciplina = false;
                    $arrayPeriodos[] = $periodo[1];
                }
            }
        }
        
        // disciplinas pedagogico
        $opts = array();
        if ( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' &&
             strlen(prtUsuario::obterInscricaoAtiva()) > 0 )
        {
            //Busca turmas pedagógico e equivale com periodos do academico
            $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
            $disciplinasPedagogico = new PrtDisciplinasPedagogico();

            //Periodos
            $turmas = $disciplinasPedagogico->obterTurmasAluno($this->personid);
            $periodos = array_merge((array) $periodos, (array) $turmas);

            //Disciplinas
            foreach( $turmas as $cod => $turma )
            {
                $ofertaTurmaId = $turma[0];
                
                foreach( $disciplinasPedagogico->obterDisciplinasDoAlunoNaTurmaParaOPortal($this->personid, $ofertaTurmaId) as $cod => $ofertacomponentecurricularArray )
                {
                    $ofertaComponenteCurricular = new stdClass();
                    
                    list( $ofertaComponenteCurricular->ofertaComponenteCurricularId,
                          $ofertaComponenteCurricular->titulo ) = $ofertacomponentecurricularArray;
                    
                    $actionArgs = array(
                        'ofertaComponenteCurricularId' => $ofertaComponenteCurricular->ofertaComponenteCurricularId,
                        'titulo' => $ofertaComponenteCurricular->titulo,
                        'personId' => $this->personid
                    );
                    $jCollapsibleSection = new jCollapsibleSection($ofertaComponenteCurricular->titulo, null, false, 'divOfertaComponenteCurricular_' . $ofertaComponenteCurricular->ofertaComponenteCurricularId);
                    $jCollapsibleSection->addAttribute('onClick', MUtil::getAjaxAction('infoDisciplinaPedagogico', $actionArgs));
                    
                    $opts[$turma[0]][] = $jCollapsibleSection;
                }

                $sections[] = new jCollapsibleSection($turma[1], $opts, false);
            }
        }
        
        if ( count($sections) > 0 )
        {
            $fields[] = new jCollapsible('contratos_collapsible', $sections);
        }
        else
        {
            $fields[] = MPrompt::information(_M('Nenhum disciplina encontrado para o aluno.'),'NONE',_M('Information'));
        }
        
	parent::addFields($fields);
    }
    
    public function disciplina($periodId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();

        $data = $disciplinas->obterDisciplinasDoPeriodo($this->personid, $periodId);

        foreach($data as $d)
        {
            $opts = array();
            
            if($d[1])
            {
                $opts[] = $this->infoDisciplina($d);
            }
            
            $sections[] = new jCollapsibleSection(_M($d[4]), $opts);
        }
        
        return new jCollapsible('disc'.$d[1], $sections);
    }
    
    public function infoDisciplina($data)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
   
        $info = $disciplinas->obterInfoDisciplina($data[1],$data[0]);
        
        $notas = $disciplinas->obterNotas($data[1],$data[0]);
        
        $frequencia = $disciplinas->obterFrequencia($data[0]);
        
        $fields = array();
        
        $dados[0][0] = $data[10];
        $dados[0][1] = $data[6];
        $dados[0][2] = $info->professor;
        
        $columns[0] = _M('Status');
        $columns[1] = _M('Carga horária');
        $columns[2] = _M('Professor');
        
        $fields[] = $this->listView('info', 'Informação da disciplina', $columns, $dados, $options);
        
        $fields[] = $this->exibeNotas($notas, $data[0], $data[1]);

        $fields[] = $this->exibeFrequencias($frequencia, $data[1], $data[0]);
        
        $fields[] = $this->exibeDocumentos($data[1]);
        
        return $fields;
    }
    
    public function infoDisciplinaPedagogico()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();   
        $ajaxArgs = MUtil::getAjaxActionArgs();
        
        $ofertaComponenteCurricularId = $ajaxArgs->ofertaComponenteCurricularId;
        $titulo = $ajaxArgs->titulo;
        $personId = $ajaxArgs->personId;        

        $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
        $disciplinasPedagogico = new PrtDisciplinasPedagogico();
        $ofertacomponente = new AcpOfertaComponenteCurricular($ofertaComponenteCurricularId);
        
        $dados = array();
        $dados[0][0] = SAGU::NVL($ofertacomponente->obterDiasDaSemanaDaOferta(true), _M("Nenhuma data marcada", $module));
        $dados[0][1] = $ofertacomponente->ofertaturma->ofertacurso->ocorrenciacurso->turn->description;
        $dados[0][2] = SAGU::NVL($ofertacomponente->obterPrimeiroHorarioDaOferta(), _M('Nenhuma data marcada', $module));
        $dados[0][3] = SAGU::NVL($ofertacomponente->obterUltimoHorarioDaOferta(), _M("Nenhuma data marcada", $module));

        $columns[0] = _M('Dia (Sala)');
        $columns[1] = _M('Turno');
        $columns[2] = _M('Início');
        $columns[3] = _M('Fim');
        
        $fields = array();
        $fields[] = $this->listView('info', 'Informação da disciplina', $columns, $dados, null);
        
        //Modelo de avaliação
        $modelodeavaliacao = AcpModeloDeAvaliacao::obterModeloDaOfertaDeComponenteCurricular($ofertacomponente->ofertaComponenteCurricularId);

        //Caso utilize um modelo de avaliação exibe as avaliações
        if( $modelodeavaliacao->tipoDeDados != AcpModeloDeAvaliacao::TIPO_NENHUM )
        {
            $fields[] = $this->exibeNotasPedagogico($ofertacomponente, $personId);
        }

        //Caso utilize controle de frequencia exibe as frequências
        if( $modelodeavaliacao->habilitaControleDeFrequencia == DB_TRUE )
        {
            $fields[] = $this->exibeFrequenciasPedagogico($ofertacomponente, $personId);
        }
        
        //Exibe documentos
        $fields[] = $this->exibeDocumentosPedagogico($ofertacomponente);
        
        $divId = 'divOfertaComponenteCurricular_' . $ofertaComponenteCurricularId;
        $jCollapsibleSection = new jCollapsibleSection($titulo, $fields, true);
        $this->setResponse(array($jCollapsibleSection), $divId);
        
        // Controle para não executar a consulta de obtenção das informações do componente curricular pela segunda vez, somente necessário uma vez.
        $MIOLO->page->addJsCode("document.getElementById('{$divId}').setAttribute('onclick', '');");
    }
    
    public function exibeDocumentos($groupId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        $busTransaction = new BusinessAdminTransaction();
        
        $countDocs = 0;
        $div = NULL;
        
        $panel = new mobilePanel('panel');
        
        if ( $MIOLO->checkAccess('DocPlanoDeEnsinoAluno', A_ACCESS, FALSE) )
        {
            $panel->addActionAJAX(_M('Plano de ensino'), $ui->getImageTheme($module, 'notas.png'), 'planoDeEnsino', $groupId);
            $countDocs++;
        }
        
        if ( $countDocs > 0 )
        {
            $bg = new MBaseGroup('bgDocs', 'Documentos', array($panel));
            $div = new MDiv('divDocs', $bg);
        }
        
        return $div;
    }

    public function exibeDocumentosPedagogico($ofertacomponente)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        $busTransaction = new BusinessAdminTransaction();

        $infos = array();

        $infos[_M('Plano de aulas')] = $ofertacomponente->planoaulas;
        $infos[_M('Metodologia')] = $ofertacomponente->metodologia;
        $infos[_M('Avaliação')] = $ofertacomponente->avaliacao;

        $infos = array_filter($infos);
        
        if ( count($infos) > 0 )
        {
            $info = new sInformationField(array(
                'title' => _M('Informações'),
                'columns' => 1,
                'value' => $infos));
        }
        else
        {
            $info = null;
        }
        
        return $info;
    }
    
    public function exibeNotas($notas, $enrollId, $groupId)
    {
        $MIOLO = MIOLO::getInstance();
        $img = $MIOLO->getUI()->getImageTheme('portal', 'bar.png');

        $fields[] = new MSpacer('&nbsp;');
        if ( !(count($notas) > 0) )
        {
            $fields[] = new MText('nota', '<b>Nenhuma nota cadastrada/lançada.</b>');
        }
        
        foreach ( $notas as $descricao => $valor )
        {
            //Nota
            $nota = new MText('nota', '<b>' . $descricao . ': </b>' . SAGU::NVL($valor['nota'], 'não lançada'));

            //Gráfico de desempenho - somente notas numéricas
            if ( SAGU::getParameter('PORTAL', 'EXIBIR_GRAFICO_DESEMPENHO_ALUNO') == DB_TRUE && 
                 is_numeric($valor['nota']) && 
                 strlen($valor['nota']) > 0 )
            {
                $btn = new MImageButton('btnMostraGrafico_' . rand(), NULL, MUtil::getAjaxAction('mostrarGraficoDesempenho', rawurlencode($valor['degreeid'] . '|' . $descricao . '|' . $valor['nota'] . '|' . $enrollId . '|' . $groupId)), $img);
            }
            
            $fields[] = $hctNota = new MVContainer('hctNota', array($nota, $btn));
            $fields[] = new MSpacer("&nbsp;");
            
            //Avaliações da nota
            foreach ( $valor['avaliacoes'] as $avaliacao => $conceito )
            {
                $dataAva[] = array($avaliacao, SAGU::NVL($conceito, ' não lançada '));
            }
            
            //Tabela das avaliações
            if ( count($dataAva) > 0 )
            {
                $fields[] = new MTableRaw(_M("Avaliações") . ' (' . $descricao . ')', $dataAva, array(_M('Descrição'), _M('Nota/conceito')));
                $fields[] = new MSpacer("&nbsp;");
            }
            
            //Mata dados da table
            unset($dataAva);
        }
        
        $bg = new MBaseGroup('bgNotas', 'Notas', $fields);

        $div = new MDiv('divNotas', $bg);
        
        return $div;
    }
    
    public function exibeNotasPedagogico($ofertacomponente, $personId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $img = $MIOLO->getUI()->getImageTheme('portal', 'bar.png');

        $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
        $disciplinasPedagogico = new PrtDisciplinasPedagogico();
        
        $ofertacomponentecurricularid = $ofertacomponente->ofertacomponentecurricularid;
        $modeloAva = AcpModeloDeAvaliacao::obterModeloDaOfertaDeComponenteCurricular($ofertacomponentecurricularid);
        $componentes = AcpComponenteDeAvaliacao::obterComponentesDeAvaliacaoDoModelo($modeloAva->modelodeavaliacaoid);
        $matriculaId = $disciplinasPedagogico->obterMatriculaPelaOferta($personId, $ofertacomponentecurricularid);

        if ( count($componentes) == 0 )
        {
            $dataTable[] = array(_M('Nenhum nota lançada.'));
        }
        else
        {
            foreach ( $componentes as $componente )
            {
                $nota = $disciplinasPedagogico->obterNotaPedagogico($componente->componenteDeAvaliacaoId, $matriculaId);
                $dataTable[] = array('&nbsp;', '&nbsp;', '<b>'.$nota->descricao.': </b>', '<b>'.$nota->valor.'</b>');
            }
        }
        
        $table = new MTableRaw('', $dataTable, null);
        $table->SetAlternate(true);
        $table->setWidth('100%');
        
        $table->setCell(0, 0, null, 'width="80"');
        $table->setCell(0, 1, null, 'width="20"');
        $table->setCell(0, 1, null, 'width="50"');
        $table->setCell(0, 1, null, 'width="20"');
        
        $fields[] = $table;
        $bg = new MBaseGroup('bgNotas', 'Notas', $fields);
        $div = new MDiv('divNotas', $bg);
        
        return $div;
    }
    
    public function mostrarGraficoDesempenho($args)
    {
        $args = rawurldecode($args);        
        $args = explode('|', $args);
        
        $prtDisciplina = new PrtDisciplinas();
        $media = $prtDisciplina->obterMediaDaDisciplina($args[0], $args[4]);
        
        $dados[] = $args[2];
        $dados[] = $media;
        
        $chart = new MChart('chart_' . rand(), $dados, $args[1], MChart::TYPE_BAR);
        $chart->setYMax(SAGU::getParameter('BASIC', 'MAX_EVALUATION_POINTS'));
        $chart->setYMin(0);
        $chart->setXTicks(array('Você', 'Média da disciplina'));
        
        $divChart = new MDiv('divChart', array($chart));
        
        $dlg = new MDialog('dlgChart_' . rand(), _M('Gráfico de desempenho'), array($divChart));
        $dlg->show();
        
        $this->setNullResponseDiv();
    }
    
    public function exibeFrequencias($frequencias, $groupid, $enrollid)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
     
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
 
        $horarioDeAula = $disciplinas->obterHorarioDeAula2($groupid, $dia);

        if ( count($frequencias) == 0 )
        {
            $dataTable[] = array(_M('Nenhum frequência lançada.'), '&nbsp;', '&nbsp;');
        }
        else
        {
            $frequencias = null;            
            foreach ($horarioDeAula as $h)
            {
                $cronogramas = PrtDisciplinas::obterCronograma2($groupid, $h['occurrencedate']);
                foreach( $cronogramas as $cron )
                {
                    if(strlen($cron['description']) > 0 )
                    {
                        $ok = true;
                    }
                } 
                $frequencia = $disciplinas->obterFrequencia($enrollid, $h['occurrencedate'], $h['timeid']);
                $occurrencedate = $frequencia[0][0];
                if ( $occurrencedate )
                {
                    $beginHour = $frequencia[0][1];
                    $endHour = $frequencia[0][2];
                    $freq = $frequencia[0][3];

                    $frequencias[$occurrencedate]['horario'][] = $beginHour . ' - ' . $endHour;
                    $frequencias[$occurrencedate]['frequencia'][] = $freq;
                }   
            }
            if($ok)
            {
                foreach($frequencias as $data => $frequencia)
                {
                    $horario = implode(' / ', $frequencia['horario']);

                    $presencas = null;
                    foreach( $frequencia['frequencia'] as $freq )
                    {
                        $presencas .= $this->obterImagemFrequencia($freq).'&nbsp;';
                    }

                    $dataTable[] = array('<b>'.$data.'</b>', $horario, '<center><b>'.$presencas.'<b></center>');
                }
            }
        }
        
        $table = new MTableRaw('', $dataTable, array(_M('Data'),_M('Horário'), _M('Presença')));
        $table->SetAlternate(true);
        $table->setWidth('100%');
        $fields[] = $table;
        $bg = new MBaseGroup('bgFrequncias', 'Frequências', $fields);
        $div = new MDiv('divFrequencias', $bg);
        
        return $div;
    }
    
    public function exibeFrequenciasPedagogico($ofertacomponente, $personId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
        $disciplinasPedagogico = new PrtDisciplinasPedagogico();
        
        $ofertacomponentecurricularid = $ofertacomponente->ofertacomponentecurricularid;
        $matriculaId = $disciplinasPedagogico->obterMatriculaPelaOferta($personId, $ofertacomponentecurricularid);
        $frequencias = $disciplinasPedagogico->buscaFrequencias($matriculaId);

        if ( count($frequencias) == 0 )
        {
            $dataTable[] = array(_M('Nenhum frequencia lançada.'), '&nbsp;', '&nbsp;');
        }
        else
        {
            foreach( $frequencias as $freq )
            {
                $cronogramas = PrtDisciplinasPedagogico::obterCronograma($ofertacomponentecurricularid, $freq['dataaula']);
                foreach( $cronogramas as $cron )
                {
                    if(strlen($cron['conteudo']) > 0 )
                    {
                        $okP = true;
                    }
                }
                if($okP)
                {
                    $presencas = '';
                    $listaStatus = (array) explode(' ', $freq['status']);
                    $listaJustificativa = (array) explode('||', $freq['justificativa']);

                    foreach( $listaStatus as $key => $status )
                    {
                        if ( strlen($status) > 0 )
                        {
                            $presencas .= $this->obterImagemFrequencia($status, $listaJustificativa[$key]).'&nbsp;';
                        }   
                    }

                    $dataTable[] = array('<center><b>'.$freq['dataaula'].'</b></center>', $freq['horarios'], '<center><b>'.$presencas.'<b></center>');
                }
            }
            if(!$okP)
            {
                $dataTable[] = array(_M('Nenhum frequencia lançada.'), '&nbsp;', '&nbsp;');
            }
        }
        
        $table = new MTableRaw('', $dataTable, array(_M('Data'),_M('Horário'), _M('Presença')));
        $table->SetAlternate(true);
        $table->setWidth('100%');
        $fields[] = $table;
        $bg = new MBaseGroup('bgFrequncias', 'Frequencias', $fields);
        $div = new MDiv('divFrequencias', $bg);
        
        return $div;
    }
    
    public function obterImagemFrequencia($status, $justificativa = null)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        $title = '';
        
        switch ($status)
        {   
            case 1 :
            case 'P':
                $imagem = $ui->getImageTheme($module, 'presenca.png'); break;
            
            case 0.5 :
            case 'M':
                $imagem = $ui->getImageTheme($module, 'meiapresenca.png'); break;
                break;
                
            case 'J':
                $imagem = $ui->getImageTheme($module, 'faltajustificada.png');
                
                if ( strlen($justificativa) > 0 )
                {
                    $title = 'title="'.$justificativa.'"';
                }
                break;
            
            case 0 :
            case 'A':
                $imagem = $ui->getImageTheme($module, 'falta.png'); break;
            
            default :
                $imagem = false;
        }
        
        
        if($imagem)
        {
            return '<img src="'.$imagem.'" '.$title.'/>';
        }
        
        return '';
    }
    
    public function planoDeEnsino($groupId)
    {
        $MIOLO = MIOLO::getInstance();
        $prtDisciplinas = new PrtDisciplinas();
        
        $infoDisciplina = $prtDisciplinas->obterInfoDisciplina($groupId);
        
        $report = new MJasperReport();
        $report->executeJRXML('academic', 'gradebook', array(
            'int_groupid' => $groupId,
            'professorName' => $infoDisciplina->professor
        ));
        
        $this->setNullResponseDiv();
    }
}

?>