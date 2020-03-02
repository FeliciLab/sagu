CREATE OR REPLACE FUNCTION calculaNota_unemat()
  RETURNS trigger AS
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
    v_notas_obrigatorias record;
    v_notas_obrigatorias_preenchidas boolean;
    v_groupId integer;
    v_media_atual double precision;
    v_isclosed boolean;

BEGIN
    --verifica se a disciplia oferecida está fechada e não realiza a verificação das notas/graus/obrigatóriedade
    SELECT A.isclosed INTO v_isclosed
      FROM acdgroup A 
INNER JOIN acdenroll B
        ON A.groupid = B.groupid
     WHERE B.enrollid = NEW.enrollId;

    IF v_isclosed = TRUE THEN
        RETURN NEW;
    END IF;

       v_conta_alteracoes:=0;
       v_notas_obrigatorias_preenchidas = TRUE;

    -- Obtém o período letivo da matrícula  
    SELECT b.learningperiodid INTO v_learningperiodId
      FROM acdenroll A
INNER JOIN acdgroup B
        ON A.groupid = B.groupid
     WHERE enrollid = NEW.enrollId;

    -- Obtém a disciplina oferecida
    SELECT b.groupid INTO v_groupId
      FROM acdenroll A
INNER JOIN acdgroup B
        ON A.groupid = B.groupid
     WHERE enrollid = NEW.enrollId;

    RAISE NOTICE 'Período letivo % ',v_learningperiodId ;

    --Verifica se tem nota de exame para a matrícula
    SELECT B.note, A.examcalcmethod INTO v_exame
      FROM acddegree A
INNER JOIN acddegreeenroll B 
        ON b.degreeid = a.degreeid
     WHERE enrollid = NEW.enrollId
       AND isexam ='t'
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

        FOR v_notas
         IN SELECT (SELECT AA.note 
                                 FROM acddegreeenroll AA
                                WHERE AA.enrollId = B.enrollid  
                                  AND AA.degreeid = A.degreeid ORDER BY aa.recorddate desc LIMIT 1) AS note,
                              a.description
                         FROM acddegree A
                   INNER JOIN acddegreeenroll B 
                           ON b.degreeid = a.degreeid
                        WHERE A.parentdegreeid = v_fatherdegree.degreeid
                          AND enrollid = NEW.enrollId
                          AND isexam ='f' 
                          AND note IS NOT NULL
                     GROUP BY a.description, a.degreeid, b.enrollid
        LOOP
            IF ( v_notas.note IS NOT NULL ) THEN
            BEGIN
                RAISE NOTICE 'Nota filho nome -  % - valor: %',v_notas.description, v_notas.note;
                v_conta_notas:= v_conta_notas +1;
                v_somatorio_notas:= v_somatorio_notas + v_notas.note;
            END;
            END IF;

        END LOOP;

        --Cálculo da média
        IF ( v_conta_notas::integer != 0 )
        THEN
            v_media:= (v_somatorio_notas/v_conta_notas::integer);
        END IF;

        RAISE NOTICE ' A média será %',v_media;

         --Testa se for grau final
        IF (v_fatherdegree.parentdegreeid IS NULL)
        THEN
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
            END IF;
         END IF;

       v_nota_antiga:= AA.note 
                       FROM acddegreeenroll AA
                      WHERE AA.enrollId = NEW.enrollId  
                        AND AA.degreeid = v_fatherdegree.degreeid ORDER BY aa.recorddate desc LIMIT 1;

       RAISE NOTICE ' DEBUG ANTIGA % NOVA %',v_nota_antiga,round(v_media::numeric,2);

       -- Verifica se todas as notas obrigatórias foram preenchidas
       FOR v_notas_obrigatorias IN SELECT i.degreeid
                                     FROM acdgroup g
                               INNER JOIN acdlearningperiod h
                                       ON g.learningperiodid = h.learningperiodid
                               INNER JOIN acddegree i
                                       ON i.learningperiodid = h.learningperiodid  
                                    WHERE g.groupid =  v_groupId
                                      AND isexam ='f'
                                      AND parentdegreeid = v_fatherdegree.degreeid
                                      AND maybenull = 'f'
                                      AND i.degreeid NOT IN (SELECT degreeid FROM acddegree WHERE parentdegreeid = i.degreeid)
       LOOP
           IF ((SELECT note FROM acddegreeenroll WHERE degreeid = v_notas_obrigatorias.degreeid AND enrollid = NEW.enrollId ORDER BY recorddate DESC LIMIT 1) IS NULL)
           THEN            
               v_notas_obrigatorias_preenchidas = FALSE;
           END IF;    
       END LOOP;

       IF ( v_notas_obrigatorias_preenchidas IS FALSE ) THEN
       BEGIN
            SELECT INTO v_media_atual note FROM acddegreeenroll WHERE degreeid = v_fatherdegree.degreeid AND enrollid = NEW.enrollId ORDER BY recorddate DESC LIMIT 1;
            IF v_media_atual IS NOT NULL THEN
            INSERT INTO acddegreeenroll (note,enrollid,degreeid,recorddate,description) VALUES (NULL,NEW.enrollId,v_fatherdegree.degreeid, now(), 'Média excluída por falta de uma nota obrigatória.');
            END IF;
       END;
       END IF;

       -- GRAVA
       --Se a média foi recalculada
       --E
       --Notas obrigatórias preenchidas
       --E
       ----não havia nota antes
       ----OU Se a nota recalculada for diferente da antiga
       IF( v_notas_obrigatorias_preenchidas AND v_media != -1 AND ( v_nota_antiga IS NULL OR (v_nota_antiga != round(v_media::numeric,2)) ) )
       --IF( (v_nota_antiga IS NULL AND v_media != -1) OR (v_nota_antiga != round(v_media::numeric,2)) )
       THEN
       v_conta_alteracoes:= v_conta_alteracoes+1;
           RAISE NOTICE 'note = %,
                         recorddate = %
                         enrollid = %
                         degreeid = % ',v_media, now(),NEW.enrollId,v_fatherdegree.degreeid  ;

           INSERT INTO acddegreeenroll (note,enrollid,degreeid,recorddate,description) VALUES (round(v_media::numeric,2),NEW.enrollId,v_fatherdegree.degreeid, NEW.recorddate + interval '30 second' * random(), 'Média recalculada automaticamente por alteração em uma das notas que compõem essa nota');

       END IF;
       RAISE NOTICE 'notas modificadas %',v_conta_alteracoes;
    END LOOP;
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION calculanota(integer)
  OWNER TO postgres;
--
