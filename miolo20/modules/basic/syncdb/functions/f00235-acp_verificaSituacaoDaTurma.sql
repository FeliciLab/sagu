CREATE OR REPLACE FUNCTION acp_verificaSituacaoDaTurma()
RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: acp_verificasituacaodaturma
  DESCRIPTION: Trigger que verifica a data de fechamento de todas as disciplinas oferecidas
  da turma, caso todas estiverem fechadas (com a data preenchida) fecha a turma.

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       17/07/14   Jonas Diel         Função criada.
******************************************************************************/
DECLARE
    v_datafechamento DATE; --data de fechamento da disciplina
    v_dataDisciplina DATE; --data de fechamento da disciplina
    v_situacao CHAR(1); --Situacao da turma
    v_ofertacomponentecurricularid INT;
BEGIN

    IF ((OLD.datafechamento IS NULL AND NEW.datafechamento IS NOT NULL) --Fecha disciplina
        OR (OLD.datafechamento IS NOT NULL AND NEW.datafechamento IS NULL) --Reabre disciplina
        OR (OLD.datafechamento != NEW.datafechamento)) --Altera fechamento
    THEN 
        v_situacao := 'F'; --Sitacao fechada
        v_datafechamento := NEW.datafechamento;

        --Percorre todas as datas de fechamento das disciplinas da turma
        FOR v_dataDisciplina, v_ofertacomponentecurricularid IN SELECT datafechamento, ofertacomponentecurricularid FROM acpofertacomponentecurricular WHERE ofertaturmaid = NEW.ofertaturmaid
        LOOP
            --Caso alguma não estiver fechada define o status da turma como aberta
            IF v_dataDisciplina IS NULL
            THEN
                v_situacao := 'A'; --Situacao aberta
                v_datafechamento := NULL;
            END IF;
        END LOOP;

        --Se estiver fechando disciplina, verifica se existe pendencias, e caso existir, mantem a turma em aberto
        IF ((OLD.datafechamento IS NULL AND NEW.datafechamento IS NOT NULL) AND
            (EXISTS(SELECT 1
                      FROM acpmatricula M
                INNER JOIN acpofertacomponentecurricular O
                     USING (ofertacomponentecurricularid)
                INNER JOIN acpestadodematricula E
                     USING (estadodematriculaid)
                     WHERE O.ofertacomponentecurricularid = v_ofertacomponentecurricularid
                       AND E.aprovado IS NULL))) -- E.aprovado = NULL significa que esta pendente
        THEN
            v_situacao := 'A'; --Situacao aberta
            v_datafechamento := NULL;
        END IF;

        --Atualiza o status da turma
        --RAISE NOTICE 'Atualizando para % , data % , em ofertaturmaid %', v_situacao, v_datafechamento, NEW.ofertaturmaid;
        UPDATE acpOfertaTurma SET situacao = v_situacao, dataencerramento = v_datafechamento WHERE ofertaturmaid = NEW.ofertaturmaid;

    END IF;

    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql;


DROP TRIGGER IF EXISTS trg_verificasituacaodaturma ON acpofertacomponentecurricular;
CREATE TRIGGER trg_verificasituacaodaturma AFTER UPDATE ON acpofertacomponentecurricular FOR EACH ROW EXECUTE PROCEDURE acp_verificasituacaodaturma();
