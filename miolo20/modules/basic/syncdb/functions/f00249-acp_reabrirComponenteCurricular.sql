CREATE OR REPLACE FUNCTION acp_reabrircomponentecurricular(p_ofertacomponentecurricularid integer, p_calculamediadocurso boolean)
RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: acp_reabrircomponentecurricular
  DESCRIPTION: Realiza a reabertura do camponente curricular  já fechado com 
  todas as matriculas relacionadas da turma.

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       23/07/14   Jonas Diel         Função criada.
******************************************************************************/
DECLARE
    v_matricula acpmatricula; --Matrículas
    v_ofertaturmaid integer;
    v_cursoid integer;
    v_calculamediadocurso boolean;
BEGIN

    IF p_calculamediadocurso IS NULL THEN
      v_calculamediadocurso := true;
    ELSE
      v_calculamediadocurso := p_calculamediadocurso;
    END IF;

    --Percorre todas matriculas com status DIFERENTE de SITUACAO_CANCELAMENTO, SITUACAO_APROVEITAMENTO_OUTRA_INSTITUICAO, SITUACAO_APROVEITAMENTO_INTERNO, SITUACAO_TRANCAMENTO, SITUACAO_INSCRICAO
    FOR v_matricula IN SELECT * FROM acpmatricula WHERE ofertacomponentecurricularid = p_ofertacomponentecurricularid AND situacao NOT IN ('P', 'E', 'C', 'T', 'I')
    LOOP
        --Reabre a matricula
        UPDATE acpmatricula 
        SET frequencia = NULL, 
            notafinal = NULL, 
            conceitofinal = NULL, 
            situacao = 'M',  --Matriculado
            estadodematriculaid = NULL
        WHERE matriculaid = v_matricula.matriculaid;

        SELECT INTO v_ofertaturmaid ofertaturmaid FROM acpofertacomponentecurricular WHERE ofertacomponentecurricularid = v_matricula.ofertacomponentecurricularid;
        SELECT INTO v_cursoid acpocorrenciacurso.cursoid FROM acpOfertaTurma LEFT JOIN acpOfertaCurso ON acpOfertaCurso.ofertacursoid = acpOfertaTurma.ofertacursoid LEFT JOIN acpocorrenciacurso ON acpocorrenciacurso.ocorrenciacursoid = acpOfertaCurso.ocorrenciacursoid WHERE acpOfertaTurma.ofertaturmaid = v_ofertaturmaid;

        -- Reseta informações do curso
        UPDATE acpcursoinscricao SET situacao = 'M' WHERE personid = v_matricula.personid;
        --Limpa as avaliações 
        DELETE FROM acpcursoinscricaoavaliacao WHERE cursoinscricaoid IN (SELECT cursoinscricaoid FROM acpcursoinscricao WHERE personid = v_matricula.personid AND cursoid = v_cursoid);
        --Apaga a data de fechamento
        UPDATE acpcursoinscricao SET datafechamento = NULL WHERE cursoinscricaoid IN (SELECT cursoinscricaoid FROM acpcursoinscricao WHERE personid = v_matricula.personid AND cursoid = v_cursoid);
    END LOOP;

    IF v_calculamediadocurso THEN
      PERFORM calculaMediasDoCurso(v_ofertaturmaid);
    END IF;

    --Reabre oferta do componente curricular
    UPDATE acpofertacomponentecurricular SET datafechamento = NULL WHERE ofertacomponentecurricularid = p_ofertacomponentecurricularid;

    RETURN true;
END;
$BODY$
  LANGUAGE plpgsql;
