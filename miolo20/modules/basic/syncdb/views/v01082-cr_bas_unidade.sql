CREATE OR REPLACE VIEW cr_bas_unidade AS (
    SELECT U.unitid AS codigo_unidade,
	   U.description AS unidade,
	   U.accountingcode AS codigo_contabilizacao,
	   L.locationid AS codigo_logradouro,
	   L.name AS logradouro,
	   L.zipcode AS cep,
	   L.neighborhoodid AS codigo_bairro,
	   NH.name AS bairro,
	   C.cityid AS codigo_cidade,
	   C.name AS cidade,
	   C.stateid AS uf_estado
      FROM basUnit U
INNER JOIN basLocation L
     USING (locationid)
INNER JOIN basCity C
        ON C.cityid = L.cityId
 LEFT JOIN basNeighborHood NH
     USING (neighborhoodid)
);
