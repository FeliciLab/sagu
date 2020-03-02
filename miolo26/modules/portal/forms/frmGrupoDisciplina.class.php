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

class frmGrupoDisciplina extends frmMobile
{
    public $autoSave = false;
    
    public $_columns;
    
    public $conceptGroupId;
    
    public function __construct($titulo = null)
    {
        self::$fazerEventHandler = FALSE;
        $titulo = strlen($titulo) > 0 ? ' ' : 'DIGITAÇÃO DE NOTAS '; 
        parent::__construct(_M($titulo, MIOLO::getCurrentModule()));
        $this->setShowPostButton(FALSE);
        $module = MIOLO::getCurrentModule();
        
        //colunas da tabela
         $this->_columns = array(
        _M('Ação', $module),
        _M('Código do grupo das disciplinas', $module),
        _M('Nome', $module)
             );
    }

    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        
        // Obtem os parâmetros da passados pela grid
        $periodId = MIOLO::_REQUEST('periodid');
        $courseId = MIOLO::_REQUEST('courseId');
        $courseVersion = MIOLO::_REQUEST('courseVersion');
        $turnId = MIOLO::_REQUEST('turnId');
        $unitId = MIOLO::_REQUEST('unitId');
        $curricularComponentGroupId = MIOLO::_REQUEST('curricularComponentGroupId');
        $classId = MIOLO::_REQUEST('classId');

        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $busLearningPeriod = $MIOLO->getBusiness('academic', 'BusLearningPeriod');
        $busSchedule = $MIOLO->getBusiness('academic', 'BusSchedule');
        $busPhysicalPerson = $MIOLO->getBusiness('basic', 'BusPhysicalPerson');
        $busGradeTyping = $MIOLO->getBusiness('academic', 'BusGradeTyping');
        $businessCurricularComponentGroup = $MIOLO->getBusiness('academic','BusCurricularComponentGroup');

