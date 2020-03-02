<?php

class PrtCamposConfiguraveisPessoa extends bTipo
{
    
    public function __construct()
    {
        parent::__construct('acdcamposconfiguraveispessoa');
    }

    public function obterDadosDaPessoa($personId)
    {
        $sql = new MSQL();
        $sql->setTables('ONLY basphysicalperson P 
            LEFT JOIN ONLY basdocument D ON (P.personid = D.personid)
            LEFT JOIN ONLY basdocumenttype DT ON (D.documenttypeid = DT.documenttypeid)
        ');
        $sql->setColumns('P.name AS personName,
                          P.shortname,
                          P.cityid,
                          P.zipcode,
                          P.location,
                          P.number,
                          P.complement,
                          P.neighborhood,
                          P.email,
                          P.emailalternative,
                          P.url,
                          P.datein,
                          P.password,
                          P.locationtypeid,
                          P.sentemail,
                          P.photoid,
                          P.miolousername AS login,
                          D.obs,
                          P.sex,
                          P.maritalstatusid,
                          P.residentialphone,
                          P.workphone,
                          P.cellphone,
                          P.messagephone,
                          P.messagecontact,
                          P.datebirth,
                          P.cityidbirth,
                          P.countryidbirth,
                          P.responsablelegalid,
                          P.specialnecessitydescription,
                          P.cityidwork,
                          P.zipcodework,
                          P.locationwork,
                          P.complementwork,
                          P.mothername,
                          P.fathername,
                          P.workemployername,
                          P.workfunction,
                          P.workstartdate,
                          P.workenddate,
                          P.ethnicorigin,
                          DT.name as tipodocumento, 
                          D.content AS valordocumento,
                          P.specialnecessityid,
                          D.organ');
        $sql->setWhere('P.personid = ?');
        $sql->addParameter($personId);
        
        return SDatabase::queryAssociative($sql);
    }
}

?>
