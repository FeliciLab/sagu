CREATE OR REPLACE VIEW cr_bas_aluno AS (
    SELECT PPS.personId AS codigo_aluno,
           PPS.name AS nome,
           PPS.miolousername AS login,
           PPS.email,
           C.courseId AS codigo_curso,
           CO.name AS nome_curso,
           C.courseVersion AS versao_curso,
           T.turnId AS codigo_turno,
           T.description AS descricao_turno,
           U.unitId AS codigo_unidade,
           U.description AS descricao_unidade,
           SC.stateContractId AS codigo_situacao_contratual,
           SC.description AS situacao_contratual,
           (CASE WHEN (SC.inOutTransition = 'O' OR 
		       SC.stateContractId = getParameter('ACADEMIC', 'STATE_CONTRACT_ID_LOCKED')::int OR
		       SC.stateContractId IS NULL)
		 THEN
		      FALSE
		 ELSE
		      TRUE
	    END) AS ativo_no_curso,
	   get_semester_contract(C.contractId) AS semestre_aluno_curso,
	   PPS.sex AS sexo,
	   datetouser(PPS.dateBirth) AS data_nascimento,
	   PPS.mothername AS nome_mae,
	   PPS.fathername AS nome_pai,
	   D.content AS rg,
	   D.organ AS expeditor_rg,
	   datetouser(D.dateexpedition) AS data_expedicao_rg,
	   DC.content AS cpf,
	   PPS.location AS logradouro,
	   PPS.number AS numero,
	   PPS.complement AS complemento,
	   PPS.neighborhood AS bairro,
	   PPS.cityId AS codigo_cidade,
	   getCity(PPS.cityId) AS cidade,
	   PPS.zipcode AS cep,
	   COALESCE(PPS.workPhone, PP.phone) AS telefone_trabalho,
	   COALESCE(PPS.residentialPhone, PR.phone) AS telefone_residencial,
	   COALESCE(PPS.cellPhone, PC.phone) AS telefone_celular,
           C.contractId AS codigo_contrato
 FROM ONLY basPhysicalPersonStudent PPS
 LEFT JOIN acdContract C
     USING (personId)
 LEFT JOIN acdCourse CO
     USING (courseId)
 LEFT JOIN basTurn T
     USING (turnId)
 LEFT JOIN basUnit U
     USING (unitId)
 LEFT JOIN acdStateContract SC
        ON SC.stateContractId = getContractState(C.contractId)
 LEFT JOIN basDocument D
        ON D.personId = C.personId
       AND D.documentTypeId = getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_RG')::INT
 LEFT JOIN basDocument DC
        ON DC.personId = C.personId
       AND DC.documentTypeId = getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::INT
 LEFT JOIN basPhone PP
        ON PP.personId = C.personId
       AND PP.type = 'PRO'
 LEFT JOIN basPhone PR
        ON PP.personId = C.personId
       AND PP.type = 'RES'
 LEFT JOIN basPhone PC
        ON PP.personId = C.personId
       AND PP.type = 'CEL'
);
