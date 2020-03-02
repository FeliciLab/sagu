CREATE OR REPLACE FUNCTION unificarPessoa(p_dePessoa INT, p_paraPessoa INT) 
RETURNS boolean AS $$
/*************************************************************************************
  NAME: unificarPessoa
  PURPOSE: Processo de unificacao de pessoa.
**************************************************************************************/
DECLARE
    v_documentos RECORD;
    v_email VARCHAR;
    v_login_de VARCHAR;
    v_login_para VARCHAR;
    v_iduser_de INT;
    v_iduser_para INT;
    v_processoSeletivo RECORD;

    v_deTitulosEmAberto BOOLEAN;
    v_paraTitulosEmAberto BOOLEAN;
    
    v_tabela VARCHAR;
    v_tabelas VARCHAR[] := ARRAY[
        'basphysicalpersonprofessor',
        'basphysicalpersonemployee',
        'basphysicalpersonstudent',
        'basphysicalperson',
        'basperson'
    ];
    v_spc_movement BOOLEAN;
    v_titulo_aberto RECORD;
    v_exits BOOLEAN;
    v_pessoa_que_fica RECORD;
    v_isstudent BOOLEAN;
    v_isprofessor BOOLEAN;
    v_isemployee BOOLEAN;

    v_sql TEXT;

    v_telefone RECORD;
    v_existe_telefone BOOLEAN;
