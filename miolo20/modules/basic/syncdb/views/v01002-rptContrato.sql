CREATE OR REPLACE VIEW rptContrato AS
            (SELECT A.*,
                    C.*
               FROM cr_acd_rptcontratosomente C
         INNER JOIN rptPessoa A
                 ON C.codigo_da_pessoa = A.personid
         );