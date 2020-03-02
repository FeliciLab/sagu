CREATE OR REPLACE FUNCTION obterMediaGlobal(p_contractId INTEGER, p_considera_reprovacoes BOOLEAN DEFAULT FALSE)
RETURNS double precision AS 
$BODY$
DECLARE
    v_globalAverage double precision := 0;
    v_quantidade_matriculas INT;
BEGIN
    SELECT INTO v_quantidade_matriculas
                COUNT(EN.*)
           FROM acdEnroll EN
          WHERE EN.contractId = p_contractId
	    AND (CASE p_considera_reprovacoes
		      WHEN TRUE
		      THEN
			   (EN.statusId IN (getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::int, 
					    getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::int,
					    getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED')::int))
		      ELSE
			   (EN.statusId IN (getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::int, 
					    getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::int))
		 END)
            AND obterNotaOuConceitoFinal(EN.enrollId) IS NOT NULL;
    
    BEGIN
        SELECT INTO v_globalAverage 
                    (CASE v_quantidade_matriculas
                          WHEN 0
                          THEN 
                               NULL
                          ELSE
                               ROUND((SUM(obterNotaOuConceitoFinal(A.enrollId)::NUMERIC) / v_quantidade_matriculas)::NUMERIC, getParameter('BASIC', 'REAL_ROUND_VALUE')::INT)
                     END) AS mediaGlobal
               FROM acdEnroll A
         INNER JOIN acdContract B
                 ON B.contractId = A.contractId
              WHERE A.contractId = p_contractId 
                AND (CASE p_considera_reprovacoes
		          WHEN TRUE
		          THEN
			       (A.statusId IN (getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::int, 
					       getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::int,
					       getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED')::int))
		          ELSE
			       (A.statusId IN (getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::int, 
					       getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::int))
		     END)
                AND obterNotaOuConceitoFinal(A.enrollId) IS NOT NULL;
    EXCEPTION WHEN data_exception
    THEN
        RAISE NOTICE 'A notas das disciplinas são por conceito, não possibilitando o cálculo de média global';
        v_globalAverage := NULL;
    END;

    RETURN v_globalAverage;
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;