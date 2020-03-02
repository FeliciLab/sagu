CREATE OR REPLACE FUNCTION calculaNota(p_enrollid acdenroll.enrollId%TYPE)
RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: calculanota
  DESCRIPTION: 

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- -----------------  ------------------------------------
  1.0       10-12-2012 ftomasini          FUNÇÃO responsável por calcular 
                                          composição de graus acadêmicos
******************************************************************************/
DECLARE

    v_fatherdegree record;
    v_learningperiodId integer;
    v_media float;
    v_notas record;
    v_exame record;
    v_conta_notas integer;
    v_somatorio_notas float;
    v_nota_antiga float;
    v_conta_alteracoes integer;
    v_escala double precision;
    v_calcula boolean;
    v_notaobrigatoria integer;
    v_valornotaobrigatoria double precision;
    v_notaobrigatoriaparent integer;
    v_recorddate_excluida timestamp;
    v_isclosed boolean;
    v_notaexame record;
    v_soma_grau integer;
    v_soma_grau_final integer;
    
BEGIN
    --verifica se a disciplia oferecida está fechada e não realiza a verificação das notas/graus/obrigatóriedade
    SELECT A.isclosed INTO v_isclosed
      FROM acdgroup A 
INNER JOIN acdenroll B
        ON A.groupid = B.groupid
     WHERE B.enrollid = p_enrollid;

    IF v_isclosed = TRUE THEN
        RETURN FALSE;
    END IF;

       v_conta_alteracoes:=0;
       SELECT INTO v_escala getParameter('ACADEMIC', 'ESCALA_DE_ARREDONDAMENTO_DO_GRAU_FINAL')::float;

    -- Obtém o período letivo da matrí­cula  
    SELECT b.learningperiodid INTO v_learningperiodId
      FROM acdenroll A
