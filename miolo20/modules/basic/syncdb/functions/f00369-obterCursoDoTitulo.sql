CREATE OR REPLACE FUNCTION obterCursoDoTitulo(p_invoiceId INT)
RETURNS VARCHAR AS
$BODY$
DECLARE 
    v_curso VARCHAR;
BEGIN
    -- Se estiver atrelado a um contrato do acadêmico, retorna o curso de acdCourse.
    SELECT INTO v_curso
                C.courseId || ' - ' || getCourseName(C.courseId) AS curso
           FROM acdContract C
          WHERE EXISTS (SELECT contractId
                          FROM finEntry
                         WHERE invoiceId = p_invoiceId
                           AND contractId = C.contractId);
           
    IF v_curso IS NULL
    THEN
        --Se estiver atrelado a uma inscrição do pedagógico, retorna o curso de acpCurso.
        SELECT INTO v_curso
                    C.codigo || ' - ' || C.nome AS curso
               FROM prcTituloInscricao TI
         INNER JOIN acpInscricao I
                 ON I.inscricaoId = TI.inscricaoId
         INNER JOIN acpOfertaCurso OC
                 ON OC.ofertaCursoId = I.ofertaCursoId
         INNER JOIN acpOcorrenciaCurso OCU
                 ON OCU.ocorrenciaCursoId = OC.ocorrenciaCursoId
         INNER JOIN acpCurso C
                 ON C.cursoId = OCU.cursoId
              WHERE TI.invoiceId = p_invoiceId;
    END IF;

    RETURN v_curso;
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;