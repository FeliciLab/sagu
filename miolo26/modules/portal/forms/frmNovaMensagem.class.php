<?php

$MIOLO->uses('classes/prtUsuario.class.php', $module);
$MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
$MIOLO->uses('forms/frmMobile.class.php', $module);

class frmNovaMensagem extends frmMobile
{
    const PARA_TODOS = 'T';
    const PARA_ALUNO = 'A';
    const PARA_COORDENADOR = 'C';
    const PARA_PROFESSOR = 'P';
    const PARA_TODOS_PROFESSORES = 'TP';
    const PARA_ALUNO_ORIENTACAO = 'O';
    const PARA_TODOS_DISCIPLINA = 'TD';
    const PARA_TODOS_PROFESSORES_DISCIPLINA = 'TPD';
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Nova mensagem'));
    }
    
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $fields[] = $this->button('btvoltarMensagen', _M('Voltar para as mensagens'), null, MUtil::getAjaxAction('voltarMensagem'));
        
        $fields[] = $label = new MLabel(_M('Destinatários:'));
        $label->addStyle('font-weight', 'bold');
        $label->addStyle('color', 'navy');
        $label->addStyle('margin-top', '8px');
        $label->addStyle('margin-left', '10px');
        
        $fields[] = new MDiv('divDestinatarios', $this->destinatario(1));
        
        $fields[] = new MDiv('dests');
        
        $fields[] = new MHiddenField('nrDest',1);
        
        $fields[] = MUtil::centralizedDiv(new MButton('btnAddDestinatario', _M('Adicionar destinatário'), MUtil::getAjaxAction('adicionarDestinatario')));
        
        $fields[] = new MDiv();
        
        $fields[] = $this->fileField('anexo');
        
        $fields[] = $this->mensagem();
        
        $this->autoSave = false;
        
	parent::addFields($fields);
    }
    
    public function destinatario($id=null)
    {
        $label = new MLabel(_M('Para:'));
        $label->addStyle('margin-top', '8px');
        $label->addStyle('margin-left', '30px');
        $select = new MSelection("para[$id]", $value, '', $this->paraDestinatario());
        $select->addAttribute('onchange', MUtil::getAjaxAction('selecionarDestinatario',$id));
        
        if($id)
        {
            $btnRemover = new MButton('btnRmDestinatario', _M('Remover'), MUtil::getAjaxAction('removerDestinatario',$id));
        }
        
        $fields[] = new MHContainer('divComboDestinatario'.$id, array($label, $select, $btnRemover));
        
        $div[] = $div1 =new MDiv('divDestinatario'.$id, $fields);
        $div[] = new MSpacer();
        $div[] = $div2 = new MDiv('divDestinatario_'.$id);
        
        return $div;
    }
    
    public function paraDestinatario()
    {
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
        {
            $options[] = array(self::PARA_TODOS,'Todos os alunos de uma disciplina');
            $options[] = array(self::PARA_ALUNO,'Um aluno de uma disciplina');
            $options[] = array(self::PARA_PROFESSOR,'Professor(es) de uma disciplina');
            $options[] = array(self::PARA_COORDENADOR,'Coordenador de curso');
        }
        elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR )
        {
            $options[] = array(self::PARA_TODOS,'Todos os alunos de uma disciplina');
            $options[] = array(self::PARA_ALUNO,'Um aluno de uma disciplina');
            $options[] = array(self::PARA_PROFESSOR,'Um professor');
            $options[] = array(self::PARA_COORDENADOR,'Coordenador de curso');
            //$options[] = array(self::PARA_ALUNO_ORIENTACAO,'Um aluno de orientação');
        }
        elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
        {
            $options[] = array(self::PARA_TODOS, 'Todos os alunos de um curso');
            $options[] = array(self::PARA_TODOS_PROFESSORES, 'Todos os professores de um curso');
            $options[] = array(self::PARA_TODOS_DISCIPLINA, 'Todos os alunos de uma disciplina');
            $options[] = array(self::PARA_TODOS_PROFESSORES_DISCIPLINA, 'Todos os professores de uma disciplina');
            $options[] = array(self::PARA_PROFESSOR, 'Um professor');
            $options[] = array(self::PARA_ALUNO, 'Um aluno');
            $options[] = array(self::PARA_COORDENADOR, 'Coordenador de curso');
        }
        
        return $options;
    }
    
    public function removerDestinatario($id)
    {
        $this->setResponse(null, 'divDestinatario'.$id);
    }
    
    public function selecionarDestinatario($id)
    {
        $args = $this->getAjaxData();

        if( $args->para[$id] == self::PARA_TODOS )
        {
            $campos[] = $hidden = new MTextField("para[$id]", $args->para[$id]);
            $hidden->setVisibility(false);
            
            if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
            {
                $campos[] = $label = new MLabel(_M('Todos os alunos do curso: '));
                $label->addStyle('margin-top', '8px');
                $campos[] = $this->comboCursos($id, $args->para[$id]);
            }
            else
            {
                $campos[] = $label = new MLabel(_M('Todos os alunos da disciplina: '));
                $label->addStyle('margin-top', '8px');
                $campos[] = $this->comboDisciplinas($id, $args->para[$id]);
            }
            
            if($id)
            {
                $btnRemover = new MButton('btnRmDestinatario', _M('Remover'), MUtil::getAjaxAction('removerDestinatario',$id));
            }
            $campos[] = $btnRemover;
            
            $fields[] = new MHContainer('divTodosAlunos', $campos);
            
            $this->setResponse($fields, 'divComboDestinatario'.$id);
        }
        elseif($args->para[$id] == self::PARA_ALUNO)
        {
            $campos[] = $hidden = new MTextField("para[$id]", $args->para[$id]);
            $hidden->setVisibility(false);
            
            if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
            {
                $campos[] = $label = new MLabel(_M('Para: '));
                $label->addStyle('margin-top', '8px');
                $campos[] = $comboDisciplinas = $this->comboCursos($id, $args->para[$id]);
            }
            else
            {
                $campos[] = $label = new MLabel(_M('Para: '));
                $label->addStyle('margin-top', '8px');
                $campos[] = $comboDisciplinas = $this->comboDisciplinas($id, $args->para[$id]);
            }
            
            $comboDisciplinas->addAttribute('onchange', MUtil::getAjaxAction('comboAlunos',array('divId'=>$id)));
            
            $campos[] = new MDiv('divComboDestinatarioAluno'.$id);
            
            if($id)
            {
                $btnRemover = new MButton('btnRmDestinatario', _M('Remover'), MUtil::getAjaxAction('removerDestinatario',$id));
            }
            $campos[] = $btnRemover;
            
            $fields[] = new MHContainer('divUmAlunos', $campos);
            
            $this->setResponse($fields, 'divComboDestinatario'.$id);
        }
        elseif($args->para[$id] == self::PARA_PROFESSOR)
        {
            $campos[] = $hidden = new MTextField("para[$id]", $args->para[$id]);
            $hidden->setVisibility(false);
            
            if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
            {
                $campos[] = $label = new MLabel(_M('Para o(s) professor(es) da disciplina: '));
                $label->addStyle('margin-top', '8px');
                $campos[] = $comboDisciplinas = $this->comboDisciplinas($id, $args->para[$id]);
            }
            else
            {
                $campos[] = $label = new MLabel(_M('Para o professor: '));
                $label->addStyle('margin-top', '8px');
                $campos[] = $comboDisciplinas = $this->comboProfessores($id, $args->para[$id]);
            }
            
            if($id)
            {
                $btnRemover = new MButton('btnRmDestinatario', _M('Remover'), MUtil::getAjaxAction('removerDestinatario',$id));
            }
            $campos[] = $btnRemover;
            $fields[] = new MHContainer('divProfessor', $campos);
            
            $this->setResponse($fields, 'divComboDestinatario'.$id);
        }
        elseif($args->para[$id] == self::PARA_TODOS_PROFESSORES)
        {
            $campos[] = $hidden = new MTextField("para[$id]", $args->para[$id]);
            $hidden->setVisibility(false);
            
            $campos[] = $label = new MLabel(_M('Todos os professores do curso: '));
            $label->addStyle('margin-top', '8px');
            $campos[] = $this->comboCursos($id, $args->para[$id]);
            
            if($id)
            {
                $btnRemover = new MButton('btnRmDestinatario', _M('Remover'), MUtil::getAjaxAction('removerDestinatario',$id));
            }
            $campos[] = $btnRemover;
            $fields[] = new MHContainer('divTodosProfessores', $campos);
            
            $this->setResponse($fields, 'divComboDestinatario'.$id);
        }
        elseif($args->para[$id] == self::PARA_COORDENADOR)
        {
            $campos[] = $hidden = new MTextField("para[$id]", $args->para[$id]);
            $hidden->setVisibility(false);
            
            $campos[] = $label = new MLabel(_M('Para o(s) coordenador(es) do curso: '));
            $label->addStyle('margin-top', '8px');
            
            $campos[] = $comboCursos = $this->comboCursos($id, $args->para[$id]);
            
            if($id)
            {
                $btnRemover = new MButton('btnRmDestinatario', _M('Remover'), MUtil::getAjaxAction('removerDestinatario',$id));
            }
            $campos[] = $btnRemover;
            
            $fields[] = new MHContainer('divCoordenador', $campos);
            
            $this->setResponse($fields, 'divComboDestinatario'.$id);
        }
        elseif( $args->para[$id] == self::PARA_TODOS_DISCIPLINA )
        {
            $campos[] = $hidden = new MTextField("para[$id]", $args->para[$id]);
            $hidden->setVisibility(false);
            
            $campos[] = $label = new MLabel(_M('Todos os alunos da disciplina: '));
            $label->addStyle('margin-top', '8px');
            $campos[] = $this->comboDisciplinas($id, $args->para[$id]);
            
            if($id)
            {
                $btnRemover = new MButton('btnRmDestinatario', _M('Remover'), MUtil::getAjaxAction('removerDestinatario',$id));
            }
            $campos[] = $btnRemover;
            
            $fields[] = new MHContainer('divTodosAlunos', $campos);
            
            $this->setResponse($fields, 'divComboDestinatario'.$id);
        }
        elseif( $args->para[$id] == self::PARA_TODOS_PROFESSORES_DISCIPLINA )
        {
            $campos[] = $hidden = new MTextField("para[$id]", $args->para[$id]);
            $hidden->setVisibility(false);
            
            $campos[] = $label = new MLabel(_M('Todos os professores da disciplina: '));
            $label->addStyle('margin-top', '8px');
            $campos[] = $this->comboDisciplinas($id, $args->para[$id]);
            
            if($id)
            {
                $btnRemover = new MButton('btnRmDestinatario', _M('Remover'), MUtil::getAjaxAction('removerDestinatario',$id));
            }
            $campos[] = $btnRemover;
            
            $fields[] = new MHContainer('divTodosProfessores', $campos);
            
            $this->setResponse($fields, 'divComboDestinatario'.$id);
        }
        elseif($args->para[$id] == self::PARA_ALUNO_ORIENTACAO)
        {
            // TODO
            $this->setResponse($fields, 'divComboDestinatario'.$id);
        }
    }
    
    public function adicionarDestinatario($args=null)
    {
        $args = $this->getAjaxData();
        $nrDest = $args->nrDest;
        $proximoDest = $args->nrDest + 1;
        
        $combo = new MDiv("divDestinatario_$nrDest",$this->destinatario($proximoDest));
        $combo->addStyle('margin', '0');
//        $combo = $combo->generate();
//        
//        $combo = str_replace("'", "\'", $combo);
//        $combo = preg_replace('/\s+/', ' ', $combo);
//        $combo = preg_replace("/\n+/", "", $combo);

        $jscode = " document.getElementById('nrDest').value = '$proximoDest'; ";                    
        
        $this->addJsCode($jscode);
        $this->setResponse($combo, "divDestinatario_$nrDest");
    }
    
    public function comboDisciplinas($id, $para = NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        
        if ( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' )
        {
            $disciplinas = new PrtDisciplinasPedagogico();
            
            if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
            {
                $data = $disciplinas->obterGradeDeHorariosDoProfessor($this->personid);
            }
            elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
            {
                $data = $disciplinas->obterGradeDeHorariosDoAluno($this->personid);
            }
            elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR )
            {
                $data = $disciplinas->obterGradeDeHorariosDoProfessor($this->personid);
            }
            
            $options = NULL;
        
            foreach($data as $d)
            {
                $options[$d[0]] = array('PEDAGOGICO_' . $d[0],$d[1]);
            }
        }
        
        $disciplinas = new PrtDisciplinas();

        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
        {
            $data = $disciplinas->obterDisciplinasDoCoordenador($this->personid);
        }
        elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
        {
            $data = $disciplinas->obterDisciplinasMatriculadas($this->personid);
        }
        elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR )
        {
            $data = $disciplinas->obterDisciplinasDoProfessor($this->personid);
        }

        $groupId = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');
        if ( strlen($groupId) > 0 )
        {
            $nomeDaDisciplina = $disciplinas->obterNomeDisciplina($groupId);
                $options[] = array('ACADEMICO_' . $groupId,$nomeDaDisciplina);
        }
        else
        {        
            foreach($data as $d)
            {
                $nomeDaDisciplina = $disciplinas->obterNomeDisciplina($d);
                    $options[] = array('ACADEMICO_' . $d,$nomeDaDisciplina);
            }
        }
        
        if ( !$options )
        {
            $options = array(_M('Nenhuma disciplina encontrada'));
        }
        
        
        //Hint do MSelection
        $hint = "";
        
        if ( $para == self::PARA_TODOS 
          || $para == self::PARA_TODOS_DISCIPLINA)
        {
            $hint = _M("Envia para todos alunos matriculados em determinada disciplina no período.");
        }
        
        if ($para == self::PARA_TODOS_PROFESSORES_DISCIPLINA
         || $para == self::PARA_PROFESSOR)
        {
            $hint = _M("Envia para todos professores ativos de determinado curso no período.");
        }
        
        return new MSelection("disciplina[$id]", null, null, $options, NULL, $hint);
    }
    
    public function comboAlunos($ajax=true)
    {
        $hint = _M("Envia mensagem para determinado aluno ativo do curso.");
        if($ajax)
        {
            $args = $this->getAjaxData();
        }
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busProfessorFrequency = $MIOLO->getBusiness('services', 'BusProfessorFrequency');
        
        $options = array();
        
        //foreach por alunos
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
        {
            $busPupilListing = new BusinessAcademicBusPupilListing();
            
            $alunos = $busPupilListing->obterAlunosDoCurso($args->curso[$args->nrDest]);
            
            foreach($alunos as $aluno)
            {
                $options[] = array($aluno[0],$aluno[1]); 
            }
        }
        else
        {
            if ( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' )
            {
                $disciplina = new PrtDisciplinasPedagogico();
                
                if ( preg_match('/PEDAGOGICO_/', $args->disciplina[$args->nrDest]) )
                {
                    $alunos = $disciplina->obterAlunosDaDisciplina(substr($args->disciplina[$args->nrDest], strlen('PEDAGOGICO_')));

                    foreach($alunos as $aluno)
                    {
                        $options[] = array($aluno[0],$aluno[1]); 
                    }
                }
            }
            
            if ( preg_match('/ACADEMICO_/', $args->disciplina[$args->nrDest]) )
            {
                $alunos = $busProfessorFrequency->listGroupPupilsEnrolled(substr($args->disciplina[$args->nrDest], strlen('ACADEMICO_')));
                foreach($alunos as $aluno)
                {
                    $options[] = array($aluno[1],$aluno[0]); 
                }
            }
        }
        
        if ( !$options )
        {
            $options = array(_M('Nenhum aluno encontrado'));
        }

        if(!$ajax)
        {
            return NULL;
        }
        else
        {
            $retorno = new MSelection("aluno[{$args->nrDest}]", null, _M('Aluno'), $options, NULL, $hint);
            $this->setResponse($retorno, 'divComboDestinatarioAluno'.$args->nrDest);
        }
    }
    
    public function comboProfessores($id, $para = NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $prtDisciplinas = new PrtDisciplinas();
        
        if ( $para == self::PARA_PROFESSOR )
        {
            $hint = _M("Envia mensagem para determinado professor ativo do curso.");
        }
        
        $listaDeProfessores = $prtDisciplinas->obterListaDeProfessores($this->personid);
        
        return new MSelection("professor[$id]", null, null, $listaDeProfessores, NULL, $hint);
    }
    
    public function comboCursos($id, $para = NULL)
    {    
        $busCursos = new BusinessAcademicBusCourseOccurrence();
        $filters = new stdClass();
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
        {
            $filters->coordinatorId = $this->personid;
        }
        else
        {
            $filters->alunoId = $this->personid;
        }
        
        $cursos = NULL;
        $cursosPedagogico = NULL;
        
        if ( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' )
        {
            $prtDisciplinasPedagogico = new PrtDisciplinasPedagogico();
            
            $cursosPedagogico = $prtDisciplinasPedagogico->obterCursos();
        }
        
        $ocorrencias = $busCursos->listCourseOccurrence($filters);

        foreach ( $ocorrencias as $ocorrencia )
        {
            $coordenadorCurso = $ocorrencia[16];
            $unidade = $ocorrencia[15];

            $descricaoCurso = $ocorrencia[14] . ' - ' . $unidade . ' - ' . $coordenadorCurso;

            $cursos[$ocorrencia[0]] = array($ocorrencia[0], $descricaoCurso);
        }
        
        $cursos = array_merge($cursos, $cursosPedagogico);
        
        if ( !$cursos )
        {
            $cursos = array(_M('Nenhum curso encontrado'));
        }
        
        //Hint do MSelection
        $hint = _M("");
        if ( $para == self::PARA_COORDENADOR )
        {
            $hint = _M("Envia mensagem para o coordenador ativo de determinado curso.");
        }

        if ( $para == self::PARA_TODOS_PROFESSORES )
        {
            $hint = _M("Envia para todos professores ativos de determinado curso no período.");
        }

        if ( $para == self::PARA_TODOS )
        {
            $hint = _M("Envia para todas alunos matriculados de determinado curso no período.");
        }
        
        return new MSelection("curso[$id]", null, null, $cursos, NULL, $hint);
    }
    
    public function mensagem()
    {
        $label = new MLabel(_M('Mensagem:'));
        $label->addStyle('font-weight', 'bold');
        $label->addStyle('color', 'navy');
        $label->addStyle('margin-top', '8px');
        $label->addStyle('margin-left', '10px');
        
        $msg = new MMultiLineField('mensagem', $value, null, SAGU::getParameter('BASIC', 'FIELD_DESCRIPTION_SIZE'), 2, SAGU::getParameter('BASIC', 'FIELD_DESCRIPTION_SIZE'));
        $msg->setWidth('98%');
        $msg->addStyle('margin-left', '10px');
        $msg->addStyle('margin-right', '10px');
        
        $btnEnviar = new MButton('btnEnviar', _M('Enviar mensagem'));
                
        $fields[] = new MDiv();
        $fields[] = $label;
        $fields[] = $msg;
        $fields[] = MUtil::centralizedDiv($btnEnviar);
        
        return new MDiv('divMensagem', $fields);
    }
    
    public function btnEnviar_click($args)
    {
        try
        {
            if ( strlen($args->mensagem) > 0 && count($args->para) > 0 )
            {
                $MIOLO = MIOLO::getInstance();
                $module = MIOLO::getCurrentModule();
                $ui = $MIOLO->getUI();
                $erro = NULL;

                $MIOLO->uses('types/PrtMensagem.class.php', $module);
                $MIOLO->uses('types/PrtMensagemDestinatario.class.php', $module);
                $MIOLO->uses('classes/prtDisciplinas.class.php', $module);

                $mensagem = new PrtMensagem();

                if ( $args->anexo )
                {
                    $uploaded = NULL;
                    $uploaded = MFileField::uploadFiles($MIOLO->getConf('home.html') . "/files/tmp/");
                }

                $mensagem->unitid = $this->unitid;
                $mensagem->remetenteid = $this->personid;
                $mensagem->conteudo = $args->mensagem;
                
                //Insere os anexos na base (via type PrtAnexo)
                $mensagemId = $mensagem->inserir(NULL, $_REQUEST['uploadInfo']);

                if ( $mensagemId )
                {
                    $envio = NULL;
                    foreach ( $args->para as $key => $para )
                    {
                        if ( preg_match('/PEDAGOGICO_/', $args->disciplina[$key]) )
                        {
                            $disciplina = new PrtDisciplinasPedagogico();
                            $args->disciplina[$key] = substr($args->disciplina[$key], strlen('PEDAGOGICO_'));
                        }
                        else
                        {
                            $disciplina = new PrtDisciplinas();
                            $args->disciplina[$key] = substr($args->disciplina[$key], strlen('ACADEMICO_'));
                        }
                        
                        $mensagemDestinatario = new PrtMensagemDestinatario();

                        $mensagemDestinatarioData = new stdClass();
                        $mensagemDestinatarioData->groupid = $args->disciplina[$key];
                        $mensagemDestinatarioData->mensagemid = $mensagemId;
                        $mensagemDestinatarioData->courseid = NULL;

                        if( $para == self::PARA_TODOS )
                        {
                            if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
                            {
                                $busPupilListing = new BusinessAcademicBusPupilListing();
            
                                $alunos = $busPupilListing->obterAlunosDoCurso($args->curso[$key]);
                            }
                            else
                            {
                                $alunos = $disciplina->obterAlunosDaDisciplina($args->disciplina[$key]);
                            }

                            // Enviar para todos os alunos de uma disciplina
                            if ( is_array($alunos) )
                            {
                                foreach ( $alunos as $aluno )
                                {
                                    $mensagemDestinatarioData->personid = $aluno[0];
                                    $mensagemDestinatarioData->courseid = $args->curso[$key];
                                    $envio = $mensagemDestinatario->salvar($mensagemDestinatarioData);
                                }
                            }
                            else
                            {
                                $erro = _M('Selecione a disciplina.');
                            }
                        }
                        elseif ( $para == self::PARA_TODOS_DISCIPLINA )
                        {
                            $alunos = $disciplina->obterAlunosDaDisciplina($args->disciplina[$key]);
                            
                            // Enviar para todos os alunos de uma disciplina
                            if ( is_array($alunos) )
                            {
                                foreach ( $alunos as $aluno )
                                {
                                    $mensagemDestinatarioData->personid = $aluno[0];
                                    $envio = $mensagemDestinatario->salvar($mensagemDestinatarioData);
                                }
                            }
                            else
                            {
                                $erro = _M('Selecione a disciplina.');
                            }
                        }
                        elseif( $para == self::PARA_ALUNO )
                        {
                            //para um aluno de uma disciplina
                            $mensagemDestinatarioData->personid = $args->aluno[$key];
                            $mensagemDestinatarioData->courseid = $args->curso[$key];
                            if ( $mensagemDestinatarioData->personid )
                            {
                                $envio = $mensagemDestinatario->salvar($mensagemDestinatarioData);
                            }
                            else
                            {
                                $erro = _M('Selecione o destinatário.');
                            }
                        }
                        elseif( $para == self::PARA_PROFESSOR )
                        {
                            if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
                            {
                                $busEnroll = $MIOLO->getBusiness('academic', 'BusEnroll');
                                $professor = $busEnroll->getGroupProfessor($args->disciplina[$key], TRUE);

                                if ( is_array($professor) )
                                {
                                    foreach($professor as $prof)
                                    {
                                        $mensagemDestinatarioData->personid = $prof[0];
                                        $mensagemDestinatarioData->courseid = $args->curso[$key];
                                        $envio = $mensagemDestinatario->salvar($mensagemDestinatarioData);
                                    }
                                }
                                else
                                {
                                    $erro = _M('Selecione a disciplina.');
                                }
                            }
                            else
                            {
                                $mensagemDestinatarioData->personid = $args->professor[$key];
                                $mensagemDestinatarioData->courseid = $args->curso[$key];
                                if ( $mensagemDestinatarioData->personid )
                                {
                                    $envio = $mensagemDestinatario->salvar($mensagemDestinatarioData);
                                }
                                else
                                {
                                    $erro = _M('Selecione o professor.');
                                }
                            }
                        }
                        elseif ( $para == self::PARA_TODOS_PROFESSORES_DISCIPLINA )
                        {
                            if ( !$args->disciplina[$key] )
                            {
                                $erro = _M('Selecione a disciplina.');
                            }
                            else
                            {
                                $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
                                $professores = $busGroup->obterProfessoresDaDisciplina($args->disciplina[$key]);
                                
                                // Enviar para todos os professores de uma disciplina
                                if ( is_array($professores) )
                                {
                                    foreach ( $professores as $professor )
                                    {
                                        $mensagemDestinatarioData->personid = $professor[0];
                                        $envio = $mensagemDestinatario->salvar($mensagemDestinatarioData);
                                    }
                                }
                            }
                        }
                        elseif ( $para == self::PARA_TODOS_PROFESSORES )
                        {
                            if ( !$args->curso[$key] )
                            {
                                $erro = _M('Selecione o curso.');
                            }
                            else
                            {
                                $busCourse = $MIOLO->getBusiness('academic', 'BusCourse');
                                $professores = $busCourse->obterProfessoresDoCurso($args->curso[$key]);
                                
                                // Enviar para todos os alunos de uma disciplina
                                if ( is_array($professores) )
                                {
                                    foreach ( $professores as $professor )
                                    {
                                        $mensagemDestinatarioData->personid = $professor[0];
                                        $mensagemDestinatarioData->courseid = $args->curso[$key];
                                        $envio = $mensagemDestinatario->salvar($mensagemDestinatarioData);
                                    }
                                }
                            }                            
                        }
                        elseif( $para == self::PARA_COORDENADOR )
                        {
                            $busCourseCoordinator = $MIOLO->getBusiness('academic', 'BusCourseCoordinator');
                            $filter->courseId = $args->curso[$key];
                            if ( !$filter->courseId )
                            {
                                $erro = _M('Selecione o curso.');
                            }
                            else
                            {
                                if ( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' )
                                {
                                    $prtDisciplinas = new PrtDisciplinasPedagogico();
                                    $coordenadoresPedagogico = $prtDisciplinas->obterCoordenadoresDoCurso($filter->courseId);
                                    
                                    if ( is_array($coordenadoresPedagogico) )
                                    {
                                        foreach($coordenadoresPedagogico as $coordenador)
                                        {
                                            $mensagemDestinatarioData->groupid = NULL;
                                            $mensagemDestinatarioData->courseid = $filter->courseId;
                                            $mensagemDestinatarioData->personid = $coordenador[0];
                                            $envio = $mensagemDestinatario->salvar($mensagemDestinatarioData);
                                        }
                                    }
                                }
                                else if ( SAGU::getParameter('BASIC', 'MODULE_ACADEMIC_INSTALLED') == 'YES' )
                                {
                                    $coordenadores = $busCourseCoordinator->searchCourseCoordinator($filter);
                                    
                                    if ( is_array($coordenadores) )
                                    {
                                        foreach($coordenadores as $coordenador)
                                        {
                                            $mensagemDestinatarioData->groupid = NULL;
                                            $mensagemDestinatarioData->courseid = $filter->courseId;
                                            $mensagemDestinatarioData->personid = $coordenador[8];
                                            $envio = $mensagemDestinatario->salvar($mensagemDestinatarioData);
                                        }
                                    }
                                }
                                
                                if ( !is_array($coordenadores) && !is_array($coordenadoresPedagogico) )
                                {
                                    $erro = _M('Não há um coordenador definido para este curso.');
                                }
                            }
                        }
                        else
                        {
                            $erro = _M('Selecione um destinatário.');
                            $erro = utf8_decode($erro);
                        }
                    }
                }

                //Se não enviou é porque deu erro, vamos exibí-lo
                if ( $envio === true )
                {
                    new MMessageSuccess(_M('Mensagem enviada com sucesso'));
                }
                else
                {
                    if ( strlen($erro) > 0 )
                    {
                        $msgErro = $erro . '</br>';
                    }
                    
                    if ( strlen($envio) )
                    {
                        $msgErro .= _M($envio);
                    }
                    
                    new MMessageError(utf8_encode($msgErro));
                }
            }
            else
            {
                if ( strlen($args->mensagem) > 0 )
                {
                    new MMessageWarning(_M('Informe pelo menos um destinatário.'));
                }
                else
                {
                    new MMessageWarning(_M("Preencha o campo 'Mensagem'."));
                }
            }
        }
        catch(Exception $e)
        {
            new MMessageError($e->getMessage());
        }
        
        $this->setNullResponseDiv();
    }
    
    public function voltarMensagem()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->page->redirect($MIOLO->getActionURL($module, 'main:mensagens', null, array('groupid' => MIOLO::_REQUEST('groupid'))));
    }
    
}


?>
