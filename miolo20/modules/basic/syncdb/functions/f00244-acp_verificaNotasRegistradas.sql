CREATE OR REPLACE FUNCTION acp_verificanotasregistradas(p_ofertacomponentecurricularid integer)
RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: acp_verificanotasregistradas
  DESCRIPTION: Verifica se todas as notas da oferta do componente curricular foram digitadas

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       17/07/14   Jonas Diel         Função criada.
******************************************************************************/
DECLARE
    v_modelodeavaliacao acpmodelodeavaliacao; --Modelo de avaliacao
    v_componentedeavaliacaoid integer; --Código do componente de avaliacao não registrado
    v_componentedeavaliacao acpcomponentedeavaliacao; --Componente de avaliação não registrado
    v_aluno basphysicalpersonstudent; --Aluno
    v_ofertacomponentecurricular acpofertacomponentecurricular; --Oferta componente curricular
    v_componentecurricularmatriz acpComponenteCurricularMatriz; --Componente curricular da matriz
    v_componentecurricular acpComponenteCurricular; --Componente curricular
    v_matricula acpmatricula; --Matricula
BEGIN
    SELECT INTO v_modelodeavaliacao * FROM acp_obtermodelodaofertadecomponentecurricular(p_ofertacomponentecurricularid);

    SELECT INTO v_ofertacomponentecurricular * FROM acpOfertaComponenteCurricular WHERE ofertacomponentecurricularid = p_ofertacomponentecurricularid;
    SELECT INTO v_componentecurricularmatriz * FROM acpComponenteCurricularMatriz WHERE componentecurricularmatrizid = v_ofertacomponentecurricular.componentecurricularmatrizid;
    SELECT INTO v_componentecurricular * FROM acpComponenteCurricular WHERE componentecurricularid = v_componentecurricularmatriz.componentecurricularid;

    --Caso o tipo de dados for Nenhum
    IF v_modelodeavaliacao.tipodedados = '-' THEN
        RETURN TRUE;
    END IF;

    --Percorre todas matriculas com status MATRICULADO da oferta do componente curricular e verifica se suas frequencias foram registradas
    FOR v_matricula IN SELECT * FROM acpmatricula WHERE ofertacomponentecurricularid = p_ofertacomponentecurricularid AND situacao = 'M'
    LOOP
        --Aluno
        SELECT INTO v_aluno * FROM basphysicalpersonstudent WHERE personid = v_matricula.personid;

        --Nota ou conceito
        IF v_modelodeavaliacao.tipodedados != 'P' THEN
            --Verifica se nao existe um estado Pendente cadastrado
            IF NOT EXISTS(SELECT 1 FROM acpestadodematricula WHERE aprovado IS NULL)
            THEN
                --Percorre todas avaliações da disciplina
                FOR v_componentedeavaliacao IN SELECT * FROM acpcomponentedeavaliacao WHERE modelodeavaliacaoid = v_modelodeavaliacao.modelodeavaliacaoid AND classedecomponente = 'F' ORDER BY ordem
                LOOP
                    --Nota
                    IF v_modelodeavaliacao.tipodedados = 'N' THEN

                        --Verifica se o aluno possui a nota registrada
                        IF count(*) = 0 FROM acpavaliacao WHERE componentedeavaliacaoid = v_componentedeavaliacao.componentedeavaliacaoid AND matriculaid = v_matricula.matriculaid AND nota IS NOT NULL
                        THEN
                            RAISE EXCEPTION '%:Componente curricular %: % não registrada para o(a) aluno(a) %.',v_ofertacomponentecurricular.ofertacomponentecurricularid ,v_componentecurricular.descricao, v_componentedeavaliacao.descricao, v_aluno.name;
                        END IF;

                    --Parecer
                    ELSEIF v_modelodeavaliacao.tipodedados = 'C' THEN

                        --Verifica se o aluno possui oconceito registrado
                        IF count(*) = 0 FROM acpavaliacao WHERE componentedeavaliacaoid = v_componentedeavaliacao.componentedeavaliacaoid AND matriculaid = v_matricula.matriculaid AND conceitodeavaliacaoid IS NOT NULL
                        THEN
                            RAISE EXCEPTION '%:Componente curricular %: % não registrada para o(a) aluno(a) %.', v_ofertacomponentecurricular.ofertacomponentecurricularid , v_componentecurricular.descricao, v_componentedeavaliacao.descricao, v_aluno.name;
                        END IF;

                    END IF;
                END LOOP;
            END IF;
        ELSE --Parecer

            IF v_matricula.parecerfinal IS NULL THEN
                RAISE EXCEPTION '%:Componente curricular %: Parecer final não registrado para o(a) aluno(a) %.', v_ofertacomponentecurricular.ofertacomponentecurricularid , v_componentecurricular.descricao, v_aluno.name;
            END IF;

        END IF;

    END LOOP;

    RETURN true;

END;
$BODY$
  LANGUAGE plpgsql;