         // Grupo
        $bussinessCurricularComponentGroup = new BusinessAcademicBusCurricularComponentGroup();
        $curricularComponent = $bussinessCurricularComponentGroup->getCurricularComponentGroup($curricularComponentGroupId);
        $curricularComponentGroupLabel = new MTextLabel('curricularComponentGroupLabel', _M('Grupo',$module) .':');
        $curricularComponentGroupLabel->setWidth( SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        $curricularComponentGroup = new MTextLabel('curricularComponentGroupName',$curricularComponent->curricularComponentGroupName);
        $baseData[] = new MHContainer('hctPeriod', array($curricularComponentGroupLabel,$curricularComponentGroup));

        // Período
        $periodLabel = new MTextLabel('periodLabel', _M('Período',$module) .':');
        $periodLabel->setWidth( SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        $period = new MTextLabel('period', $periodId);
        $baseData[] = new MHContainer('hctPeriod', array($periodLabel,$period));
        
        $colTitle[] = _M('Código da disciplina',$module) . '/' . _M('versão', $module);
        $colTitle[] = _M('Nome da disciplina',$module);
        $colTitle[] = _M('Créditos acadêmicos',$module);
        $colTitle[] = _M('Horas acadêmicas',$module);
        $colTitle[] = _M('Tipo de disciplina',$module);

        $filters = new stdClass();
        $filters->courseId = $courseId;
        $filters->courseVersion = $courseVersion;
        $filters->courseTurnId = $turnId;
        $filters->courseUnitId = $unitId;
        $filters->curricularComponentGroupId = $curricularComponentGroupId;

        // Obtem as disciplinas
        $businessCurriculum = $MIOLO->getBusiness('academic', 'BusCurriculum');
        $dataCurriculum = $businessCurriculum->searchCurriculumComponent($filters);

        // Bus group
        $businessGroup = $MIOLO->getBusiness('academic', 'BusGroup');

        $isClosed = DB_FALSE;
        $msg = _M('Os dados não podem ser alterados', $module).'</ul></li>';
        foreach( (array)$dataCurriculum as $key=>$line )
        {
            // Verifica se a disciplina oferecida não esta fechada, se tiver exibe uma mensagem.
            $filterGroup = new stdClass();
            $filterGroup->curriculumId = $line[0];
            $filterGroup->periodId = $periodId;
            $groups = $businessGroup->searchGroup($filterGroup);

            foreach ( (array)$groups as $group )
            {
                $columnIsClosed = $group[10];
                if( $columnIsClosed  == DB_TRUE )
                {
                    $msg .= '<ul><li>' ._M('A disciplina oferecida de código @1 está fechada, portanto não é permitida qualquer alteração, para alterar algum dado reabra a oferecida', $module, $group[0]).'.'.'</ul></li>';
                    $isClosed = DB_TRUE;
                }
            }

            $dataResult[$key][0] = $line[1];
            $dataResult[$key][1] = $line[2];
            $dataResult[$key][2] = $line[3];
            $dataResult[$key][3] = $line[4];
            $dataResult[$key][4] = $line[5];
        }

        $baseData[] = $table = new MTableRaw(_M('Disciplinas',$module), $dataResult,$colTitle);
        $table->setWidth('100%');
        //alinhamento
        for($b = 0;$b < count($dataResult); $b++)
        {
            $table->setCellAttribute($b, 2, 'align', 'right');
            $table->setCellAttribute($b, 3, 'align', 'right');
                
        }
        
        $fields[] = new MBaseGroup('baseInformation', _M('Informações do grupo de disciplinas',$module), $baseData, 'vertical');
        $fields[] = new MSeparator();
        $fields[] = new MDiv('divEdit', null);
        
        // Tabela de alunos matriculados nas disciplinas
        unset($filters);
        $filters = new stdClass();
        $filters->periodId = $periodId;
        $filters->courseId = $courseId;
        $filters->courseVersion = $courseVersion;
        $filters->turnId = $turnId;
        $filters->unitId = $unitId;
        $filters->curricularComponentGroupId = $curricularComponentGroupId;
        $filters->classId = $classId;
        $filters->existingContractInDisciplines = DB_TRUE;

        $businessDegreeCurricularComponentGroup = $MIOLO->getBusiness('academic','BusDegreeCurricularComponentGroup');
        $dataEnrolledPupils = $businessDegreeCurricularComponentGroup->searchEnrolledPupilsOfDisciplineGroup($filters);

        // Business enroll status
        $businessEnrollStatus = $MIOLO->getBusiness('academic','BusEnrollStatus');

        unset($dataResult);
        foreach( (array)$dataEnrolledPupils as $key=>$line )
        {
            list($learningPeriodId,
                 $contractId,
                 $learningPeriodDescription,
                 $personId,
                 $personName,
                 $gradesByConcept) = $line;

            $dataResult[$key][0] = $personId;
            $dataResult[$key][1] = $personName;
            $dataResult[$key][2] = $learningPeriodDescription;

            // Obtém a ultima nota registrada para o mesmo período letivo e contrato
            $evaluationValue = $businessDegreeCurricularComponentGroup->getDegreeEnrollCurrentGrade($learningPeriodId, $contractId,$curricularComponentGroupId, $gradesByConcept == DB_TRUE ? true : false);

            // Business enrollStatus
            $businessEnrollStatus = $MIOLO->getBusiness('academic','BusEnrollStatus');
            $listEnrollStatus = $businessEnrollStatus->listEnrollStatus(1);
            
             // obtem o conceito
            $filters = new stdClass();
            $filters->conceptGroupId = $group[13];
            $businessConcept = $MIOLO->getBusiness('academic', 'BusConcept');
            $conceptData = $businessConcept->searchConceptAsObject($filters);
            
            $listSelection = array();
            foreach($conceptData as $list)
            {
                $listSelection[] = array($list->descriptionConcept,$list->descriptionConcept);
            }
            // Campo onde sera digitado a nota correspondente ao aluno
            $fieldNote = new MSelection('evaluation[' . $learningPeriodId . '][' . $contractId . ']', $evaluationValue->evaluation, null, $listSelection);
            $fieldNote->addAttribute('onChange', MUtil::getAjaxAction('validateNote', array('gradesByConcept'=>$gradesByConcept, 'learningPeriodId'=>$learningPeriodId, 'contractId'=>$contractId, 'conceptGroupId' => $group[13])));

            // Hidden field com o valor do status
            $fieldStatus = new MHiddenField('enrollStatus[' . $learningPeriodId . '][' . $contractId . ']', $evaluationValue->enrollStatusId, null);
            $fieldEnrollStatusDescription = new MTextField('enrollStatusDescription[' . $learningPeriodId . '][' . $contractId . ']', $listEnrollStatus[$evaluationValue->enrollStatusId], null, 10, null, null,true);
            $divEnrollStatus = new MDiv('divEnrollStatus['.$learningPeriodId.']['.$contractId.']', array($fieldStatus, $fieldEnrollStatusDescription));
            
            $dataResult[$key][3] = array($fieldNote->generate(), $linkEdit ? $linkEdit->generate() : null, $fieldDescription ? $fieldDescription : null );
            $dataResult[$key][4] = array($divEnrollStatus ? $divEnrollStatus->generate() : null);

            unset($linkEdit);
            unset($divEnrollStatusDescription);
            unset($fieldDescription);
        }

        $fields[] = new MHiddenField('gradesByConcept',$gradesByConcept);
        unset($colTitle);
        $colTitle[] = _M('Código',$module);
        $colTitle[] = _M('Nome',$module);
        $colTitle[] = _M('Período letivo',$module);
        $colTitle[] = _M('Nota',$module);
        $colTitle[] = _M('Estado',$module);

        $tableEnrollPupils[] = $table = new MTableRaw(null, $dataResult, $colTitle);
        $table->SetAlternate(true);
        $table->setWidth('100%');
        
        // alinhamento
        $table->setCell(0, 4, null, 'width="20%"');
        for($b = 0;$b < count($dataResult); $b++)
        {
         $table->setCellAttribute($b, 0, 'align', 'right');
         $table->setCellAttribute($b, 1, 'align', 'left');
         $table->setCellAttribute($b, 2, 'align', 'left');
        }
        
        $fields[] = new MBaseGroup('baseGroupEnrollPupils', _M('Alunos matriculados', $module), $tableEnrollPupils);

        $this->page->addScript('cpaint/cpaint.inc.js');
        
        $fields[] = new MButton('save', _M('Salvar'));

	parent::addFields($fields);
    }
    
     /**
     *
     * @param <type> $args
     * @param <type> $popUp
     * @return MTextLabel 
     */
    public function validateNote($args)
    {
        $argsParam = explode('=',$args);
        
        //ajustando args que vem como url
        $argsS = new stdClass();
        $array0 = explode('&', $argsParam[1]);
        $argsS->gradesByConcept = $array0[0];
        $array1 = explode('&', $argsParam[2]);
        $argsS->learningPeriodId = $array1[0];
        $array2 = explode('&', $argsParam[3]);
        $argsS->contractId = $array2[0];
        $array3 = explode('&', $argsParam[4]);
        $argsS->conceptGroupId = $array3[0];
        $array4 = explode('&', $argsParam[5]);
        $argsS->evaluation = $array4[0];
                
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $enrollData->statusId = SAGU::getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED');

        // Business learning period
        $busLearningPeriod = $MIOLO->getBusiness('academic', 'BusLearningPeriod');
        $learningPeriod = $busLearningPeriod->getLearningPeriod($argsS->learningPeriodId);

        // Business enrollStatus
        $businessEnrollStatus = $MIOLO->getBusiness('academic','BusEnrollStatus');
        $listEnrollStatus = $businessEnrollStatus->listEnrollStatus(1);

        // Nota obtida pelo aluno no eixo temático
        $argsS->evaluation = MIOLO::_REQUEST('evaluation');
        $pupilGrade = $argsS->evaluation[$argsS->learningPeriodId][$argsS->contractId];
        
        // Verificar se usa conceito
        if ( $argsS->gradesByConcept == DB_FALSE )
        {
            if ( is_numeric($pupilGrade) && (strlen($pupilGrade)>0) )
            {
                // Se nota maior que a definada como nota mínima no período letivo aprova se nao desaprova
                if ( $pupilGrade >= $learningPeriod->finalAverage )
                {
                    $statusId = SAGU::getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED');
                }
                else
                {
                    $statusId = SAGU::getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED');
                }
            }
            else
            {
                $statusId = null;
                $this->AddAlert(_M('Nota digitada está inválida',$module).'.');
            }
        }
        // Verificar notas se a disciplina usa conceito
        elseif ( $argsS->gradesByConcept == DB_TRUE )
        {
            // obtem o conceito
            $filters = new stdClass();
            $filters->conceptGroupId = $argsS->conceptGroupId;
            $filters->description = $pupilGrade;
            $businessConcept = $MIOLO->getBusiness('academic', 'BusConcept');
            $conceptData = $businessConcept->searchConceptAsObject($filters);
            $concept = $conceptData[0];
            if ( (!is_numeric($pupilGrade)) && ((strlen($pupilGrade)>0)) && (is_object($concept)) )
            {
                $statusId = $concept->enrollStatusId;
            }
            else
            {
                $statusId = null;
                $this->AddAlert(_M('Conceito digitado está inválido',$module).'.');
            }
        }
        
        $namestatus = 'enrollStatus[' .$argsS->learningPeriodId . '][' . $argsS->contractId . ']';
        $MIOLO->page->addJsCode(" document.getElementById('{$namestatus}').value = '{$statusId}' ");
  
        $nameDescription = 'enrollStatusDescription[' .$argsS->learningPeriodId . '][' . $argsS->contractId . ']';
        $MIOLO->page->addJsCode(" document.getElementById('{$nameDescription}').value = '{$listEnrollStatus[$statusId]}' ");
    }
    
     /**
     * Event triggered when user chooses Save from the toolbar
     **/
    public function save_click($sender = NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $MIOLO->checkAccess('FrmGrupoDisciplina', A_EXECUTE, true, true);
        
        $data = $this->getData();
        // Array com as notas ou conceitos
        $evaluations = MIOLO::_REQUEST('evaluation');
        // Array com status do aluno no grupo
        $enrollStatus = MIOLO::_REQUEST('enrollStatus');
        // Array com os motivos de troca de status ou nota
        $description = MIOLO::_REQUEST('description');

        $businessDegreeCurricularComponentGroup = new BusinessAcademicBusDegreeCurricularComponentGroup();
        try
        {
            // Begin transaction
            $businessDegreeCurricularComponentGroup->beginTransaction();

            foreach( (array) $evaluations as $learningPeriodId=>$learningPeriod )
            {
                foreach ( (array) $learningPeriod as $contractId=>$evaluation )
                {
                    $newStatus = $enrollStatus[$learningPeriodId][$contractId];
                    $newDescription = $description[$learningPeriodId][$contractId];

                    // Obtem nota e status anterior caso exista
                    $oldEvaluationValueAndStatus = $businessDegreeCurricularComponentGroup->getDegreeEnrollCurrentGrade($learningPeriodId, $contractId, $data->curricularComponentGroupId,  $data->gradesByConcept == DB_TRUE ? true : false);
                    $oldEvaluation = $oldEvaluationValueAndStatus->evaluation;
                    $oldStatus = $oldEvaluationValueAndStatus->enrollStatusId;

                    // Verifica se mudou a nota ou o status
                    if ( ($evaluation!=$oldStatus) || ($newStatus!=$oldStatus) )
                    {
                        // Dados para inserir uma nova nota para o aluno no grupo de disciplinas
                        $data->learningPeriodId = $learningPeriodId;
                        $data->contractId = $contractId;
                        $data->description = $newDescription;
                        $data->enrollStatusId = $newStatus;

                        if ( $data->gradesByConcept == DB_TRUE )
                        {
                            $data->concept = $evaluation;
                        }
                        else
                        {
                            $data->note = $evaluation;
                        }

                        $result = $businessDegreeCurricularComponentGroup->insertDegreeCurricularComponentGroup($data);
                    }
                }
            }
            // End transaction
            $businessDegreeCurricularComponentGroup->endTransaction();

            $msg = _M('Notas gravadas com sucesso!',$module);
            return new MMessageSuccess($msg);
        }
        catch ( Exception $e )
        {
            $this->addAlert($e->getMessage());
        }
    }
    
}
?>
