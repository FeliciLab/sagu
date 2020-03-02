CREATE OR REPLACE FUNCTION obterStatusDeMatriculaAbreviado(p_enrollStatusId INT)
RETURNS VARCHAR AS
$BODY$
BEGIN
    RETURN (
        CASE p_enrollStatusId
            WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT
            THEN 
                 'MA'
            WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT
            THEN 
                 'AP'
            WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED')::INT
            THEN 
                 'RP'
            WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED_FOR_LACKS')::INT
            THEN 
                 'RF'
            WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT
            THEN 
                 'DS'
            WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT
            THEN 
                 'AP'
            ELSE
                 (SELECT description
                    FROM acdEnrollStatus
                   WHERE statusId = p_enrollStatusId)
       END
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;