<?php

/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Solu��es Livres Ltda.
 *
 * Este arquivo � parte do programa Sagu.
 *
 * O Sagu � um software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * dentro dos termos da Licen�a P�blica Geral GNU como publicada pela Funda��o
 * do Software Livre (http://sagu.fametro.edu.br/consultasatuaisFSF); na vers�o 2 da Licen�a.
 *
 * Este programa � distribu�do na esperan�a que possa ser �til, mas SEM
 * NENHUMA GARANTIA; sem uma garantia impl�cita de ADEQUA��O a qualquer MERCADO
 * ou APLICA��O EM PARTICULAR. Veja a Licen�a P�blica Geral GNU/GPL em
 * portugu�s para maiores detalhes.
 *
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral GNU, sob o t�tulo
 * "LICENCA.txt", junto com este programa, se n�o, acesse o Portal do Software
 * P�blico Brasileiro no endere�o www.softwarepublico.gov.br ou escreva para a
 * Funda��o do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 * Usuario portal
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 * Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * @since
 * Class created on 28/09/2012
 *
 */

$MIOLO->uses('classes/prtCommonForm.class.php', $module);
$MIOLO->uses('classes/prtUsuario.class.php', $module);
$MIOLO->uses('db/BusGroup.class', 'academic');
$MIOLO->uses('db/BusFrequenceEnroll.class', 'academic');
$MIOLO->uses('db/BusLearningPeriod.class', 'academic');
$MIOLO->uses('types/AcdRegimeDomiciliar.class', 'academic');

class PrtDisciplinas
{
    public function __construct(){}
    
    public function obterPeriodos($personId)
    {
        $msql = new MSQL();
        $msql->setTables('acdLearningPeriod CC 
                          INNER JOIN acdperiod FF ON FF.periodid = CC.periodid 
                          INNER JOIN acdGroup BB ON (CC.learningPeriodId = BB.learningPeriodId)
                          INNER JOIN acdEnroll DD ON (BB.groupId = DD.groupId) 
                          INNER JOIN acdContract EE ON (DD.contractId = EE.contractId)
                          INNER JOIN acdcourse HH ON (HH.courseid = ee.courseid)');

        $msql->setColumns('CC.learningperiodid, CC.periodId, FF.description, EE.courseid, EE.courseversion, HH.name');

        
        $msql->setWhere(" EE.personId = $personId ");
        
        $contractId = prtUsuario::obterContratoAtivo();
        if ( strlen($contractId) > 0 )
        {
            $msql->setWhereAnd(" DD.contractid = $contractId ");
        }
        
        $msql->setOrderBy('CC.begindate DESC');
        
        $msql->setGroupBy('1,2,3,4,5,6,CC.begindate');

        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado;
    }
    
    
    public function obterPeriodosProfessor($professorId)
    {        
        $msql = new MSQL();
        $msql->setTables('acdGroup A
                     INNER JOIN acdlearningperiod B
                             ON (B.learningPeriodId = A.learningPeriodId)
                     INNER JOIN acdcurriculum C
                             ON (C.curriculumId = A.curriculumId)
                     INNER JOIN acdcurricularcomponent D
                             ON (D.curricularComponentId = C.curricularComponentId AND
                                 D.curricularComponentVersion = C.curricularComponentVersion)
                    INNER JOIN acdschedule F
                            ON (A.groupId = F.groupId)
                    INNER JOIN acdScheduleProfessor G
                            ON (F.scheduleId = G.scheduleId)
                    LEFT JOIN acdRegimen H
                            ON (A.regimenId = H.regimenId)
                    LEFT JOIN acdlearningperiod I
                            ON (I.learningPeriodId = A.learningPeriodId)');

        $msql->setColumns('DISTINCT A.groupId, D.name');

        $msql->setWhere("G.professorId = $professorId");
        
        $msql->setOrderBy('D.name');

        $resultado = bBaseDeDados::consultar($msql);
        
        $MIOLO = MIOLO::getInstance();
        
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        
        $data = array();

        foreach($resultado as $r)
        {
            $group = $busGroup->getGroup($r[0]);
            
            if ( strlen($group->groupId) > 0 )
            {
                $data[] = $group;
            }
        }
        
        return $data;
    }
    
        
    public function obterDisciplinasDoPeriodo($personId, $periodId)
    {
        $busGroup = new BusinessAcademicBusGroup();
        return $busGroup->obterDisciplinasDaPessoaPorPeriodo($personId, $periodId);
    }
    
    public function obterInfoDisciplina($groupId)
    {
        $MIOLO = MIOLO::getInstance();

        $busDiverseConsultation = $MIOLO->getBusiness('academic', 'BusDiverseConsultation');
        
        $professores = $busDiverseConsultation->getGroupProfessorNames($groupId);
        
        if ( count($professores) > 0 )
        {
            $professor = implode(' / ', $professores);
        }
        else
        {
            $professor = _M('A definir', $module);
        }
        
        $info->professor = $professor;
        
        return $info;
    }
    
    public function obterNotas($groupId, $enrollId)
    {
        $MIOLO = MIOLO::getInstance();

        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $busEvaluation = $MIOLO->getBusiness('academic', 'BusEvaluation');
        $busDegreeEnroll = $MIOLO->getBusiness('academic', 'BusDegreeEnroll');
        $busEvaluationEnroll = $MIOLO->getBusiness('academic', 'BusEvaluationEnroll');
        $busGradeTyping = $MIOLO->getBusiness('academic', 'BusGradeTyping');
        
        $group = $busGroup->getGroup($groupId);
        $learningPeriodDegrees = $busGradeTyping->getLearningPeriodDegrees($group->learningPeriodId);
        
        $notas = array();
        foreach ( $learningPeriodDegrees as $degree )
        {
            $filters = new stdClass();
            $filters->degreeId = $degree->degreeId;
            $filters->groupId = $groupId;

            $evaluations = $busEvaluation->searchEvaluation($filters);

            $subnotas = array();
            if ( count($evaluations) > 0 )
            {
                foreach ( $evaluations as $evaluation )
                {
                    $evaluationData = $busEvaluation->getEvaluation($evaluation[0]);
                    $evaluationGrade = $busEvaluationEnroll->getEvaluationEnrollCurrentGrade($evaluationData->evaluationId, $enrollId, $group->useConcept == DB_TRUE);
                    
                    $subnotas[$evaluationData->description] = $evaluationGrade;
                }
            }

            // Avaliação
            $degreeGrade = $busDegreeEnroll->getDegreeEnrollCurrentGrade($degree->degreeId, $enrollId, $group->useConcept == DB_TRUE);
            
            $notas[$degree->description]['nota'] = $degreeGrade;
            $notas[$degree->description]['degreeid'] = $degree->degreeId;
            $notas[$degree->description]['avaliacoes'] = $subnotas;
            $notas[$degree->description]['exame'] = $degree->isExam;
            $notas[$degree->description]['final'] = $degree->parentDegreeId ? false : true;
        }
        
        return $notas;
    }
    
    public function obterFrequencia($enrollid, $date=null, $timeid=null)
    {   
        $msql = new MSQL();
        $msql->setTables('acdfrequenceenroll a  inner join acdtime b on (a.timeid = b.timeid)');

        $msql->setColumns('a.frequencydate, b.beginhour, b.endhour, a.frequency, a.justification');

        $where = "enrollid = $enrollid";
        
        if($date)
        {
            $where .= " AND a.frequencydate = dateToDb('$date') ";
        }
        
        if($timeid)
        {
            $where .= " AND a.timeid = $timeid ";
        }
        
        $msql->setWhere($where);
        
        $msql->setOrderBy('a.frequencydate, b.beginhour desc');

        $resultado = bBaseDeDados::consultar($msql);

        return $resultado;
    }
    
    public function removerFrequencia($enrollid, $date, $timeid)
    {
        $args = array($enrollid, $date, $timeid);
        
        $sql = 'delete from acdfrequenceenroll where enrollid = ? and frequencydate = ? and timeid = ? ';
        
        return bBaseDeDados::executar(SAGU::prepare($sql, $args));
    }
    
    public function verificaFrequencia($enrollid, $date, $timeid, $groupId, $registraFalta=false, $infosAlunos = NULL, $infosHorarios = NULL)
    {
        $scheduleid = $this->obterHorarioPelasChaves($groupId, $date, $timeid);
        
        $sql = $this->obterConsultaDeFrequencia($enrollid, $date, $timeid, $scheduleid);

        $frequenceEnroll = bBaseDeDados::consultar($sql);
        // Se não encontrou um registro de frequência para o aluno nesse dia, nesse horário, insere por padrão a presença.
        if ( !$frequenceEnroll[0][0] )
        {
            $this->salvarFrequencia($enrollid, $date, $timeid, $scheduleid);
            
            return '</br>' . $infosAlunos[1] . ' - ' . $infosAlunos[0] . ' (' . $infosHorarios[0] . ' - ' . $infosHorarios[1] . ' às ' . $infosHorarios[2] . ')';
        }
        elseif( $registraFalta )
        {
            $this->salvarFrequencia($enrollid, $date, $timeid, $scheduleid, 0);
        }
    }
    
    public function salvarFrequencia($enrollid, $date, $timeid, $scheduleid, $frequency=1)
    {
        $sql = $this->obterConsultaDeFrequencia($enrollid, $date, $timeid, $scheduleid);
        
        $personId = prtUsuario::obtemUsuarioLogado();
        
        $data = new stdClass();
        $data->personId = $personId->personId;
        $data->groupId = MIOLO::_REQUEST('groupid');
        $data->navegador = MUtil::getBrowser();
        $data->enrollId = $enrollid;
        $data->frequencydate = $date;
        $data->timeid = $timeid;
        $data->scheduleid = $scheduleid;
        $data->frequency = $frequency;

        $frequenceEnroll = bBaseDeDados::consultar($sql);        
        if ( !$frequenceEnroll[0][0] )
        {
            $return = $this->inserirFrequencia($enrollid, $date, $timeid, $scheduleid, $frequency);
            
            // Salva log da frequência
            $data->salvou = $return;
            $this->salvaLogDeFrequencia($data);
            
            return $return;
        }
        else
        {
            $return = $this->atualizarFrequencia($enrollid, $date, $timeid, $scheduleid, $frequency);
        
            // Salva log da frequência
            $data->salvou = $return;
            $this->salvaLogDeFrequencia($data);
                        
            return $return;
        }
    }
    
    private function salvaLogDeFrequencia($data)
    {
        $sql = new MSQL();
        $sql->setTables('logFrequenciaPortal');
        $sql->setColumns('personId, groupId, navegador, enrollid, scheduleid, frequencydate, frequency, timeid, salvou');
        $sql->addParameter($data->personId);
        $sql->addParameter($data->groupId);
        $sql->addParameter($data->navegador);
        $sql->addParameter($data->enrollId);
        $sql->addParameter($data->scheduleid);
        $sql->addParameter($data->frequencydate);
        $sql->addParameter($data->frequency);
        $sql->addParameter($data->timeid);
        $sql->addParameter($data->salvou);
        
        return bBaseDeDados::executar($sql->insert());
    }
    
    private function inserirFrequencia($enrollid, $date, $timeid, $scheduleid, $frequency)
    {
        $sql = new MSQL();
        $sql->setTables('acdfrequenceenroll');
        $sql->setColumns('enrollid, frequencydate, timeid, frequency, scheduleid');
        $sql->addParameter($enrollid);
        $sql->addParameter($date);
        $sql->addParameter($timeid);
        $sql->addParameter($frequency);
        $sql->addParameter($scheduleid);
        
        return bBaseDeDados::executar($sql->insert());
    }
    
    private function atualizarFrequencia($enrollid, $date, $timeid, $scheduleid, $frequency)
    {
        $sql = new MSQL();
        $sql->setTables('acdfrequenceenroll');
        $sql->setColumns('frequency');
        $sql->addParameter($frequency);
        
        $sql->setWhere('enrollid = ?');
        $sql->addParameter($enrollid);
        $sql->setWhere("frequencydate = TO_DATE(?, 'dd/mm/yyyy')");
        $sql->addParameter($date);
        $sql->setWhere('timeid = ?');
        $sql->addParameter($timeid);
        $sql->setWhere('scheduleid = ?');
        $sql->addParameter($scheduleid);
        
        return bBaseDeDados::executar($sql->update());
    }
    
    private function obterConsultaDeFrequencia($enrollid, $date, $timeid, $scheduleid)
    {
        $args = array($enrollid, $date, $timeid, $scheduleid);
        
        $sql = new MSQL();
        $sql->setTables('acdfrequenceenroll');
        $sql->setColumns('enrollid, frequencydate, timeid, frequency, scheduleid');
        $sql->setWhere('enrollid = ?');
        $sql->addParameter($enrollid);
        $sql->setWhere('frequencydate = dateToDb(?)');
        $sql->addParameter($date);
        $sql->setWhere('timeid = ?');
        $sql->addParameter($timeid);
        $sql->setWhere('scheduleid = ?');
        $sql->addParameter($scheduleid);
        
        return $sql;
    }
    
    public function obterTotalDeFrequenciasRegistradas($groupId, $data)
    {
        $sql = new MSQL('count(*)', 'acdfrequenceenroll');
        
        $sql->setWhere('enrollid IN (SELECT enrollid FROM acdenroll WHERE groupid = ?)');
        $sql->addParameter($groupId);
        
        $sql->setWhere('frequencydate = ?::date');
        $sql->addParameter($data);
        
        $result = bBaseDeDados::consultar($sql);
        
        return $result[0][0];
    }
    
    /**
     * @return int
     */
    public function obterHorarioPelasChaves($groupId, $date, $timeId)
    {
        static $horarios = array();
        
        $horario = $horarios[$groupId][$date][$timeId];
        
        if ( !$horario )
        {
            $sql = new MSQL();
            $sql->setTables('rpthorarios');
            $sql->setColumns('scheduleId');
            $sql->addEqualCondition('groupId', $groupId);
            $sql->addEqualCondition('timeId', $timeId);
            $sql->addEqualCondition('occurrenceDate', SAGU::dateToDb($date));

            $res = bBaseDeDados::consultar($sql);
            
            $horarios[$groupId][$date][$timeId] = $horario = $res[0][0];
        }
        
        return $horario;
    }
    
    /**
     * DEPRECATED
     */
    public function obterCronograma($groupId, $dia=null, $personId=null)
    {   
        $msql = new MSQL();
        $msql->setTables('rpthorarios a inner join acdscheduleprofessor c on (a.scheduleid = c.scheduleid) left join acdscheduleprofessorcontent b on (a.occurrencedate = b.date and a.timeid = b.timeid and c.scheduleprofessorid = b.scheduleprofessorid)');

        $msql->setColumns('a.occurrencedate, a.beginhour, a.endhour, a.scheduleid, b.description, a.timeid, b.scheduleprofessorid');

        $sql = " groupid = $groupId ";
        
        if($dia)
        {
            $sql .= " AND a.occurrencedate = '$dia' ";
        }
        
        if( strlen($personId) > 0 )
        {
            $sql .= " AND c.professorid = $personId ";
        }
        
        $msql->setWhere($sql);
        
        $msql->setOrderBy('1, 2');

        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado;
    }

    /**
     * Retorna no formato array associativo
     */
    public function obterCronograma2($groupId, $dia=null, $scheduleId=null)
    {   
        $msql = new MSQL();
        $msql->setTables('rpthorarios a inner join acdscheduleprofessor c on (a.scheduleid = c.scheduleid) left join acdscheduleprofessorcontent b on (a.occurrencedate = b.date and a.timeid = b.timeid and c.scheduleprofessorid = b.scheduleprofessorid)');

        $msql->setColumns('b.scheduleprofessorcontentid, a.occurrencedate, a.beginhour, a.endhour, a.scheduleid, b.description, a.timeid, c.scheduleprofessorid');

        $sql = " groupid = $groupId ";
        
        if($dia)
        {
            $sql .= " AND a.occurrencedate = '$dia' ";
        }
        
        if ( $scheduleId )
        {
            $sql .= " AND a.scheduleid = $scheduleId ";
        }
        
        $msql->setWhere($sql);
        $msql->setOrderBy('1, 2');

        $resultado = SDatabase::queryAssociative($msql);
        
        return $resultado;
    }
    
    public function obterUltimoDiaCronograma($groupId, $personId=null, $dataSchedule=false)
    {
        $msql = new MSQL();
        $msql->setTables('rpthorarios');
        if ( !$dataSchedule )
        {
            $msql->setColumns('acdscheduleprofessorcontent.date');
        }
        else
        {
            $msql->setColumns('acdscheduleprofessorcontent.date || \'_\' || rpthorarios.scheduleid');
        }
        $msql->addInnerJoin('acdscheduleprofessor', 'rpthorarios.scheduleid = acdscheduleprofessor.scheduleid');
        $msql->addInnerJoin('acdscheduleprofessorcontent', '
                acdscheduleprofessorcontent.date = rpthorarios.occurrencedate
            AND acdscheduleprofessor.scheduleprofessorid = acdscheduleprofessorcontent.scheduleprofessorid
            AND rpthorarios.timeid = acdscheduleprofessorcontent.timeid
        ');
        $msql->addEqualCondition('rpthorarios.groupid', $groupId);
        $msql->setWhere('acdscheduleprofessorcontent.description IS NOT NULL');
        
        if( strlen($personId) > 0 )
        {
            $msql->setWhereAnd("acdscheduleprofessor.professorid = $personId");
        }
        
        $msql->setOrderBy('acdscheduleprofessorcontent.date DESC');
        $msql->setLimit(1);

        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado[0][0];
    }

    /**
     * @return string
     */
    public function obterCronogramaDescricao($groupId, $dia=null, $professor=null, $scheduleId=null)
    {
        $msql = new MSQL();
        $msql->setTables('rpthorarios');
        $msql->setColumns('acdscheduleprofessorcontent.description');
        $msql->addInnerJoin('acdscheduleprofessor', 'rpthorarios.scheduleid = acdscheduleprofessor.scheduleid');
        $msql->addInnerJoin('acdscheduleprofessorcontent', '
                acdscheduleprofessorcontent.date = rpthorarios.occurrencedate
            AND acdscheduleprofessor.scheduleprofessorid = acdscheduleprofessorcontent.scheduleprofessorid
            AND rpthorarios.timeid = acdscheduleprofessorcontent.timeid
        ');
        $msql->addEqualCondition('rpthorarios.groupid', $groupId);
        $msql->setWhere('acdscheduleprofessorcontent.description IS NOT NULL');
        
        if ( $dia )
        {
            $msql->addEqualCondition('rpthorarios.occurrencedate', $dia);
        }
        
        if( strlen($professor) > 0 )
        {
            $msql->addEqualCondition('acdscheduleprofessor.professorid', $professor);
        }
        
        if ( $scheduleId )
        {
            $msql->addEqualCondition('rpthorarios.scheduleid', $scheduleId);
        }
        
        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado[0][0];
    }
    
    public function salvarCronograma2($groupId, $descricao, $dia, $scheduleId)
    {
        $MIOLO = MIOLO::getInstance();
        $user = $MIOLO->getLogin();
        $userId = $user->id;
        
        $cronogramas = $this->obterCronograma2($groupId, $dia, $scheduleId);
        
        foreach ( $cronogramas as $cron )
        {
            if ( strlen($cron['scheduleprofessorcontentid']) > 0 )
            {
                $conditions = array(
                    'scheduleprofessorcontentid' => $cron['scheduleprofessorcontentid'],
                    'date' => $cron['occurrencedate'],
                    'timeid' => $cron['timeid']
                );

                $sql = MSQL::updateTable('acdScheduleProfessorContent', array('description' => $descricao, 'username' => $userId), $conditions);
                
                bBaseDeDados::executar($sql);
            }
            else
            {
                $sql = MSQL::insertTable('acdScheduleProfessorContent', array(
                    'scheduleprofessorid' => $cron['scheduleprofessorid'],
                    'date' => $cron['occurrencedate'],
                    'timeid' => $cron['timeid'],
                    'description' => $descricao,
                    'classoccurred' => DB_TRUE,
                    'username' => $userId,
                ));
                
                bBaseDeDados::executar($sql);
            }
        }
    }
    
    /**
     * Retorna a data anterior a qual foi passada, caso exista.
     * 
     * @return string Data anterior
     */
    public function obterDiaAnterior($groupId, $dia)
    {
        $dias = $this->obterDiasDeAula($groupId);
        $anterior = null;
        
        foreach ( $dias as $chave => $reg )
        {
            if ( $reg[0] == $dia )
            {
                $anterior = $dias[$chave - 1][0];
            }
        }
        
        return $anterior;
    }
    
    public function obterDiasDeAula($groupId, $personId=null)
    {
        $msql = new MSQL();
        $msql->setTables('rpthorarios A
                            LEFT JOIN acdscheduleprofessor B
                                   ON A.scheduleid = B.scheduleid');

        $msql->setColumns('distinct A.occurrencedate, A.scheduleid');
        
        $msql->setWhereAnd("A.groupid = $groupId");
        
        if( strlen($personId) > 0 )
        {
            $msql->setWhereAnd("B.professorid = $personId");
        }
        
        $msql->setOrderBy('A.occurrencedate');

        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado;
    }
    
    public function obterHorarioDeAula($groupId, $dia=null, $professor=null, $scheduleId=null)
    {
        $msql = new MSQL();
        $msql->setTables('rpthorarios A
                            LEFT JOIN acdscheduleprofessor B
                                   ON A.scheduleid = B.scheduleid');

        $msql->setColumns('DISTINCT A.occurrencedate, A.beginhour, A.endhour, A.scheduleid, A.timeid');

        $where = " A.groupid = $groupId ";
        
        if($dia)
        {
            $where .= " and occurrencedate = '$dia' ";
        }
        if( strlen($professor) > 0 )
        {
            $where .= " AND B.professorid = $professor";
        }
        
        if ( $scheduleId )
        {
            $where .= " AND A.scheduleid = $scheduleId ";
        }
        
        $msql->setWhere( $where );
        
        $msql->setOrderBy(' occurrencedate, beginhour ');
        
        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado;
    }
    
    public function obterHorarioDeAula2($groupId, $dia=null, $professor=null, $scheduleId=null)
    {
        $msql = new MSQL();
        $msql->setTables('rpthorarios A
                            LEFT JOIN acdscheduleprofessor B
                                   ON A.scheduleid = B.scheduleid');
        $msql->setColumns('DISTINCT A.horarioidunique, A.occurrencedate, A.beginhour, A.endhour, A.scheduleid, A.timeid');
        //$msql->setWhere("groupid = $groupId and occurrencedate = '$dia'");
        
        $where = " groupid = $groupId ";
        
        if($dia)
        {
            $where .= " and occurrencedate = '$dia' ";
        }
        if( strlen($professor) > 0 )
        {
            $where .= " AND B.professorid = $professor ";
        }
        
        if ( $scheduleId )
        {
            $where .= " AND A.scheduleid = $scheduleId ";
        }
        
        $msql->setWhere( $where );
        
        $msql->setOrderBy('occurrencedate, beginhour');
        
        $resultado = SDatabase::queryAssociative($msql);
        
        return $resultado;
    }
    
    /**
     * DEPRECATED
     */
    public function salvarCronograma($description, $data, $scheduleprofessorid)
    {
        $args = array($description, $data, $scheduleprofessorid);
        
        $sql = 'update acdscheduleprofessorcontent set description = ? where date = ? and scheduleprofessorid = ?';

        return bBaseDeDados::executar(SAGU::prepare($sql, $args));
    }
    
    public function salvarPlanoDeCurso($groupid, $observation, $methodology, $objectives)
    {
        $sql = "UPDATE acdgroup 
                   SET observation = $$$observation$$,
                       methodology = $$$methodology$$,
                       objectives =$$$objectives$$
                 WHERE groupid = $groupid";        

        return bBaseDeDados::executar($sql);
    }
    
    public function obterBibliografiaBasica($groupId)
    {
        $msql = new MSQL();
        $msql->setTables('acdgroup');

        $msql->setColumns('distinct unnest(basicbibliography)');

        $msql->setWhere("groupid = $groupId");
        
        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado;
    }
    
    public function removerBibliografiaBasica($descricao, $groupid)
    {
        $sql = "select x.bibliography from (SELECT unnest(basicbibliography) AS bibliography from acdgroup where groupid = '$groupid') x where x.bibliography != '$descricao'";
        $bibliografia = bBaseDeDados::obterInstancia()->_db->query($sql);

        $sql = "UPDATE acdgroup set basicbibliography = NULL where groupid = '$groupid'";
        $ok = bBaseDeDados::executar($sql);
        
        foreach ($bibliografia as $b)
        {
            $ok = $ok && $this->salvarBibliografiaBasica($b[0], $groupid);
        }
            
        return $ok;
    }
    
    public function salvarBibliografiaBasica($descricao, $groupid)
    {
        $sql = "select x.bibliography from (SELECT unnest(basicbibliography) AS bibliography from acdgroup where groupid = '$groupid') x where x.bibliography = $$$descricao$$";
        if( !bBaseDeDados::obterInstancia()->_db->query($sql) )
        {   
            $sql = "UPDATE acdgroup 
                        SET basicbibliography = array_append(
                                                      (SELECT ARRAY(SELECT distinct unnest(basicbibliography) AS bibliography from acdgroup where groupid = '$groupid')),
                                                        $$$descricao$$) 
                    WHERE groupid = '$groupid'";
            $ok =  bBaseDeDados::executar($sql);
        }
        return $ok;
    }
    
    public function obterBibliografiaComplementar($groupId)
    {
        $msql = new MSQL();
        $msql->setTables('acdgroup');

        $msql->setColumns('distinct unnest(complementarybibliography)');

        $msql->setWhere("groupid = $groupId");

        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado;
    }
    
    public function removerBibliografiaComplementar($descricao, $groupid)
    {
        $sql = "select x.bibliography from (SELECT unnest(complementarybibliography) AS bibliography from acdgroup where groupid = '$groupid') x where x.bibliography != $$$descricao$$";
        $bibliografia = bBaseDeDados::obterInstancia()->_db->query($sql);

        $sql = "UPDATE acdgroup set complementarybibliography = NULL where groupid = '$groupid'";
        $ok = bBaseDeDados::executar($sql);
        
        foreach ($bibliografia as $b)
        {
            $ok = $ok && $this->salvarBibliografiaComplementar($b[0], $groupid);
        }
            
        return $ok;
    }
    
    public function salvarBibliografiaComplementar($descricao, $groupid)
    {
        $sql = "select x.bibliography from (SELECT unnest(complementarybibliography) AS bibliography from acdgroup where groupid = '$groupid') x where x.bibliography = $$$descricao$$";
        if( !bBaseDeDados::obterInstancia()->_db->query($sql) )
        {        
            $sql = "UPDATE acdgroup 
                        SET complementarybibliography = array_append(
                                                      (SELECT ARRAY(SELECT distinct unnest(complementarybibliography) AS bibliography from acdgroup where groupid = '$groupid')),
                                                        $$$descricao$$) 
                    WHERE groupid = '$groupid'";
            
            $ok =  bBaseDeDados::executar($sql);
        }
        return $ok;
    }
    
    public function obterAvaliacao($groupId, $degreeId, $professorId, $description)
    {
        $msql = new MSQL();
        $msql->setTables('acdevaluation');

        $msql->setColumns('evaluationid');

        $msql->setWhere("groupid='$groupId' and degreeid='$degreeId' and professorid='$professorId' and description='$description'");

        return bBaseDeDados::consultar($msql);
    }
    
    public function atualizarAvaliacao($evaluationId, $groupId, $degreeId, $professorId, $description, $weight)
    {
        $args = array($description, $weight, $evoluationid);
        $sql = 'update acdevaluation set description = ?, weight = ? where evaluationid = ?';
        return bBaseDeDados::executar(SAGU::prepare($sql, $args));
    }
    
    public function inserirAvaliacao($groupId, $degreeId, $professorId, $description, $weight)
    {
        $args = array($degreeId, $groupId, $professorId, $description, $weight);
        $sql = 'insert into acdevaluation (degreeid, groupid, professorid, description, weight) values (?, ?, ?, ?, ?)';
        return bBaseDeDados::executar(SAGU::prepare($sql, $args));
    }
    
    public function salvarAvaliacao($groupId, $degreeId, $professorId, $description, $weight )
    {
        if($evaluation = $this->obterAvaliacao($groupId, $degreeId, $professorId, $description))
        {
            $evaluationId = $evaluation[0][0];
            $ok = $this->atualizarAvaliacao($evaluationId, $groupId, $degreeId, $professorId, $description, $weight);
        }
        else
        {
            $ok = $this->inserirAvaliacao($groupId, $degreeId, $professorId, $description, $weight);
        }
        
        return $ok;
    }
    
    public function salvarNota($enrollId, $degreeId, $nota, $description = '')
    {
        $MIOLO = MIOLO::getInstance();
        $user = $MIOLO->getLogin();
        $userId = $user->id;
        
        $nota = str_replace(',', '.', $nota);

        $msql = new MSQL();
        $msql->setColumns('username, enrollid, degreeid, note, description');
        $msql->setTables('acddegreeenroll');
        $msql->addParameter($userId);
        $msql->addParameter($enrollId);
        $msql->addParameter($degreeId);            
        $msql->addParameter($nota);
        $msql->addParameter($description);
        
        $sql = $msql->insert();
        
        return bBaseDeDados::executar($sql);
    }
    
    public function salvarConceito($enrollId, $degreeId, $conceito)
    {
        $MIOLO = MIOLO::getInstance();
        $user = $MIOLO->getLogin();
        $userId = $user->id;
        
        $msql = new MSQL();
        $msql->setColumns('username, enrollid, degreeid, concept');
        $msql->setTables('acddegreeenroll');
        $msql->addParameter($userId);
        $msql->addParameter($enrollId);
        $msql->addParameter($degreeId);            
        $msql->addParameter($conceito);
        
        $sql = $msql->insert();
        
        return bBaseDeDados::executar($sql);
    }
    
    public function obterHistorico($enrollId, $degreeId, $usaConceito = false)
    {
        $sql = new MSQL();
        $sql->setTables('acddegreeenroll');
        if ( MUtil::getBooleanValue($usaConceito) )
        {
            $sql->setColumns("to_char(recorddate, 'DD/MM/YYYY HH24:MI:SS'), username, concept, description");
        }
        else
        {
            $sql->setColumns("to_char(recorddate, 'DD/MM/YYYY HH24:MI:SS'), username, note, description");
        }
        $sql->setWhere('enrollid = ?');
        $sql->addParameter($enrollId);
        $sql->setWhere('degreeid = ?');
        $sql->addParameter($degreeId);
        $sql->setOrderBy('recorddate DESC');        
        
        $result = bBaseDeDados::consultar($sql);
        return $result;
    }
    
    public function salvarNotaAvaliacao($evaluationId, $enrollId, $nota, $usaConceito = false)
    {
        $msql = new MSQL();
        $msql->setTables('acdevaluationenroll');
        $msql->setColumns('evaluationenrollid');
        $msql->setWhere("evaluationid = $evaluationId and enrollid = $enrollId");
        $msql->setOrderBy('recorddate DESC');
        
        $resultado = bBaseDeDados::consultar($msql);
        
        if ( $usaConceito )
        {
            if($resultado)
            {
                $args = array($nota, $resultado[0][0]);
                $sql = 'update acdevaluationenroll set concept = ? where evaluationenrollid = ?';
                $ok = bBaseDeDados::executar(SAGU::prepare($sql, $args));
            }
            else
            {
                $args = array($evaluationId, $enrollId, $nota);
                $sql = 'insert into acdevaluationenroll (evaluationid, enrollid, concept) values (?, ?, ?)';
                $ok =  bBaseDeDados::executar(SAGU::prepare($sql, $args));
            }
        }
        else
        {
            if($resultado)
            {
                $args = array($nota, $resultado[0][0]);
                $sql = 'update acdevaluationenroll set note = ? where evaluationenrollid = ?';
                $ok = bBaseDeDados::executar(SAGU::prepare($sql, $args));
            }
            else
            {
                $args = array($evaluationId, $enrollId, $nota);
                $sql = 'insert into acdevaluationenroll (evaluationid, enrollid, note) values (?, ?, ?)';
                $ok =  bBaseDeDados::executar(SAGU::prepare($sql, $args));
            }
        }
        
        return $ok;
    }
    
    public function calcularMediaAvaliacoes($enrollId, $degreeId)
    {
        $busEvaluation = new BusinessAcademicBusEvaluation();
        $busEvaluationEnroll = new BusinessAcademicBusEvaluationEnroll();
        
        $filtros = new stdClass();
        $filtros->degreeId = $degreeId;
        $filtros->groupId = MIOLO::_REQUEST('groupid');
        $evaluations = $busEvaluation->searchEvaluation($filtros);
        
        $nota = 0;
        $peso = 0;
        $media = 0;
                
        foreach ( $evaluations as $evaluation )
        {
            $evaluationEnroll = $busEvaluationEnroll->getEvaluationEnrollCurrentGrade($evaluation[0], $enrollId);
            $nota += $evaluationEnroll * $evaluation[5];
                        
            $peso += $evaluation[5];
        }
        
        if ( $peso > 0 )
        {
            $media = $nota/$peso;
        }
        
        return number_format(str_replace(',', '.', $media), 2);
    }
    
    public function obterAlunosMatriculados($groupId)
    {
        $msql = new MSQL();
        $msql->setTables('acdenroll');

        $msql->setColumns('count(*)');

        $msql->setWhere("groupid = $groupId");

        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado[0][0];
    }
    
    public function obterAlunosCancelados($groupId)
    {
        $msql = new MSQL();
        $msql->setTables('acdenroll');

        $msql->setColumns('count(*)');

        $msql->setWhere("groupid = $groupId and (statusid=5 or statusid=6)");
        
        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado[0][0];
    }
    
    public function obterAlunosRepetentes($groupId)
    {
        $msql = new MSQL();
        $msql->setTables('acdenroll');

        $msql->setColumns('count(*)');

        $msql->setWhere("groupid = $groupId and (statusid=3 or statusid=4)");
        
        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado[0][0];
    }
    
    public function obterAlunosPorCurso($groupId)
    {
        $msql = new MSQL();
        $msql->setColumns('c.name, count(a.*)');

        $msql->setTables('acdenroll a INNER JOIN acdcontract b ON (a.contractid = b.contractid) INNER JOIN acdcourse c ON (b.courseid = c.courseid)');

        $msql->setWhere(" a.groupid = $groupId ");
        
        $msql->setGroupBy(' c.name ');
        
        return bBaseDeDados::consultar($msql);
    }
    
    public function obterAlunosAvaliacao($groupId)
    {
        $MIOLO = MIOLO::getInstance();
        
        // Obter os business necessários.
        $busProfessorFrequency = $MIOLO->getBusiness('services', 'BusProfessorFrequency');
        $busEnrollStatus = $MIOLO->getBusiness('academic', 'BusEnrollStatus');
        $commonForm = new prtCommonForm();
        
        $data = array();
        $estadoDoAluno = array();
        
        $alunos = $busProfessorFrequency->listGroupPupilsEnrolled($groupId);
        
        foreach ( $alunos as $aluno )
        {
             $matricula = $aluno[2];
             $estado = $busEnrollStatus->getEnrollStatus($commonForm->obterEstadoDaMatriculaId($matricula));
             
             $estadoDoAluno[$estado->statusId]['contador']++;
             $estadoDoAluno[$estado->statusId]['descricao'] = $estado->description;
        }
        
        foreach ( $estadoDoAluno as $estAluno )
        {
            $data[] = array($estAluno['descricao'], $estAluno['contador']);
        }
        
        return $data;
    }
    
    public function obterAlunosNotas($groupId)
    {
        $MIOLO = MIOLO::getInstance();
        
        // Obter os business necessários.
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $busEnroll = $MIOLO->getBusiness('academic', 'BusEnroll');
        $busLearningPeriod = $MIOLO->getBusiness('academic', 'BusLearningPeriod');
        
        $group = $busGroup->getGroup($groupId);
        
        $filtros = new stdClass();
        $filtros->groupId = $groupId;
        $matriculas = $busEnroll->searchEnroll($filtros);
        
        $learningPeriod = $busLearningPeriod->getLearningPeriod($group->learningPeriodId);
        
        // Obter alunos e nota final destes e comparar com learningperiod->finalAverage
        $acima = 0;
        $abaixo = 0;
        foreach ( $matriculas as $aluno )
        {
            $enrollId = $aluno[0];
            
            $notas = $this->obterNotas($groupId, $enrollId);
            foreach ( $notas as $key => $nota )
            {
                if ( $nota['final'] )
                {
                    if ( $nota['nota'] >= $learningPeriod->finalAverage )
                    {
                        $acima++;
                    }
                    else
                    {
                        $abaixo++;
                    }
                }
            }
        }
        
        $dados[] = array('ACIMA DA MÉDIA', $acima);
        $dados[] = array('ABAIXO DA MÉDIA', $abaixo);
        
        return $dados;
    }
    
    public function obterAlunosDaDisciplina($groupId)
    {
        $msql = new MSQL();
        $msql->setColumns('A.personId, A.name, A.email, A.residentialphone, A.workphone, A.cellphone');
        $msql->setTables('obterAlunosAtivosDaDisciplina(?) A');
        $msql->addParameter($groupId);
        $msql->setOrderBy('A.name');

        return bBaseDeDados::consultar($msql);
    }
    
    public function obterNomeDisciplina($groupId)
    {      
        $msql = new MSQL();
        $msql->setColumns('B.name AS curriculumCourseName');
        $msql->setTables('
            acdGroup A 
            LEFT JOIN acdCurriculum C ON (C.curriculumId = A.curriculumId)
            LEFT JOIN acdCurricularComponent B ON (B.curricularcomponentid = C.curricularcomponentid AND B.curricularcomponentversion = C.curricularcomponentversion)
            LEFT JOIN acdCourse D ON (D.courseId = C.courseId)');
        $msql->setWhere(" a.groupid = $groupId ");
        
        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado[0][0];
    }
    
    public function isEstadoDetalhado()
    {
        $msql = new MSQL();
        $msql->setTables('acddetailenrollstatus');
        $msql->setColumns('count(*)');
        
        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado[0][0] > 0;
    }
    
    public function isDisciplinaEncerrada($groupId)
    {
        $msql = new MSQL();
        $msql->setTables('acdgroup');
        $msql->setColumns('isclosed');
        $msql->setWhere('groupid = ?');
        $msql->addParameter($groupId);
        
        $resultado = bBaseDeDados::consultar($msql);
        
        return MUtil::getBooleanValue($resultado[0][0]);
    }
    
    public function obterDisciplinasMatriculadas($personId)
    {
        $sql = new MSQL();
        $sql->setColumns('E.groupid');
        $sql->setTables('acdcontract C LEFT JOIN acdenroll E ON (C.personid = ? AND C.contractid = E.contractid)');
        $sql->setWhere('E.statusid = 1');
        $sql->addParameter($personId);
        
        $resultado = bBaseDeDados::consultar($sql);
        
        $disciplinas = array();
        foreach( $resultado as $disciplina )
        {
            $disciplinas[] = $disciplina[0];
        }
        
        return $disciplinas;
    }
    
    public function obterDisciplinasDoProfessor($personId)
    {
        $sql = new MSQL();
        $sql->setColumns('DISTINCT(E.groupid)');
        $sql->setTables('
                            acdenroll E 
                            LEFT JOIN acdschedule S ON (E.groupid = S.groupid) 
                            LEFT JOIN acdscheduleprofessor P ON (P.scheduleid = S.scheduleid)
        ');
        $sql->setWhere('E.statusid = 1');
        $sql->setWhere('P.professorid = ?');
        $sql->addParameter($personId);
        
        $resultado = bBaseDeDados::consultar($sql);
        
        $disciplinas = array();
        foreach( $resultado as $disciplina )
        {
            $disciplinas[] = $disciplina[0];
        }
        
        return $disciplinas;
    }
    
    public function obterDisciplinasDoCoordenador($personId)
    {
        $sql = new MSQL();
        $sql->setColumns('DISTINCT(G.groupid), B.name');
        $sql->setTables('
            acdgroup G 
            LEFT JOIN acdcurriculum C ON (G.curriculumid = C.curriculumid)
            LEFT JOIN acdlearningperiod P ON (G.learningperiodid = P.learningperiodid)
            LEFT JOIN acdCurricularComponent B ON (B.curricularcomponentid = C.curricularcomponentid AND B.curricularcomponentversion = C.curricularcomponentversion)
        ');
        $sql->setWhere('
            C.courseid || C.courseversion IN (SELECT courseid || courseversion FROM acdcoursecoordinator WHERE coordinatorid = ?) 
            AND G.isclosed = false
            AND P.periodid = ?
        ');
        $sql->addParameter($personId);
        $sql->addParameter(SAGU::getParameter('BASIC', 'CURRENT_PERIOD_ID'));
        $sql->setOrderBy('B.name');
        
        $resultado = bBaseDeDados::consultar($sql);
        
        $disciplinas = array();
        foreach( $resultado as $disciplina )
        {
            $disciplinas[] = $disciplina[0];
        }
        
        return $disciplinas;
    }
    
    public function obterListaDeProfessores($personId)
    {
        $sql = new MSQL();
        $sql->setTables('basphysicalpersonprofessor');
        $sql->setColumns('personid, name');
        $sql->setWhere('personid <> ? and situacao = 1');
        $sql->setOrderBy('name');
        $sql->addParameter($personId);
        
        return bBaseDeDados::consultar($sql);
    }
    
    public function obterNotaMaximaDaDisciplina($groupId)
    {
        $busLearningPeriod = new BusinessAcademicBusLearningPeriod();
        return $busLearningPeriod->obterNotaMaximaDaDisciplina($groupId);
    }
    
    public function usaConceito($groupId)
    {
        $sql = new MSQL();
        $sql->setTables('acdgroup');
        $sql->setColumns('useconcept');
        $sql->setWhere('groupid = ?');
        $sql->addParameter($groupId);

        $result = bBaseDeDados::consultar($sql);
        return $result[0][0];
    }
    
    public function obterConceitos($groupId)
    {
        $sql = new MSQL();
        $sql->setTables('acdconcept C LEFT JOIN acdgroup G ON (C.conceptgroupid = G.conceptgroupid)');
        $sql->setColumns('COALESCE(C.subtitle, C.description)');
        $sql->setWhere('G.groupid = ?');
        $sql->setOrderBy('C.description ASC');
        $sql->addParameter($groupId);
        
        $result = bBaseDeDados::consultar($sql);
        $conceitos = array();
        foreach ( $result as $line )
        {
            $conceitos[$line[0]] = $line[0];
        }
        
        return $conceitos;
    }
    
    public function obterRegimeDomiciliar($filtros)
    {
        $acdRegimeDomiciliar = new AcdRegimeDomiciliar();
        
        return $acdRegimeDomiciliar->obterRegimeDomiciliar($filtros);
    }
    
    public function updateOrInsertFrequenceEnroll($data)
    {
        $busFrequenceEnroll = new BusinessAcademicBusFrequenceEnroll();
        
        return $busFrequenceEnroll->updateOrInsertFrequenceEnroll($data);
    }
    
    public function obterMediaDaDisciplina($degreeId, $groupId)
    {
        $sql = new MSQL();
        $sql->setTables("obtermedia($degreeId, $groupId)");
        $sql->setColumns('*');

        $result = bBaseDeDados::consultar($sql);
        
        return $result[0][0];
    }
    
    public function obterPeriodosDoAluno($personId, $orderBy = NULL)
    {
        $busPeriod = new BusinessAcademicBusPeriod();
        
        return $busPeriod->listPupilsPeriods($personId, $orderBy);
    }
    
    public function obterDiasDaDisciplina($groupId)
    {
        $sql = new MSQL();
        $sql->setTables('rpthorarios');
        $sql->setColumns('occurrencedate');
        $sql->setWhere('groupid = ?');
        $sql->addParameter($groupId);
        
        return bBaseDeDados::consultar($sql);
    }
    
    public function obterHorariosDaDisciplina($groupId)
    {
        $sql = new MSQL();
        $sql->setTables('rpthorarios');
        $sql->setColumns("distinct(beginhour || ' - ' || endhour)");
        $sql->setWhere('groupid = ?');
        $sql->addParameter($groupId);
        
        return bBaseDeDados::consultar($sql);
    }
    
    public function obterGrafico($args)
    {
        $chart = NULL;
        
        $MIOLO = MIOLO::getInstance();
        $bus = $MIOLO->getBusiness('academic', 'BusBusinessIntelligence');
        
        switch ($args->grafico)
        {
            case 'estadoContratual':                
                $estados = $bus->obterEstadoContratualPorPeriodo($args->periodo, $args->curso, $args->unidade);
                $dados = array();
                $xTicks = array();
                foreach ( $estados as $key => $estado )
                {
                    $xTicks[] = BString::isUTF8($estado[0]) ? $estado[0] : utf8_encode($estado[0]);
                    $dados[] = $estado[1];
                }
                
                if ( count($dados) )
                {
                    if ( count($xTicks) > 6 )
                    {
                        foreach ( $xTicks as $key => $tick )
                        {
                            $xTicks[$key] = substr($tick, 0, 25);
                        }
                    }
                    
                    $chart = new MChart('estadoContratual_' . rand(), $dados, _M('Número de matrículas, renovações, trancamentos, reingressos e transferências por período.'), MChart::TYPE_PIE);
                    $chart->setYMin(0);
                    $chart->setLegendLabels($xTicks);
                }
                break;
                
            case 'estadoMatriculas':
                $estados = $bus->obterEstadoDasMatriculas($args->periodo, $args->curso, $args->unidade);
                $dados = array();
                $xTicks = array();
                foreach ( $estados as $key => $estado )
                {
                    $xTicks[] = BString::isUTF8($estado[0]) ? $estado[0] : utf8_encode($estado[0]);
                    $dados[] = $estado[1];
                }
                
                if ( count($dados) )
                {
                    if ( count($xTicks) > 6 )
                    {
                        foreach ( $xTicks as $key => $tick )
                        {
                            $xTicks[$key] = substr($tick, 0, 25);
                        }
                    }
                    
                    $chart = new MChart('estadoMatriculas_' . rand(), $dados, _M('Total de alunos aprovados, reprovados e reprovados por falta no período.'), MChart::TYPE_PIE);
                    $chart->setYMin(0);
                    $chart->setLegendLabels($xTicks);
                }
                break;
                
            case 'mediaAlunosDisciplinasSemestre':
                $semestres = $bus->obterMediaDeAlunosPorDisciplinaPorSemestre($args->periodo, $args->curso, $args->unidade);
                $dados = array();
                $xTicks = array();
                foreach ( $semestres as $key => $semestre )
                {
                    $xTicks[] = _M('SEMESTRE ') . $key;
                    $dados[] = $semestre;
                }
                
                if ( count($dados) )
                {
                    if ( count($xTicks) > 6 )
                    {
                        foreach ( $xTicks as $key => $tick )
                        {
                            $xTicks[$key] = substr($tick, 0, 25);
                        }
                    }
                    
                    $chart = new MChart('mediaAlunosDisciplinasSemestre_' . rand(), $dados, _M('Média de alunos por disciplina e semestre do curso.'), MChart::TYPE_BAR);
                    $chart->setYMin(0);
                    $chart->setXTicks($xTicks);
                    $chart->setYAxesLabel(_M("Média de alunos"));
                }
                break;
                
            case 'numeroAlunosSemestre':
                $data = $bus->obterNumeroDeAlunosPorSemestre($args->periodo, $args->curso, $args->unidade);
                $dados = array();
                $xTicks = array();
                foreach ( $data as $key => $line )
                {
                    $xTicks[] = _M('SEMESTRE ') . $line[0];
                    $dados[] = $line[1];
                }
                
                if ( count($dados) )
                {
                    if ( count($xTicks) > 6 )
                    {
                        foreach ( $xTicks as $key => $tick )
                        {
                            $xTicks[$key] = substr($tick, 0, 25);
                        }
                    }
                    
                    $chart = new MChart('numeroAlunosSemestre_' . rand(), $dados, _M('Número de alunos por semestre do curso.'), MChart::TYPE_BAR);
                    $chart->setYMin(0);
                    $chart->setXTicks($xTicks);
                    $chart->setYAxesLabel(_M("Número de alunos"));
                }
                break;
                
            case 'numeroInscritosProcessoSeletivo':
                $data = $bus->obterNumeroInscritosProcessoSeletivo($args->dataInicio, $args->dataFim);
                $dados = array();
                $xTicks = array();
                
                foreach ( $data as $key => $line )
                {
                    $xTicks[] = $line[0] . ' - ' . $line[1];
                    $dados[] = $line[2];
                }
                
                if ( count($dados) )
                {
                    if ( count($xTicks) > 6 )
                    {
                        foreach ( $xTicks as $key => $tick )
                        {
                            $xTicks[$key] = substr($tick, 0, 25);
                        }
                    }
                    
                    $chart = new MChart('numeroInscritosProcessoSeletivo_' . rand(), $dados, _M('Número de inscritos por processo seletivo.'), MChart::TYPE_BAR);
                    $chart->setYMin(0);
                    $chart->setXTicks($xTicks);
                    $chart->setYAxesLabel(_M("Número de inscritos"));
                }
                
                break;
                
            case 'numeroInadimplentesSemestre':
                $data = $bus->obterNumeroInadimplentesPorSemestre($args->periodo, $args->curso, $args->unidade);
                $dados = array();
                $xTicks = array();
                foreach ( $data as $key => $line )
                {
                    $xTicks[] = _M('SEMESTRE ') . $line[0];
                    $dados[] = $line[1];
                }
                
                if ( count($dados) )
                {
                    if ( count($xTicks) > 6 )
                    {
                        foreach ( $xTicks as $key => $tick )
                        {
                            $xTicks[$key] = substr($tick, 0, 25);
                        }
                    }
                    
                    $chart = new MChart('numeroInadimplentesSemestre_' . rand(), $dados, _M('Número de inadimplentes por semestre do curso.'), MChart::TYPE_BAR);
                    $chart->setYMin(0);
                    $chart->setXTicks($xTicks);
                    $chart->setYAxesLabel(_M("Número de inadimplentes"));
                }
                break;
                
            case 'inadimplenciaPeriodo':
                $data = $bus->obterInadimplenciaPorPeriodo($args->periodo, $args->curso, $args->unidade);
                $dados = array();
                $xTicks = array();
                foreach ( $data as $key => $line )
                {
                    $xTicks[] = _M('PERÍODO: ') . $line[0];
                    $dados[] = $line[1];
                }
                
                if ( count($dados) )
                {
                    if ( count($xTicks) > 6 )
                    {
                        foreach ( $xTicks as $key => $tick )
                        {
                            $xTicks[$key] = substr($tick, 0, 25);
                        }
                    }
                    
                    $chart = new MChart('inadimplenciaPeriodo_' . rand(), $dados, _M('Percentual de inadimplências por período.'), MChart::TYPE_BAR);
                    $chart->setYMin(0);
                    $chart->setXTicks($xTicks);
                    $chart->setYAxesLabel(_M("Valor percentual"));
                }
                break;
                
            case 'inadimplenciaAtual':
                $data = $bus->obterInadimplenciaAtual($args->curso, $args->unidade);
                $dados = array();
                $xTicks = array();
                $xTicks[] = _M('VALOR (R$):');
                $dados[] = $data[1];
                
                if ( count($dados) )
                {
                    if ( count($xTicks) > 6 )
                    {
                        foreach ( $xTicks as $key => $tick )
                        {
                            $xTicks[$key] = substr($tick, 0, 25);
                        }
                    }
                    
                    $chart = new MChart('inadimplenciaAtual_' . rand(), $dados, _M('Inadimplência atual, em valor e número de alunos.<br>Total de ' . $data[0] . ' aluno(s).'), MChart::TYPE_BAR);
                    $chart->setYMin(0);
                    $chart->setXTicks($xTicks);
                    $chart->setYAxesLabel(_M("Total de inadimplências"));
                }
                break;

            default:
                break;
        }
        
        return $chart;
    }
    
    public function obterMatriculasDaDisciplina($groupId)
    {
        $sql = new MSQL();
        $sql->setTables('
            acdEnroll A
            INNER JOIN acdContract B
            ON (A.contractId = B.contractId)
            INNER JOIN acdEnrollStatus C
            ON (C.statusId = A.statusId)
            INNER JOIN ONLY basPhysicalPerson D
            ON (B.personId = D.personId)
        ');
        $sql->setColumns('A.enrollid, D.name');
        $sql->setWhere('groupid = ?');
        $sql->addParameter($groupId);
        
        return bBaseDeDados::consultar($sql);
    }
    
    public function obterMatriculasOrientados($groupId, $directorId)
    {
        $sql = new MSQL();
        $sql->setTables('
            acdEnroll A
            INNER JOIN acdContract B
            ON (A.contractId = B.contractId)
            INNER JOIN acdEnrollStatus C
            ON (C.statusId = A.statusId)
            INNER JOIN ONLY basPhysicalPerson D
            ON (B.personId = D.personId)
        ');
        
        $sql->setColumns('A.enrollid, D.name');
        
        $sql->setWhere('A.groupid = ?');
        $sql->addParameter($groupId);
        
        $sql->setWhere('A.enrollid IN (SELECT enrollid FROM acdfinalexaminationdirectors WHERE personid = ?)');
        $sql->addParameter($directorId);
        
        return bBaseDeDados::consultar($sql);
    }
    
    /**
     * Função que verifica se existe alguma disicplina aberta no período vigente que tem conteúdo de aula registrado e não tem todas as frequências lançadas.
     * 
     * @param type $personId
     * @return null
     */
    public static function verificaRegistroDeFrequencia($personId)
    {
        $sql = " select a.groupid, 
                        sr.contractid, 
                        rt.occurrencedate, 
                        xxx.description
                   from acdenroll a 
             inner join acdgroup b
                     on a.groupid = b.groupid
             inner join acdlearningperiod c
                     on c.learningperiodid = b.learningperiodid
             inner join acdcontract sr
                     on sr.contractid = a.contractid
             inner join only basperson df
                     on df.personid = sr.personid
             inner join acdcurriculum ew
                     on ew.curriculumid = b.curriculumid
             inner join acdcurricularcomponent cx
                     on cx.curricularcomponentid = ew.curricularcomponentid
                        and cx.curricularcomponentversion = ew.curricularcomponentversion
             inner join (select unnest(occurrencedates) as occurrencedate, 
                                unnest(timeids) as timeid, 
                                groupid, 
                                scheduleid 
                           from acdschedule fg) as rt
                     on rt.groupid = a.groupid 
             inner join acdscheduleprofessor xx
                     on (rt.scheduleid = xx.scheduleid)
                    and xx.professorid = ?
             inner join acdscheduleprofessorcontent xxx
                     on (xxx.scheduleprofessorid = xx.scheduleprofessorid
                         and rt.timeid = xxx.timeid
                         and xxx.date = rt.occurrencedate
                         and classoccurred = true)
                  WHERE c.periodid = GETPARAMETER('BASIC', 'CURRENT_PERIOD_ID')
                    AND a.statusid = GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::int
                    and xxx.description is not null
                    
                    and xxx.date < now()::date
                    and a.dateenroll <= xxx.date
                    AND NOT EXISTS (select 'x'
                                      from acdfrequenceenroll x
                                     where x.enrollid = a.enrollid
                                       and x.scheduleid = rt.scheduleid
                                       and x.timeid = rt.timeid
                                       and x.frequencydate = rt.occurrencedate)
                    and a.datecancellation is null
                    and getcontractstate(a.contractid) in (GETPARAMETER('BASIC','STATE_CONTRACT_ID_ENROLLED')::int, GETPARAMETER('ACADEMIC','STATE_CONTRACT_ID_ADJUSTMENT')::int)
               group by 1,2,3,4
               order by 1,2,3,4 ";
        
        return SDatabase::query($sql, array($personId));
    }
    
}


?>
