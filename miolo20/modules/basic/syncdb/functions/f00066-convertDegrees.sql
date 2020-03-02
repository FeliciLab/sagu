CREATE OR REPLACE FUNCTION convertDegrees() RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE
    learningPeriodLine RECORD;
    enrollLine RECORD;
    totalLp int;
    totalEn int;
    finalDegreeId int;
    averageDegreeId int;
    examDegreeId int;
BEGIN
    totalLp := 0;
    totalEn := 0;
    
    RAISE NOTICE 'Beginning conversion...';
    FOR learningPeriodLine IN SELECT * FROM acdLearningPeriod LOOP
        SELECT acdDegree.degreeId INTO finalDegreeId FROM acdDegree WHERE acdDegree.learningPeriodId = learningPeriodLine.learningPeriodId AND acdDegree.parentDegreeId IS NULL AND acdDegree.degreeNumber = 0;
    
        IF finalDegreeId IS NULL THEN
            SELECT nextVal('seq_degreeId') INTO finalDegreeId;
            
            INSERT INTO acdDegree (degreeId, learningPeriodId, description, degreeNumber) VALUES (finalDegreeId, learningPeriodLine.learningPeriodId, 'NOTA FINAL', 0);
            
            SELECT nextVal('seq_degreeId') INTO examDegreeId;
            
            INSERT INTO acdDegree (degreeId, learningPeriodId, description, degreeNumber, parentDegreeId, mayBeNull) VALUES (examDegreeId, learningPeriodLine.learningPeriodId, 'EXAME', 2, finalDegreeId, true);
            
            SELECT nextVal('seq_degreeId') INTO averageDegreeId;
            
            INSERT INTO acdDegree (degreeId, learningPeriodId, description, degreeNumber, parentDegreeId) VALUES (averageDegreeId, learningPeriodLine.learningPeriodId, 'MEDIA', 1, finalDegreeId);
            
            UPDATE acdDegree SET parentDegreeId = averageDegreeId WHERE learningPeriodId = learningPeriodLine.learningPeriodId AND degreeId NOT IN (finalDegreeId, averageDegreeId, examDegreeId);
            
            FOR enrollLine IN SELECT A.enrollId, A.note, A.examNote, A.finalNote, A.concept FROM acdEnroll A INNER JOIN acdGroup B ON (B.groupId = A.groupId) WHERE B.learningPeriodId = learningPeriodLine.learningPeriodId LOOP
                IF (enrollLine.note IS NOT NULL) THEN
                    INSERT INTO acdDegreeEnroll (degreeId, enrollId, note) VALUES (averageDegreeId, enrollLine.enrollId, enrollLine.note);
                END IF;
                
                IF (enrollLine.examNote IS NOT NULL) THEN
                    INSERT INTO acdDegreeEnroll (degreeId, enrollId, note) VALUES (examDegreeId, enrollLine.enrollId, enrollLine.examNote);
                END IF;
                
                IF (enrollLine.finalNote IS NOT NULL OR enrollLine.concept IS NOT NULL) THEN
                    INSERT INTO acdDegreeEnroll (degreeId, enrollId, note, concept) VALUES (finalDegreeId, enrollLine.enrollId, enrollLine.finalNote, enrollLine.concept);
                END IF;
                
                totalEn := totalEn + 1;
            END LOOP;
        ELSE
            RAISE NOTICE 'Learning period % already has a final degree registered. The changes must be made manually.', learningPeriodLine.learningPeriodId;
        END IF;
        
        totalLp := totalLp + 1;
    END LOOP;
    RAISE NOTICE '% learning periods readed and the data of % enrolls was processed.', totalLp, totalEn;
    RETURN TRUE;
END 
$$;

