CREATE OR REPLACE VIEW cr_bas_professor AS (
    SELECT PPP.personId AS codigo_professor,
           PPP.name AS nome,
           PPP.miolousername AS login,
           PPP.email AS email
 FROM ONLY basPhysicalPersonProfessor PPP
);
