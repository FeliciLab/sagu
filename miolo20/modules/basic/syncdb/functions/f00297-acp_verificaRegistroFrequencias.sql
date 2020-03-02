CREATE OR REPLACE FUNCTION acp_verificaRegistroFrequencias()
RETURNS TRIGGER AS 
$BODY$
DECLARE

BEGIN

    --POr padrão assume que todos devem tem frequencia registrada
    UPDATE acpmatricula 
       SET frequenciasregistradas = true 
     WHERE EXISTS (SELECT DISTINCT MO.matriculaid
                              FROM acpmatricula M 
                        INNER JOIN acpocorrenciahorariooferta OHO 
                                ON (M.ofertacomponentecurricularid = OHO.ofertacomponentecurricularid) 
                        INNER JOIN acpmatricula MO 
                                ON MO.ofertacomponentecurricularid = M.ofertacomponentecurricularid 
                               AND MO.situacao = 'M'
                         LEFT JOIN acpfrequencia F
                                ON (F.ocorrenciahorarioofertaid = OHO.ocorrenciahorarioofertaid 
                               AND M.matriculaid = M.matriculaid)
                             WHERE M.matriculaid = NEW.matriculaid  
                               AND M.situacao = 'M'
                               AND M.matriculaid = acpmatricula.matriculaid)

              --para performance
              AND NOT EXISTS (SELECT DISTINCT MO.matriculaid 
                                         FROM acpmatricula M 
                                   INNER JOIN acpocorrenciahorariooferta OHO 
                                           ON (M.ofertacomponentecurricularid = OHO.ofertacomponentecurricularid) 
                                   INNER JOIN acpmatricula MO 
                                           ON MO.ofertacomponentecurricularid = M.ofertacomponentecurricularid 
                                          AND MO.situacao = 'M'
                                    LEFT JOIN acpfrequencia F
                                           ON (F.ocorrenciahorarioofertaid = OHO.ocorrenciahorarioofertaid 
                                          AND M.matriculaid = M.matriculaid)
                                        WHERE M.matriculaid = NEW.matriculaid  
                                          AND M.situacao = 'M' 
                                          AND F.frequencia IS NULL
                                          AND M.matriculaid = acpmatricula.matriculaid);

    --Adiciona falso para os alunos que não tem todas as frenquencias registradas
    UPDATE acpmatricula 
       SET frequenciasregistradas = false 
     WHERE EXISTS (SELECT DISTINCT MO.matriculaid 
                              FROM acpmatricula M 
                        INNER JOIN acpocorrenciahorariooferta OHO 
                                ON (M.ofertacomponentecurricularid = OHO.ofertacomponentecurricularid) 
                        INNER JOIN acpmatricula MO 
                                ON MO.ofertacomponentecurricularid = M.ofertacomponentecurricularid 
                               AND MO.situacao = 'M'
                         LEFT JOIN acpfrequencia F
                                ON (F.ocorrenciahorarioofertaid = OHO.ocorrenciahorarioofertaid 
                               AND M.matriculaid = M.matriculaid)
                             WHERE M.matriculaid = NEW.matriculaid  
                               AND M.situacao = 'M' 
                               AND F.frequencia IS NULL
                               AND M.matriculaid = acpmatricula.matriculaid);

    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql;
