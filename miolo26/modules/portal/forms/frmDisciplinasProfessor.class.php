<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/10/09
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
$MIOLO->uses('types/AcpCoordenadores.class', 'pedagogico');

class frmDisciplinasProfessor extends frmMobile
{
    
    public $_columns;

    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Disciplinas'));
    }

    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busPeriod = new BusinessAcademicBusPeriod();
        $busProfessor = new BusinessServicesBusProfessor();
        
        $periodId = MIOLO::_REQUEST('periodid');        
        
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
        {
            //Verifica onde é coordenador
            $busCourseCoordinator = new BusinessAcademicBusCourseCoordinator();
            $coordenadorAcademico = $busCourseCoordinator->isCourseCoordinator(prtUsuario::obtemUsuarioLogado()->personId);
            $coordenadorPedagogico = AcpCoordenadores::verificaPessoaECoordenador(prtUsuario::obtemUsuarioLogado()->personId);

            //Se for do acadêmico, mostra o selection
            //Trás as disciplinas do período do selection
            //Mais todas as turmas do pedagógico, independentemente do período
            if ( $coordenadorAcademico && !(strlen($periodId) > 0) )
            {
                $label = new MLabel(_M('Por favor, selecione um período para visualização das disciplinas:'));
                $label->addStyle('padding', '14px');
                $selection = new MSelection('periodId', NULL, NULL, $busPeriod->listPeriod('periodId DESC'));

                // Precisa ser hardcode o 'doAjax', pois precisa montá-lo com o 'this.value' (valor selecionado em tempo real pelo usuário).
                $selection->addAttribute('onchange', "miolo.doAjax('obterDisciplinas', this.value, '__mainForm');"); 
                $fields[] = new MHContainer('contPeriodo', array($label, $selection));

                $fields[] = new MDiv('divDisciplinas');
            }
            elseif ( $coordenadorPedagogico )
            {
                $campos = $this->obterDisciplinas('pedagogico', true);
                $fields[] = new MDiv('divDisciplinas', $campos);
            }
        }
        else
        {
            //Se possuir períodos cadastrados, deixa escolher, para poupar memória
            $lista = $busProfessor->listProfessorPeriods($this->personid);
            
            if ( count($lista) > 0 )
            {
                $label = new MLabel(_M('Por favor, selecione um período para visualização das disciplinas:'));
                $label->addStyle('padding', '14px');
                $selection = new MSelection('periodId', NULL, NULL, $lista);

                // Precisa ser hardcode o 'doAjax', pois precisa montá-lo com o 'this.value' (valor selecionado em tempo real pelo usuário).
                $selection->addAttribute('onchange', "miolo.doAjax('obterDisciplinas', this.value, '__mainForm');"); 
                $fields[] = new MHContainer('contPeriodo', array($label, $selection));
                
                $fields[] = new MDiv('divDisciplinas');
            }
            else
            {
                $fields[] = new MDiv('divDisciplinas');
                //Senão obtem as disciplinas de uma vez (pega caso usuário for do pedagógico e caso não tenha nenhuma também)
                $MIOLO->page->addJsCode(MUtil::getAjaxAction('obterDisciplinas', $periodId));
            }
        }
        
	parent::addFields($fields);
    }
    
    /**
     * Renderiza as disciplinas do professor/cordenador, recebendo o período referente
     * por parâmetro.
     * 
     * @param String $periodId
     * @param boolean $somentePedagogico - Buscar somentes disciplinas do pedagógico.
     */
    public function obterDisciplinas($periodId, $somentePedagogico = false )
    {        
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        
        $MIOLO->uses('classes/prtUsuario.class.php', $module);
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        
        $busLearningPeriod = $MIOLO->getBusiness('academic', 'BusLearningPeriod');
        $currentPeriod = $busLearningPeriod->obterPeriodoAtual();
        $busPeriod = new BusinessAcademicBusPeriod();
        $disciplinas = new PrtDisciplinas();
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $busProfessor = new BusinessServicesBusProfessor();

        $fields = array(); 

        if ( !$somentePedagogico )
        {
            if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
            {
                $periodo = $busPeriod->getPeriod($periodId);

                if ( strlen($periodo->periodId) > 0 )
                {
                    $periodos[0][] = $periodo->periodId;
                    $periodos[0][] = $periodo->description;
                }
            }
            else
            {
                if ( strlen($periodId) > 0 )
                {
                    $periodo = $busPeriod->getPeriod($periodId);
                    $periodos[0][] = $periodo->periodId;
                    $periodos[0][] = $periodo->description;
                }
                else
                {
                    $periodos = $busProfessor->listProfessorPeriods($this->personid);
                }
            }
                
        $opts = array();
        $closed = array();

        if(  count($periodos)>0 )
        {
            $infoDisciplina = true;

            foreach($periodos as $periodo)
            {             
                    if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
                    {
                        $filter = new stdClass();
                        $filter->periodId = $periodo[0];
                        $filter->coordinatorId = $this->personid;
                        $filter->isCancellation = DB_FALSE;

                        $data = $busProfessor->listProfessorCurricularComponents($filter);
                    }
                    else
                    {
                        $filter = new stdClass();
                        $filter->periodId = $periodo[0];
                        $filter->personId = $this->personid;
                        $filter->listTcc = true;
                        $filter->isCancellation = DB_FALSE;

                        $data = $busProfessor->listProfessorCurricularComponents($filter);
                    }
                    
                    //  GRUPO DISCIPLINAS
                    $businessCurricularComponentGroup = new BusinessAcademicBusCurricularComponentGroup();

                    $filters = new stdClass();
                    $filters->professorId = $this->personid;
                    $filters->periodId = $periodo[0]; 
                    $filters->evaluationcontrolmethodId = 2; // grupo disciplina
                    $dataCurricularComponentGroup = $businessCurricularComponentGroup->searchCurricularComponentGroupByLearningPeriod($filters);

                    $param[$periodo[0]] = $this->grupoDisciplinas($dataCurricularComponentGroup);
                    if($dataCurricularComponentGroup)
                    {
                        $currentPeriod = $busLearningPeriod->obterPeriodoAtual();
                        $busTransaction = new BusinessAdminTransaction();
                        $ui = $MIOLO->getUI();
                        $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
                        $disciplinasPedagogico = new PrtDisciplinasPedagogico();
                        $turmas = $disciplinasPedagogico->obterTurmasProfessor($this->personid);

                        $sect = new jCollapsibleSection(_M('GRUPO DE DISCIPLINAS'), $param[$periodo[0]]);

                        $abaGrupoDisc = new jCollapsible('collapGrupoDisc', $sect);

                        $opts[$periodo[0]][] = $abaGrupoDisc;
                    }

                    foreach($data as $r)
                    {
                        $group = $busGroup->getGroup($r[0]);
                        $isClosed = $disciplinas->isDisciplinaEncerrada($group->groupId);
                        if ( $isClosed )
                        {
                            $closed[$periodo[0]][] = $this->disciplina($group);
                        }
                        else
                        {
                            $opts[$periodo[0]][] = $this->disciplina($group);
                        }
                    }
                }
            }
        }
        
        //Busca turmas pedagógico e equivale com periodos do academico
        if ( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' )
        {
            $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
            $disciplinasPedagogico = new PrtDisciplinasPedagogico();
            
            //Periodos
            $turmas = $disciplinasPedagogico->obterTurmasProfessor($this->personid);
            $periodos = array_merge((array) $periodos, (array) $turmas);

            //Disciplinas
            foreach( $turmas as $cod => $turma )
            {
                foreach( $disciplinasPedagogico->obterDisciplinasDoProfessorNaTurma($this->personid, $turma[0]) as $cod => $ofertacomponentecurricularid )
                {
                    $ofertacomponente = new AcpOfertaComponenteCurricular($ofertacomponentecurricularid[0]);
                    $opts[$turma[0]][] = $this->disciplinaPedagogico($ofertacomponente);
                }
                //disciplinas EAD
                foreach( $disciplinasPedagogico->obterDisciplinasDoProfessorNaTurmaEad($this->personid, $turma[0]) as $cod => $ofertacomponentecurricularid )
                {
                    $ofertacomponente = new AcpOfertaComponenteCurricular($ofertacomponentecurricularid[0]);
                    $opts[$turma[0]][] = $this->disciplinaPedagogico($ofertacomponente);
                }
            }
        }
            
        $coordenaMaisDeUmCurso = prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR && AcpCoordenadores::verificaCoordenadorMaisDeUmCurso($this->personid) == DB_TRUE;
                
        if ( count($periodos)>0 )
        {
            foreach($periodos as $periodo)
            {
                if ( count($opts[$periodo[0]]) > 0 )
                {
                    if ( count($closed[$periodo[0]]) > 0 )
                    {
                        $opts[$periodo[0]][] = new jCollapsibleSection(_M('DISCIPLINAS ENCERRADAS'), $closed[$periodo[0]]);
                    }
                }
                else
                {
                    $opts[$periodo[0]] = $closed[$periodo[0]];
                }

                $tituloDisciplina = $periodo[1];
                
                // Verifica se o usuário é coordenador em mais de um curso
                if ( $coordenaMaisDeUmCurso )
                {
                    $tituloDisciplina = $periodo[2];
                }
                
                $sections[] = new jCollapsibleSection($tituloDisciplina, $opts[$periodo[0]], $periodo[0] == $currentPeriod);
            }

            $fields[] = new jCollapsible('contratos_'.rand(), $sections);
        }
        else
        {
            $fields[] = MPrompt::information(_M('Nenhum disciplina encontrada para o professor.'),'NONE',_M('Information'));
        }

        if ( !$somentePedagogico )
        {
            $this->setResponse($fields, 'divDisciplinas');
        }
        else
        {
           return $fields;
        }
    }
    
    public function grupoDisciplinas($data)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        
        $this->_columns = array(
        _M('Ação', $module),
        _M('Código do grupo das disciplinas', $module),
        _M('Nome', $module)
             );

        $linha = 0;
            foreach ( $data as $ind => $grpDisc )
            {
                $panel = new mobilePanel('panel');
                $panel->height = '10px';
                $panel->width = '22%';
                $panel->widthIcon = '10px';
                $panel->heightIcon = '10px';
                
                $linkHistorico = $this->button('actionGrupoDisc', NULL,NULL, MUtil::getAjaxAction('mostraGrupoDisciplina',array('personid'=> $this->personid, 'periodid' => $grpDisc[4], 'courseId' => $grpDisc[5], 'courseVersion' => $grpDisc[6], 'turnId' => $grpDisc[7], 'unitid' => $grpDisc[8], 'curricularComponentGroupId' => $grpDisc[0])), $MIOLO->getUI()->getImageTheme('portal', 'bf-explorar-on.png'));

                $linkHistorico = new MHContainer('actionGrupoDiscCont' , array($div, $linkHistorico));
                $panel->addAction(null, $ui->getImageTheme($module, 'livro_presenca.png'), $module, 'main:grupoDisciplina', null, array('personid'=> $this->personid ));
                
                $dataTable[$linha][0] = $linkHistorico;
                $dataTable[$linha][1] = $grpDisc[0];
                $dataTable[$linha][2] = $grpDisc[1];
                $linha++;
            }
         
        $fields[] = $table = new MTableRaw('<font style="font-size: 15px">' ._M('Grupo de disciplinas ', $module). '</font>', $dataTable, $this->_columns);
        $table->SetAlternate(true);
        $table->setWidth('100%');
        
        // alinhamento
        $table->setCell(0, 0, null, 'width="50"');
        $table->setCell(0, 1, null, 'width="20%"');
        for($b = 0; $b < count($dataTable); $b++)
        {
            $table->setCellAttribute($b, 1, 'align', 'right');
            $table->setCellAttribute($b, 2, 'align', 'left');
        }
        
        return $fields;
    }
    
     public function mostraGrupoDisciplina($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $this->page->redirect($MIOLO->getActionURL($module, 'main:grupoDisciplina&&'.$args, NULL, NULL));
    }
    
    public function disciplina($disciplina)
    {        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $opts = array();
        
        $opts[] = $this->infoDisciplina($disciplina);
        
        $titulo = _M($disciplina->curriculumCurricularComponentName);
        
        //Caso parâmetro esteja preenchido, monta o título da disciplina concatenando as variáveis informadas.
        if ( strlen($tituloDisciplina = SAGU::getParameter('PORTAL', 'TITULO_DISCIPLINA_PORTAL')) > 0 )
        {
            $disciplinas = new PrtDisciplinas();
            
            $diasDaDisciplina = $disciplinas->obterDiasDaDisciplina($disciplina->groupId);
            $diasDaSemana = array();
            foreach($diasDaDisciplina as $diaDaDisciplina)
            {
                $data = explode('/', $diaDaDisciplina[0]);
                $weekDay = date('w',strtotime("$data[2]-$data[1]-$data[0]"));
                $diaDaSemana = $this->diaDaSemana($weekDay);
                
                $diasDaSemana[$weekDay] = substr($diaDaSemana, 0, 3);
            }            
            sort($diasDaSemana);
            
            $horariosDaDisciplina = array();
            foreach ( $disciplinas->obterHorariosDaDisciplina($disciplina->groupId) as $horarioDaDisciplina )
            {
                $horariosDaDisciplina[] = $horarioDaDisciplina[0];
            }
            sort($horariosDaDisciplina);
            
            $diasSemana = '<br><div style="font-size: 12px;">' .
                    implode(' - ', $diasDaSemana) .
            '</div>
            <div style="font-size: 12px;">' .
                    implode(' , ', $horariosDaDisciplina) .
            '</div>';
            
            $dataInicial = _M($disciplina->startDate);
            $dataFinal = _M($disciplina->endDate);
            $groupId = $disciplina->groupId;
            $courseId = $disciplina->curriculumCourseId;
            $courseVersion = $disciplina->curriculumCourseVersion;
            $courseName = $disciplina->curriculumCourseName;
            $turno = $disciplina->turnDescription;
            $unidade = $disciplina->unitDescription;
            
            /**
             * Variáveis possíveis para concatenação ao nome da disciplina
             * 
             * $COURSEID, $COURSENAME, $COURSEVERSION, $TURNDESCRIPTION, $UNITDESCRIPTION, $GROUPID, $DATAINICIAL, $DATAFINAL, $DIASDASEMANA
             */
            
            $tituloDisciplina = str_replace('$COURSENAME', $courseName, $tituloDisciplina);
            $tituloDisciplina = str_replace('$COURSEID', $courseId, $tituloDisciplina);
            $tituloDisciplina = str_replace('$COURSEVERSION', $courseVersion, $tituloDisciplina);
            $tituloDisciplina = str_replace('$TURNDESCRIPTION', $turno, $tituloDisciplina);
            $tituloDisciplina = str_replace('$UNITDESCRIPTION', $unidade, $tituloDisciplina);
            $tituloDisciplina = str_replace('$GROUPID', $groupId, $tituloDisciplina);
            $tituloDisciplina = str_replace('$DATAINICIAL', $dataInicial, $tituloDisciplina);
            $tituloDisciplina = str_replace('$DATAFINAL', $dataFinal, $tituloDisciplina);
            $tituloDisciplina = str_replace('$DIASDASEMANA', $diasSemana, $tituloDisciplina);
            
            $titulo .= $tituloDisciplina;
            
        }
        
        if ( MUtil::getBooleanValue($disciplina->isClosed) )
        {
            $titulo .= _M(' - ENCERRADA ');
        }
        
        $sections[] = new jCollapsibleSection($titulo, $opts, MIOLO::_REQUEST('mostrar') == $disciplina->groupId);
        
        return new jCollapsible('disc'.$disciplina->curricularComponentName, $sections);
    }
    
    public function disciplinaPedagogico($ofertacomponente)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $opts = array();        
        $opts[] = $this->infoDisciplinaPedagogico($ofertacomponente);

        //Caso parâmetro esteja ativado, monta o título da disciplina concatenando a data.
        if(SAGU::getParameter('PORTAL', 'TITULO_DISCIPLINA_COM_DATA') == 't')
        {
            $titulo = _M($ofertacomponente->componentecurricularmatriz->componentecurricular->codigo).' - '._M($ofertacomponente->componentecurricularmatriz->componentecurricular->nome).' - '._M($ofertacomponente->dataInicio); 
        }
        else
        {
           $titulo = _M($ofertacomponente->componentecurricularmatriz->componentecurricular->codigo.' - '.$ofertacomponente->componentecurricularmatriz->componentecurricular->nome);  
        }
              
        $sections[] = new jCollapsibleSection($titulo, $opts, MIOLO::_REQUEST('mostrar') == $ofertacomponente->ofertaComponenteCurricularId);
        
        return new jCollapsible('disc'.$ofertacomponente->componentecurricularmatriz->componentecurricular->nome, $sections);
    }
    
    public function diaDaSemana($dia)
    {
        switch ($dia)
        {
            case 0: return _M('Domingo'); 
            
            case 1: return _M('Segunda-feira');
            
            case 2: return _M('Terça-feira');
            
            case 3: return _M('Quarta-feira');
            
            case 4: return _M('Quinta-feira');
            
            case 5: return _M('Sexta-feira');
            
            case 6: return _M('Sábado');
            
            default : return _M('Não definido'); 
        }
    }
    
    public function infoDisciplinaPedagogico($ofertacomponente)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        $busTransaction = new BusinessAdminTransaction();
        $prtDisciplinasPedagogico = new PrtDisciplinasPedagogico();
        $gradedehorarios = $prtDisciplinasPedagogico->obterGradeDeHorariosDoProfessor($this->personid);
        
        $dias = array();
        foreach ( $gradedehorarios as $grade )
        {
            if ( $grade[0] == $ofertacomponente->ofertaComponenteCurricularId )
            {
                $dias[$grade[2]] = $grade[3];
            }
        }