INNER JOIN acdgroup B
        ON A.groupid = B.groupid
     WHERE enrollid = p_enrollid;

    -- Obtem a nota de exame
    SELECT INTO v_notaexame * FROM acddegree WHERE learningperiodid = v_learningperiodId AND isexam LIMIT 1;

    RAISE NOTICE 'Período letivo % ',v_learningperiodId ;
    
    v_calcula := true;
    -- Verifica se todas as notas obrigatórias foram informadas
    FOR v_notaobrigatoria IN SELECT degreeid FROM acddegree WHERE learningperiodid = v_learningperiodId AND maybenull = false AND NOT isexam AND degreeid NOT IN (SELECT parentdegreeid FROM acddegree WHERE learningperiodid = v_learningperiodId AND parentdegreeid IS NOT NULL) LOOP
    
        IF GETPARAMETER('ACADEMIC', 'CONSIDER_HIGHER_PUNCTUATION_DEGREE') = 't' THEN
        BEGIN
            SELECT INTO v_recorddate_excluida recorddate FROM acddegreeenroll WHERE enrollid = p_enrollid AND degreeid = v_notaobrigatoria AND note IS NULL ORDER BY recorddate DESC LIMIT 1;
            IF v_recorddate_excluida IS NULL THEN
                v_recorddate_excluida := '2000-01-01'::timestamp;
            END IF;
            SELECT INTO v_valornotaobrigatoria note FROM acddegreeenroll WHERE enrollid = p_enrollid AND degreeid = v_notaobrigatoria AND recorddate > v_recorddate_excluida ORDER BY note DESC LIMIT 1;
        END;
        ELSE
            SELECT INTO v_valornotaobrigatoria note FROM acddegreeenroll WHERE enrollid = p_enrollid AND degreeid = v_notaobrigatoria ORDER BY recorddate DESC LIMIT 1;
        END IF;
        
        IF ( v_valornotaobrigatoria IS NOT NULL ) THEN
        BEGIN
            RAISE NOTICE 'Nota % : %', v_notaobrigatoria, v_valornotaobrigatoria ;
        END;
        ELSE
        BEGIN
            RAISE NOTICE 'Uma nota obrigatoria nao foi preenchida e por isso as medias nao serao calculadas.';

            -- Se ja tiver valor na media, remove.
            SELECT INTO v_notaobrigatoriaparent parentdegreeid FROM acddegree  WHERE degreeid = v_notaobrigatoria;
            SELECT INTO v_soma_grau count(*) FROM acddegreeenroll WHERE enrollid = p_enrollid AND degreeid = v_notaobrigatoriaparent;
            IF ( v_soma_grau > 0 ) THEN
            BEGIN
                INSERT INTO acddegreeenroll (note,enrollid,degreeid,recorddate,description) VALUES (NULL,p_enrollid,v_notaobrigatoriaparent, now() - interval '4 second', 'NOTA EXCLUÍDA AUTOMATICAMENTE');
                EXCEPTION WHEN unique_violation THEN
                RAISE NOTICE 'UNIQUE EXCEPTION: enrollid: %, degreeid: %', p_enrollid, v_notaobrigatoriaparent;
            END;
            END IF;

            -- Verifica se a nota pai da media tem valor. Se sim, remove.
            SELECT INTO v_fatherdegree parentdegreeid FROM acddegree WHERE degreeid = v_notaobrigatoriaparent;
            SELECT INTO v_soma_grau_final count(*) FROM acddegreeenroll WHERE enrollid = p_enrollid AND degreeid = v_fatherdegree.parentdegreeid;
            IF ( v_soma_grau_final > 0 ) THEN
            BEGIN
                INSERT INTO acddegreeenroll (note,enrollid,degreeid,recorddate,description) VALUES (NULL,p_enrollid,v_fatherdegree.parentdegreeid, now() - interval '3 second', 'NOTA EXCLUÍDA AUTOMATICAMENTE');
                EXCEPTION WHEN unique_violation THEN
                RAISE NOTICE 'UNIQUE EXCEPTION: enrollid: %, degreeid: %', p_enrollid, v_fatherdegree.parentdegreeid;
            END;
            END IF;
            
            v_calcula := false;
        END;
        END IF;
        
    END LOOP;
    
    IF ( v_calcula ) THEN
    BEGIN
        --Verifica se tem nota de exame para a matrícula
        SELECT B.degreeid, B.note, A.examcalcmethod INTO v_exame
          FROM acddegree A
    INNER JOIN acddegreeenroll B 
            ON b.degreeid = a.degreeid
         WHERE enrollid = p_enrollid
           AND isexam ='t'
           AND note IS NOT NULL 
      ORDER BY recorddate desc 
         LIMIT 1;

        --Percorre todas as notas que devem ser calculadas média, nota final
        FOR v_fatherdegree IN SELECT AA.degreeid,
                                     AA.parentdegreeid,
                                     AA.degreenumber
                                FROM acddegree AA
                               WHERE AA.degreeid IN (SELECT parentdegreeid
                                                       FROM acddegree A 
                                                      WHERE A.learningperiodid = v_learningperiodId
                                                   GROUP BY 1
                                                     HAVING count(parentdegreeid) >1)
                            GROUP BY AA.degreeid,AA.parentdegreeid,AA.parentdegreeid,AA.degreenumber
                            ORDER BY AA.parentdegreeid ASC, AA.degreenumber
                                                   
        LOOP

            RAISE NOTICE 'Nota pai % ',v_fatherdegree.degreeid ;

            --zera a média
            v_media:= -1;
            --
            --Percorre as notas e mostra seu valor
            --
            v_conta_notas:=0;
            v_somatorio_notas:=0;
            
            IF GETPARAMETER('ACADEMIC', 'CONSIDER_HIGHER_PUNCTUATION_DEGREE') = 't' THEN
            BEGIN
                FOR v_notas
                IN SELECT (SELECT AA.note
                                        FROM acddegreeenroll AA
                                       WHERE AA.enrollId = B.enrollid  
                                         AND AA.degreeid = A.degreeid AND recorddate > (SELECT CASE WHEN (
                                         SELECT recorddate FROM acddegreeenroll 
                                         WHERE enrollid = B.enrollid AND degreeid = A.degreeid AND note IS NULL 
                                         ORDER BY recorddate DESC LIMIT 1) IS NULL THEN '2000-01-01'::timestamp ELSE
                                         (SELECT recorddate FROM acddegreeenroll 
                                         WHERE enrollid = B.enrollid AND degreeid = A.degreeid AND note IS NULL 
                                         ORDER BY recorddate DESC LIMIT 1)
                                         END)
                                         ORDER BY aa.note desc LIMIT 1) AS note,
                                     A.description,
                                     A.weight
                                FROM acddegree A
                          INNER JOIN acddegreeenroll B 
                                  ON b.degreeid = a.degreeid
                               WHERE A.parentdegreeid = v_fatherdegree.degreeid
                                 AND enrollid = p_enrollid
                                 AND isexam ='f' 
                                  AND note IS NOT NULL
                            GROUP BY a.description, a.degreeid, b.enrollid, A.weight
               LOOP
                   RAISE NOTICE 'Nota filho nome -  % - valor: %',v_notas.description, v_notas.note;
                   v_conta_notas:= v_conta_notas + v_notas.weight;
                   v_somatorio_notas:= v_somatorio_notas + (v_notas.note * v_notas.weight);

               END LOOP;
            END;
            ELSE
            BEGIN
                FOR v_notas
                IN SELECT (SELECT AA.note
                                        FROM acddegreeenroll AA
                                       WHERE AA.enrollId = B.enrollid  
                                         AND AA.degreeid = A.degreeid ORDER BY aa.recorddate desc LIMIT 1) AS note,
                                     A.description,
                                     A.weight
                                FROM acddegree A
                          INNER JOIN acddegreeenroll B 
                                  ON b.degreeid = a.degreeid
                               WHERE A.parentdegreeid = v_fatherdegree.degreeid
                                 AND enrollid = p_enrollid
                                 AND isexam ='f' 
                                  AND note IS NOT NULL
                            GROUP BY a.description, a.degreeid, b.enrollid, A.weight
               LOOP
                   RAISE NOTICE 'Nota filho nome -  % - valor: %',v_notas.description, v_notas.note;
                   v_conta_notas:= v_conta_notas + v_notas.weight;
                   v_somatorio_notas:= v_somatorio_notas + (v_notas.note * v_notas.weight);

               END LOOP;
            END;
            END IF;

            --Cálculo da média
            IF ( v_conta_notas::integer != 0 )
            THEN
                v_media:= (v_somatorio_notas/v_conta_notas::integer);
            END IF;

            RAISE NOTICE ' A média será %',v_media;
            
            IF ( v_escala > 0 ) THEN
            BEGIN
                SELECT INTO v_media round((v_media + 0.01)/v_escala) * v_escala;
            END;
            END IF;
            
             --Testa se for grau final
            IF (v_fatherdegree.parentdegreeid IS NULL)
            THEN

                -- Se média for maior que exame e existir nota de exame, exclui a nota do exame.
                IF ( v_media >= v_notaexame.exammaximumnote AND v_exame.note IS NOT NULL ) THEN
                BEGIN
                    RAISE NOTICE 'Média de aprovado sem exame. Excluindo nota de exame.';
                    INSERT INTO acddegreeenroll (note,enrollid,degreeid,recorddate,description) VALUES (NULL,p_enrollid,v_exame.degreeid, now() - interval '2 second', 'NOTA EXCLUÍDA AUTOMATICAMENTE');
                END;                
                ELSE
                BEGIN
                  --Se existe nota de exame para a matricula
                  IF(v_exame.note IS NOT NULL)
                  THEN
                      --Se a forma de calculo for (substitui grau pai)
                      IF(v_exame.examcalcmethod = 'S')
                      THEN
                          RAISE NOTICE 'Exame substitui grau final % ',v_exame.note;
                          v_media:= v_exame.note;
                      END IF;
                      --Se a forma de calculo for (media com grau pai)
                      IF(v_exame.examcalcmethod = 'M')
                      THEN
                          RAISE NOTICE 'Exame média com grau final % ',((v_exame.note + v_media)/2);
                          v_media:= ((v_exame.note + v_media)/2);
                      END IF;
                  ELSE
                      -- Se nota de exame for requerida, nao pode calcular grau final quando aluno estiver em exame e nao tiver sido registrada nota de exame
                      IF ( v_notaexame.maybenull = 'f' AND (v_media >= v_notaexame.examminimumnote AND v_media < v_notaexame.exammaximumnote) ) THEN
                          v_media := -1;
                          SELECT INTO v_soma_grau_final count(*) FROM acddegreeenroll WHERE enrollid = p_enrollid AND degreeid = v_fatherdegree.degreeid;
                          IF ( v_soma_grau_final > 0 ) THEN
                              INSERT INTO acddegreeenroll (note,enrollid,degreeid,recorddate,description) VALUES (NULL,p_enrollid,v_fatherdegree.degreeid, now() - interval '2 second', 'NOTA EXCLUÍDA AUTOMATICAMENTE');                        
                          END IF;
                      END IF;
                  END IF;
                END;
                END IF;

             END IF;
           
           
           v_nota_antiga:= AA.note 
                           FROM acddegreeenroll AA
                          WHERE AA.enrollId = p_enrollid  
                            AND AA.degreeid = v_fatherdegree.degreeid ORDER BY aa.recorddate desc LIMIT 1;

           RAISE NOTICE ' DEBUG ANTIGA % NOVA %',v_nota_antiga,round(v_media::numeric,2);

           -- GRAVA
           --Se a média foi recalculada
           --E
           ----não havia nota antes
           ----OU Se a nota recalculada for diferente da antiga
           IF( v_media != -1 AND ( v_nota_antiga IS NULL OR (v_nota_antiga != round(v_media::numeric,2)) ) )
           --IF( (v_nota_antiga IS NULL AND v_media != -1) OR (v_nota_antiga != round(v_media::numeric,2)) )
           THEN
           v_conta_alteracoes:= v_conta_alteracoes+1;
               RAISE NOTICE 'note = %,
                             recorddate = %
                             enrollid = %
                             degreeid = % ',v_media, now(),p_enrollid,v_fatherdegree.degreeid  ;
                             
               IF GETPARAMETER('ACADEMIC', 'CONSIDER_HIGHER_PUNCTUATION_DEGREE') = 't' THEN
                    INSERT INTO acddegreeenroll (note,enrollid,degreeid,recorddate,description) VALUES (NULL,p_enrollid,v_fatherdegree.degreeid, now() - interval '1 second', 'NOTA EXCLUÍDA AUTOMATICAMENTE');
               END IF;
                          
               INSERT INTO acddegreeenroll (note,enrollid,degreeid,recorddate,description) VALUES (round(v_media::numeric,2),p_enrollid,v_fatherdegree.degreeid, now(), 'Média recalculada automaticamente por alteração em uma das notas que compõem essa nota');
           END IF;
           RAISE NOTICE 'notas modificadas %',v_conta_alteracoes;
        END LOOP;
    END;
    END IF;
    RETURN TRUE;
END;
$BODY$
LANGUAGE 'plpgsql';
