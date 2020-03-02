<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class ASagu extends middleware
{
    private $db;
    //
    // Classe de construção da conexão, utiliza
    // a estrutura sql do miolo para conexão com a base
    // instanciada aqui
    //
    public function __construct()
    {
        try
	{
            $MIOLO = MIOLO::getInstance();
            $this->db = $MIOLO->getDatabase('sagu');
        }
        catch  (Exception $e)
        {
           return $e->getMessage(); 
        }
    }

    //
    // Verifica se o login fornecido tem algum contrato ativo no momento
    //
    public function saguAutenticaPessoa($parametros)
    {
        $sql = 'SELECT DISTINCT true
                  FROM basPerson B
                 WHERE miolousername = ? ';

        $args[] = $parametros[0];

        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        if ($result[0][0] == DB_TRUE)
        {
            return true;
        }
        return false;
    }
    
    public function saguObtemPessoa($parametros)
    {
         $sql = ' SELECT A.sex as "person_sex",
                        A.datebirth as "person_datebirth",
                        A.cityid as "person_cityid",
                        B.name as "person_city_description",
                        B.stateid as "person_stateid",
                        C.name as "person_state_description",
                        A.maritalstatusid as "person_maritalstatusid",
                        D.description as "person_maritalstatus_description",
                        A.specialnecessityid as "person_specialnecessityid",
                        E.description as "person_specialnecessity_description"
                   FROM ONLY basPhysicalPerson A
              LEFT JOIN basCity B
                     ON (A.cityId = B.cityId)
             LEFT JOIN basState C
                     ON (B.stateid = C.stateid)
             LEFT JOIN basMaritalStatus D
                     ON (A.maritalstatusid = D.maritalstatusid)
              LEFT JOIN basspecialnecessity E
                     ON (A.specialnecessityid = E.specialnecessityid )
                    WHERE A.miolousername = ? ';

        $args[] = $parametros[0];

        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        if (is_array($result[0]))
        {
            $dados = new stdClass();
            $dados->person_sex = $result[0][0];
            $dados->person_datebirth = $result[0][1];
            $dados->person_cityid = $result[0][2];
            $dados->person_city_description = $result[0][3];
            $dados->person_stateid = $result[0][4];
            $dados->person_state_description = $result[0][5];
            $dados->person_maritalstatusid = $result[0][6];
            $dados->person_maritalstatus_description = $result[0][7];
            $dados->person_regimetrabalho = $result[0][8];
            $dados->person_specialnecessityid = $result[0][8];
            $dados->person_specialnecessity_description = $result[0][9];
            
            $data = array($dados);
            
            return $data;
        }
        
        return false;
    }
    
    //
    // Verifica se o login fornecido tem algum contrato ativo no momento
    //
    public function saguAutenticaAluno($parametros)
    {
        $sql = 'SELECT DISTINCT true
                  FROM acdContract A
            INNER JOIN basPerson B
                 USING (personId)
                 WHERE EXISTS (SELECT enrollid 
                                 FROM acdenroll AA 
                           INNER JOIN acdgroup BB 
                                USING (groupid) 
                                WHERE AA.contractid = A.contractid 
                                  AND AA.statusId IN ( getParameter(\'ACADEMIC\', \'ENROLL_STATUS_ENROLLED\')::INT)
                                  AND BB.learningperiodid IN (SELECT learningperiodid 
                                                                FROM acdlearningperiod 
                                                               WHERE now()::date 
                                                             BETWEEN begindate 
                                                                 AND enddate))
                   AND miolousername = ? ';
    
        $args[] = $parametros[0];
        
        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        if ($result[0][0] == DB_TRUE)
        {
            return true;
        }
        return false;
    }
    
    //
    // Verifica se o login fornecido tem algum contrato ativo no momento
    //
    public function saguObtemCursosAluno($parametros)
    {
        $sql = ' SELECT DISTINCT A.courseId as "ref_curso",
                                 C.shortname as "curso",
                                 D.turnId as "ref_turno",
                                 D.description as "turno",
                                 E.unitId as "ref_unidade",
                                 E.description as "unidade",
                                 B.sex as "person_sex",
                                 B.datebirth as "person_datebirth",
                                 B.cityid as "person_cityid",
                                 F.name as "person_city_description",
                                 F.stateid as "person_stateid",
                                 G.name as "person_state_description",
                                 B.maritalstatusid as "person_maritalstatusid",
                                 H.description as "person_maritalstatus_description",
                                 A.courseversion as "course_version",
                                 L.centerid as "course_centerid",
                                 K.name as "course_center_description",
                                 C.name as "course_name",
                                 B.specialnecessityid as "person_specialnecessityid",
                                 M.description as "person_specialnecessity_description",
                                 (SELECT datetouser(statetime::date)
                                    FROM obterMovimentacaoContratualDeIngressoDoAluno(A.contractId)) as "date_entry",
                                 (SELECT periodId
                                    FROM (SELECT learningPeriodId
                                            FROM acdmovementcontract
                                           WHERE contractId = A.contractId
                                             AND learningPeriodId IS NOT NULL
                                        ORDER BY stateTime LIMIT 1) AS pId
                              INNER JOIN acdLearningPeriod USING (learningPeriodId)) as "period_entry"
                            FROM acdContract A
                      INNER JOIN ONLY basphysicalperson B
                           USING (personId)
                      INNER JOIN acdcourse C
                              ON (A.courseid=C.courseid)
                      INNER JOIN basTurn D
                              ON (A.turnId = D.turnId)
                      INNER JOIN basUnit E
                              ON (A.unitId = E.unitId)
                      INNER JOIN acdcourseoccurrence L
                              ON (A.courseid = L.courseid AND A.courseversion = L.courseversion AND A.turnid = L.turnid AND A.unitid = L.unitid)
                      LEFT JOIN basCity F
                              ON (B.cityId = F.cityId)
                      LEFT JOIN basState G
                              ON (F.stateid = G.stateid)
                      LEFT JOIN basMaritalStatus H
                              ON (B.maritalstatusid = H.maritalstatusid)
                       LEFT JOIN acdcenter K
                              ON (L.centerId = K.centerId)
                       LEFT JOIN basspecialnecessity M
                              ON (B.specialnecessityid = M.specialnecessityid )
                    WHERE EXISTS (select \'x\' 
                                      from acdcontract a2
                                inner join acdstatecontract b2
                                        on b2.statecontractid = getcontractstate(a2.contractid) 
                                     where a2.contractid = A.contractid
                                       and b2.inouttransition in (\'I\', \'T\'))

                             AND B.miolousername=? ';
        $args[] = $parametros[0];
        $sql = ADatabase::prepare($sql, $args);
        
        $result = ADatabase::query($sql);
        
        if (is_array($result[0]))
        {
            $data = array();
            foreach ($result as $line => $lineData)
            {
                $data[$line]->ref_curso = $lineData[0];
                $data[$line]->curso = $lineData[1];
                $data[$line]->ref_turno = $lineData[2];
                $data[$line]->turno = $lineData[3];
                $data[$line]->ref_unidade = $lineData[4];
                $data[$line]->unidade = $lineData[5];       
                $data[$line]->person_sex = $lineData[6];       
                $data[$line]->person_datebirth = $lineData[7];       
                $data[$line]->person_cityid = $lineData[8];       
                $data[$line]->person_city_description = $lineData[9];       
                $data[$line]->person_stateid = $lineData[10];       
                $data[$line]->person_state_description = $lineData[11];       
                $data[$line]->person_maritalstatusid = $lineData[12];       
                $data[$line]->person_maritalstatus_description = $lineData[13];       
                $data[$line]->course_version = $lineData[14];       
                $data[$line]->course_centerid = $lineData[15];       
                $data[$line]->course_center_description = $lineData[16];   
                $data[$line]->course_name = $lineData[17];   
                $data[$line]->person_specialnecessityid = $lineData[18];   
                $data[$line]->person_specialnecessity_description = $lineData[19];   
                $data[$line]->ref_course = $lineData[0];
                $data[$line]->course = $lineData[1];
                $data[$line]->ref_turn = $lineData[2];
                $data[$line]->turn = $lineData[3];
                $data[$line]->ref_unit = $lineData[4];
                $data[$line]->unit = $lineData[5];      
                $data[$line]->date_entry = $lineData[20];      
                $data[$line]->period_entry = $lineData[21];      
            }
            
            return $data;
        }
        return false;
    }    
    
    
    public function saguObtemDisciplinasAluno($parametros)
    {
        
        $sql = ' SELECT DISTINCT H.curriculumid as "ref_curriculum",
                                 H.curricularcomponentid as "ref_curricular_component",
                                 H.curricularcomponentversion as "ref_curricular_component_version",
                                 I.name as "curricular_component",
                                 A.courseId as "ref_course",
                                 C.shortname as "course", 
                                 D.turnId as "ref_turn",
                                 D.description as "turn",
                                 E.unitId as "ref_unit",
                                 E.description as "unit",
                                 B.sex as "person_sex",
                                 B.datebirth as "person_datebirth",
                                 B.cityid as "person_cityid",
                                 J.name as "person_city_description",
                                 J.stateid as "person_stateid",
                                 K.name as "person_state_description",
                                 B.maritalstatusid as "person_maritalstatusid",
                                 L.description as "person_maritalstatus_description",
                                 A.courseversion as "course_version",
                                 M.centerid as "course_centerid",
                                 N.name as "course_center_description",
                                 C.name as "course_name",
                                 H.curricularcomponentversion as "curricularcomponentversion",
                                 G.groupid as "groupid",
                                 Q.personid as "professorid",
                                 Q.name as "professor_name",
                                 B.specialnecessityid as "person_specialnecessityid",
                                 T.description as "person_specialnecessity_description", 
                                 (SELECT datetouser(statetime::date)
                                    FROM obterMovimentacaoContratualDeIngressoDoAluno(A.contractId)) as "date_entry",
                                 (SELECT periodId
                                    FROM (SELECT learningPeriodId
                                            FROM acdmovementcontract
                                           WHERE contractId = A.contractId
                                             AND learningPeriodId IS NOT NULL
                                        ORDER BY stateTime LIMIT 1) AS pId
                              INNER JOIN acdLearningPeriod USING (learningPeriodId)) as "period_entry"
                            FROM acdContract A
                      INNER JOIN ONLY basPhysicalPerson B
                           USING (personId)
                      INNER JOIN acdcourse C
                              ON (A.courseid=C.courseid)
                      INNER JOIN basTurn D
                              ON (A.turnId = D.turnId)
                      INNER JOIN basUnit E
                              ON (A.unitId = E.unitId)
                      INNER JOIN acdenroll F
                              ON (F.contractid=A.contractid)
                      INNER JOIN acdgroup G 
                              ON (G.groupid=F.groupid)
                       LEFT JOIN acdschedule O
                              ON ( G.groupId = O.groupId)
                       LEFT JOIN acdscheduleprofessor P
                              ON (O.scheduleid = P.scheduleid)
                       LEFT JOIN basphysicalpersonprofessor Q
                              ON (P.professorid = Q.personid)
                      INNER JOIN acdCurriculum H
                              ON (G.curriculumId = H.curriculumId)
                      INNER JOIN acdCurricularComponent I
                              ON (H.curricularcomponentid = I.curricularComponentid 
                              AND H.curricularcomponentversion = I.curricularcomponentversion) 
                      INNER JOIN acdcourseoccurrence M
                              ON (A.courseid = M.courseid AND A.courseversion = M.courseversion AND A.turnid = M.turnid AND A.unitid = M.unitid)
                      LEFT JOIN basCity J
                              ON (B.cityId = J.cityId)
                      LEFT JOIN basState K
                              ON (J.stateid = K.stateid)
                      LEFT JOIN basMaritalStatus L
                              ON (B.maritalstatusid = L.maritalstatusid)
                      LEFT JOIN acdcenter N
                             ON (M.centerId = N.centerId) 
                      LEFT JOIN basspecialnecessity T
                             ON (B.specialnecessityid = T.specialnecessityid )
                           WHERE G.learningperiodid IN (SELECT learningperiodid 
                                                           FROM acdlearningperiod 
                                                          WHERE now()::date 
                                                        BETWEEN begindate 
                                                            AND enddate)
                             AND F.statusId IN ( getParameter(\'ACADEMIC\', \'ENROLL_STATUS_ENROLLED\')::INT)
                        AND B.miolousername=? ';
    
        $args[] = $parametros[0];
        
        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        if (is_array($result[0]))
        {
            $data = array();
            foreach ($result as $line => $lineData)
            {
                $data[$line]->ref_curriculum = $lineData[0];
                $data[$line]->ref_curricular_component = str_replace(' ', '', $lineData[1]);
                $data[$line]->ref_curricular_component_version = $lineData[2];
                $data[$line]->curricular_component = $lineData[3];
                $data[$line]->ref_course = $lineData[4];
                $data[$line]->course = $lineData[5];
                $data[$line]->ref_turn = $lineData[6];
                $data[$line]->turn = $lineData[7];
                $data[$line]->ref_unit = $lineData[8];
                $data[$line]->unit = $lineData[9];
                $data[$line]->person_sex = $lineData[10];
                $data[$line]->person_datebirth = $lineData[11];
                $data[$line]->person_cityid = $lineData[12];
                $data[$line]->person_city_description = $lineData[13];
                $data[$line]->person_stateid = $lineData[14];
                $data[$line]->person_state_description = $lineData[15];
                $data[$line]->person_maritalstatusid = $lineData[16];
                $data[$line]->person_maritalstatus_description = $lineData[17];
                $data[$line]->course_version = $lineData[18];
                $data[$line]->course_centerid = $lineData[19];
                $data[$line]->course_center_description = $lineData[20];
                $data[$line]->course_name = $lineData[21];
                $data[$line]->curricularcomponentversion = $lineData[22];
                $data[$line]->groupid = $lineData[23];
                $data[$line]->professorid = $lineData[24];
                $data[$line]->professor_name = $lineData[25];
                $data[$line]->person_specialnecessityid = $lineData[26];   
                $data[$line]->person_specialnecessity_description = $lineData[27];
                $data[$line]->date_entry = $lineData[28];      
                $data[$line]->period_entry = $lineData[29];
                
            }
            return $data;
        }
        return false;
    }
    
    
    public function saguObtemDisciplinasAlunoPeriodoAnterior($parametros)
    {
        
        $sql = ' SELECT DISTINCT H.curriculumid as "ref_curriculum",
                                 H.curricularcomponentid as "ref_curricular_component",
                                 H.curricularcomponentversion as "ref_curricular_component_version",
                                 I.name as "curricular_component",
                                 A.courseId as "ref_course",
                                 C.shortname as "course", 
                                 D.turnId as "ref_turn",
                                 D.description as "turn",
                                 E.unitId as "ref_unit",
                                 E.description as "unit",
                                 B.sex as "person_sex",
                                 B.datebirth as "person_datebirth",
                                 B.cityid as "person_cityid",
                                 J.name as "person_city_description",
                                 J.stateid as "person_stateid",
                                 K.name as "person_state_description",
                                 B.maritalstatusid as "person_maritalstatusid",
                                 L.description as "person_maritalstatus_description",
                                 A.courseversion as "course_version",
                                 M.centerid as "course_centerid",
                                 N.name as "course_center_description",
                                 C.name as "course_name",
                                 H.curricularcomponentversion as "curricularcomponentversion",
                                 G.groupid as "groupid",
                                 Q.personid as "professorid",
                                 Q.name as "professor_name",
                                 B.specialnecessityid as "person_specialnecessityid",
                                 T.description as "person_specialnecessity_description" 

                            FROM acdContract A
                      INNER JOIN ONLY basPhysicalPerson B
                           USING (personId)
                      INNER JOIN acdcourse C
                              ON (A.courseid=C.courseid)
                      INNER JOIN basTurn D
                              ON (A.turnId = D.turnId)
                      INNER JOIN basUnit E
                              ON (A.unitId = E.unitId)
                      INNER JOIN acdenroll F
                              ON (F.contractid=A.contractid)
                      INNER JOIN acdgroup G 
                              ON (G.groupid=F.groupid)
                       LEFT JOIN acdschedule O
                              ON ( G.groupId = O.groupId)
                       LEFT JOIN acdscheduleprofessor P
                              ON (O.scheduleid = P.scheduleid)
                       LEFT JOIN basphysicalpersonprofessor Q
                              ON (P.professorid = Q.personid)
                      INNER JOIN acdCurriculum H
                              ON (G.curriculumId = H.curriculumId)
                      INNER JOIN acdCurricularComponent I
                              ON (H.curricularcomponentid = I.curricularComponentid 
                              AND H.curricularcomponentversion = I.curricularcomponentversion) 
                      INNER JOIN acdcourseoccurrence M
                              ON (A.courseid = M.courseid AND A.courseversion = M.courseversion AND A.turnid = M.turnid AND A.unitid = M.unitid)
                      LEFT JOIN basCity J
                              ON (B.cityId = J.cityId)
                      LEFT JOIN basState K
                              ON (J.stateid = K.stateid)
                      LEFT JOIN basMaritalStatus L
                              ON (B.maritalstatusid = L.maritalstatusid)
                      LEFT JOIN acdcenter N
                             ON (M.centerId = N.centerId) 
                      LEFT JOIN basspecialnecessity T
                             ON (B.specialnecessityid = T.specialnecessityid ) 
                           WHERE G.learningperiodid IN (SELECT learningperioranterior FROM (
                                                                                            SELECT (SELECT B.learningperiodid 
                                                                                                      FROM acdlearningperiod b 
                                                                                                     WHERE A.courseid = B.courseid 
                                                                                                       AND A.courseversion = B.courseversion 
                                                                                                       AND A.turnid = B.turnid 
                                                                                                       AND A.unitid = B.unitid 
                                                                                                       AND B.enddate < A.begindate 
                                                                                                  ORDER BY enddate DESC 
                                                                                                     LIMIT 1) AS learningperioranterior
                                                                                               FROM acdlearningperiod A   
                                                                                              WHERE now()::date BETWEEN begindate AND enddate ) as period WHERE learningperioranterior IS NOT NULL)
                             AND F.statusId IN ( getParameter(\'ACADEMIC\', \'ENROLL_STATUS_ENROLLED\')::INT,
                                                 getParameter(\'ACADEMIC\', \'ENROLL_STATUS_APPROVED\')::INT, 
                                                 getParameter(\'ACADEMIC\', \'ENROLL_STATUS_DISAPPROVED\')::INT,
                                                 getParameter(\'ACADEMIC\', \'ENROLL_STATUS_DISAPPROVED_FOR_LACKS\')::INT)
                        AND B.miolousername=? ';
    
        $args[] = $parametros[0];
        
        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        if (is_array($result[0]))
        {
            $data = array();
            foreach ($result as $line => $lineData)
            {
                $data[$line]->ref_curriculum = $lineData[0];
                $data[$line]->ref_curricular_component = str_replace(' ', '', $lineData[1]);
                $data[$line]->ref_curricular_component_version = $lineData[2];
                $data[$line]->curricular_component = $lineData[3];
                $data[$line]->ref_course = $lineData[4];
                $data[$line]->course = $lineData[5];
                $data[$line]->ref_turn = $lineData[6];
                $data[$line]->turn = $lineData[7];
                $data[$line]->ref_unit = $lineData[8];
                $data[$line]->unit = $lineData[9];
                $data[$line]->person_sex = $lineData[10];
                $data[$line]->person_datebirth = $lineData[11];
                $data[$line]->person_cityid = $lineData[12];
                $data[$line]->person_city_description = $lineData[13];
                $data[$line]->person_stateid = $lineData[14];
                $data[$line]->person_state_description = $lineData[15];
                $data[$line]->person_maritalstatusid = $lineData[16];
                $data[$line]->person_maritalstatus_description = $lineData[17];
                $data[$line]->course_version = $lineData[18];
                $data[$line]->course_centerid = $lineData[19];
                $data[$line]->course_center_description = $lineData[20];
                $data[$line]->course_name = $lineData[21];
                $data[$line]->curricularcomponentversion = $lineData[22];
                $data[$line]->groupid = $lineData[23];
                $data[$line]->professorid = $lineData[24];
                $data[$line]->professor_name = $lineData[25];
                $data[$line]->person_specialnecessityid = $lineData[26];   
                $data[$line]->person_specialnecessity_description = $lineData[27];
                
            }
            return $data;
        }
        return false;
    }
    
    /**
     * Obtém os dados do aluno.
     * 
     * @param: String $parametros Login do aluno.
     * @return array de uma posição com object Objeto com os dados do aluno.
     */
    public function saguObtemAluno($parametros)
    {
        $alunoCursos = $this->saguObtemCursosAluno($parametros);
        
        if ( is_array($alunoCursos) )
        {
            return array($alunoCursos[0]);
        }
        else
        {
            return false;
        }
    }

    //
    // Obtém os dados do professor
    //
    public function saguAutenticaProfessor($parametros)
    {
        $sql = ' SELECT DISTINCT true
                            FROM acdgroup
                      INNER JOIN acdlearningperiod
                           USING (learningperiodid)
                      INNER JOIN acdschedule
                           USING (groupid)
                      INNER JOIN acdscheduleprofessor
                           USING (scheduleid)
                      INNER JOIN ONLY basPhysicalPerson
                              ON (acdScheduleProfessor.professorid=basPhysicalPerson.personId)
                           WHERE now()::date BETWEEN begindate AND enddate
                             AND basPhysicalPerson.miolousername = ? ';

        $args[] = $parametros[0];
        
        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        if ($result[0][0] == DB_TRUE)
        {
            return true;
        }
        return false;
    }
    
    
    /**
     * Obtém os dados do professor.
     * 
     * @param String $parametros login da pessoa.
     * @return array|boolean
     */
    public function saguObtemProfessor($parametros)
    {
        $sql = ' SELECT A.sex as "person_sex",
                        A.datebirth as "person_datebirth",
                        A.cityid as "person_cityid",
                        B.name as "person_city_description",
                        B.stateid as "person_stateid",
                        C.name as "person_state_description",
                        A.maritalstatusid as "person_maritalstatusid",
                        D.description as "person_maritalstatus_description",
                        A.regimetrabalho as "person_regimetrabalho",
                        A.specialnecessityid as "person_specialnecessityid",
                        E.description as "person_specialnecessity_description"
                   FROM ONLY basPhysicalPersonProfessor A
              LEFT JOIN basCity B
                     ON (A.cityId = B.cityId)
             LEFT JOIN basState C
                     ON (B.stateid = C.stateid)
             LEFT JOIN basMaritalStatus D
                     ON (A.maritalstatusid = D.maritalstatusid)
              LEFT JOIN basspecialnecessity E
                     ON (A.specialnecessityid = E.specialnecessityid )
                    WHERE A.miolousername = ? ';

        $args[] = $parametros[0];

        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        if (is_array($result[0]))
        {
            $dados = new stdClass();
            $dados->person_sex = $result[0][0];
            $dados->person_datebirth = $result[0][1];
            $dados->person_cityid = $result[0][2];
            $dados->person_city_description = $result[0][3];
            $dados->person_stateid = $result[0][4];
            $dados->person_state_description = $result[0][5];
            $dados->person_maritalstatusid = $result[0][6];
            $dados->person_maritalstatus_description = $result[0][7];
            $dados->person_regimetrabalho = $result[0][8];
            $dados->person_specialnecessityid = $result[0][8];
            $dados->person_specialnecessity_description = $result[0][9];
            
            $data = array($dados);
            
            return $data;
        }
        return false;
    }
    
    /**
     * Obtém os dados do coordenador.
     * 
     * @param String $parametros login da pessoa.
     * @return array|boolean
     */
    public function saguObtemCoodernador($parametros)
    {
        $sql = ' SELECT B.sex as "person_sex",
                        B.datebirth as "person_datebirth",
                        B.cityid as "person_cityid",
                        C.name as "person_city_description",
                        C.stateid as "person_stateid",
                        D.name as "person_state_description",
                        B.maritalstatusid as "person_maritalstatusid",
                        E.description as "person_maritalstatus_description",
                        B.regimetrabalho as "person_regimetrabalho",
                        B.specialnecessityid as "person_specialnecessityid",
                        F.description as "person_specialnecessity_description"
                   FROM acdCourseCoordinator A
             INNER JOIN ONLY basPhysicalPersonProfessor B
                     ON ( A.coordinatorId = B.personId )
              LEFT JOIN basCity C
                     ON (B.cityId = C.cityId)
              LEFT JOIN basState D
                     ON (C.stateid = D.stateid)
              LEFT JOIN basMaritalStatus E
                     ON (B.maritalstatusid = E.maritalstatusid)
              LEFT JOIN basspecialnecessity F
                     ON (B.specialnecessityid = F.specialnecessityid )
                  WHERE B.miolousername = ?
                    AND A.enddate IS NULL ';

        $args[] = $parametros[0];

        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        if (is_array($result[0]))
        {
            $dados = new stdClass();
            $dados->person_sex = $result[0][0];
            $dados->person_datebirth = $result[0][1];
            $dados->person_cityid = $result[0][2];
            $dados->person_city_description = $result[0][3];
            $dados->person_stateid = $result[0][4];
            $dados->person_state_description = $result[0][5];
            $dados->person_maritalstatusid = $result[0][6];
            $dados->person_maritalstatus_description = $result[0][7];
            $dados->person_regimetrabalho = $result[0][8];
            $dados->person_specialnecessityid = $result[0][9];
            $dados->person_specialnecessity_description = $result[0][10];
            
            $data = array($dados);
            return $data;
        }
        return false;
    } 
    
    /**
     * Obtém os cursos do coordenador.
     * 
     * @param String $parametros login da pessoa.
     * @return array|boolean
     */
    public function saguObtemCursosCoodernador($parametros)
    {
        $sql = 'SELECT B.sex as "person_sex",
                                 B.datebirth as "person_datebirth",
                                 B.cityid as "person_cityid",
                                 C.name as "person_city_description",
                                 C.stateid as "person_stateid",
                                 D.name as "person_state_description",
                                 B.maritalstatusid as "person_maritalstatusid",
                                 E.description as "person_maritalstatus_description",
                                 B.regimetrabalho as "person_regimetrabalho",
                                 F.centerid as "course_centerid",
                                 H.name as "course_center_description",
                                 G.courseid as "ref_course",
                                 G.name as "course",
                                 F.courseversion as "course_version",
                                 F.turnid as "ref_turn",
                                 I.description as "turn",
                                 F.unitid as "ref_unit",
                                 J.description as "unit",
                                 B.specialnecessityid as "person_specialnecessityid",
                                 L.description as "person_specialnecessity_description"
                            FROM acdCourseCoordinator A
                      INNER JOIN ONLY basPhysicalPersonProfessor B
                              ON ( A.coordinatorId = B.personId )
                      INNER JOIN acdcourseoccurrence F
                              ON (A.courseid = F.courseid AND A.courseversion = F.courseversion AND A.turnid = F.turnid AND A.unitid = F.unitid ) 
                      INNER JOIN acdcourse G
                              ON (F.courseid = G.courseid)
                      INNER JOIN basTurn I
                              ON (F.turnId = I.turnId)
                      INNER JOIN basUnit J
                              ON (F.unitId = J.unitId)  
                       LEFT JOIN basCity C
                              ON (B.cityId = C.cityId)
                       LEFT JOIN basState D
                              ON (C.stateid = D.stateid)
                       LEFT JOIN basMaritalStatus E
                              ON (B.maritalstatusid = E.maritalstatusid)
                       LEFT JOIN acdcenter H
                              ON (F.centerId = H.centerId)
                       LEFT JOIN basspecialnecessity L
                              ON (B.specialnecessityid = L.specialnecessityid )
                          WHERE B.miolousername = ?
                            AND A.enddate IS NULL ';

        $args[] = $parametros[0];

        $sql = ADatabase::prepare($sql, $args);
        
        $result = ADatabase::query($sql);
        if (is_array($result[0]))
        {
            $data = array();
            foreach ($result as $line => $lineData)
            {
                $data[$line]->person_sex = $lineData[0];
                $data[$line]->person_datebirth = $lineData[1];
                $data[$line]->person_cityid = $lineData[2];
                $data[$line]->person_city_description = $lineData[3];
                $data[$line]->person_stateid = $lineData[4];
                $data[$line]->person_state_description = $lineData[5];
                $data[$line]->person_maritalstatusid = $lineData[6];
                $data[$line]->person_maritalstatus_description = $lineData[7];
                $data[$line]->person_regimetrabalho = $lineData[8];
                $data[$line]->course_centerid = $lineData[9];
                $data[$line]->course_center_description = $lineData[10];
                $data[$line]->ref_course = $lineData[11];
                $data[$line]->course = $lineData[12];
                $data[$line]->course_version = $lineData[13];
                $data[$line]->ref_turn = $lineData[14];
                $data[$line]->turn = $lineData[15];
                $data[$line]->ref_unit = $lineData[16];
                $data[$line]->unit = $lineData[17];
                $data[$line]->person_specialnecessityid = $lineData[18];
                $data[$line]->person_specialnecessity_description = $lineData[19];
            }

            return $data;
        }
        return false;
    }

    //
    // Obtém os dados do coordenador
    //
    public function saguAutenticaCoordenador($parametros)
    {
        $sql = ' SELECT DISTINCT true
                            FROM acdCourseCoordinator A1
                      INNER JOIN basPhysicalPerson B1
                              ON ( A1.coordinatorId = B1.personId )
                           WHERE B1.miolousername = ?
                             AND (now()::date between A1.begindate AND A1.enddate) OR (A1.enddate IS NULL) ';

        $args[] = $parametros[0];
        
        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        
        return $result[0][0] == DB_TRUE;        
    }

    //
    // Obtém os dados das disciplinas do professor
    //
    public function saguObtemDisciplinasProfessor($parametros)
    {
          $sql = '  SELECT DISTINCT A.groupid as "ref_group",
                                    E.courseid as "ref_course",
                                    getCourseName(E.courseId) as "course",
                                    E.curriculumid as "ref_curriculum",
                                    E.curricularcomponentid as "ref_curricular_component",
                                    getCurricularComponentName(E.curricularComponentId),
                                    getTurnDescription(E.turnId) as "turn",
                                    E.unitId as "ref_unit",
                                    getUnitDescription(E.unitId) as "unit",
                                    F.sex as "person_sex",
                                    F.datebirth as "person_datebirth",
                                    F.cityid as "person_cityid",
                                    G.name as "person_city_description",
                                    G.stateid as "person_stateid",
                                    H.name as "person_state_description",
                                    F.maritalstatusid as "person_maritalstatusid",
                                    I.description as "person_maritalstatus_description",
                                    F.regimetrabalho as "person_regimetrabalho",
                                    E.courseversion as "courseverion",
                                    E.centerid as "course_centerid",
                                    J.name as "course_center_description",
                                    E.curricularcomponentversion as "ref_curriculum_version",
                                    A.totalenrolled as "totalenrolled",
                                    F.specialnecessityid as "person_specialnecessityid",
                                    L.description as "person_specialnecessity_description"
                               FROM acdgroup A
                         INNER JOIN acdlearningperiod B
                                 ON (A.learningperiodid=B.learningperiodid)
                         INNER JOIN acdschedule C
                                 ON (A.groupid=C.groupid)
                         INNER JOIN acdscheduleprofessor D
                                 ON (C.scheduleid=D.scheduleid)
                         INNER JOIN acdcurriculum E
                                 ON (A.curriculumid=E.curriculumid)
                         LEFT JOIN acdcenter J
                                ON (E.centerId = J.centerId)    		
                         INNER JOIN ONLY basphysicalpersonProfessor F
                                 ON (F.personId = D.professorid)
                          LEFT JOIN basCity G
                                 ON (F.cityId = G.cityId)
                          LEFT JOIN basState H
                                 ON (G.stateid = H.stateid)
                          LEFT JOIN basMaritalStatus I
                                 ON (F.maritalstatusid = I.maritalstatusid)
                          LEFT JOIN basspecialnecessity L
                                 ON (F.specialnecessityid = L.specialnecessityid )
                              WHERE now()::date
                            BETWEEN B.begindate AND B.enddate
                                AND A.isClosed=false AND A.isCancellation=false
                                AND F.miolousername = ? ';

        $args[] = $parametros[0];

        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        if (is_array($result[0]))
        {
            $data = array();
            foreach ($result as $line => $lineData)
            {
                $data[$line]->ref_group = $lineData[0];
                $data[$line]->ref_course = $lineData[1];
                $data[$line]->course= $lineData[2];
                $data[$line]->ref_curriculum = $lineData[3];
                $data[$line]->ref_curricular_component = str_replace(' ', '', $lineData[4]);
                $data[$line]->curricular_component = $lineData[5];
                $data[$line]->turn = $lineData[6];
                $data[$line]->ref_unit = $lineData[7];
                $data[$line]->unit = $lineData[8];
                $data[$line]->person_sex = $lineData[9];
                $data[$line]->person_datebirth = $lineData[10];
                $data[$line]->person_cityid = $lineData[11];
                $data[$line]->person_city_description = $lineData[12];
                $data[$line]->person_stateid = $lineData[13];
                $data[$line]->person_state_description = $lineData[14];
                $data[$line]->person_maritalstatusid = $lineData[15];
                $data[$line]->person_maritalstatus_description = $lineData[16];
                $data[$line]->person_regimetrabalho = $lineData[17];
                $data[$line]->courseverion = $lineData[18];
                $data[$line]->course_centerid = $lineData[19];
                $data[$line]->course_center_description = $lineData[20];
                $data[$line]->ref_curriculum_version = $lineData[21];
                $data[$line]->totalenrolled = $lineData[22];
                $data[$line]->person_specialnecessityid = $lineData[23];
                $data[$line]->person_specialnecessity_description = $lineData[24];
                
            }
            return $data;
        }
        return false;
    }
    
    //
    // Obtém os dados dos cursos do professor
    //
    public function saguObtemCursosProfessor($parametros)
    {
        $sql = ' SELECT DISTINCT E.courseid as "ref_course",
                                 G.name as "course",
                                 H.formationlevelid as "ref_formation_level",
                                 H.description as "formation_level",
                                 F.sex as "person_sex",
                                 F.datebirth as "person_datebirth",
                                 F.cityid as "person_cityid",
                                 I.name as "person_city_description",
                                 I.stateid as "person_stateid",
                                 J.name as "person_state_description",
                                 F.maritalstatusid as "person_maritalstatusid",
                                 K.description as "person_maritalstatus_description",
                                 F.regimetrabalho as "person_regimetrabalho",
                                 E.turnId as "ref_turno",
                                 L.description as "turno",
                                 E.unitId as "ref_unidade",
                                 M.description as "unidade",
                                 E.centerid as "course_centerid",
                                 N.name as"course_center_description",
                                 F.specialnecessityid as "person_specialnecessityid",
                                 O.description as "person_specialnecessity_description"
                            FROM acdgroup A
                      INNER JOIN acdlearningperiod B
                              ON (A.learningperiodid=B.learningperiodid)
                      INNER JOIN acdschedule C
                              ON (A.groupid=C.groupid)
                      INNER JOIN acdscheduleprofessor D
                              ON (C.scheduleid=D.scheduleid)
                      INNER JOIN acdcurriculum E
                              ON (A.curriculumid=E.curriculumid)
                      INNER JOIN ONLY basphysicalpersonProfessor F
                              ON (F.personId = D.professorid)
                      INNER JOIN acdCourse G
                              ON (G.courseId=E.courseId)
                      INNER JOIN acdFormationLevel H
                              ON (G.formationLevelId=H.formationLevelId)
                      LEFT JOIN  basTurn L
                              ON (E.turnId = L.turnId)
                      LEFT JOIN basUnit M
                              ON (E.unitId = M.unitId)
                       LEFT JOIN basCity I
                              ON (F.cityId = I.cityId)
                       LEFT JOIN basState J
                              ON (I.stateid = J.stateid)
                       LEFT JOIN basMaritalStatus K
                              ON (F.maritalstatusid = K.maritalstatusid)
                       LEFT JOIN acdcenter N
                              ON (E.centerId = N.centerId) 
                       LEFT JOIN basspecialnecessity O
                              ON (F.specialnecessityid = O.specialnecessityid ) 
                           WHERE now()::date
                         BETWEEN B.begindate AND B.enddate
                             AND A.isClosed=false AND A.isCancellation=false
                             AND F.miolousername = ? ';
        
        $args[] = $parametros[0];

        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        if (is_array($result[0]))
        {
            $data = array();
            foreach ($result as $line => $lineData)
            {
                $data[$line]->ref_course = $lineData[0];
                $data[$line]->course= $lineData[1];
		$data[$line]->ref_formation_level = $lineData[2];
		$data[$line]->formation_level = $lineData[3];
                $data[$line]->person_sex = $lineData[4];
		$data[$line]->person_datebirth = $lineData[5];
		$data[$line]->person_cityid = $lineData[6];
		$data[$line]->person_city_description = $lineData[7];
		$data[$line]->person_stateid = $lineData[8];
		$data[$line]->person_state_description = $lineData[9];
		$data[$line]->person_maritalstatusid = $lineData[10];
		$data[$line]->person_maritalstatus_description = $lineData[11];
		$data[$line]->person_regimetrabalho = $lineData[12];
		$data[$line]->ref_turno = $lineData[13];
		$data[$line]->turno = $lineData[14];
		$data[$line]->ref_unidade = $lineData[15];
		$data[$line]->unidade = $lineData[16];   
		$data[$line]->course_centerid = $lineData[17];   
		$data[$line]->course_center_description = $lineData[18];
                $data[$line]->person_specialnecessityid = $lineData[19];
                $data[$line]->person_specialnecessity_description = $lineData[20];
                $data[$line]->ref_turn = $lineData[13];
		$data[$line]->turn = $lineData[14];
		$data[$line]->ref_unit = $lineData[15];
		$data[$line]->unit = $lineData[16];   
            }
            return $data;
        }
        return false;
    }
    
    //
    // Obtém os dados do professor
    //
    public function saguAutenticaFuncionario($parametros)
    {
        $sql = ' SELECT DISTINCT true
                            FROM ONLY basPhysicalPersonEmployee
                      INNER JOIN basEmployee
                              ON (basPhysicalPersonEmployee.personId = basEmployee.personid)
                           WHERE now()::date 
                                BETWEEN COALESCE(beginDate, now()::date) 
                                    AND COALESCE(endDate, now()::date)
                             AND basphysicalpersonemployee.miolousername = ? ';

        $args[] = $parametros[0];
        
        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        if ($result[0][0] == DB_TRUE)
        {
            return true;
        }
        return false;
    }

    
   /**
     * Obtém os dados do funcionário.
     * 
     * @param String $parametros login da pessoa.
     * @return array|boolean
     */
    public function saguObtemFuncionario($parametros)
    {
        $sql = 'SELECT  A.sex as "person_sex",
                        A.datebirth as "person_datebirth",
                        A.cityid as "person_cityid",
                        C.name as "person_city_description",
                        c.stateid as "person_stateid",
                        D.name as "person_state_description",
                        A.maritalstatusid as "person_maritalstatusid",
                        E.description as "person_maritalstatus_description",
                        B.employeeTypeId as "person_employee_typeid",
                        G.description as "person_employee_description",
                        B.sectorId as "ref_sector",
                        F.description as "sector",
                        B.weeklyhours as "person_weeklyhours",
                        B.unitid as "ref_unit",
                        H.description as "unit",
                        A.specialnecessityid as "person_specialnecessityid",
                        I.description as "person_specialnecessity_description" 
                   FROM ONLY basPhysicalPersonEmployee A
             INNER JOIN basEmployee B
                     ON (A.personId = B.personId)
             LEFT JOIN basSector F
                     ON (B.sectorid = F.sectorid)
              LEFT JOIN basEmployeeType G
                     ON (B.employeeTypeid = G.employeeTypeid)
              LEFT JOIN basUnit H
                     ON (B.unitId = H.unitId)
              LEFT JOIN basCity C
                     ON (A.cityId = C.cityId)
              LEFT JOIN basState D
                     ON (C.stateid = D.stateid)
              LEFT JOIN basMaritalStatus E
                     ON (A.maritalstatusid = E.maritalstatusid)
              LEFT JOIN basspecialnecessity I
                     ON (A.specialnecessityid = I.specialnecessityid )         
                  WHERE now()::date 
                BETWEEN COALESCE(B.beginDate, now()::date) 
                   AND COALESCE(B.endDate, now()::date)
                   AND A.miolousername = ? ';
        $args[] = $parametros[0];

        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        
        if (is_array($result[0]))
        {
            $dados = new stdClass();
            $dados->person_sex = $result[0][0];
            $dados->person_datebirth = $result[0][1];
            $dados->person_cityid = $result[0][2];
            $dados->person_city_description = $result[0][3];
            $dados->person_stateid = $result[0][4];
            $dados->person_state_description = $result[0][5];
            $dados->person_maritalstatusid = $result[0][6];
            $dados->person_maritalstatus_description = $result[0][7];
            $dados->person_employee_typeid = $result[0][8];
            $dados->person_employee_description = $result[0][9];
            $dados->ref_sector = $result[0][10];
            $dados->sector = $result[0][11];
            $dados->person_weeklyhours = $result[0][12];
            $dados->ref_unit = $result[0][13];
            $dados->unit = $result[0][14];
            $dados->person_specialnecessityid = $result[0][15];
            $dados->person_specialnecessity_description = $result[0][16];
            
            $data = array($dados);
            return $data;
        }
        return false;
    }
    
    
    //
    // Obtém os dados dos cursos do professor
    //
    public function saguObtemSetorFuncionario($parametros)
    {
        $sql = ' SELECT  A.sex as "person_sex",
                        A.datebirth as "person_datebirth",
                        A.cityid as "person_cityid",
                        C.name as "person_city_description",
                        c.stateid as "person_stateid",
                        D.name as "person_state_description",
                        A.maritalstatusid as "person_maritalstatusid",
                        E.description as "person_maritalstatus_description",
                        B.employeeTypeId as "person_employee_typeid",
                        G.description as "person_employee_description",
                        B.sectorId as "ref_sector",
                        F.description as "sector",
                        B.weeklyhours as "person_weeklyhours",
                        B.unitid as "ref_unit",
                        H.description as "unit",
                        A.specialnecessityid as "person_specialnecessityid",
                        I.description as "person_specialnecessity_description" 
                   FROM ONLY basPhysicalPersonEmployee A
             INNER JOIN basEmployee B
                     ON (A.personId = B.personId)
             LEFT JOIN basSector F
                     ON (B.sectorid = F.sectorid)
              LEFT JOIN basEmployeeType G
                     ON (B.employeeTypeid = G.employeeTypeid)
              LEFT JOIN basUnit H
                     ON (B.unitId = H.unitId)
              LEFT JOIN basCity C
                     ON (A.cityId = C.cityId)
              LEFT JOIN basState D
                     ON (C.stateid = D.stateid)
              LEFT JOIN basMaritalStatus E
                     ON (A.maritalstatusid = E.maritalstatusid)
              LEFT JOIN basspecialnecessity I
                     ON (A.specialnecessityid = I.specialnecessityid )         
                  WHERE now()::date 
                BETWEEN COALESCE(B.beginDate, now()::date) 
                   AND COALESCE(B.endDate, now()::date)
                   AND A.miolousername = ? ';
        $args[] = $parametros[0];

        $sql = ADatabase::prepare($sql, $args);
        $result = ADatabase::query($sql);
        if (is_array($result[0]))
        {
            $data = array();
            foreach ($result as $line => $lineData)
            {
                $data[$line]->person_sex = $lineData[0];
                $data[$line]->person_datebirth = $lineData[1];
                $data[$line]->person_cityid = $lineData[2];
                $data[$line]->person_city_description = $lineData[3];
                $data[$line]->person_stateid = $lineData[4];
                $data[$line]->person_state_description = $lineData[5];
                $data[$line]->person_maritalstatusid = $lineData[6];
                $data[$line]->person_maritalstatus_description = $lineData[7];
                $data[$line]->person_employee_typeid = $lineData[8];
                $data[$line]->person_employee_description = $lineData[9];
                $data[$line]->ref_sector = $lineData[10];
                $data[$line]->sector = $lineData[11];
                $data[$line]->person_weeklyhours = $lineData[12];
                $data[$line]->ref_unit = $lineData[13];
                $data[$line]->unit = $lineData[14];
                $data[$line]->person_specialnecessityid = $lineData[15];
                $data[$line]->person_specialnecessity_description = $lineData[16];
            }
            
            return $data;
        }
        return false;
    }
}
?>