//        
        $fields = array();
        if ( strlen($ofertacomponente->dataInicio) > 0 )
        {
            $data = explode('/', $ofertacomponente->dataInicio);
            $dados[0][0] = $this->diaDaSemana(date('w',strtotime("$data[2]-$data[1]-$data[0]")));
        }
        else
        {
            $dados[0][0] = implode(', ', $dias);
        }
        $dados[0][1] = $ofertacomponente->ofertaturma->ofertacurso->ocorrenciacurso->turn->description;
        $dados[0][2] = $ofertacomponente->dataInicio;
        $dados[0][3] = ''; //endDate
//        $dados[0][4] = $info->lessonNumberHours;
        
        $isClosed = ( strlen($ofertacomponente->dataFechamento) > 0);
        
        $columns[0] = _M('Dia(s)');
        $columns[1] = _M('Turno');
        $columns[2] = _M('Inicio');
        $columns[3] = _M('Fim');
        $columns[4] = _M('Carga horária');
        
        $fields[] = $this->listView('info', 'Informação da disciplina', $columns, $dados, $options);
        
        $panel = new mobilePanel('panel');
        $panel->height = '100px';
        $panel->width = '22%';
        $panel->widthIcon = '50px';
        $panel->heightIcon = '50px';
        
//        if ( $MIOLO->checkAccess('FrmProgramaProfessor', A_ACCESS, FALSE) )
//        {
//            $panel->addAction($busTransaction->getTransactionName('FrmProgramaProfessor'), $ui->getImageTheme($module, 'programa.png'), $module, 'main:programaProfessor', null, array('groupid'=>$disciplina->groupId));
//        }
//        
//        if ( $MIOLO->checkAccess('FrmPostagensProfessor', A_ACCESS, FALSE) )
//        {
//            $panel->addAction($busTransaction->getTransactionName('FrmPostagensProfessor'), $ui->getImageTheme($module, 'postagens.png'), $module, 'main:postagensProfessor', null, array('groupid'=>$disciplina->groupId));
//        }
//
//        if ( $MIOLO->checkAccess('FrmMensagensProfessor', A_ACCESS, FALSE) )
//        {
//            $panel->addAction($busTransaction->getTransactionName('FrmMensagensProfessor'), $ui->getImageTheme($module, 'mail.png'), $module, 'main:mensagens', null, array('groupid'=>$disciplina->groupId));
//        }
        
        //Modelo de avaliação da disciplina
        $modelodeavaliacao = AcpModeloDeAvaliacao::obterModeloDaOfertaDeComponenteCurricular($ofertacomponente->ofertaComponenteCurricularId);
        if ( $isClosed )
        {
            if ( $MIOLO->checkAccess('FrmFrequenciasEncerradaProfessor', A_ACCESS, FALSE) && $modelodeavaliacao->habilitaControleDeFrequencia == DB_TRUE )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmFrequenciasEncerradaProfessor'), $ui->getImageTheme($module, 'livro_presenca.png'), $module, 'main:frequenciasProfessorPedagogico', null, array('ofertacomponentecurricularid'=>$ofertacomponente->ofertacomponentecurricularid), 'return false;');
            }
            
            if ( $MIOLO->checkAccess('FrmNotasEncerradaProfessor', A_ACCESS, FALSE) && $modelodeavaliacao->tipoDeDados != AcpModeloDeAvaliacao::TIPO_NENHUM )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmNotasEncerradaProfessor'), $ui->getImageTheme($module, 'notas.png'), $module, 'main:notasProfessorPedagogico', null, array('ofertacomponentecurricularid'=>$ofertacomponente->ofertacomponentecurricularid), 'return false;');
            }
        }
        else
        {
            if ( $MIOLO->checkAccess('FrmFrequenciasProfessor', A_ACCESS, FALSE) && $modelodeavaliacao->habilitaControleDeFrequencia == DB_TRUE )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmFrequenciasProfessor'), $ui->getImageTheme($module, 'livro_presenca.png'), $module, 'main:frequenciasProfessorPedagogico', null, array('ofertacomponentecurricularid'=>$ofertacomponente->ofertacomponentecurricularid));
            }
            
            if ( $MIOLO->checkAccess('FrmNotasProfessor', A_ACCESS, FALSE) && $modelodeavaliacao->tipoDeDados != AcpModeloDeAvaliacao::TIPO_NENHUM )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmNotasProfessor'), $ui->getImageTheme($module, 'notas.png'), $module, 'main:notasProfessorPedagogico', null, array('ofertacomponentecurricularid'=>$ofertacomponente->ofertacomponentecurricularid));
            }
            
            if ( SAGU::getParameter('BASIC', 'MOODLE_INSTALLED') == 'YES' )
            {
                if ( $MIOLO->checkAccess('FrmMoodleProfessor', A_ACCESS, FALSE) )
                {
                    $panel->addAction($busTransaction->getTransactionName('FrmMoodleProfessor'), $ui->getImageTheme($module, 'moodle.png'), $module, 'main:inscricaoMoodle', null, array('groupid' => $ofertacomponente->ofertacomponentecurricularid, 'professorid' => $this->personid, 'idmodule' => 'pedagogico'));
                }
            }
        }
        
