CREATE OR REPLACE VIEW searchmaterialview AS
--comentário qualquer
(
    (
    /* Ajuste da searchmaterialview para levar não precisar usar a preferencia
     * MATERIAL_TYPE_ID_PERIODIC_COLLECTION se tiver dúvidas do porque deste update
     * olhe o ticket #12511
     */
       --Busca todos as obras e une com o próprio exemplar
                 SELECT gtcmaterialcontrol.controlnumber, 
                        gtcmaterialcontrol.entrancedate, 
                        gtcmaterialcontrol.lastchangedate, 
                        gtcmaterialcontrol.category, 
                        gtcmaterialcontrol.level, 
                        gtcmaterialcontrol.materialgenderid, 
                        gtcmaterialcontrol.materialtypeid, 
                        gtcmaterialcontrol.materialphysicaltypeid, 
                        gtcexemplarycontrol.itemnumber AS exemplaryitemnumber, 
                        gtcexemplarycontrol.originallibraryunitid AS exemplaryoriginallibraryunitid, 
                        gtcexemplarycontrol.libraryunitid AS exemplarylibraryunitid, 
                        gtcexemplarycontrol.acquisitiontype AS exemplaryacquisitiontype, 
                        gtcexemplarycontrol.exemplarystatusid AS exemplaryexemplarystatusid, 
                        gtcexemplarycontrol.materialgenderid AS exemplarymaterialgenderid, 
                        gtcexemplarycontrol.materialtypeid AS exemplarymaterialtypeid, 
                        gtcexemplarycontrol.materialphysicaltypeid AS exemplarymaterialphysicaltypeid, 
                        gtcexemplarycontrol.entrancedate AS exemplaryentrancedate, 
                        gtcexemplarycontrol.lowdate AS exemplarylowdate
                   FROM gtcmaterialcontrol
              LEFT JOIN gtcexemplarycontrol USING (controlnumber)
              UNION
                 --Busca os dados do pai com a obra e exemplar do filho. A categoria e nível é do pai
                 SELECT gtcmaterialcontrol.controlnumberfather AS controlnumber, 
                        gtcmaterialcontrol.entrancedate, 
                        gtcmaterialcontrol.lastchangedate, 
                        gtcmaterialcontrolfather.category, 
                        gtcmaterialcontrolfather.level, 
                        gtcmaterialcontrol.materialgenderid, 
                        gtcmaterialcontrol.materialtypeid, 
                        gtcmaterialcontrol.materialphysicaltypeid, 
                        gtcexemplarycontrol.itemnumber AS exemplaryitemnumber, 
                        gtcexemplarycontrol.originallibraryunitid AS exemplaryoriginallibraryunitid, 
                        gtcexemplarycontrol.libraryunitid AS exemplarylibraryunitid, 
                        gtcexemplarycontrol.acquisitiontype AS exemplaryacquisitiontype, 
                        gtcexemplarycontrol.exemplarystatusid AS exemplaryexemplarystatusid, 
                        gtcexemplarycontrol.materialgenderid AS exemplarymaterialgenderid, 
                        gtcexemplarycontrol.materialtypeid AS exemplarymaterialtypeid, 
                        gtcexemplarycontrol.materialphysicaltypeid AS exemplarymaterialphysicaltypeid, 
                        gtcexemplarycontrol.entrancedate AS exemplaryentrancedate, 
                        gtcexemplarycontrol.lowdate AS exemplarylowdate
                   FROM gtcmaterialcontrol
              LEFT JOIN gtcexemplarycontrol USING (controlnumber)
             INNER JOIN gtcmaterialcontrol as gtcmaterialcontrolfather ON (gtcmaterialcontrol.controlnumberfather = gtcmaterialcontrolfather.controlnumber)
                  WHERE gtcmaterialcontrol.controlnumberfather IS NOT NULL
    )
    UNION
    (
             --Busca os dados de obra do PAI e relaciona com os dados do exemplare do filho
             SELECT a.controlnumberfather AS controlnumber, 
                    b.entrancedate, 
                    b.lastchangedate, 
                    b.category, 
                    b.level, 
                    b.materialgenderid, 
                    b.materialtypeid, 
                    b.materialphysicaltypeid, 
                    c.itemnumber AS exemplaryitemnumber, 
                    c.originallibraryunitid AS exemplaryoriginallibraryunitid, 
                    c.libraryunitid AS exemplarylibraryunitid, 
                    c.acquisitiontype AS exemplaryacquisitiontype, 
                    c.exemplarystatusid AS exemplaryexemplarystatusid, 
                    c.materialgenderid AS exemplarymaterialgenderid, 
                    c.materialtypeid AS exemplarymaterialtypeid, 
                    c.materialphysicaltypeid AS exemplarymaterialphysicaltypeid, 
                    c.entrancedate AS exemplaryentrancedate, 
                    c.lowdate AS exemplarylowdate
               FROM gtcmaterialcontrol a
          LEFT JOIN gtcmaterialcontrol b ON b.controlnumber = a.controlnumberfather
          LEFT JOIN gtcexemplarycontrol c ON a.controlnumber = c.controlnumber
              WHERE a.controlnumberfather IS NOT NULL)
    UNION
    (
             --Busca os dados de obra do filho e une com os exemplares do PAI
             SELECT gtcmaterialcontrol.controlnumber, 
                    gtcmaterialcontrol.entrancedate, 
                    gtcmaterialcontrol.lastchangedate, 
                    gtcmaterialcontrol.category, 
                    gtcmaterialcontrol.level, 
                    gtcmaterialcontrol.materialgenderid, 
                    gtcmaterialcontrol.materialtypeid, 
                    gtcmaterialcontrol.materialphysicaltypeid, 
                    gtcexemplarycontrol.itemnumber AS exemplaryitemnumber, 
                    gtcexemplarycontrol.originallibraryunitid AS exemplaryoriginallibraryunitid, 
                    gtcexemplarycontrol.libraryunitid AS exemplarylibraryunitid, 
                    gtcexemplarycontrol.acquisitiontype AS exemplaryacquisitiontype, 
                    gtcexemplarycontrol.exemplarystatusid AS exemplaryexemplarystatusid, 
                    gtcexemplarycontrol.materialgenderid AS exemplarymaterialgenderid, 
                    gtcexemplarycontrol.materialtypeid AS exemplarymaterialtypeid, 
                    gtcexemplarycontrol.materialphysicaltypeid AS exemplarymaterialphysicaltypeid, 
                    gtcexemplarycontrol.entrancedate AS exemplaryentrancedate, 
                    gtcexemplarycontrol.lowdate AS exemplarylowdate
               FROM gtcmaterialcontrol
               JOIN gtcexemplarycontrol ON gtcmaterialcontrol.controlnumberfather = gtcexemplarycontrol.controlnumber
    )
    UNION
    (
         --Busca os dados do kardex trazendo mesmo os que não tem exemplares
         SELECT gtcmaterialcontrol.controlnumber,
                gtcmaterialcontrol.entrancedate,
                gtcmaterialcontrol.lastchangedate,
                gtcmaterialcontrol.category,
                gtcmaterialcontrol.level,
                gtcmaterialcontrol.materialgenderid,
                gtcmaterialcontrol.materialtypeid,
                gtcmaterialcontrol.materialphysicaltypeid,
                ''::varchar(20) AS exemplaryitemnumber,
                gtckardexcontrol.libraryunitid AS exemplaryoriginallibraryunitid,
                gtckardexcontrol.libraryunitid AS exemplarylibraryunitid,
                gtckardexcontrol.acquisitiontype AS exemplaryacquisitiontype,
                null AS exemplaryexemplarystatusid,
                null AS exemplarymaterialgenderid,
                --Antes estava assim : (SELECT value FROM basconfig WHERE parameter = 'MATERIAL_TYPE_ID_PERIODIC_COLLECTION')::int AS exemplarymaterialtypeid,
                gtcmaterialcontrol.materialtypeid AS exemplarymaterialtypeid, 
                null AS exemplarymaterialphysicaltypeid,
                gtckardexcontrol.entrancedate AS exemplaryentrancedate,
                null AS exemplarylowdate
                FROM gtcmaterialcontrol
     RIGHT JOIN gtckardexcontrol USING (controlnumber) 
    )
);