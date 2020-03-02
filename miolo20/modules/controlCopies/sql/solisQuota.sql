CREATE or REPLACE FUNCTION getPersonId(varchar) RETURNS integer AS
'select personid from ccpPerson where UPPER(name) = UPPER($1);'
LANGUAGE SQL;


CREATE or REPLACE FUNCTION getPrinterId(varchar) RETURNS integer AS
'select printerid from ccpPrinter where UPPER(name) = UPPER($1);'
LANGUAGE SQL;

CREATE or REPLACE FUNCTION getSectorId(varchar ) RETURNS integer AS
'select sectorid from ccpSector where UPPER(description) = UPPER($1) limit 1;'
LANGUAGE SQL;

CREATE or REPLACE FUNCTION checkUser(varchar) RETURNS  boolean AS
'SELECT (SELECT name FROM ccpPerson WHERE UPPER(name) = UPPER($1) ) IS NOT NULL'
LANGUAGE SQL;

CREATE or REPLACE FUNCTION getCurrentPeriod() RETURNS int AS
'SELECT periodId
   FROM ccpPeriod
  WHERE NOW()::date between beginDate and endDate'
LANGUAGE SQL;

CREATE or REPLACE FUNCTION getUserFlags(varchar, OUT name boolean , OUT preferSector boolean, out canExceed boolean)  AS
'SELECT
    (SELECT P.name
       FROM ccpPerson P
  LEFT JOIN ccpPersonSector PS
         ON P.personId = PS.sectorId
      WHERE UPPER(P.name) = UPPER($1) ) IS NOT NULL,
    (SELECT PP.preferSector
       FROM ccpPersonPeriod PP
  LEFT JOIN ccpPerson P
         ON PP.personId = P.personId
      WHERE periodId = getCurrentPeriod()
        AND UPPER(P.name) = UPPER($1)),
    (SELECT canExceed
       FROM ccpPerson
      WHERE UPPER(name) = UPPER($1))'
LANGUAGE SQL;

CREATE or REPLACE FUNCTION getGroupFlags(varchar) RETURNS RECORD AS
'select canExceed from ccpSector where UPPER(description) = UPPER($1)'
LANGUAGE SQL;

CREATE or REPLACE FUNCTION getGroup(varchar) RETURNS varchar AS
'   SELECT S.description
      FROM ccpPersonSector SP
 LEFT JOIN ccpSector S
        ON SP.sectorId = S.sectorId
 LEFT JOIN ccpPerson P
        ON P.personId = SP.personId
     WHERE UPPER(P.name) = UPPER($1)
       AND now()::time BETWEEN
           COALESCE(SP.beginTime, now()::TIME) AND COALESCE(SP.endTime, now()::TIME)'
LANGUAGE SQL;

CREATE or REPLACE FUNCTION getQuota(varchar, OUT copiesNumber bigint, OUT quota integer) AS
'SELECT
(SELECT sum(PC.copiesNumber)
    FROM ccpPersonCopy PC
LEFT JOIN ccpPerson P
        ON PC.personId = P.personId
    WHERE UPPER(P.name) = UPPER($1)
    AND PC.periodId = getCurrentPeriod()),
(SELECT PP.copiesNumber
    FROM ccpPersonPeriod PP
LEFT JOIN ccpPerson P
    ON PP.personId = P.personId
    WHERE periodId = getCurrentPeriod()
    AND UPPER(P.name) = UPPER($1))
'
LANGUAGE SQL;

CREATE or REPLACE FUNCTION checkPrinter(varchar, varchar ) RETURNS boolean AS
'select (SELECT PE.name
           FROM ccpPersonPrinter PP
      LEFT JOIN ccpPrinter P
             ON PP.printerId = P.printerId
      LEFT JOIN ccpPerson  PE
             ON PE.personId = PP.personId
          WHERE UPPER(PE.name) = UPPER($1)
            AND UPPER(P.name) = UPPER($2)) IS NOT NULL'
LANGUAGE SQL;

CREATE or REPLACE FUNCTION getSpoolPath() RETURNS varchar AS
' SELECT value
    FROM basConfig
   WHERE moduleconfig = ''CONTROLCOPIES''
     AND parameter    = ''SPOOL_DIR'';'
LANGUAGE SQL;

CREATE or REPLACE FUNCTION getLogfilePath() RETURNS varchar AS
' SELECT value
    FROM basConfig
   WHERE moduleconfig = ''CONTROLCOPIES''
     AND parameter    ilike ''%quota%'';'
LANGUAGE SQL;

CREATE or REPLACE FUNCTION registerQuotaUser(varchar, varchar, integer ) RETURNS boolean AS
        'INSERT INTO ccpPersonCopy ( personId, periodId, time, copiesNumber, printerId)
            VALUES (getPersonId($1),getCurrentPeriod(), now(), $3, getPrinterId($2));
            select true;'
        LANGUAGE SQL;

CREATE or REPLACE FUNCTION registerQuotaSector(varchar, varchar, integer ) RETURNS boolean AS
'INSERT INTO ccpSectorCopy ( sectorId, periodId, time, copiesNumber, printerId)
    VALUES (getSectorId($1),getCurrentPeriod(), now(), $3, getPrinterId($2));
    select true;'
LANGUAGE SQL;

CREATE OR REPLACE FUNCTION checkHour(varchar) RETURNS boolean AS
'select (select getGroup($1)) is not null;'
LANGUAGE SQL;

CREATE OR REPLACE FUNCTION addPrinter(varchar) RETURNS boolean AS
'INSERT INTO ccpPrinter (name) VALUES ($1);
 SELECT TRUE;'
LANGUAGE SQL;