//        if ( $MIOLO->checkAccess('FrmEstatisticasProfessor', A_ACCESS, FALSE) )
//        {
//            $panel->addAction($busTransaction->getTransactionName('FrmEstatisticasProfessor'), $ui->getImageTheme($module, 'stats.png'), $module, 'main:estatisticaDisciplina', null, array('groupid'=>$disciplina->groupId));
//        }
//        
//        if ( $MIOLO->checkAccess('FrmResultadoFinalProfessor', A_ACCESS, FALSE) )
//        {
//            $panel->addAction($busTransaction->getTransactionName('FrmResultadoFinalProfessor'), $ui->getImageTheme($module, 'resultado.png'), $module, 'main:resultadoFinal', null, array('groupid'=>$disciplina->groupId));
//        }
//        
//        if ( $MIOLO->checkAccess('FrmDocumentosProfessor', A_ACCESS, FALSE) )
//        {
//            $panel->addAction($busTransaction->getTransactionName('FrmDocumentosProfessor'), $ui->getImageTheme($module, 'docs.png'), $module, 'main:documentosProfessor', null, array('groupid'=>$disciplina->groupId));
//        }
//        
//        if ( SAGU::getParameter('BASIC', 'MOODLE_INSTALLED') == 'YES' )
//        {
//            $period = $busLearningPeriod->getLearningPeriod($info->learningPeriodId);
//            
//            if ( $MIOLO->checkAccess('FrmMoodleProfessor', A_ACCESS, FALSE) )
//            {
//                $panel->addAction($busTransaction->getTransactionName('FrmMoodleProfessor'), $ui->getImageTheme($module, 'moodle.png'), $module, 'main:inscricaoMoodle', null, array('groupid' => $disciplina->groupId, 'professorid' => $this->personid, 'periodid' => $period->periodId));
//            }
//        }        
        
        $fields[] = $panel;
        
        return $fields;
    }
    
    
    public function infoDisciplina($disciplina)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        $busTransaction = new BusinessAdminTransaction();
        
        $busLearningPeriod = $MIOLO->getBusiness('academic', 'BusLearningPeriod');
        $info = $disciplina;        
        $period = $busLearningPeriod->getLearningPeriod($info->learningPeriodId);
        $isClosed = MUtil::getBooleanValue($info->isClosed);
        
        $user = $MIOLO->getLogin();
        $filters->mioloUserName = $user->id;
        $busPhysicalPerson = new BusinessBasicBusPhysicalPerson();
        $professor = $busPhysicalPerson->searchPhysicalPerson($filters);
        
        $isProfessorResponsible = $this->verificaProfessorResponsavel($disciplina->groupId, $professor[0][0]);
        
        $fields = array();
        $data = explode('/', $info->startDate);
        $dados[0][0] = $this->diaDaSemana(date('w',strtotime("$data[2]-$data[1]-$data[0]")));
        $dados[0][1] = $info->turnDescription;
        $dados[0][2] = $info->startDate;
        $dados[0][3] = $info->endDate;
        $dados[0][4] = $info->lessonNumberHours;
        
        $columns[0] = _M('Dia');
        $columns[1] = _M('Turno');
        $columns[2] = _M('Inicio');
        $columns[3] = _M('Fim');
        $columns[4] = _M('Carga horária');
        
        $fields[] = $this->listView('info', 'Informação da disciplina', $columns, $dados, $options);
        
        $panel = new mobilePanel('panel');
        $panel->height = '100px';
        $panel->width = '22%';
        $panel->widthIcon = '50px';
        $panel->heightIcon = '50px';
        
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
        {
            $panel->addAction($busTransaction->getTransactionName('FrmMensagensProfessor'), $ui->getImageTheme($module, 'mail.png'), $module, 'main:mensagens', null, array('groupid' => $disciplina->groupId, 'periodid' => MIOLO::_REQUEST('periodid')));
            $panel->addAction($busTransaction->getTransactionName('FrmEstatisticasProfessor'), $ui->getImageTheme($module, 'stats.png'), $module, 'main:estatisticaDisciplina', null, array('groupid' => $disciplina->groupId, 'periodid' => MIOLO::_REQUEST('periodid')));
            $panel->addAction($busTransaction->getTransactionName('FrmResultadoFinalProfessor'), $ui->getImageTheme($module, 'resultado.png'), $module, 'main:resultadoFinal', null, array('groupid' => $disciplina->groupId, 'periodid' => MIOLO::_REQUEST('periodid')));
            $panel->addAction($busTransaction->getTransactionName('FrmDocumentosProfessor'), $ui->getImageTheme($module, 'docs.png'), $module, 'main:documentosProfessor', null, array('groupid' => $disciplina->groupId, 'periodid' => MIOLO::_REQUEST('periodid')));
        }
        else
        {
            if ( $MIOLO->checkAccess('FrmProgramaProfessor', A_ACCESS, FALSE) )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmProgramaProfessor'), $ui->getImageTheme($module, 'programa.png'), $module, 'main:programaProfessor', null, array('groupid'=>$disciplina->groupId));
            }

            if ( $MIOLO->checkAccess('FrmPostagensProfessor', A_ACCESS, FALSE) )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmPostagensProfessor'), $ui->getImageTheme($module, 'postagens.png'), $module, 'main:postagensProfessor', null, array('groupid'=>$disciplina->groupId));
            }

            if ( $MIOLO->checkAccess('FrmMensagensProfessor', A_ACCESS, FALSE) )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmMensagensProfessor'), $ui->getImageTheme($module, 'mail.png'), $module, 'main:mensagens', null, array('groupid'=>$disciplina->groupId));
            }

            if ( $isClosed )
            {
                if ( $MIOLO->checkAccess('FrmFrequenciasEncerradaProfessor', A_ACCESS, FALSE) )
                {
                    $panel->addAction($busTransaction->getTransactionName('FrmFrequenciasEncerradaProfessor'), $ui->getImageTheme($module, 'livro_presenca.png'), $module, 'main:frequenciasProfessor', null, array('groupid'=>$disciplina->groupId), 'return false;');
                }

                if ( $MIOLO->checkAccess('FrmNotasEncerradaProfessor', A_ACCESS, FALSE) )
                {
                    $panel->addAction($busTransaction->getTransactionName('FrmNotasEncerradaProfessor'), $ui->getImageTheme($module, 'notas.png'), $module, 'main:notasProfessor', null, array('groupid'=>$disciplina->groupId), 'return false;');
                }
            }
            else
            {
                if ( $MIOLO->checkAccess('FrmFrequenciasProfessor', A_ACCESS, FALSE) )
                {
                    $panel->addAction($busTransaction->getTransactionName('FrmFrequenciasProfessor'), $ui->getImageTheme($module, 'livro_presenca.png'), $module, 'main:frequenciasProfessor', null, array('groupid'=>$disciplina->groupId));
                }

                if ( $MIOLO->checkAccess('FrmNotasProfessor', A_ACCESS, FALSE) )
                {
                    if ( $isProfessorResponsible == DB_TRUE )
                    {
                        $busGroup = new BusinessAcademicBusGroup();
                        
                        if ( !$busGroup->isFinalExaminationGroup($disciplina->groupId) || ( $period->blockFinalExaminationGradesTyping != DB_TRUE ) )
                        {
                            $panel->addAction($busTransaction->getTransactionName('FrmNotasProfessor'), $ui->getImageTheme($module, 'notas.png'), $module, 'main:notasProfessor', null, array('groupid'=>$disciplina->groupId));
                        }
                    }
                }            
            }

            if ( $MIOLO->checkAccess('FrmEstatisticasProfessor', A_ACCESS, FALSE) )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmEstatisticasProfessor'), $ui->getImageTheme($module, 'stats.png'), $module, 'main:estatisticaDisciplina', null, array('groupid'=>$disciplina->groupId));
            }

            if ( $MIOLO->checkAccess('FrmResultadoFinalProfessor', A_ACCESS, FALSE) )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmResultadoFinalProfessor'), $ui->getImageTheme($module, 'resultado.png'), $module, 'main:resultadoFinal', null, array('groupid'=>$disciplina->groupId));
            }

            if ( $MIOLO->checkAccess('FrmDocumentosProfessor', A_ACCESS, FALSE) )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmDocumentosProfessor'), $ui->getImageTheme($module, 'docs.png'), $module, 'main:documentosProfessor', null, array('groupid'=>$disciplina->groupId));
            }

            if ( SAGU::getParameter('BASIC', 'MOODLE_INSTALLED') == 'YES' )
            {
                if ( $MIOLO->checkAccess('FrmMoodleProfessor', A_ACCESS, FALSE) )
                {
                    $panel->addAction($busTransaction->getTransactionName('FrmMoodleProfessor'), $ui->getImageTheme($module, 'moodle.png'), $module, 'main:inscricaoMoodle', null, array('groupid' => $disciplina->groupId, 'professorid' => $this->personid, 'periodid' => str_replace('/', '_', $period->periodId)));
                }
            }

            if ( $MIOLO->checkAccess('FrmCadastroAvaliacoes', A_ACCESS, FALSE) )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmCadastroAvaliacoes'), $ui->getImageTheme($module, 'evaluation.png'), $module, 'main:avaliacoes', null, array('groupid' => $disciplina->groupId));
            }
        }
        
        $fields[] = $panel;
        
        return $fields;
    }
    
    public function exibeNotas($notas)
    {
        if ( count($notas) == 0 )
        {
            $dataTable[] = array(_M('Nenhum nota lançada.'));
        }
        else
        {            
            foreach ( $notas as $descricao => $nota )
            {                   
                $dataTable[] = array('<b>'.$descricao.': </b>', '<b>'.$nota['nota'].'</b>', '&nbsp;', '&nbsp;');
                if($nota['avaliacoes'])
                {
                    foreach ($nota['avaliacoes'] as $descricao_avaliacao => $nota_avaliacao)
                    {
                        $dataTable[] = array('&nbsp;', '&nbsp;', '<b>'.$descricao_avaliacao.': </b>', '<b>'.$nota_avaliacao.'</b>');
                    }
                }
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
    
    /*
     * Verifica se a pessoa logada e o professor responsavel da disciplina ou tem uma avaliação cadastrada para a mesma.
     * Esta verificacao acontece apenas se o parametro esta habilitado e existe um professor cadastrado com responsavel
     * caso controrio, mantem a funcionalidade original.
     */
    public function verificaProfessorResponsavel($groupId, $personId)
    {
        if ( SAGU::getParameter('ACADEMIC', 'SOMENTE_PROFESSOR_RESPONSAVEL') == DB_FALSE )
        {
            $return = DB_TRUE;
        }
        else
        {
            $busGroup = new BusinessAcademicBusGroup();
            $grupo = $busGroup->getGroup($groupId);
            
            $busEvaluation = new BusinessAcademicBusEvaluation();
            $filters = new stdClass();
            $filters->groupId = $groupId;
            $filters->professorId = $personId;
            
            $avalicao = $busEvaluation->searchEvaluation($filters);
            
            if ( $grupo->professorResponsible )
            {
                if ( $grupo->professorResponsible == $personId || count($avalicao) > 0 )
                {
                    $return = DB_TRUE;
                }
                else
                {
                    $return = DB_FALSE;
                }
            }
            else
            {
                $return = DB_TRUE;
            }
        }
        
        return $return;
    }
}

?>
