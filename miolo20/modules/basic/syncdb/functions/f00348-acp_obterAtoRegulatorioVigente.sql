CREATE OR REPLACE FUNCTION acp_obterAtoRegulatorioVigente(p_ocorrenciaCursoId INT)
RETURNS TABLE (
    atoregulatorioid INT, 
    ocorrenciacursoid INT, 
    documento TEXT, 
    datadocumento DATE, 
    datainicial DATE, 
    datafinal DATE, 
    centerid INT
) AS
$BODY$
/**************************************************************************************
NOME: acp_obterAtoRegulatorioVigente
PURPOSE: Obtém o ato regulatório (portaria) vigente 
         de uma ocorrência de curso do pedagógico.
REVISIONS:
Ver        Date       Author                      Description
---------- ---------- --------------------------- ----------------------------------
1.0        27/02/2015 Augusto A. Silva            Função criada.
**************************************************************************************/
BEGIN
    RETURN QUERY (
        SELECT A.atoregulatorioid, 
               A.ocorrenciacursoid, 
               A.documento, 
               A.datadocumento, 
               A.datainicial, 
               A.datafinal, 
               A.centerid
          FROM acpatoregulatorio A
         WHERE A.ocorrenciacursoid = p_ocorrenciaCursoId
           AND (A.datafinal IS NULL OR NOW()::DATE BETWEEN A.datainicial AND A.datafinal)
         LIMIT 1
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
