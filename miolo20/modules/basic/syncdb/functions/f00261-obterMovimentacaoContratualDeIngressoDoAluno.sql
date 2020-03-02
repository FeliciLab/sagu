CREATE OR REPLACE FUNCTION obterMovimentacaoContratualDeIngressoDoAluno(p_contractId INT)
RETURNS TABLE (
    contractid INTEGER, 
    statecontractid INTEGER, 
    statetime TIMESTAMP WITHOUT TIME ZONE, 
    reasonid INTEGER, 
    learningperiodid INTEGER, 
    centerid INTEGER,
    description TEXT,
    inouttransition CHAR(1),
    needsreason BOOLEAN,
    isclosecontract BOOLEAN,
    statecontractisactive BOOLEAN
) AS
$BODY$
/******************************************************************************
  NAME: obterMovimentacaoContratualDeIngressoDoAluno
  PURPOSE: Retorna tabela com os dados da movimentação contratual de ingresso do contrato recebido.
  DESCRIPTION:
  O aluno pode ter origem de transferência de contrato, sendo assim, é necessário obter a forma de ingresso,
  pelo contrato antigo.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       22/09/2014 Augusto A. Silva  1. Função criada.
  2.0       01/10/2014 Augusto A. Silva  1. Ajustada função para atender dados inconsistentes.
  3.0       19/03/2014 ftomasini         2. Otimização, e implementação de bloqueio para resultado
                                            não entrar em loop
******************************************************************************/
DECLARE
    v_last_contracts integer[];
    v_auxContractId INT := p_contractId;
    v_contratoDeOrigemId INT;
    v_contratoDeOrigemEncontrado BOOLEAN := FALSE;
    v_select TEXT;
BEGIN
	WHILE ( v_contratoDeOrigemEncontrado IS FALSE )
	LOOP
		SELECT INTO v_contratoDeOrigemId obterContratoTransferidoDeOrigem(v_auxContractId);

		IF ( v_contratoDeOrigemId IS NULL )
		THEN
		     v_contratoDeOrigemId := v_auxContractId;
		     v_contratoDeOrigemEncontrado := TRUE;
		ELSE
            --Array de contratos ja percorridos
            v_last_contracts := array_append(v_last_contracts, v_contratoDeOrigemId);
            --Verifica se o contrato ja foi percorrido
            IF v_auxContractId = any(v_last_contracts)
            THEN
                v_contratoDeOrigemEncontrado := TRUE;
            END IF;
		    v_auxContractId := v_contratoDeOrigemId;
		END IF;
	END LOOP;

        RETURN QUERY (
            SELECT MC.contractid,
                   MC.statecontractid,
                   MC.statetime,
                   MC.reasonid,
                   MC.learningperiodid,
                   MC.centerid,
                   SC.description,
                   SC.inouttransition,
                   SC.needsreason,
                   SC.isclosecontract,
                   SC.statecontractisactive
              FROM acdMovementContract MC
        INNER JOIN acdStateContract SC
                ON SC.stateContractId = MC.stateContractId
             WHERE MC.contractId = v_contratoDeOrigemId 
          ORDER BY (CASE SC.inouttransition 
                        WHEN 'I' THEN -3 
                        WHEN 'T' THEN -2
                        ELSE -1
                    END), 
                   MC.stateTime ASC
             LIMIT 1
        );
END;
$BODY$
LANGUAGE plpgsql
IMMUTABLE;
