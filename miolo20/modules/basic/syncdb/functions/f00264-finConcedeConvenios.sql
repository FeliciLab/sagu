CREATE OR REPLACE FUNCTION finConcedeConvenios()
  RETURNS trigger AS
$BODY$
/*************************************************************************************
  NAME: finconcedeconvenios
  
  PURPOSE: Concede convênio automaticamente para os alunos que estiverem fazendo matrícula
           e que se enquadram nas regras definidas no cadastro de convênios.
           
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       15/09/2014 ftomasini         1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
    v_contract acdcontract;
    v_learningperiod acdlearningperiod;
    v_estado_contratual acdstatecontract.statecontractid%TYPE;
    v_convenios_do_curso record;
    v_aluno_calouro boolean;
    v_periodo_matricula record;
    v_todas_disciplinas record;
    v_concede_convenio boolean;
    v_remove_convenio boolean;
    v_creditos_matriculado numeric;
    v_convenio_ja_concedido boolean;

BEGIN
    IF ( (NEW.contractid IS NOT NULL) AND (NEW.learningperiodid IS NOT NULL) )
    THEN
        --Início log de geração de mensalidades
        PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'INICIO', 'RESUMO');
    END IF;

    --Obtem contrato da pessoa
    SELECT INTO v_contract * FROM acdcontract WHERE contractid = NEW.contractid;
    --Obtém período letivo
    SELECT INTO v_learningperiod * FROM acdlearningperiod WHERE learningperiodid = NEW.learningperiodid;
    --Obtém o estado contratual que está sendo inserido
    v_estado_contratual := NEW.statecontractid;
    --Verifica se aluno é calouro
    v_aluno_calouro := isfreshmanbyperiod(v_contract.contractid, v_learningperiod.periodid);
    --Periodo de matricula
    SELECT INTO v_periodo_matricula * FROM acdperiodenrolldate WHERE learningperiodid = v_learningperiod.learningperiodid AND now()::date BETWEEN begindate AND enddate;
    --Inicializa variável que controla a concessão de convênio
    v_concede_convenio:=FALSE;
    --Inicializa variável que controla a remoção de convênio
    v_remove_convenio:=FALSE;
    --Créditos matriculados
    v_creditos_matriculado:=obtemcreditomatriculado(v_contract.contractid, v_learningperiod.learningperiodid);

    IF ( (NEW.contractid IS NOT NULL) AND (NEW.learningperiodid IS NOT NULL) )
    THEN
        -- Início concessão de convênios
        PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Estado contratual: ' || (SELECT description FROM acdstatecontract WHERE statecontractid = v_estado_contratual), '8 - CONVÊNIOS');
        PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Aluno calouro: ' || (CASE WHEN v_aluno_calouro IS TRUE THEN 'SIM' ELSE 'NÃO' END), '8 - CONVÊNIOS');
        PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Período atual: ' || (CASE WHEN v_periodo_matricula.isadjustment IS TRUE THEN 'Ajuste' ELSE 'Matrícula' END) || '   Data inicial: '|| v_periodo_matricula.begindate ||'   Data final: '|| v_periodo_matricula.enddate, '8 - CONVÊNIOS');
        PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Número de créditos matriculado: ' || v_creditos_matriculado || ' cr', '8 - CONVÊNIOS');
    END IF;

    --Verifica se o estado da movimentação contratual é de PRÉ-MATRICULA, MATRÍCULA, AJUSTE DE MATRÍCULA
    --somente nesses estados os convênios devem ser adicionados ou removidos
    IF v_estado_contratual = GETPARAMETER('ACADEMIC', 'STATE_CONTRACT_ID_PRE_ENROLL')::int 
    OR v_estado_contratual = GETPARAMETER('BASIC', 'STATE_CONTRACT_ID_ENROLLED')::int 
    OR v_estado_contratual = GETPARAMETER('ACADEMIC', 'STATE_CONTRACT_ID_ADJUSTMENT')::int 
    THEN
      --Percorre os convênios que são concedidos automaticamente para o curso do aluno
        FOR v_convenios_do_curso IN (SELECT A.* FROM finconvenant A 
                                          INNER JOIN finocorrenciadoconvenio B 
                                               USING (convenantid)
                                               WHERE B.courseid = v_contract.courseid
                                                 AND B.courseversion = v_contract.courseversion
                                                 AND B.turnid = v_contract.turnid
                                                 AND B.unitid = v_contract.unitid)
        LOOP
            PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Convênio: ' || v_convenios_do_curso.convenantid || '-' || v_convenios_do_curso.description, '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
            PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Valor: '|| v_convenios_do_curso.value, '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
            PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Valor é percentual: '|| CASE WHEN v_convenios_do_curso.ispercent IS TRUE THEN 'Sim' ELSE 'Não' END, '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
            PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Convênio condicional: '|| CASE WHEN v_convenios_do_curso.condicional IS TRUE THEN 'Sim' ELSE 'Não' END, '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
            PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Convênio acumulativo: '|| CASE WHEN v_convenios_do_curso.acumulativo IS TRUE THEN 'Sim' ELSE 'Não' END, '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
            PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Aplicar para calouros: '|| CASE WHEN v_convenios_do_curso.aplicacalouros IS TRUE THEN 'Sim' ELSE 'Não' END, '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
            PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Aplicar para veteranos: '|| CASE WHEN v_convenios_do_curso.aplicaveteranos IS TRUE THEN 'Sim' ELSE 'Não' END, '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
            PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Range de créditos: '|| COALESCE('Mínimo de créditos: ' || v_convenios_do_curso.crminimo || ' cr   Máximo de créditos: ' || v_convenios_do_curso.crmaximo || ' cr ','Todas as disciplinas de um período do curso'), '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
            PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Conceder no período: '|| v_convenios_do_curso.concederperiodo ||' ( M = matricula A = ajuste O = Ambos)', '9 - CONVÊNIO - '|| v_convenios_do_curso.description);

            --Inicializa vairavel de concessão e remoção de convenio como FALSE, só deverá ser TRUE se aluno
            --contemplar todos os requisitos definido no cadastro de convênio.
            v_concede_convenio:=FALSE;
            v_remove_convenio:=FALSE;
            
            IF (v_convenios_do_curso.condicional = TRUE)
            THEN
                v_convenio_ja_concedido:= NULL;
                v_convenio_ja_concedido:= TRUE FROM finconvenantperson WHERE convenantid = v_convenios_do_curso.convenantid AND v_learningperiod.begindate >= begindate AND v_learningperiod.begindate <= enddate AND contractid = v_contract.contractid limit 1;
                --Verifica se o aluno já tem o convênio que está sendo analisado

                IF ( v_convenio_ja_concedido IS NOT NULL )
                THEN
                    PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Aluno já possui esse convênio: Sim', '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
                  --Verifica se o aluno ainda tem todos os requisitos definidos no cadastro de convênio
                  --caso contrário deve remover o convênio do aluno
                  --do semestre
                    IF (v_convenios_do_curso.todasdisciplinas = TRUE)
                    THEN
                        --Verifica se aluno se matriculou em todas as disciplinas de um semestre, caso sim marca a flag
                        --concede_convenio = TRUE
                        IF ( verifica_matricula_todas_disciplinas(v_contract.contractid, v_learningperiod.learningperiodid) <= 0 )
                        THEN
                            PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Aluno não está matriculado em todas as disciplinas de um período do curso', '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
                            v_remove_convenio:=TRUE;
                        END IF;
                    --Range de créditos
                    ELSE
                        v_creditos_matriculado:=obtemcreditomatriculado(v_contract.contractid, v_learningperiod.learningperiodid);

                        IF ( v_creditos_matriculado < v_convenios_do_curso.crminimo  )
                        THEN
                            PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Aluno não está matriculado no range de créditos definido', '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
                            v_remove_convenio:=TRUE;
                        END IF;
                    END IF;

                --Se aluno ainda não tem o convênio que está sendo analisado
                ELSE
                    --Se período for de ajuste, verifica se o convênio pode ser concedido conforme configuracao
                    --do convenio
                    PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Aluno já possui esse convênio: Não', '9 - CONVÊNIO - '|| v_convenios_do_curso.description);

                    IF((v_periodo_matricula.isadjustment = TRUE AND 
                        (v_convenios_do_curso.concederperiodo = 'O' OR 
                        v_convenios_do_curso.concederperiodo = 'A'))
                       OR --periodo de matricula
                       (v_periodo_matricula.isadjustment = FALSE AND 
                        (v_convenios_do_curso.concederperiodo = 'O' OR 
                        v_convenios_do_curso.concederperiodo = 'M')))
                    THEN
                        --Verifica se convenio pode ser concedido verificando se o aluno é calouro ou veterano 
                        IF((v_aluno_calouro = TRUE AND v_convenios_do_curso.aplicacalouros = TRUE) OR
                           (v_aluno_calouro = FALSE AND v_convenios_do_curso.aplicaveteranos = TRUE) )
                        THEN  
                            --Verifica se o convênio só sera concedido para alunos que se matricularem em todas as disciplinas
                            --do semestre
                            IF (v_convenios_do_curso.todasdisciplinas = TRUE)
                            THEN
                                --Verifica se aluno se matriculou em todas as disciplinas de um semestre, caso sim marca a flag
                                --concede_convenio = TRUE
                                IF (verifica_matricula_todas_disciplinas(v_contract.contractid, v_learningperiod.learningperiodid) > 0 )
                                THEN
                                    PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Aluno está matriculado em todas as disciplinas de um período do curso', '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
                                    v_concede_convenio:=TRUE;
                                END IF;
                            --Range de créditos
                            ELSE
                                v_creditos_matriculado:=obtemcreditomatriculado(v_contract.contractid, v_learningperiod.learningperiodid);

                                IF ( (v_creditos_matriculado >= v_convenios_do_curso.crminimo AND 
                                      v_creditos_matriculado <= v_convenios_do_curso.crmaximo) )
                                THEN
                                    PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Aluno está matriculado no range de créditos definido', '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
                                    v_concede_convenio:=TRUE;

                                END IF;
                            END IF;
                        END IF;
                    END IF;
                END IF;
            END IF;

            --Verifica se convênio deve ser removido    
            IF (v_remove_convenio)
            THEN
                PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Convênio do aluno removido: Sim (aluno perdeu o convênio pois não atende as exigências determinadas na configuração do convênio)', '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
                DELETE 
                  FROM finconvenantperson 
                 WHERE contractid = v_contract.contractid
                   AND convenantid = v_convenios_do_curso.convenantid
                   AND begindate BETWEEN v_learningperiod.begindate 
                   AND v_learningperiod.enddate;
            ELSE
                PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Convênio do aluno removido: Não '|| CASE WHEN v_convenio_ja_concedido IS TRUE THEN '(aluno não possui os requisitos definidos na configuração do convênio)' ELSE '(aluno não tem o convênio)' END, '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
            END IF;
            --Verifica se convênio deve ser concedido    
            IF (v_concede_convenio)
            THEN
                PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Convênio concedido para o aluno: Sim', '9 - CONVÊNIO - '|| v_convenios_do_curso.description);

                INSERT INTO finconvenantperson
                    (convenantid, 
                     personid, 
                     begindate, 
                     enddate, 
                     contractid,
                     tipoconcessao)
                VALUES 
                    (v_convenios_do_curso.convenantid,
                     v_contract.personid,
                     v_learningperiod.begindate,
                     v_learningperiod.enddate,
                     v_contract.contractid,
                     'A');
            ELSE
                PERFORM finresumomatriculalog(NEW.contractid, NEW.learningperiodid, 'Convênio concedido para o aluno: Não ' || CASE WHEN v_convenio_ja_concedido IS TRUE THEN '(aluno já tem o convênio)' ELSE '(aluno não possui os requisitos definidos na configuração do convênio)' END, '9 - CONVÊNIO - '|| v_convenios_do_curso.description);
            END IF;    
        END LOOP;                                          
    END IF;

    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_finconcedeconvenios ON acdmovementcontract;
CREATE TRIGGER trg_finconcedeconvenios
  BEFORE INSERT
  ON acdmovementcontract
  FOR EACH ROW
  EXECUTE PROCEDURE finconcedeconvenios();
