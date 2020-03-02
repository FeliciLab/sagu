<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 * Amir Montechi [montecchi@gmail.com]
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
class frmHistoricoEscolar extends frmMobile
{
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Histórico escolar', MIOLO::getCurrentModule()));

        $this->setShowPostButton(FALSE);
    }

    public function defineFields()
    {
		$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtHistoricoEscolar.class.php', $module);
        $historicoEscolar = new PrtHistoricoEscolar();
	
        $fields = array();
        
        $contratos = $historicoEscolar->obterContratos($this->personid);
        
        foreach($contratos as $contrato)
        {
            $opts = array();
            
            $opts[] = $this->disciplinasDoPeriodo($contrato->id);
            $opts[] = $this->historicoEscolar($contrato->id);
            $opts[] = $this->aproveitamentos($contrato->id);
            $opts[] = $this->proficiencias($contrato->id);
            $opts[] = $this->atividadesComplementares($contrato->id);
            $opts[] = $this->movimentacoesContratuais($contrato->id);
            
            $nomeCurso = $historicoEscolar->obterNomeDoCurso($contrato->curso);
            $unidade = $contrato->unidade ? " - {$contrato->unidade}" : "";
                        
            $sections[] = new jCollapsibleSection(_M($contrato->curso.' - '. $nomeCurso . $unidade), $opts, false, 'contract_' . $contrato->id);
        }
        
        $fields[] = new jCollapsible('contratos_aluno', $sections);

		parent::addFields($fields);
    }
    
    public function disciplinasDoPeriodo($contractId)
    {
    	$MIOLO  = MIOLO::getInstance();
    	$module = MIOLO::getCurrentModule();
    	$action = MIOLO::getCurrentAction();
    
    	$filter = new stdClass();
    	$filter->periodId   = SAGU::getParameter('BASIC', 'CURRENT_PERIOD_ID');
    	$filter->contractId = $contractId;
    
    	$businessDiverseConsultation = new BusinessAcademicBusDiverseConsultation();
    	$disciplinas = $businessDiverseConsultation->getCurricularComponentCoursed($filter);

        if ( count($disciplinas) > 0 )
        {            
            foreach ( $disciplinas as $k=>$disciplina )
            {
                if($disciplina[14])
                {
                    $professor = str_replace(array('"','{','}'), array('','',''), $disciplina[14]);
                }
                else
                {
                    $professor = 'Professor não definido';
                }
                
                if($disciplina[13])
                {
                    $horario = str_replace(array('"','{','}'), array('','',''), $disciplina[13]);
                }
                else
                {
                    $horario = 'Horário não definido';
                }
                
                $dataTable[] = array(   $disciplina[2], 
                                        $disciplina[4].' ('.$professor.')', 
                                        $horario,
                                        $disciplina[10]
                                        );
            }
        }
        
        $columns[2] = _M('Horário/sala');
        $columns[3] = _M('Estado');
        
        $options = array('title_key'=>'1');
        
        $fields[] = $this->listView($id, $title, $columns, $dataTable, $options);
    
    	$sections[] = new jCollapsibleSection(_M('Disciplinas do período '.SAGU::getParameter('BASIC', 'CURRENT_PERIOD_ID')), $fields);
        
        return new jCollapsible('periodo', $sections);
    }
    
    public function historicoEscolar($contractId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtHistoricoEscolar.class.php', $module);
        $historicoEscolar = new PrtHistoricoEscolar();
	
        $historico = $historicoEscolar->obterHistoricoEscolar($contractId);
        
        $fields = array();
        
        #FIXME: se nao for usado remover
        if($desktop) //habilitar someente para desktop se ficar bom
        {
            $grdResults = $MIOLO->getUI()->getGrid('academic','GrdDiverseConsultationCurricularComponentRegistered');        
            $grdResults->setData($historico);        
            $grdResults->setClose(null);        
            $grdResults->setTitle(null);
            //$fields[] = $this->mobileGrid($grdResults);
            $fields[] = new MDiv('divSchoolHistoric', utf8_encode($grdResults->generate()));
        }
        
        $columns[2] = _M('Período');
        $columns[11] = _M('Turma');
        $columns[7] = _M('Nota');
        $columns[8] = _M('Frequência');
        $columns[10] = _M('Estado');
        
        $options = array('title_key'=>'4');
        
        $fields[] = $this->listView($id, $title, $columns, $historico, $options);
        
        $sections[] = new jCollapsibleSection(_M('Histórico escolar'), $fields);
        
        return new jCollapsible('historico', $sections);
    }
    
    public function aproveitamentos($contractId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtHistoricoEscolar.class.php', $module);
        $historicoEscolar = new PrtHistoricoEscolar();
        
        $aproveitamentos = $historicoEscolar->obterAproveitamentos($contractId);

        $columns[2] = _M('Período');
        $columns[6] = _M('Créditos');
        $columns[5] = _M('Estado');
        
        $options = array('title_key'=>'4');
        
        $fields[] = $this->listView($id, $title, $columns, $aproveitamentos, $options);
        
        $sections[] = new jCollapsibleSection(_M('Aproveitamentos'), $fields);
        
        return new jCollapsible('aproveitamentos', $sections);
    }
    
    public function proficiencias($contractId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtHistoricoEscolar.class.php', $module);
        $historicoEscolar = new PrtHistoricoEscolar();
        
        $proficiencias = $historicoEscolar->obterProficiencias($contractId);
        
        $columns[7] = _M('Nota');
        $columns[10] = _M('Estado');
        
        $options = array('title_key'=>'4');
        
        $fields[] = $this->listView($id, $title, $columns, $proficiencias, $options);

        $sections[] = new jCollapsibleSection(_M('Proficiencias'), $fields);
        
        return new jCollapsible('proficiencias', $sections);
    }
    
    /**
     * Obtém as atividades complementares.
     * 
     * @param int $contractId
     * @return \jCollapsible
     */
    public function atividadesComplementares($contractId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/prtHistoricoEscolar.class.php', $module);
        
        $busCompActivCategory = new BusinessAcademicBusComplementaryActivitiesCategory();
        $compActivCategorys   = $busCompActivCategory->searchComplementaryActivitiesCategory(new stdClass());
        
        $cargaHorariaTotalACursar     = 0;
        $cargaHorariaTotalCursada     = 0;
        $disciAtividadeComplementarId = null;
        
        // Percorre todas as categorias de atividades complementares, e procura por atividades referentes do contrato.
        foreach ( $compActivCategorys as $compActivCategory )
        {
            $cargaHorariaTotalCursadaDaCategoria = 0;
            $cargaHorariaTotalACursarDaCategoria = null;            
            
            $historicoEscolar = new PrtHistoricoEscolar();
            $atividadesComp   = $historicoEscolar->obterAtividadesComplementares($contractId, $compActivCategory[0]);
            
            // Se houver atividades para a categoria.
            if ( count($atividadesComp) > 0 )
            {
                unset($todasAtividades);
                
                // Obtém todas as atividades complementares da categoria, para serem exibidas dentro de sua categoria.
                foreach ( $atividadesComp as $atividade )
                {
                    // Verifica se já encontrou a disciplina de atividade complementar.
                    if ( is_null($disciAtividadeComplementarId) )
                    {
                        $busEnroll = new BusinessAcademicBusEnroll();
                        $enroll    = $busEnroll->getEnroll($atividade[1]);

                        $busCurriculum = new BusinessAcademicBusCurriculum();
                        $curriculum    = $busCurriculum->getCurriculum($enroll->curriculumId);

                        $busCurricularComponent = new BusinessAcademicBusCurricularComponent();
                        $curricularComponent    = $busCurricularComponent->getCurricularComponent($curriculum->curricularComponentId, $curriculum->curricularComponentVersion);
                        
                        $disciAtividadeComplementarId = $curriculum->curricularComponentId;
                        $cargaHorariaTotalACursar     = $curricularComponent->academicNumberHours;
                    }
                    
                    $cargaHorariaTotalCursadaDaCategoria += $atividade[3];
                            
                    $todasAtividades[] = array(
                        $atividade[2],
                        $atividade[3] . 'h',
                        $atividade[4]
                    );
                }
                
                // Obtém regras da categoria, caso existam.
                if ( !is_null($disciAtividadeComplementarId) )
                {
                    $args = new stdClass();
                    $args->curricularComponentId             = $disciAtividadeComplementarId;
                    $args->complementaryActivitiesCategoryId = $compActivCategory[0];

                    $busComplementaryActivitiesCategoryRules = new BusinessAcademicBusComplementaryActivitiesCategoryRules();
                    $complementaryActivitiesCategoryRules = $busComplementaryActivitiesCategoryRules->searchComplementaryActivitiesCategoryRules($args);

                    if ( count($complementaryActivitiesCategoryRules) > 0 )
                    {
                        $cargaHorariaTotalACursarDaCategoria = ' / ' . $complementaryActivitiesCategoryRules[0][6] . 'h';
                    }
                }
                
                // Monta a tabela de atividades da categoria.
                $mTableRaw = new MTableRaw(null, $todasAtividades, array('Nome da atividade', 'Carga horária', 'Créditos'));
                $mTableRaw->addAttribute('style', 'width:40%; margin-left:20px; font-size:16px !important');
                
                $jCollapse = new jCollapsibleSection($compActivCategory[1] . ' ' . $cargaHorariaTotalCursadaDaCategoria . 'h' . $cargaHorariaTotalACursarDaCategoria, array($mTableRaw), true);
                $jCollapse->addAttribute('style', 'margin-bottom:10px');
                $categorias[] = $jCollapse; 
            }
            
            $cargaHorariaTotalCursada += $cargaHorariaTotalCursadaDaCategoria;
        }
        
        $sections[] = new jCollapsibleSection(_M('Atividades complementares') . ' ' . $cargaHorariaTotalCursada . 'h / ' . $cargaHorariaTotalACursar . 'h', $categorias, false, 'complementaryActivities');
        
        return new jCollapsible('atividades', $sections);
    }
    
    public function movimentacoesContratuais($contractId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtHistoricoEscolar.class.php', $module);
        $historicoEscolar = new PrtHistoricoEscolar();
        
        $movimentacoes = $historicoEscolar->obterMovimentacoesContratuais($contractId);
        
        //$columns[4] = _M('Motivo');
        $columns[5] = _M('Data do estado');
        $columns[7] = _M('Último período');
        
        $options = array('title_key'=>'2');
        
        $fields[] = $this->listView($id, $title, $columns, $movimentacoes, $options);

        $sections[] = new jCollapsibleSection(_M('Movimentos contratuais'), $fields);
        
        return new jCollapsible('movimentos', $sections);
    }
    
    public function salvar($args)
    {   
        $this->setResponse(NULL, 'responseDiv');
    }

}

?>