BEGIN
    --Verifica se a pessoa que sai e aluno e a que fica nao
    SELECT INTO v_isstudent COUNT(*) = 1 
      FROM ONLY basphysicalpersonstudent
          WHERE personid = p_dePessoa
            AND NOT EXISTS (SELECT 1 
                         FROM ONLY basphysicalpersonstudent
                             WHERE personid = p_paraPessoa);
     
    --Verifica se a pessoa que sai e professor e a que fica nao
    SELECT INTO v_isprofessor COUNT(*) = 1
      FROM ONLY basphysicalpersonprofessor
          WHERE personid = p_dePessoa
            AND NOT EXISTS (SELECT 1 
                         FROM ONLY basphysicalpersonprofessor
                             WHERE personid = p_paraPessoa);
     
    --Verifica se a pessoa que sai e funcionario e a que fica nao
    SELECT INTO v_isemployee COUNT(*) = 1
      FROM ONLY basphysicalpersonemployee
          WHERE personid = p_dePessoa
            AND NOT EXISTS (SELECT 1 
                         FROM ONLY basphysicalpersonemployee
                             WHERE personid = p_paraPessoa);
     
    --Obtem dados da pessoa que fica
    SELECT INTO v_pessoa_que_fica * 
      FROM ONLY basphysicalperson
          WHERE personid = p_paraPessoa;

    --Insere a pessoa que fica como aluno 
    IF v_isstudent = TRUE THEN
        INSERT INTO basphysicalpersonstudent(personid,name,sex) 
             VALUES (p_paraPessoa,v_pessoa_que_fica.name,v_pessoa_que_fica.sex);
    END IF;

    --Insere a pessoa que fica como professor 
    IF v_isprofessor = TRUE THEN
        INSERT INTO basphysicalpersonprofessor(personid,name,sex) 
             VALUES (p_paraPessoa,v_pessoa_que_fica.name,v_pessoa_que_fica.sex);
    END IF;

    --Insere a pessoa que fica como funcionario 
    IF v_isemployee = TRUE THEN
        INSERT INTO basphysicalpersonemployee(personid,name,sex) 
             VALUES (p_paraPessoa,v_pessoa_que_fica.name,v_pessoa_que_fica.sex);
    END IF;
    
    -- Pega as 'unic constraints' de email e login da pessoa que sai para ser setado na que fica.
    SELECT INTO v_email email
      FROM ONLY basPerson
          WHERE personId = p_dePessoa;
          
    SELECT INTO v_login_de miolousername
      FROM ONLY basPerson
          WHERE personId = p_dePessoa;

    SELECT INTO v_login_para miolousername
      FROM ONLY basPerson
          WHERE personId = p_paraPessoa;

    -- Obtem iduser da pessoa que sai
    SELECT INTO v_iduser_de u.iduser FROM miolo_user u WHERE u.login = v_login_de;

    UPDATE basPerson 
       SET email = NULL,
           miolousername = NULL
     WHERE personId = p_dePessoa;

    -- Verifica se a pessoa que sai possui telefone registrado
    For v_telefone IN
        ( SELECT *
            FROM basPhone
           WHERE personId = p_dePessoa )
    LOOP
        SELECT INTO v_existe_telefone COUNT(*) > 0
               FROM basPhone 
              WHERE personId = p_paraPessoa
                AND basPhone.type = v_telefone.type;

        IF v_existe_telefone IS NULL OR v_existe_telefone IS FALSE
        THEN
            INSERT INTO basPhone
                        (personid, type, phone)
                 VALUES (p_paraPessoa, v_telefone.type, v_telefone.phone);
        END IF;
        
        DELETE FROM basPhone WHERE phoneId = v_telefone.phoneId;

    END LOOP;


    -- Desabilita trigger de documentos unicos
    ALTER TABLE basdocument DISABLE TRIGGER ALL;

    -- Verifica se as duas pessoas possuem os mesmos documentos.
    FOR v_documentos IN 
	( SELECT documentTypeId
	    FROM basDocument
	   WHERE personid = p_dePessoa )
    LOOP
        SELECT INTO v_exits count(*) > 0
	       FROM basDocument
	      WHERE personid = p_paraPessoa
	        AND documentTypeId = v_documentos.documentTypeId;
        IF v_exits = 't'THEN
            DELETE FROM basDocument
		  WHERE personid = p_dePessoa
		    AND documentTypeId = v_documentos.documentTypeId;
	END IF;
    END LOOP;

    --Verifica inscrições nos processos seletivos
    --Primeiro verifica se as duas pessoas tem inscrições no mesmo processo seletivo
    FOR v_processoSeletivo IN
        (SELECT subscriptionId,
                selectiveProcessId
           FROM spr.subscription
          WHERE personId = p_paraPessoa)
    LOOP
        IF (SELECT (COUNT(subscriptionId) > 0)
              FROM spr.subscription
             WHERE personId = p_dePessoa
               AND selectiveProcessId = v_processoSeletivo.selectiveProcessId)
        THEN
            --Então verifica se a pessoa que sai tem contrato, se o contrato for da 
            --mesma ocorrência, precisa mandar fazer unificação de contratos
             IF EXISTS (SELECT 1
                          FROM acdContract A
                    INNER JOIN acdContract B
                            ON (A.courseId = B.courseId AND
                                A.courseVersion = B.courseVersion AND
                                A.turnId = B.turnId AND
                                A.unitId = B.unitId)
                         WHERE A.personId = p_dePessoa
                           AND B.personId = p_paraPessoa)
             THEN
                 RAISE EXCEPTION 'Não será possível realizar a unificação de pessoas, pois as duas pessoas possuem contratos de mesma ocorrência. Por favor, realize a unificação de contratos antes de unificar as pessoas.';
             ELSE
                 --Se quem tem contrato é a pessoa que sai, vamos passar a subscriptionid da pessoa que fica pra ele
                 IF EXISTS (SELECT contractId
                              FROM acdContract
                             WHERE personId = p_dePessoa)
                 THEN
                     UPDATE acdContract 
                        SET subscriptionId = v_processoSeletivo.subscriptionId
                      WHERE personId = p_dePessoa;
                      PERFORM delete_subscription('subscription'::TEXT, 'spr'::TEXT, (SELECT subscriptionId FROM spr.subscription WHERE personId = p_dePessoa AND selectiveProcessId = v_processoSeletivo.selectiveProcessId)::TEXT);
                 ELSE
                     --Se a pessoa que sai não tem contrato, só deleta essa inscrição de modo recursivo
                     PERFORM delete_subscription('subscription'::TEXT, 'spr'::TEXT, (SELECT subscriptionId FROM spr.subscription WHERE personId = p_dePessoa AND selectiveProcessId = v_processoSeletivo.selectiveProcessId)::TEXT);
                 END IF;
             END IF;
        END IF;
    END LOOP;
    

    -- Verifica se existe financeiro em aberto para as duas pessoas.
    SELECT INTO v_deTitulosEmAberto
		COUNT(*) > 0
	   FROM finReceivableInvoice
	  WHERE personid = p_dePessoa
            AND iscanceled = FALSE
	    AND invoiceIdDependence IS NULL
	    AND ROUND(balance(invoiceId)::numeric, 2) > 0.00;

    SELECT INTO v_paraTitulosEmAberto
		COUNT(*) > 0
	   FROM finReceivableInvoice
	  WHERE personid = p_paraPessoa
            AND iscanceled = FALSE
	    AND invoiceIdDependence IS NULL
	    AND ROUND(balance(invoiceId)::numeric, 2) > 0.00;

    -- Verifica se as duas pessoas são funcionários(registro na basemployee) e exclui a que está saindo
    IF ( SELECT COUNT(*) > 0
           FROM basEmployee
          WHERE personid = p_paraPessoa
            AND EXISTS ( SELECT personid
                           FROM basEmployee
                          WHERE personid = p_dePessoa ) )
    THEN
        DELETE FROM basEmployee WHERE personid = p_dePessoa; 
    END IF;

    IF v_deTitulosEmAberto = TRUE AND v_paraTitulosEmAberto = TRUE
    THEN
	RAISE EXCEPTION 'Ambas as pessoas possuem financeiro em aberto, favor ajustar para prosseguir com o processo.';
    END IF;

    SELECT INTO v_spc_movement COUNT(*) > 0 FROM finspcmovement  WHERE  personid = p_dePessoa;
    IF v_spc_movement = TRUE 
    THEN
        SELECT INTO v_titulo_aberto invoiceid FROM finspcmovement  WHERE  personid = p_dePessoa;
        RAISE EXCEPTION 'O processo de unificacao nao pode ser concluido, pois a pessoa % esta com registro no spc referente ao titulo %.', p_dePessoa, v_titulo_aberto.invoiceid;
    END IF;

    -- Executa o processo de unificacao dos registros.
    FOR v_tabela IN 
	( SELECT vt
	    FROM unnest(v_tabelas) x(vt) )
    LOOP
        BEGIN
            EXECUTE 'SELECT testar_valor_vazio(''' || v_tabela || ''', ''' || p_dePessoa || ''', ''' || p_paraPessoa || ''')';
            EXECUTE 'SELECT uniao_de_registros(''' || v_tabela || ''', ''' || p_dePessoa || ''', ''' || p_paraPessoa || ''')';
        EXCEPTION 
            WHEN foreign_key_violation THEN
		CASE v_tabela
                    WHEN 'basphysicalpersonprofessor' THEN
		        RAISE EXCEPTION 'A pessoa % nao e um professor, para prosseguir com o processo, cadastre a pessoa como professor.', p_paraPessoa;
		    WHEN 'basphysicalpersonemployee' THEN
		        RAISE EXCEPTION 'A pessoa % nao e um funcionario, para prosseguir com o processo, cadastre a pessoa como funcionario.', p_paraPessoa;
		    WHEN 'basphysicalpersonstudent' THEN
		        RAISE EXCEPTION 'A pessoa % nao e um aluno, para prosseguir com o processo, cadastre a pessoa como aluno.', p_paraPessoa;
                    ELSE
			RAISE EXCEPTION 'Erro ao atualizar o registro % da tabela %. ERRO: %', p_dePessoa, v_tabela, SQLERRM;
		END CASE;
        END;
    END LOOP;

    -- Seta email e login da pessoa que foi removida para a que fica, se não tiver email. 
    -- Se tiver email, tem que ressetar pra passar por cima das tabelas filhas da basPerson.
    IF EXISTS ( SELECT email FROM ONLY basPerson WHERE personId = p_paraPessoa )
    THEN
        UPDATE basPerson
           SET email = (SELECT email FROM ONLY basPerson WHERE personId = p_paraPessoa )
         WHERE personId = p_paraPessoa;
    ELSE
        UPDATE basPerson 
           SET email = v_email
         WHERE personId = p_paraPessoa;
    END IF;
    
    IF EXISTS ( SELECT miolousername FROM ONLY basPerson WHERE personId = p_paraPessoa )
    THEN
        UPDATE basPerson
           SET miolousername = (SELECT miolousername FROM ONLY basPerson WHERE personId = p_paraPessoa)
         WHERE personId = p_paraPessoa;
    ELSE
        UPDATE basPerson 
           SET miolousername = v_login_de
         WHERE personId = p_paraPessoa;
    END IF;

    -- Obtem o iduser da pessoa que fica
    SELECT INTO v_iduser_para u.iduser FROM miolo_user u WHERE u.login = v_login_para;

    -- Transfere as permissoes da pessoa que sai para pessoa que fica, caso as duas tenham usuario
    IF ( v_login_de IS NOT NULL AND v_login_para IS NOT NULL AND v_login_de <> v_login_para AND v_iduser_de IS NOT NULL AND v_iduser_para IS NOT NULL)
    THEN
        RAISE NOTICE 'Set perms: % (%) to % (%)', v_login_de, v_iduser_de, v_login_para, v_iduser_para;

        UPDATE miolo_groupUser
                SET iduser = v_iduser_para
              WHERE iduser = v_iduser_de
                AND NOT EXISTS(SELECT 1
                                 FROM miolo_groupUser mu
                                WHERE iduser = v_iduser_para
                                  AND idgroup = mu.idgroup
                                  AND unitid = mu.unitid);

        -- Remove permissoes que ficaram ainda penduradas para usuario antigo
        DELETE FROM miolo_groupUser WHERE iduser = v_iduser_de;

        -- Atualiza nas tabelas referenciadas, trocando do iduser/login antigo para o novo
        FOR v_sql IN (
            SELECT 'UPDATE ' || tc.table_schema || '.' || tc.table_name || ' SET ' || kcu.column_name || ' = ''' || (CASE WHEN ccu.column_name = 'iduser' THEN v_iduser_para::text ELSE v_login_para END ) || ''' WHERE ' || kcu.column_name || ' = ''' || (CASE WHEN ccu.column_name = 'iduser' THEN v_iduser_de::text ELSE v_login_de END ) || '''' AS sql
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name
            JOIN information_schema.constraint_column_usage ccu ON ccu.constraint_name = tc.constraint_name
            WHERE constraint_type = 'FOREIGN KEY'
            AND ccu.table_name='miolo_user' )
        LOOP
            --RAISE NOTICE '%', v_sql;
            EXECUTE v_sql;
        END LOOP;

        -- E por fim, exclui o usuario antigo
        DELETE FROM miolo_user WHERE login = v_login_de;
    END IF;

    -- Habilita novamente a trigger de documentos unicos
    ALTER TABLE basdocument ENABLE TRIGGER ALL;

    RETURN TRUE;
END;
$$
LANGUAGE 'plpgsql';
--
