CREATE OR REPLACE VIEW user_sagu AS
SELECT DISTINCT
        login,
        password,
        personid,
        name,
        mail,
        city,
        isstudent,
        isprofessor,
        isemployee,
        iscoordinator,
        cpf,

        -- Verifica se esta ATIVO (caso seja aluno E/OU professor E/OU funcionario e em ALGUM deles retornar verdadeiro, retorna positivo)
        ( TRUE IN (
            isstudent AND EXISTS(SELECT 1 FROM acdcontract C WHERE X.personid = C.personid AND iscontractclosed(C.contractid) IS FALSE),
            isprofessor AND isprofessoractive(X.personid),
            isemployee AND isemployeeactive(X.personid)
        ) ) AS isactive
  FROM
        ( SELECT m.login,
                 m.m_password::text as password,
                 p.personId,
                 p.name,
                 LOWER(p.email::text) AS mail,
                 C.name AS city,
                 EXISTS(SELECT 1 FROM basphysicalpersonstudent WHERE personid = p.personid) AS isstudent,
                 EXISTS(SELECT 1 FROM basphysicalpersonprofessor WHERE personid = p.personid) AS isprofessor,
                 EXISTS(SELECT 1 FROM basphysicalpersonemployee WHERE personid = p.personid) AS isemployee,
                 EXISTS(SELECT 1 FROM acdcoursecoordinator WHERE coordinatorid = p.personid) AS iscoordinator,
                 getpersondocument(p.personId, GETPARAMETER('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::int) AS cpf
            FROM miolo_user m
 INNER JOIN ONLY basphysicalperson p ON m.login::text = p.miolousername::text
      INNER JOIN bascity c ON c.cityid = p.cityid
           WHERE COALESCE (p.email,'') != '' AND COALESCE (c.name,'') != '' ) X;
