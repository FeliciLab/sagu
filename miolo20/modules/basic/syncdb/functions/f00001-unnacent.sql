CREATE OR REPLACE FUNCTION unaccent(text)
  RETURNS text AS  
$BODY$
BEGIN
     RETURN translate($1, 'ãáàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇñÑ`´''', 'aaaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcCnN   ');
END;
$BODY$
LANGUAGE 'plpgsql' IMMUTABLE;
