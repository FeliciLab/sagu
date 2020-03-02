CREATE OR REPLACE VIEW cr_res_residente_ocorrencia_contrato AS (
    SELECT R.*,
           OC.ocorrenciadecontratoid AS codigo_ocorrencia_contrato,
	   OC.statusdaocorrenciadecontratoid AS codigo_status_ocorrencia_contrato,
	   SOC.descricao AS status_ocorrencia_contrato,
	   SOC.bloqueiaresidencia AS status_bloqueia_residencia,
	   SOC.concluiresidencia AS status_conclui_residencia,
	   TO_CHAR(OC.dataHora, getParameter('BASIC', 'MASK_TIMESTAMP_DEFAULT')) AS data_hora_ocorrencia_contrato,
	   OC.observacoes AS observacoes_ocorrencia_contrato,
	   OC.centerId AS codigo_centro_ocorrencia_contrato,
	   CO.name AS centro_ocorrencia_contrato
      FROM cr_res_residente R
INNER JOIN res.ocorrenciaDeContrato OC
	ON OC.residenteId = R.codigo_residente
INNER JOIN res.statusDaOcorrenciaDeContrato SOC
        ON SOC.statusDaOcorrenciaDeContratoId = OC.statusDaOcorrenciaDecontratoId
 LEFT JOIN acdCenter CO
        ON CO.centerId = OC.centerId
);
