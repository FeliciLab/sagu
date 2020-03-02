CREATE OR REPLACE VIEW cr_acd_tcc_banca AS (
        SELECT TCC.*,
               FEEB.personId AS codigo_membro_banca,
               getPersonName(FEEB.personId) AS membro_banca
          FROM cr_acd_tcc TCC
    INNER JOIN acdFinalExaminationExaminingBoard FEEB
            ON FEEB.enrollId = TCC.enrollId
);
