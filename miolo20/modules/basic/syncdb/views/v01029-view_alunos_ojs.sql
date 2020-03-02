CREATE OR REPLACE VIEW view_alunos_ojs as
/*************************************************************************************
  NOME: view_alunos_ojs
  DESCRIÇÃO: Visão para integração com o sistema OJS. Desenvolvido para a URCAMP
**************************************************************************************/
SELECT C.login as usuario,
       C.m_password as senha,
       A.personid as matricula,
       A.name as nome,
       CASE WHEN exists (SELECT enrollid
                           FROM acdenroll AA
                          INNER JOIN acdgroup BB using (groupid)
                          INNER JOIN acdlearningperiod CC on (CC.learningperiodid = BB.learningperiodid)
                          WHERE now()::date between CC.begindate and CC.enddate
                            AND AA.statusid <> 5
                            AND AA.contractid = B.contractid)
       THEN
           'A'
       ELSE
           'I'
       END AS situacao,
       A.email,
       B.courseid as cod_curso,
       getcoursename(B.courseid) as curso,
       getunitdescription(B.unitid) as unidade
  FROM basphysicalpersonstudent A
 INNER JOIN acdcontract B using (personid)
  LEFT JOIN miolo_user C on (C.login = A.miolousername);
