/*************************************************************************************
  NAME: digitacaoDeNotasTrocaExamePorMenorNota
  PURPOSE: Utilizada por trigger na acddegreeenroll para substituir a menor nota pela nota do exame, se assim estiver configurado.
  AUTOR: Bruno E. Fuhr
**************************************************************************************/
CREATE OR REPLACE FUNCTION digitacaoDeNotasTrocaExamePorMenorNota(p_enrollid integer, p_degreeid integer, p_nota double precision)
RETURNS INTEGER AS
$BODY$
DECLARE
    
    v_tipo_exame char(1);
    v_is_exame boolean;
    v_degreeid integer;
    
    v_degreeenroll acddegreeenroll%ROWTYPE;
    
    v_executar_troca boolean;
    v_menor_nota double precision;
    v_degreeid_menor_nota integer;
    
    v_calcula_nota boolean;
    
BEGIN
    
    v_executar_troca = true;
    v_menor_nota := NULL;
    
    SELECT examcalcmethod INTO v_tipo_exame FROM acddegree WHERE degreeid = p_degreeid;
    SELECT isexam INTO v_is_exame FROM acddegree WHERE degreeid = p_degreeid;
    
    -- Verifica se a nota é de exame e o tipo de exame é 'Troca menor nota obrigatória'
    -- Se não for, não faz nada
    IF ( v_tipo_exame = 'T' AND v_is_exame AND p_nota > 0 ) THEN
    BEGIN

        FOR v_degreeid IN SELECT degreeid FROM acddegree WHERE learningperiodid IN (SELECT learningperiodid FROM acdgroup WHERE groupid IN (select groupid FROM acdenroll WHERE enrollid = p_enrollid)) and maybenull = false and degreeid NOT IN (SELECT parentdegreeid FROM acddegree WHERE  learningperiodid IN (SELECT learningperiodid FROM acdgroup WHERE groupid IN (select groupid FROM acdenroll WHERE enrollid = p_enrollid)) and parentdegreeid is not null) LOOP
        
           
            SELECT * INTO v_degreeenroll FROM acddegreeenroll WHERE degreeid = v_degreeid AND enrollid = p_enrollid order by recorddate desc limit 1;
            
            IF ( v_degreeenroll.degreeenrollid > 0 ) THEN
            BEGIN
                
                -- Verifica se já existe uma nota alterada por exam. Se existir, utiliza-a.
                IF ( v_degreeenroll.alteradopeloexame = 't' ) THEN
                BEGIN
                    v_menor_nota := v_degreeenroll.note;
                    v_degreeid_menor_nota := v_degreeenroll.degreeid;
                    EXIT;
                END;
                ELSE
                BEGIN
                    IF ( v_menor_nota IS NULL ) THEN
                    BEGIN
                        v_menor_nota := v_degreeenroll.note;
                        v_degreeid_menor_nota := v_degreeenroll.degreeid;
                    END;
                    ELSE
                    BEGIN
                        IF ( v_degreeenroll.note < v_menor_nota ) THEN
                        BEGIN
                            v_menor_nota := v_degreeenroll.note;
                            v_degreeid_menor_nota := v_degreeenroll.degreeid;
                        END;
                        END IF;
                    END;
                    END IF;
                END;
                END IF;
            END;
            ELSE
            BEGIN
                v_executar_troca := false;
            END;
            END IF;
        
        END LOOP;
        
        IF ( v_executar_troca AND ( p_nota > v_menor_nota) ) THEN
        BEGIN
            INSERT INTO acddegreeenroll ( degreeid, enrollid, note, description, alteradopeloexame ) VALUES ( v_degreeid_menor_nota, p_enrollid, p_nota, 'NOTA SUBSTITUÍDA PELO EXAME', true );
            SELECT INTO v_calcula_nota calculaNota(p_enrollid);
        END;
        END IF;
    END;
    END IF;
        
    RETURN 1;
    
END;
$BODY$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION trgDigitacaoDeNotasTrocaExamePorMenorNota()
RETURNS TRIGGER AS
$BODY$
DECLARE
    
BEGIN

    PERFORM digitacaoDeNotasTrocaExamePorMenorNota(NEW.enrollid, NEW.degreeid, NEW.note);

    RETURN NEW;

END;
$BODY$ LANGUAGE plpgsql;
--
