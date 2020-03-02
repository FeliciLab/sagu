CREATE OR REPLACE FUNCTION atualizaVinculoAluno()
RETURNS TRIGGER AS
$BODY$
/*************************************************************************************
  NAME: atualizavinculoaluno
  PURPOSE: Insere ou atualiza vinculo de aluno.
**************************************************************************************/
DECLARE
    --Data do vínculo
    v_dateValidate DATE;

    --Código de pessoa
    v_personId INT;

    --Quantidade de contratos
    v_quant_contrato INT;

    --Quantidade de inscrições
    v_quant_inscricao INT;

BEGIN
    
    --Obtém código da pessoa
    IF TG_RELNAME = 'acdmovementcontract'
    THEN
        v_personId := (SELECT personId FROM acdContract WHERE contractId = NEW.contractId);
    ELSE
        IF ( TG_OP != 'DELETE' )
        THEN
            v_personId = NEW.personId;
        ELSE
            v_personId = OLD.personId;
        END IF;
    END IF;

    --Obtém quantidade de contratos ativos
    IF TG_RELNAME = 'acdmovementcontract'
    THEN
        v_quant_contrato := (SELECT COUNT(*) FROM acdContract WHERE personId = v_personId AND contractId != NEW.stateContractId AND iscontractclosed(contractid) IS FALSE);
    ELSE
        v_quant_contrato := (SELECT COUNT(*) FROM acdContract WHERE personId = v_personId AND isContractClosed(contractId) IS FALSE);
    END IF;

    --Obtém quantidade de inscrições ativas
    IF ( TG_RELNAME = 'acpinscricao' AND TG_OP != 'DELETE' )
    THEN
        v_quant_inscricao := (SELECT COUNT(*) FROM acpInscricao WHERE personId = v_personId AND inscricaoId != NEW.inscricaoId AND situacao IN ('I', 'M'));
    ELSE
        v_quant_inscricao := (SELECT COUNT(*) FROM acpInscricao WHERE personId = v_personId AND situacao IN ('I', 'M'));
    END IF;

    --Se possui contratos e/ou inscrições ativos não seta data de validade
    IF ( v_quant_contrato > 0 OR v_quant_inscricao > 0 )
    THEN
        v_dateValidate := NULL;
    ELSE
        IF TG_RELNAME = 'acdmovementcontract'
        THEN
            -- Caso a movimentacao contratual sendo inserida fecha contrato (isCloseContract = TRUE), define a data final do vinculo como a data da movimentacao, senao, como vazio.
            IF (SELECT isCloseContract FROM acdStateContract WHERE stateContractId = NEW.stateContractId) IS TRUE
            THEN
                v_dateValidate := NEW.stateTime::date;
            ELSE
                v_dateValidate := NULL;
            END IF;
        ELSE
            IF TG_RELNAME = 'acpinscricao'
            THEN
                --Se estiver deletando, seta pra hoje
                IF ( TG_OP = 'DELETE') 
                THEN
                    v_dateValidate := NOW()::DATE;
                ELSE
                    --Se a situação for INSCRITO ou MATRICULADO não seta validade. Senão seta a data da situação
                    IF ( NEW.situacao = 'M' OR NEW.situacao = 'I' )
                    THEN
                        v_dateValidate := NULL;
                    ELSE    
                        v_dateValidate := NEW.dataSituacao::DATE;
                    END IF;
                END IF;
            ELSE 
                --'acpcursoinscricao'
                --Se setou datafechamento ou ficou CANCELADO seta o dia do registro (hoje). Senão seta null
                IF ( NEW.situacao = 'C' OR char_length(NEW.dataFechamento::TEXT) > 0 )
                THEN
                    v_dateValidate := NOW()::DATE;
                ELSE
                    v_dateValidate := NULL;
                END IF;
            END IF;
        END IF;
    END IF;
    
    --Atualiza/insere registro
    IF EXISTS(SELECT 1 FROM baspersonlink WHERE personid = v_personId AND linkId = GETPARAMETER('BASIC', 'PERSON_LINK_STUDENT')::int)
    THEN
        UPDATE basPersonLink SET dateValidate = v_dateValidate WHERE personid = v_personId AND linkId = GETPARAMETER('BASIC', 'PERSON_LINK_STUDENT')::int;
    ELSE
        INSERT INTO basPersonLink (personId, linkId, dateValidate) VALUES (v_personId, GETPARAMETER('BASIC', 'PERSON_LINK_STUDENT')::int, v_dateValidate);
    END IF;

    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_atualizavinculoaluno ON acdmovementcontract;
CREATE TRIGGER trg_atualizavinculoaluno AFTER INSERT ON acdmovementcontract FOR EACH ROW EXECUTE PROCEDURE atualizavinculoaluno();

DROP TRIGGER IF EXISTS trg_atualizavinculoaluno ON acpinscricao;
CREATE TRIGGER trg_atualizavinculoaluno AFTER INSERT OR UPDATE OR DELETE ON acpinscricao FOR EACH ROW EXECUTE PROCEDURE atualizavinculoaluno();

DROP TRIGGER IF EXISTS trg_atualizavinculoaluno ON acpcursoinscricao;
CREATE TRIGGER trg_atualizavinculoaluno AFTER INSERT OR UPDATE ON acpcursoinscricao FOR EACH ROW EXECUTE PROCEDURE atualizavinculoaluno();