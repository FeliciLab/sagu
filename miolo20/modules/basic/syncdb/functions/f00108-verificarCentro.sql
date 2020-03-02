CREATE OR REPLACE FUNCTION verificarCentro()
RETURNS TRIGGER AS
$BODY$
/******************************************************************************
 * Popula coluna centerid, puxando do centro do curso
******************************************************************************/
DECLARE
    v_buscouCentro boolean;
BEGIN
    IF ( TG_OP = 'INSERT' OR TG_OP = 'UPDATE' )
    THEN
        IF ( NEW.centerid IS NULL AND ( current_user <> 'postgres' ) )
        THEN
            v_buscouCentro := false;

            --
            -- Busca centro via courseId
            --
            IF v_buscouCentro IS FALSE
            THEN
                BEGIN
                    IF NEW.courseid IS NOT NULL
                    THEN
                        NEW.centerid := (SELECT centerid FROM acdcourse WHERE courseid = NEW.courseid);
                        v_buscouCentro := true;
                    END IF;
                EXCEPTION WHEN OTHERS
                THEN
                    -- Nada faz
                END;
            END IF;

            --
            -- Busca centro via curriculumId
            --
            IF v_buscouCentro IS FALSE
            THEN
                BEGIN
                    IF NEW.curriculumid IS NOT NULL
                    THEN
                        NEW.centerid := (SELECT c.centerid
                                           FROM acdcurriculum cu
                                     INNER JOIN acdcourse c
                                             ON (c.courseid = cu.courseid)
                                          WHERE cu.curriculumid = NEW.curriculumid);
                        v_buscouCentro := true;
                    END IF;
                EXCEPTION WHEN OTHERS
                THEN
                    -- Nada faz
                END;
            END IF;

            --
            -- Busca centro via groupId
            --
            IF v_buscouCentro IS FALSE
            THEN
                BEGIN
                    IF NEW.groupid IS NOT NULL
                    THEN
                        NEW.centerid := (SELECT c.centerid
                                           FROM acdgroup g
                                     INNER JOIN acdcurriculum cu
                                             ON cu.curriculumid = g.curriculumid
                                     INNER JOIN acdcourse c
                                             ON c.courseid = cu.courseid
                                          WHERE g.groupid = NEW.groupid);
                        v_buscouCentro := true;
                    END IF;
                EXCEPTION WHEN OTHERS
                THEN
                    -- Nada faz
                END;
            END IF;

          --
          -- Busca centro via cursoId
          --

            IF v_buscouCentro IS FALSE
            THEN
                BEGIN
                    IF NEW.cursoId IS NOT NULL
                    THEN
                        NEW.centerid := (SELECT centerId FROM acpCurso WHERE cursoId = NEW.cursoId);
                        v_buscouCentro := TRUE;
                    END IF;
                EXCEPTION WHEN OTHERS
                THEN
                    -- Nada faz
                END;

            END IF;

          --
          -- Busca centro via ocorrenciaCursoId
          --

            IF v_buscouCentro IS FALSE
            THEN
                BEGIN
                    IF NEW.ocorrenciaCursoId IS NOT NULL
                    THEN
                        NEW.centerid := (SELECT A.centerid 
                                           FROM acpcurso A 
                                     INNER JOIN acpocorrenciacurso B 
                                          USING(cursoid) 
                                          WHERE ocorrenciacursoid = NEW.ocorrenciaCursoId);
                        v_buscouCentro := TRUE;
                    END IF;
                EXCEPTION WHEN OTHERS
                THEN
                    -- Nada faz
                END;

            END IF;

          --
          -- Busca centro via ofertaCursoId
          --

            IF v_buscouCentro IS FALSE
            THEN
                BEGIN
                    IF NEW.ofertaCursoId IS NOT NULL
                    THEN
                        NEW.centerid := (SELECT A.centerid 
                                           FROM acpcurso A 
                                     INNER JOIN acpocorrenciacurso B 
                                          USING(cursoid) 
                                     INNER JOIN acpofertacurso C 
                                          USING(ocorrenciacursoid) 
                                          WHERE ofertacursoid = NEW.ofertaCursoId);
                        v_buscouCentro := TRUE;
                    END IF;
                EXCEPTION WHEN OTHERS
                THEN
                    -- Nada faz
                END;
            END IF;

          --
          -- Busca centro via matrizCurricularId
          --
            IF v_buscouCentro IS FALSE
            THEN
                BEGIN
                    IF NEW.matrizCurricularId IS NOT NULL
                    THEN
                        NEW.centerid := (SELECT A.centerId 
                                           FROM acpCurso A
                                     INNER JOIN acpMatrizCurricular B
                                          USING(cursoId)
                                          WHERE B.matrizCurricularId = NEW.matrizCurricularId);
                        v_buscouCentro := TRUE;
                    END IF;
                EXCEPTION WHEN OTHERS
                THEN
                    -- Nada faz
                END;
            END IF;

          --
          -- Busca centro via componenteCurricularId
          --
            IF v_buscouCentro IS FALSE
            THEN
                BEGIN
                    IF NEW.componenteCurricularId IS NOT NULL
                    THEN
                        NEW.centerid := (SELECT A.centerId
                                           FROM acpCurso A
                                     INNER JOIN acpMatrizCurricular B 
                                          USING(cursoId)
                                     INNER JOIN acpMatrizCurricularGrupo C 
                                          USING(matrizcurricularid)
                                     INNER JOIN acpcomponentecurricularmatriz D 
                                          USING(matrizcurriculargrupoid)
                                     INNER JOIN acpComponenteCurricular E 
                                             ON E.componentecurricularid=D.componentecurricularid
                      WHERE E.componenteCurricularId = NEW.componenteCurricularId);
                        v_buscouCentro := TRUE;
                    END IF;
                EXCEPTION WHEN OTHERS
                THEN
                    -- Nada faz
                END;
            END IF;

          --
          -- Busca centro via ofertaTurmaId
          --

            IF v_buscouCentro IS FALSE
            THEN
                BEGIN
                    IF NEW.ofertaTurmaId IS NOT NULL
                    THEN
                        NEW.centerid := (SELECT A.centerid 
                                           FROM acpcurso A 
                                     INNER JOIN acpocorrenciacurso B 
                                          USING(cursoid) 
                                     INNER JOIN acpofertacurso C 
                                          USING(ocorrenciacursoid) 
                                     INNER JOIN acpOfertaTurma D
                                          USING(ofertacursoid)
                                          WHERE D.ofertaTurmaId = NEW.ofertaTurmaId);
                        v_buscouCentro := TRUE;
                    END IF;
                EXCEPTION WHEN OTHERS
                THEN
                    -- Nada faz
                END;
            END IF;

          --
          -- Busca centro via ofertaComponenteCurricularId
          --
            IF v_buscouCentro IS FALSE
            THEN
                BEGIN
                    IF NEW.ofertaComponenteCurricularId IS NOT NULL
                    THEN
                        NEW.centerid := (SELECT A.centerId
                                           FROM acpCurso A
                                     INNER JOIN acpMatrizCurricular B 
                                          USING(cursoId)
                                     INNER JOIN acpMatrizCurricularGrupo C 
                                          USING(matrizcurricularid)
                                     INNER JOIN acpcomponentecurricularmatriz D 
                                          USING(matrizcurriculargrupoid)
                                     INNER JOIN acpOfertaComponenteCurricular F
                                             ON (F.componenteCurricularMatrizId = D.componenteCurricularMatrizId)
                      WHERE F.ofertaComponenteCurricularId = NEW.ofertaComponenteCurricularId);
                        v_buscouCentro := TRUE;
                    END IF;
                EXCEPTION WHEN OTHERS
                THEN
                    -- Nada faz
                END;
            END IF;

          --
          -- Busca centro via matriculaId
          --
            IF v_buscouCentro IS FALSE
            THEN
                BEGIN
                    IF NEW.matriculaId IS NOT NULL
                    THEN
                        NEW.centerid := (SELECT A.centerId
                                           FROM acpCurso A
                                     INNER JOIN acpMatrizCurricular B 
                                          USING(cursoId)
                                     INNER JOIN acpMatrizCurricularGrupo C 
                                          USING(matrizcurricularid)
                                     INNER JOIN acpcomponentecurricularmatriz D 
                                          USING(matrizcurriculargrupoid)
                                     INNER JOIN acpOfertaComponenteCurricular F
                                             ON (F.componenteCurricularMatrizId = D.componenteCurricularMatrizId)
                                     INNER JOIN acpMatricula G
                                             ON (F.ofertaComponenteCurricularId = G.ofertaComponenteCurricularId)
                      WHERE G.matriculaId = NEW.matriculaId);
                        v_buscouCentro := TRUE;
                    END IF;
                EXCEPTION WHEN OTHERS
                THEN
                    -- Nada faz
                END;
            END IF;

        END IF;

        RETURN NEW;
    END IF;

    RETURN OLD;
END;
$BODY$
LANGUAGE plpgsql;

