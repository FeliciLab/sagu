CREATE OR REPLACE VIEW cr_res_notas_residente AS
        (SELECT --Dados do residente
                R.residenteId,
                PP.personId,
                PP.name,
                E.enfaseId AS codigo_enfase,
                E.descricao AS enfase,
                NP.nucleoProfissionalId AS codigo_nucleo_profissional,
                NP.descricao AS nucleo_profissional,
                TO_CHAR(R.inicio, getParameter('BASIC', 'MASK_DATE')) AS data_inicio,
                TO_CHAR(R.fimPRevisto, getParameter('BASIC', 'MASK_DATE')) AS data_fim,
                COALESCE(T.quantidadePeriodo, 2) AS quantidade_de_periodos,

                --Período 1
                R.notaperiodo1semestre1,
                R.parecerperiodo1semestre1,
                R.notaperiodo1semestre2,
                R.parecerperiodo1semestre2,
                R.mediaperiodo1,
                R.parecermediaperiodo1,

                --Período 2
                R.notaperiodo2semestre1,
                R.parecerperiodo2semestre1,
                R.notaperiodo2semestre2,
                R.parecerperiodo2semestre2,
                R.mediaperiodo2,
                R.parecermediaperiodo2,

                --Período 3
                R.notaperiodo3semestre1,
                R.parecerperiodo3semestre1,
                R.notaperiodo3semestre2,
                R.parecerperiodo3semestre2,
                R.mediaperiodo3,
                R.parecermediaperiodo3,

                --Média final
                R.notaFinal,
                R.parecerFinal,

                --Campos atividades práticas
                R.descricao AS campos_atividades_praticas,

                --Trabalho de conclusão
                (CASE WHEN T.tipoAvaliacaoTCR = 'N'
                      THEN
                           TCC.nota::TEXT
                      ELSE
                           (CASE WHEN TCC.apto
                                 THEN
                                      'APTO'
                                 ELSE
                                      'INAPTO'
                            END)
                 END) AS nota_ou_status_do_tcr,
                 T.tipoAvaliacaoTCR
            FROM res.residente R
 INNER JOIN ONLY basPhysicalPerson PP
           USING (personId)
      INNER JOIN res.enfase E
           USING (enfaseId)
      INNER JOIN res.nucleoProfissional NP
           USING (nucleoProfissionalId)
       LEFT JOIN res.turma T
           USING (turmaId)
       LEFT JOIN res.trabalhoDeConclusao TCC
              ON TCC.residenteId = R.residenteId);
