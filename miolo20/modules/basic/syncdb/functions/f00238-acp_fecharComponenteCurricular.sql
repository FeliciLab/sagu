CREATE OR REPLACE FUNCTION acp_fecharcomponentecurricular(p_ofertacomponentecurricularid integer, p_calculamediadocurso boolean)
RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: acp_fecharComponenteCurricular
  DESCRIPTION: Realiza as validações e fechamento do camponente curricular com todas as matriculas relacionadas
  da turma, caso todas estiverem fechadas (com a data preenchida) fecha a turma.

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       17/07/14   Jonas Diel         Função criada.
******************************************************************************/
DECLARE
    v_matricula acpmatricula; --Matrículas
    v_frequenciasdigitadas boolean; --Se todas frequencias foram registradas
    v_notasdigitadas boolean; --Se todas notas foram digitadas
    v_modelodeavaliacao acpmodelodeavaliacao; --Modelo de avaliação
    v_calculamediadocurso boolean;
    v_ofertaturmaid integer;
BEGIN

    IF p_calculamediadocurso IS NULL THEN
      v_calculamediadocurso := true;
    ELSE
      v_calculamediadocurso := p_calculamediadocurso;
    END IF;

    --Modelo de avaliação
    SELECT INTO v_modelodeavaliacao * FROM acp_obtermodelodaofertadecomponentecurricular(p_ofertacomponentecurricularid);

    --Caso utiliza controle de frequencia
    IF v_modelodeavaliacao.habilitacontroledefrequencia IS TRUE
    THEN
        --Verifica o registro das frequencias
        PERFORM acp_verificafrequenciasregistradas(p_ofertacomponentecurricularid);
    END IF;

    --Verifica o registro das notas
    PERFORM acp_verificanotasregistradas(p_ofertacomponentecurricularid);

    --Percorre todas matriculas com status MATRICULADO da oferta do componente curricular e realiza o fechamento das matricula
    FOR v_matricula IN SELECT * FROM acpmatricula WHERE ofertacomponentecurricularid = p_ofertacomponentecurricularid
    LOOP
        --Fecha a matricula
        PERFORM acp_fecharmatricula(v_matricula.matriculaid);
    END LOOP;

    --Fecha a oferta do componente curricular, caso nao exista notas pendentes
    UPDATE acpofertacomponentecurricular SET datafechamento = now()::date WHERE ofertacomponentecurricularid = p_ofertacomponentecurricularid;

    IF v_calculamediadocurso THEN
      SELECT INTO v_ofertaturmaid ofertaturmaid FROM acpofertacomponentecurricular WHERE ofertacomponentecurricularid = v_matricula.ofertacomponentecurricularid;
      PERFORM calculaMediasDoCurso(v_ofertaturmaid);
    END IF;

    RETURN true;
END;
$BODY$
  LANGUAGE plpgsql;
