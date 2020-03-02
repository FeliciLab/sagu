<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 * Luís Felipe Wermann [luis_felipe@solis.com.br]
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
class frmProgramaProfessor extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Programa', MIOLO::getCurrentModule()));

        $this->setShowPostButton(FALSE);
    }

    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->autoSave = false;

        $sections[] = new jCollapsibleSection(_M('Plano de curso'), $this->planoDeCurso());
        $sections[] = new jCollapsibleSection(_M('Cronograma previsto'), $this->cronograma());

        #TODO: liberar depois de fazer o componente agrider funcionar
        //$sections[] = new jCollapsibleSection(_M('Avaliação'), $this->avaliacao());
        
        //if ( SAGU::getParameter('BASIC', 'MODULE_GNUTECA_INSTALLED') == 'YES' )
        {
            $sections[] = new jCollapsibleSection(_M('Bibliografia'), $this->bibliografia());
        }
        
        $fields[] = new jCollapsible('programas', $sections);
        
        $this->addJsCode("var cp = '';");

        parent::addFields($fields);
    }
    
    public function planoDeCurso()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $info = $busGroup->getGroup(MIOLO::_REQUEST('groupid'));
        
        $professor = $this->retornaPersonIdPessoaLogada();
        $isProfessorResponsible = $this->verificaProfessorResponsavel($info->groupId, $professor);
        
        $busCurricularComponent = $MIOLO->getBusiness('academic', 'BusCurricularComponent');
	$curricularComponent = $busCurricularComponent->getCurricularComponent($info->curriculumCurricularComponentId,$info->curriculumCurricularComponentVersion);
        
        $fields[] = new MLabel(_M('Ementa'));
        $fields[] = $ementa = new MMultiLineField('', $curricularComponent->summary, NULL, 40, 6, 40);
        $ementa->setReadOnly(true);
        
        $fields[] = new MLabel(_M('Objetivos'));
        if(!$info->objectives)
        {
            $objetives = $curricularComponent->generalobjectives;
        }
        else
        {
            $objetives = $info->objectives;
        }
        $fields[] = $objetivos = new MMultiLineField('', $objetives, NULL, 40, 6, 40);        
        $objetivos->setReadOnly(true);
        
        $fields[] = new MLabel(_M('Metodologias de aula'));
        $metodologiaAula = new MMultiLineField('metodologiaAula', $info->methodology, _M('Metodologias de aula'), 40, 6, 40);        
        $isProfessorResponsible == DB_FALSE ? $metodologiaAula->setReadOnly(true) : $metodologiaAula->addAttribute('onblur', MUtil::getAjaxAction('salvar'));
        $fields[] = $metodologiaAula;
        
        $fields[] = new MLabel(_M('Metodologias de avaliação'));
        $metodologiaAvalicao = new MMultiLineField('metodologiaAvaliacao', $info->evaluation, _M('Metodologias de avaliação'), 40, 6, 40);        
        $isProfessorResponsible == DB_FALSE ? $metodologiaAvalicao->setReadOnly(true) : $metodologiaAvalicao->addAttribute('onblur', MUtil::getAjaxAction('salvar'));
        $fields[] = $metodologiaAvalicao;
        
        $fields[] = new MLabel(_M('Observações'));
        $observarcoes = new MMultiLineField('observacoes', $info->observation, _M('Observações'), 40, 6, 40);
        $isProfessorResponsible == DB_FALSE ? $observarcoes->setReadOnly(true) : $observarcoes->addAttribute('onblur', MUtil::getAjaxAction('salvar'));
        $fields[] = $observarcoes;

        return $fields;
    }
    
    public function cronograma()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $MIOLO->uses('types/PrtCronogramaProfessor.class.php', $module);
        
        //Professor responsável
        $isProfessorResponsible = $this->verificaProfessorResponsavel(MIOLO::_REQUEST('groupid'), $this->personid);

        //Datas da disciplina
        $disciplinas = new PrtDisciplinas();
        $datas = $disciplinas->obterCronograma(MIOLO::_REQUEST('groupid'), null, $professor);
        foreach($datas as $data)
        {
            //Senão está repetindo o dia (podem haver dois, ou mais, horários)
            if ( $dia != $data[0] )
            {
                $dia = $data[0];
                $id = base64_encode($dia . ';' . $data[5] . ';' . $data[6]);

                //Cronograma previsto
                $prtCronograma = new PrtCronogramaProfessor(MIOLO::_REQUEST('groupid'), $this->personid, NULL, $dia);

                //Label
                $fields[] = new MLabel($dia);
                
                //Conteúdo
                $cronograma = new MMultiLineField("cronograma_$id", $prtCronograma->obterConteudoCronograma(), null, 20, 3);
                
                //Permissões do professor responsável
                $isProfessorResponsible == DB_TRUE ? $cronograma->addAttribute('onblur', MUtil::getAjaxAction('salvar')) : $cronograma->setReadOnly(true);
                
                $fields[] = $cronograma;
                
                //Botões especiais do 
                if( $isProfessorResponsible == DB_TRUE )
                {
                    $fields[] = new MButton('copiar', _M('Copiar'), "cp = document.getElementById('cronograma_{$id}').value;");
                    $fields[] = $btColar = new MButton('colar', _M('Colar'), "document.getElementById('cronograma_{$id}').value = cp;".MUtil::getAjaxAction('salvar'));
                    $fields[] = $btExcluir = new MButton('btnExcluirCronograma' . $id, _M("Excluir", $module), MUtil::getAjaxAction('excluirCronograma', array('groupId' => MIOLO::_REQUEST('groupid'), 'personId' => $this->personid, 'dataAula' => $dia, 'idCampo' => $id)));
                }
            }
        }
        
        return $fields;
    }
    
    public function avaliacao()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busDegree = $MIOLO->getBusiness('academic', 'BusDegree');
        $busEvaluation = $MIOLO->getBusiness('academic', 'BusEvaluation');
        
        $fields[] = new MLabel(_M('Descrição'));
        $fields[] = new MTextField('descricao', $data[4]);
        
        $fields[] = new MLabel(_M('Forma de cálculo'));
        
        $dgs = $busDegree->getEnrollDegree(MIOLO::_REQUEST('groupid'));
                
        if(  count($dgs)>0 )
        {
            foreach($dgs as $d)
            {
                if(  strlen($d[0])>0 )
                {
                    $degree = $busDegree->getDegree($d[0]);

                    #TODO: verificar se isto esta correto
                    if($degree->parentDegreeId > 1)
                    {
                        $fields_[] = $this->controlGroup( 'degree_'.$degree->degreeId, $degree->description, array(BusinessAcademicBusDegree::CALCULO_SOMA=>_M('Somatório'),
                                                                              BusinessAcademicBusDegree::CALCULO_MEDIA_SIMPLES=>_M('Média simples'),
                                                                              BusinessAcademicBusDegree::CALCULO_MEDIA_PONDERADA=>_M('Média ponderada'))
                                                           );

                        $fields_[] = new MSpacer();

                        $fields_[] = $this->obterAvaliacoes($degree->degreeId);

                        $fields[] = new MBaseGroup('bg'.$degree->degreeId, $degree->description, $fields_);
                        $fields_ = null;
                        $dataTable = null;
                    }
                }
            }
        }
        
        //$fields[] = new MBaseGroup('bgAvaliacoes', _M('Avaliações'));
        
        return $fields;
    }
    
    public function bibliografia()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
           $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $info = $busGroup->getGroup(MIOLO::_REQUEST('groupid'));
        
        $professor = $this->retornaPersonIdPessoaLogada();
        $isProfessorResponsible = $this->verificaProfessorResponsavel($info->groupId, $professor);
        
        $busCurricularComponent = $MIOLO->getBusiness('academic', 'BusCurricularComponent');
	$curricularComponent = $busCurricularComponent->getCurricularComponent($info->curriculumCurricularComponentId,$info->curriculumCurricularComponentVersion);

        if($info->basicbibliography)
        {
            $basicBiblio = $info->basicbibliography;
            $compBiblio = $info->complementarybibliography;
        }
        else
        {
            $basicBiblio = $curricularComponent->basicbibliography;
            $compBiblio = $curricularComponent->complementarybibliography;
        }
        
        $fields[] = new MLabel(_M('Bibliografia básica'));
        $bibliografiaBas = new MMultiLineField('bibliografiaBas', $basicBiblio, _M('Bibliografia básica'), 40, 6, 40);        
        $bibliografiaBas->setReadOnly(true);
        $fields[] = $bibliografiaBas;
        
        $fields[] = new MLabel(_M('Bibliografia complementar'));
        $bibliografiaComp = new MMultiLineField('bibliografiaComp', $compBiblio, _M('Bibliografia complementar'), 40, 6, 40);        
        $bibliografiaComp->setReadOnly(true);
        $fields[] = $bibliografiaComp;

        return $fields;
    }
    
    public function obterAvaliacoes($degreeId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busEvaluation = $MIOLO->getBusiness('academic', 'BusEvaluation');

        $filter->degreeId = $degree->degreeId; //160
        $evaluations = $busEvaluation->searchEvaluation($filter);
        
        foreach($evaluations as $k=>$evolualtion)
        {
            $data[$k]->desc = $evolualtion[2];
            $data[$k]->peso = $evolualtion[5];
            $data[$k]->evoluationid = $evolualtion[0];
            $data[$k]->degreeid = $degreeId;
        }

        $controls[] = new MTextField('desc', '', _M('Descrição'));
        $controls[] = new MIntegerField('peso', '', _M('Peso'));
        $controls[] = new MHiddenField('evoluationid');
        $controls[] = new MHiddenField('degreeid');

        $grider = new MGrider(_M('Avaliações'), $controls, null, 'avaliacoes'.$degreeId);
        $grider->setCountRow(true);
        $grider->setCountRowText('Nº do item');
        $grider->setWidth('750px');

        if ( $data )
        {
            $grider->setData($data);
        }

        return $grider;
    }
    
    public function listaBibliografiaBasica()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $professor = $this->retornaPersonIdPessoaLogada();
        $isProfessorResponsible = $this->verificaProfessorResponsavel(MIOLO::_REQUEST('groupid'), $professor);
        
        MUtil::MDEBUG($disciplinas->obterBibliografiaBasica(MIOLO::_REQUEST('groupid')), 1);
        
        
        foreach($disciplinas->obterBibliografiaBasica(MIOLO::_REQUEST('groupid')) as $bibliografia)
        {
            $textfield = new MTextField('bibliografiaBasica[]', $bibliografia[0]);
            $textfield->width = '700px';
            $fields_[] = $textfield;
            
            $isProfessorResponsible == DB_FALSE ? $textfield->setReadOnly(true) : $textfield->setReadOnly(false); 
            
            //addslashes para caso tenha aspas simples na bibliografia tratar ajax
            $action = MUtil::getAjaxAction('removerBibliografiaBasica', addslashes($bibliografia[0]) );
            
            if( $isProfessorResponsible == DB_TRUE )
            {
                $fields_[] = $button = new MButton($name, 'Remover', $action, $image);
                $button->addBoxStyle('float', 'left');
                $button->addBoxStyle('width', '100px');
            }
        }
        
        $fields[] = $container = new MHContainer(null, $fields_);
        $container->addBoxStyle('width', '850px');
        
        return $fields;
    }
    
    public function removerBibliografiaBasica($desc=null)
    {
        //caso exista descrição o addslashes pode ser removido
        $desc = $desc ? stripslashes($desc) : null;
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $groupid = MIOLO::_REQUEST('groupid');
        
        $disciplinas->removerBibliografiaBasica($desc, $groupid);
        
        $this->setResponse($this->listaBibliografiaBasica(), 'bbasica_itens');
    }
    
    public function exibirBibliografiaBasica()
    {   
        $professor = $this->retornaPersonIdPessoaLogada();
        $isProfessorResponsible = $this->verificaProfessorResponsavel(MIOLO::_REQUEST('groupid'), $professor);
        
        $fields[] = new MDiv('bbasica_itens', $this->listaBibliografiaBasica());
        
        $fields[] = new MSpacer();
        
        if( $isProfessorResponsible == DB_TRUE )
        {
            $fields[] = new MButton('adicionarBibliografiaBasica', _M('Adicionar'), MUtil::getAjaxAction('adicionarBibliografiaBasica'));
        }
            
        return $fields;
    }
    
    public function adicionarBibliografiaBasica()
    {
        $fields[] = new MTextField('bibliografiaBasicaTitulo', '', 'Título');
        $fields[] = new MTextField('bibliografiaBasicaAutor', '', 'Autor');
        $fields[] = new MTextField('bibliografiaBasicaDescricao', '', 'Descrição');
        
        $botoes[] = new MButton('botaoSalvar', _M('Salvar'), MUtil::getAjaxAction('salvarBibliografiaBasica'));
        $botoes[] = new MButton('botaoCancelar', _M('Cancelar'), "dijit.byId('dialogAdicionarBibliografiaBasica').hide();");
        $fields[] = MUtil::centralizedDiv($botoes);

        $dialog = new MDialog('dialogAdicionarBibliografiaBasica', _M('Adicionar Bibliografia Básica'), $fields);
        $dialog->setWidth('700px');
        $dialog->show();
    }
    
    public function salvarBibliografiaBasica($args)
    {
        MDialog::close('dialogAdicionarBibliografiaBasica');
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $args = $this->getAjaxData();
        
        //Verifica se o titulo e o autor foram setados
        if(strlen ($args->bibliografiaBasicaTitulo) > 0 && strlen ($args->bibliografiaBasicaAutor) > 0)
        {
            $descricao = $args->bibliografiaBasicaTitulo.' - '.$args->bibliografiaBasicaAutor;
            
            //Só concatena descrição caso ela exista
            if(strlen ($args->bibliografiaBasicaDescricao) > 0)
            {
                $descricao .= ' - '.$args->bibliografiaBasicaDescricao;
            }
            
            $disciplinas->salvarBibliografiaBasica($descricao, MIOLO::_REQUEST('groupid'));

            $this->setResponse($this->listaBibliografiaBasica(), 'bbasica_itens');
        }  
        else
        {
            new MMessageError(_M('Título e Autor devem ser informados!'));
        }
        
            
    }
    
    public function listarBibliografiaComplementar()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $professor = $this->retornaPersonIdPessoaLogada();
        $isProfessorResponsible = $this->verificaProfessorResponsavel(MIOLO::_REQUEST('groupid'), $professor);
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        foreach($disciplinas->obterBibliografiaComplementar(MIOLO::_REQUEST('groupid')) as $bibliografia)
        {
            $textfield = new MTextField('bibliografiaComplementar[]', $bibliografia[0]);
            $textfield->width = '700px';
            $fields_[] = $textfield;
            
            $isProfessorResponsible == DB_FALSE ? $textfield->setREadOnly(true) : null;
            
            $action = MUtil::getAjaxAction('removerBibliografiaComplementar', addslashes($bibliografia[0]) );
            
            if( $isProfessorResponsible == DB_TRUE )
            {
                $fields_[] = $button = new MButton($name, 'Remover', $action, $image);
                $button->addBoxStyle('float', 'left');
                $button->addBoxStyle('width', '100px');
            }
        }
        
        $fields[] = $container = new MHContainer(null, $fields_);
        $container->addBoxStyle('width', '850px');
        
        return $fields;
    }
    
    public function exibirBibliografiaComplementar()
    {
        $fields[] = new MDiv('bcomplementar_itens', $this->listarBibliografiaComplementar());
        
        $fields[] = new MSpacer();
        
        $professor = $this->retornaPersonIdPessoaLogada();
        $isProfessorResponsible = $this->verificaProfessorResponsavel(MIOLO::_REQUEST('groupid'), $professor);
        
        if( $isProfessorResponsible == DB_TRUE )
        {
            $fields[] = new MButton('adicionarBibliografiaComplementar', _M('Adicionar'), MUtil::getAjaxAction('adicionarBibliografiaComplementar'));
        }
            
        return $fields;
    }
    
    public function adicionarBibliografiaComplementar()
    {
        $fields[] = new MTextField('bibliografiaBasicaTitulo', '', 'Título');
        $fields[] = new MTextField('bibliografiaBasicaAutor', '', 'Autor');
        $fields[] = new MTextField('bibliografiaBasicaDescricao', '', 'Descrição');
        
        $botoes[] = new MButton('botaoSalvar', _M('Salvar'), MUtil::getAjaxAction('salvarBibliografiaComplementar'));
        $botoes[] = new MButton('botaoCancelar', _M('Cancelar'), "dijit.byId('dialogAdicionarBibliografiaComplementar').hide();");
        $fields[] = MUtil::centralizedDiv($botoes);

        $dialog = new MDialog('dialogAdicionarBibliografiaComplementar', _M('Adicionar Bibliografia Complementar'), $fields);
        $dialog->setWidth('700px');
        $dialog->show();
    }
    
    public function removerBibliografiaComplementar($desc=null)
    {
        $desc = $desc ? stripslashes($desc) : null;
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $groupid = MIOLO::_REQUEST('groupid');
        
        $disciplinas->removerBibliografiaComplementar($desc, $groupid);
        
        $this->setResponse($this->listarBibliografiaComplementar(), 'bcomplementar_itens');
    }
    
    public function salvarBibliografiaComplementar($args)
    {
        MDialog::close('dialogAdicionarBibliografiaComplementar');
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $args = $this->getAjaxData();
        
        if(strlen ($args->bibliografiaBasicaTitulo) > 0 && strlen ($args->bibliografiaBasicaAutor))
        {
            $descricao = $args->bibliografiaBasicaTitulo.' - '.$args->bibliografiaBasicaAutor;
        
            //Só concatena descrição caso ela exista
            if (strlen ($args->bibliografiaBasicaDescricao) > 0)
            {
                $descricao .= ' - '.$args->bibliografiaBasicaDescricao;
            }
                $disciplinas->salvarBibliografiaComplementar($descricao, MIOLO::_REQUEST('groupid'));

                $this->setResponse($this->exibirBibliografiaComplementar(), 'bcomplementar');
            }
        else 
        {
            new MMessageError('Título e Autor devem ser informados!');}
        }
    
    public function salvar()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $MIOLO->uses('types/PrtCronogramaProfessor.class.php', $module);
        
        $disciplinas = new PrtDisciplinas();
        
        $groupid = MIOLO::_REQUEST('groupid');
        
        $args = $this->getAjaxData();
        
        try
        {
            bBaseDeDados::iniciarTransacao();
            
            //salvar os dados
            $disciplinas->salvarPlanoDeCurso($groupid, $args->observacoes, $args->metodologias, $args->objetivos);

            //Salvar o cronograma
            foreach ( (array)$args as $k => $description )
            {
                if ( substr($k,0,10) == 'cronograma' )
                {
                    $data = base64_decode(substr($k,11));
                    list($dia, $timeid, $scheduleprofessorid) = explode(';', $data);

                    //Se obteve o dia e algo cadastrado no cronograma
                    if ( strlen($dia) > 0 && strlen($description) > 0 )
                    {
                        $prtCronograma = new PrtCronogramaProfessor($groupid, $this->personid, $description, $dia);
                        $prtCronograma->salvar();
                    }
                }
            }

            //salvar avaliacoes
            foreach((array)$args as $k=>$avaliacoes)
            {
                if(substr($k,0,10)=='avaliacoes')
                {
                    foreach($avaliacoes as $avaliacao)
                    {
                        if($avaliacao['degreeid'] && $avaliacao['desc'] && $avaliacao['peso'])
                        {
                            $disciplinas->salvarAvaliacao($avaliacao['desc'], $avaliacao['peso'], $avaliacao['degreeid'], $groupid, $this->personid);
                        }
                    }
                }
            }
            
            bBaseDeDados::finalizarTransacao();
        } 
        catch (Exception $ex) 
        {
            bBaseDeDados::reverterTransacao();
            
            $this->addError("Houveram complicações ao salvar os registros: </br>" . $ex->getMessage());
        }
        
        $this->setResponse(NULL, 'responseDiv');
    }
    
    /*
     * Verifica se a pessoa logada é o professor responsável da disciplina.
     * Está verificação acontece apenas se o parâmetro está habilitado e existe um professor cadastrado com responsável
     * caso contrário, mantêm a funcionalidade original.
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
            
            if( $grupo->professorResponsible )
            {
                if( $grupo->professorResponsible == $personId )
                {
                    return DB_TRUE;
                }
                else
                {
                    return DB_FALSE;
                }
            }
            else
            {
                return DB_TRUE;
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
    
    public function excluirCronograma($args = null)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/PrtCronogramaProfessor.class.php', $MIOLO->getCurrentModule());
        
        $args = MUtil::getAjaxActionArgs();
        
        try
        {
            bBaseDeDados::iniciarTransacao();
            
            $cronograma = new PrtCronogramaProfessor($args->groupId, $args->personId, NULL, $args->dataAula);
            if ( strlen($cronograma->obterCodigoCronograma()) > 0 )
            {
                $cronograma->salvar();
            }

            $jscode = "document.getElementById('cronograma_{$args->idCampo}==').value=null;";
            $this->page->onLoad($jscode);
            
            bBaseDeDados::finalizarTransacao();
        } 
        catch (Exception $ex) 
        {
            bBaseDeDados::reverterTransacao();
            $this->addError($ex->getMessage());
        }
        
        $this->setResponse(NULL, "responseDiv");
    }

}

?>
