CREATE OR REPLACE FUNCTION matricula_nova_oferta()
  RETURNS trigger AS
$BODY$
	BEGIN
            INSERT INTO acpmatricula (ofertacomponentecurricularid, 
                                      personid, 
                                      situacao, 
		                      datamatricula, 
				      unitid, 
				      inscricaoturmagrupoid,
				      aproveitamento,
				      aproveitamento_interno, 
				      centerid )
                     SELECT DISTINCT NEW.ofertacomponentecurricularid as ofertacomponentecurricularid, 
                                     acpinscricao.personid, 
                                     case when acpinscricao.situacao = 'I' then 'M' else 'C' end as situacao, 
                                     date(now()) as datamatricula,
                                     acpinscricao.unitid, 
                                     acpinscricaoturmagrupo.inscricaoturmagrupoid, 
                                     false as aproveitamento,
                                     false as aproveitamento_interno, 
                                     acpinscricao.centerid
                                FROM acpinscricao 
                           LEFT JOIN acpinscricaoturmagrupo 
                               USING (inscricaoid)
                           LEFT JOIN acpofertaturma 
                                  ON acpinscricaoturmagrupo.ofertaturmaid = acpofertaturma.ofertaturmaid
                               WHERE acpinscricaoturmagrupo.ofertaturmaid = NEW.ofertaturmaid
                                 AND acpofertaturma.situacao = 'A'
                                 AND acpinscricao.situacao = 'I';
		
		RETURN NEW;
	END 
$BODY$
  LANGUAGE plpgsql VOLATILE COST 100;
