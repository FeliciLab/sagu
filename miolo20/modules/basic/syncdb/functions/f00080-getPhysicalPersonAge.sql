CREATE OR REPLACE FUNCTION getPhysicalPersonAge(bigint) RETURNS integer
    LANGUAGE sql
    AS $_$
     SELECT extract(year FROM age(date(now()), dateBirth))::INT
  FROM ONLY basPhysicalPerson
      WHERE personId = $1
$_$;
