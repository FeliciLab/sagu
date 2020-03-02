<?php

/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 * 
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 * 
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 06/08/2008
 *
 * */
class BusinessGnuteca3BusPerson extends GBusiness
{

    public $colsNoId;
    public $fullColumns;
    public $MIOLO;
    public $module;
    public $busBond;
    public $busPenalty;
    public $busLibraryUnitConfig;
    public $bondOrderBy;

    /**
     * @var BusinessGnuteca3BusPhone
     */
    public $busPhone;

    /**
     * @var BusinessGnuteca3BusDocument
     */
    public $busDocument;
    public $personId;
    public $personName;
    public $personDv;
    public $personMask;
    public $shortname;
    public $dateIn;
    public $cityId;
    public $cityName;
    public $zipCode;
    public $locationTypeId;
    public $location;
    public $number;
    public $complement;
    public $neighborhood;
    public $email;
    public $password;
    public $operationProcess;
    public $login;
    public $baseLdap;
    public $personGroup;
    public $sex;
    public $dateBirth;
    public $profession;
    public $school;
    public $workPlace;
    public $obs;
    public $sentEmail;
    public $photoId;
    public $emailAlternative;
    public $url;
    public $personIdS;
    public $personNameS;
    public $shortnameS;
    public $cityIdS;
    public $cityS;
    public $cityNameS;
    public $zipCodeS;
    public $locationS;
    public $locationTypeIdS;
    public $numberS;
    public $complementS;
    public $neighborhoodS;
    public $emailS;
    public $passwordS;
    public $loginS;
    public $baseLdapS;
    public $personGroupS;
    public $sexS;
    public $dateBirthS;
    public $professionS;
    public $schoolS;
    public $workPlaceS;
    public $obsS;
    public $emailAlternativeS;
    public $urlS;

    /**
     * Filtra por link ativo da pessoa
     * @var array
     */
    public $activeBondS;
    public $bond;
    public $penalty;
    public $personLibraryUnit;
    public $phone;
    public $document;

    const PERSON_PASSWORD_ENCRYPT_TYPE_OFF = 0;
    const PERSON_PASSWORD_ENCRYPT_TYPE_MD5 = 1;

    /**
     * Class constructor
     * */
    public function __construct()
    {
        parent::__construct();
        $this->MIOLO = MIOLO::getInstance();
        $this->tables = 'basPerson';
        $this->colsNoId = 'name as personName,
                           cityId,
                           zipCode,
                           locationTypeId,
                           location,
                           number,
                           complement,
                           email,                           
                           neighborhood,
                           password,
                           login,
                           obs,
                           shortname,
                           sentEmail,
                           photoId,
                           emailAlternative,
                           url,
                           personDv,
                           personMask,
                           dateIn';

        $this->fullColumns = 'personId, ' . $this->colsNoId;
        $this->busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->busPenalty = $this->MIOLO->getBusiness($this->module, 'BusPenalty');
        $this->busPersonLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusPersonLibraryUnit');
        $this->busLibraryUnitConfig = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitConfig');
        $this->busPhone = $this->MIOLO->getBusiness($this->module, 'BusPhone');
        $this->busDocument = $this->MIOLO->getBusiness($this->module, 'BusDocument');
    }

    /**
     * Obtém o id da pessoa através do login e base Ldap
     * @param string/int $login login do usuário
     * @param int $base base Ldap
     * @return int código do usuário 
     */
    public function getPersonIdFormLoginAndBase($login, $base = null)
    {
        $this->clear();
        $this->setTables('ONLY basperson BP LEFT JOIN gtclibperson LP USING(personid)');
        $this->setColumns('personId');
        $this->setWhere('BP.login = ?');
        $args[0] = $login;

        if ($base)
        {
            $this->setWhere('LP.baseLdap = ?');
            $args[] = $base;
        }

        $sql = $this->select($args);        
        $result = $this->query($sql, true);
        
        if ( !$result[0] )
        {
            $args[0] = str_replace(array('.', ',', '-', '/', '\'', ' '), '', $login);
            $sql = $this->select($args);
            $result = $this->query($sql, true);
        }

        return $result[0];
    }

    /**
     * Return a specific record from the database
     *
     * @param $personId (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     * */
    public function getPerson($personId, $return = FALSE, $onlyActive = false, $activeLink = FALSE)
    {
        if (!$personId || !is_numeric($personId))
        {
            return false;
        }
        else
        {
            $data = array($personId);

            $this->clear();
            $this->setColumns('personId,
                           basPerson.name as personName,
                           basCity.cityId,
                           basPerson.zipCode,
                           locationTypeId,
                           location,
                           number,
                           complement,
                           email,                           
                           neighborhood,
                           password,
                           login,
                           obs,
                           shortname,
                           sentEmail,
                           photoId,
                           emailAlternative,
                           url,
                           personDv,
                           personMask,
                           dateIn,
                           basCity.name as cityName');
            $this->setTables('basPerson LEFT JOIN basCity USING(cityId)');
            $this->setWhere('personId = ?');
            $sql = $this->select($data);
            $rs = $this->query($sql, TRUE);

            if ($rs)
            {
                //Pega informações da gtclibperson
                $busLibPerson = $this->MIOLO->getBusiness($this->module, 'BusLibPerson');
                $rsLibPerson = $busLibPerson->getLibPerson($personId);

                $rs[0] = (object) array_merge((array) $rs[0], (array) $rsLibPerson[0]);

                if (!$return)
                {
                    $this->setData($rs[0]);
                    $this->email = $rs[0]->email; //o setData para o campo email por algum motivo nao esta funcionando corretamente

                    $this->busBond->personIdS = $personId;
                    $this->bond = $this->busBond->searchBond(TRUE, $this->bondOrderBy);
                    
                    $this->busPenalty->personIdS = $personId;
                    $this->penalty = $this->busPenalty->searchPenalty(TRUE, NULL, FALSE);

                    $this->busPersonLibraryUnit->personIdS = $personId;
                    $this->personLibraryUnit = $this->busPersonLibraryUnit->searchPersonLibraryUnit(TRUE);

                    $this->busPhone->personIdS = $personId;
                    $this->phone = $this->busPhone->searchPhone(true);

                    $this->busDocument->personIdS = $personId;
                    $this->document = $this->busDocument->searchDocument(true);

                    return $this;
                }
                else
                {
                    $result = $rs[0];
                    $this->busBond->personIdS = $personId;

                    //mostra somente link ativo
                    if ($onlyActive === 'ALL')
                    {
                        $this->busBond->allActive = true;
                    }
                    else if ($onlyActive == true)
                    {
                        $this->busBond->byActive = true;
                    }

                    //mostra somente link ativo
                    if ($activeLink == true)
                    {
                        $this->busBond->activeLink = true;
                    }

                    $result->bond = $this->busBond->searchBond(TRUE);

                    $this->busPenalty->personIdS = $personId;
                    $result->penalty = $this->busPenalty->searchPenalty(TRUE, NULL, TRUE);

                    $this->busPersonLibraryUnit->personIdS = $personId;
                    $result->personLibraryUnit = $this->busPersonLibraryUnit->searchPersonLibraryUnit(TRUE);

                    $this->busDocument->personIdS = $personId;
                    $result->document = $this->busDocument->searchDocument(true);

                    return $result;
                }
            }
        }
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     * */
    public function searchPerson($returnAsObject = false)
    {
        $this->clear();

        if ($v = $this->personIdS)
        {
            $this->setWhere('personId = ?');
            $data[] = $v;
        }

        if ($v = $this->personNameS)
        {
            //Ao fazer a busca pelo nome também procura pelo apelido
            $this->setWhere(' ( lower(unaccent(BP.name)) LIKE lower(unaccent(?)) OR lower(unaccent(shortname)) LIKE lower(unaccent(?)) ) ');
            $data[] = $v . '%';
            $data[] = $v . '%';
        }

        if ($v = $this->cityIdS)
        {

            $this->setWhere('B.cityid = ?');
            $data[] = $v;
        }        

        if ($v = $this->zipCodeS)
        {
            $this->setWhere('zipCode = ?');
            $data[] = $v;
        }

        if ($v = $this->locationTypeIdS)
        {
            $this->setWhere('lower(locationTypeId) LIKE lower(?)');
            $data[] = $v . '%';
        }

        if ($v = $this->locationS)
        {
            $this->setWhere('lower(location) LIKE lower(?)');
            $data[] = $v . '%';
        }

        if ($v = $this->numberS)
        {
            $this->setWhere('number = ?');
            $data[] = $v;
        }

        if ($v = $this->complementS)
        {
            $this->setWhere('lower(complement) LIKE lower(?)');
            $data[] = $v . '%';
        }
        
         if ($v = $this->cityNameS)
        {
            $this->setWhere('lower(B.name) LIKE lower(?)');
            $data[] = $v . '%';
        }

        if ($v = $this->neighborhoodS)
        {
            $this->setWhere('lower(neighborhood) LIKE lower(?)');
            $data[] = $v . '%';
        }

        if ($v = $this->emailS)
        {
            $this->setWhere('( lower(email) LIKE lower(?) OR lower(emailalternative) LIKE lower(?) )');
            $data[] = $v . '%';
            $data[] = $v . '%';
        }

        if ($v = $this->loginS)
        {
            $this->setWhere('login = ? ');
            $data[] = $v;
        }

        if ($v = $this->baseLdapS)
        {
            $this->setWhere('LP.baseLdap = ?');
            $data[] = $v;
        }

        if ($v = $this->personGroupS)
        {
            $this->setWhere('lower(LP.persongroup) LIKE lower(?)');
            $data[] = $v . '%';
        }

        if ($v = $this->sexS)
        {
            $this->setWhere('LP.sex = ?');
            $data[] = $v;
        }

        if ($v = $this->dateBirth)
        {
            $this->setWhere('lower(LP.dateBirth) = ?');
            $data[] = $v;
        }

        if ($v = $this->professionS)
        {
            $this->setWhere('lower(LP.profession) ILIKE lower(?)');
            $data[] = $v . '%';
        }

        if ($v = $this->workPlaceS)
        {
            $this->setWhere('lower(LP.workPlace) ILIKE lower(?)');
            $data[] = $v . '%';
        }

        if ($v = $this->schoolS)
        {
            $this->setWhere('lower(LP.school) ILIKE lower(?)');
            $data[] = $v . '%';
        }

        if ($v = $this->obsS)
        {
            $this->setWhere('lower(obs) ILIKE lower(?)');
            $data[] = $v . '%';
        }

        if ($v = $this->urlS)
        {
            $this->setWhere('lower(url) LIKE lower(?)');
            $data[] = $v . '%';
        }

        $columns = 'personid,
                    BP.name as personName,
                    B.name,
                    BP.zipCode,
                    locationTypeId,
                    location,
                    number,
                    complement,
                    email,                           
                    neighborhood,
                    password,
                    login,
                    obs,
                    LP.sex as sex,
                    shortname,
                    sentEmail,
                    photoId,
                    emailAlternative,
                    url';

        

        if ($this->activeBondS)
        {
            if (!is_array($this->activeBondS))
            {
                $this->activeBondS = array($this->activeBondS);
            }

            $columns .= ',( SELECT PL.linkid FROM basPersonLink PL LEFT JOIN basLink L ON ( L.linkId = PL.LINKID ) WHERE  PL.personID = BP.personId and PL.LINKID = '. implode('', $this->activeBondS) . ' and (PL.datevalidate >= now()::date OR PL.datevalidate IS NULL) ORDER BY level LIMIT 1 ) as activeLinkId';
        }
//        else
//        {
//            $columns = $this->fullColumns;
//        }
        $this->setColumns($columns);

        $this->setTables('ONLY basperson BP 
                     LEFT JOIN gtclibperson LP 
                         USING (personid) 
                     LEFT JOIN bascity B 
                               ON (BP.cityid = B.cityid)');
        $this->setOrderBy('personId');

        $sql = $this->select($data);

        if ($this->activeBondS)
        {
            $sql = "SELECT * FROM ( $sql ) as foo WHERE activeLinkId in (" . implode(',', $this->activeBondS) . ')';
           
        }
        
        return $this->query($sql, $returnAsObject);
    }

    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     * */
    public function insertPerson()
    {
        $this->encryptPassword();
        $busLibPerson = $this->MIOLO->getBusiness($this->module, 'BusLibPerson');

        $manual = false;
        //se no for informado código no formulário, pega o nextval
        if (!$this->personId)
        {
            $this->personId = $this->getNextId();
        }
        else
        {
            $manual = true;
        }

        $columns = 'personId,
                    name,
                    cityId,
                    zipCode,
                    locationTypeId,
                    location,
                    number,
                    complement,
                    email,                        
                    neighborhood,
                    password,
                    login,
                    obs,
                    shortname,
                    photoId,
                    emailAlternative,
                    url,
                    personDv,
                    personMask';
        
        $this->clear();
        $this->setColumns($columns);
        $this->setTables('basperson');

        $sql = $this->insert($this->associateData($this->fullColumns));
        $rs = $this->execute($sql);
        
        if ($rs)
        {
            //Insere informações na gtcLibPerson
            $busLibPerson->setData($this);
            $busLibPerson->insertLibPerson();

            if ($this->bond)
            {
                foreach ($this->bond as $value)
                {
                    $this->busBond->setData($value);
                    $this->busBond->personId = $this->personId;
                    $this->busBond->insertBond();
                }
            }

            if ($this->penalty)
            {
                foreach ($this->penalty as $value)
                {
                    $this->busPenalty->setData($value);
                    $this->busPenalty->personId = $this->personId;
                    $this->busPenalty->insertPenalty();
                }
            }

            if ($this->phone)
            {
                foreach ($this->phone as $key => $value)
                {
                    $this->busPhone->setData($value);
                    $this->busPhone->personId = $this->personId;
                    $this->busPhone->insertPhone();
                }
            }

            if ($this->personLibraryUnit)
            {
                foreach ($this->personLibraryUnit as $value)
                {
                    $this->busPersonLibraryUnit->setData($value);
                    $this->busPersonLibraryUnit->personId = $this->personId;
                    $this->busPersonLibraryUnit->insertPersonLibraryUnit();
                }
            }

            if ($this->document)
            {
                foreach ($this->document as $value)
                {
                    $this->busDocument->setData($value);
                    $this->busDocument->personId = $this->personId;
                    $this->busDocument->cityId = $this->cityIdDocument;
                    $this->busDocument->insertDocument();
                }
            }
        }

        //se foi especificado codigo manual, atualiza a tabela de sequência da pessoa
        if ($manual)
        {
            $this->updateSequenceId();
        }

        return $rs;
    }

    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     * */
    public function updatePerson()
    {
        $this->encryptPassword();
        $this->clear();
        
        $changePersonPermissions = $this->getPersonChangePermissions();
        
        //Quando parâmetro CHANGE_WRITE_PERSON:tabMain=t estiver configurado para não gravar dados do usuário. Não deve atualizar view de Pessoas.
        if (MUtil::getBooleanValue($changePersonPermissions->tabMain) == FALSE)
        {
            $rs = '1';
        }
        else
        {
            $columns = 'name,
                        cityId,
                        zipCode,
                        locationTypeId,
                        location,
                        number,
                        complement,
                        email,                        
                        neighborhood,
                        password,
                        login,
                        obs,
                        shortname,
                        sentEmail,
                        photoId,
                        emailAlternative,
                        url,
                        personDv,
                        personMask,
                        dateIn';

            $colsAssociate = $this->colsNoId;

            //se não tiver password
            if (!$this->password)
            {
                $columns = str_replace('password,', '', $columns);
                $colsAssociate = str_replace('password,', '', $colsAssociate);
            }

            $this->setColumns($columns);



            $this->setTables($this->tables);
            $this->setWhere('personId = ?');
            $sql = $this->update($this->associateData($colsAssociate . ', personId'));

            $rs = $this->execute($sql);

            if ($rs)
            {
                $busLibPerson = $this->MIOLO->getBusiness($this->module, 'BusLibPerson');
                $libPersonExist = $busLibPerson->getLibPerson($this->personId);
                $busLibPerson->setData($this);

                //Se já tiver pessoa cadastrada na libperson
                if (isset($libPersonExist[0]))
                {
                    //atualiza libperson
                    $busLibPerson->updateLibPerson();
                }
                else
                {
                    //senão insere pessoa na gtcLibPerson                    
                    $busLibPerson->insertLibPerson();
                }
            }
        }

        if ($this->phone)
        {
            foreach ($this->phone as $key => $value)
            {
                $this->busPhone->setData($value);
                $this->busPhone->personId = $this->personId;
                $this->busPhone->updatePhone();
            }
        }

        if ($this->bond)
        {
            foreach ($this->bond as $value)
            {
                $this->busBond->setData($value);
                $this->busBond->personId = $this->personId;
                $this->busBond->updateBond();
            }
        }

        if ($this->penalty)
        {
            foreach ($this->penalty as $value)
            {
                $this->busPenalty->setData($value);
                $this->busPenalty->personId = $this->personId;
                $this->busPenalty->updatePenalty();
            }
        }

        if ($this->personLibraryUnit && CLASS_USER_ACCESS_IN_THE_LIBRARY != 'BusBlockGroupLibraryUnit')
        {
            $this->busPersonLibraryUnit->personIdS = $this->personId;
            $search = $this->busPersonLibraryUnit->searchPersonLibraryUnit(TRUE);
            if ($search)
            {
                foreach ($search as $value)
                {
                    $this->busPersonLibraryUnit->deletePersonLibraryUnit($value->libraryUnitId, $value->personId);
                }
            }
            foreach ($this->personLibraryUnit as $value)
            {
                if (!$value->removeData)
                {
                    $this->busPersonLibraryUnit->setData($value);
                    $this->busPersonLibraryUnit->personId = $this->personId;
                    $this->busPersonLibraryUnit->insertPersonLibraryUnit();
                }
            }
        }

        if ($this->document)
        {
            foreach ($this->document as $value)
            {
                $this->busDocument->setData($value);
                $this->busDocument->personId = $this->personId;
                $this->busDocument->updateDocument();
            }
        }

        return $rs;
    }

    /**
     * Delete a record
     *
     * @param $moduleConfig (string): Primary key for deletion
     * @param $parameter (string): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     * */
    public function deletePerson($personId)
    {
        $busLibPerson = $this->MIOLO->getBusiness($this->module, 'BusLibPerson');
        $busFile = $this->MIOLO->getBusiness('gnuteca3', 'BusFile');
        $changePersonPermissions = $this->getPersonChangePermissions();
        
        if (MUtil::getBooleanValue($changePersonPermissions->tabMain) == FALSE)
        {
            throw new Exception(_M('As pessoas devem ser removidas no software relacionado.', 'gnuteca3'));
        }

        //delete telegone e documentos
        $this->busPhone->deletePhone($personId);
        $this->busDocument->deleteDocument($personId);
        //Remove a pessoa da libperson
        $rs = $busLibPerson->deleteLibPerson($personId);

        //Se conseguiu 
        if ($rs)
        {
            $this->clear();
            $this->setTables($this->tables);
            $this->setWhere('personId = ?');

            $rs = $this->execute($this->delete(array($personId)));

            //Se excluiu
            if ($rs)
            {
                //TODO incluir funcionalidade de remoção da foto
                $busFile->fileName = $personId;
                $busFile->folder = 'person';
                //Busca a foto do usuario com informação de extensão e filename
                $personPhoto = $busFile->searchFile(true);
                //Obtem o caminho da foto da pessoas
                $filePath = $busFile->getAbsoluteFilePath('person', $personPhoto[0]->filename, $personPhoto[0]->extension);
                //Remove a foto do usuario.
                $busFile->deleteFile($filePath);
            }
        }
        return $rs;
    }

    /**
     * Get constants for a specified module
     *
     * @param $moduleConfig (string): Name of the module to load values from
     *
     * @return (array): An array of key pair values
     *
     * */
    public function getPersonValues($personId)
    {
        $data = array($personId);

        $this->clear();
        $this->setColumns('personId');
        $this->setTables($this->tables);
        $this->setWhere('personId = ?');
        $sql = $this->select($data);
        $rs = $this->query($sql);
        return $rs;
    }

    /**
     * Obtem informações básicas sobre a pessoa, código, nome, email e login
     *
     * @param integer $personId
     * @return string
     */
    public function getBasicPersonInformations($personId)
    {
        if (!$personId)
        {
            return null;
        }
        
        $this->clear();
        $this->setColumns('personId, basperson.name, email,login');
        $this->setTables($this->tables);
        $this->setWhere('personId = ?');
        $sql = $this->select(array($personId));
        try
        {
            $rs = $this->query($sql, true);
        }
        catch (Exception $n)
        {
            $this->clear();
            $this->setColumns('personId, BP.name, email,login');
            $this->setTables($this->tables);
            $this->setWhere('personId = ?');
            $sql = $this->select(array($personId));
            $rs = $this->query($sql, true);
        }
        
        return $rs[0];
    }

    public function getEmail($personId)
    {
        $data = array($personId);

        $this->clear();
        $this->setColumns('email');
        $this->setTables($this->tables);
        $this->setWhere('personId = ?');
        $sql = $this->select($data);
        $rs = $this->query($sql);

        return $rs ? $rs[0][0] : false;
    }

    public function getPassword($personId)
    {
        $data = array($personId);

        $this->clear();
        $this->setColumns('password');
        $this->setTables($this->tables);
        $this->setWhere('personId = ?');
        $sql = $this->select($data);
        $rs = $this->query($sql);

        return $rs;
    }
    
    /*
     * Método implementado para funcionalidade de utilzar cartão para reconhecimento
     * Implementado em 06/2014
     * Por: Tcharles Silva
     */
    public function getPersonIdByLogin($login)
    {
        $data = array($login);

        $this->clear();
        $this->setColumns('personId');
        $this->setTables($this->tables);
        $this->setWhere('login = ?');
        $sql = $this->select($data);
        $rs = $this->query($sql);
        
        return $rs;
    }

    public function getNextId()
    {
        $query = $this->query("SELECT NEXTVAL('seq_personId')");
        return $query[0][0];
    }

    /**
     * Método que atualiza o id da sequência com o maior código de aluno
     *
     * @param $id
     */
    public function updateSequenceId()
    {
        return $this->execute("SELECT setval('seq_personid', (SELECT max(personId) FROM ONLY basPerson))");
    }

    /**
     * Make user login (authentication)
     *
     * @param int $user the user id (personId)
     * @param string $password the password of user
     * @return true if success
     */
    public function authenticate($user, $password)
    {
        if (PERSON_PASSWORD_ENCRYPT == self::PERSON_PASSWORD_ENCRYPT_TYPE_MD5)
        {
            $password = md5($password);
        }
                
        //$user é passado duas vezes para verificar no e-mail também.
        $data = array($user, $user, $password, $password);

        $this->clear();
        //Deve retornar personId para não usar o email da pessoa como código, isso impacta nos sqls de autenticação da minha biblioteca
        $this->setColumns('name,personid');
        $this->setTables('basperson');
        //Valida pelo usuario ou pelo e-mail
        $this->setWhere('(personId::varchar = ? OR email = ?)');
        //Verificação de password com e sem md5 devido a resposta 21 no ticket #15600        
        $this->setWhere('(password = ? OR password = md5(?))');
        $sql = $this->select($data);

        $rs = $this->query($sql);
        
        return $rs;
    }

    /**
     * Change the user password
     *
     * @param int $user the user code (personId)
     * @param string $password the user password
     * @param string $retype the retype of password to verify
     * @return true if change
     */
    public function changePassword($user, $password, $retype)
    {        
        if ($password != $retype || !$password || !$retype)
        {
            return false;
        }
        else
        {
            if (!$user)
            {
                return false;
            }
            else
            {
                $this->getPerson($user);
                if ($this->personId)
                {
                    $this->clear();
                    $this->setColumns('password');
                    $this->setTables('basperson');
                    $this->setWhere('personId = ?');
                    $this->password = $password;
                    $sql = $this->update($this->associateData('password, personId'));
                    $rs = $this->execute($sql);
                    return $rs;
                }
            }
        }
    }

    /**
     * Verify if user is in operation process (is making a process)
     *
     * @param integer $personId the user code
     * @return true if is in operation
     */
    public function isOperationProcess($personId)
    {
        $busLibPerson = $this->MIOLO->getBusiness($this->module, 'BusLibPerson');
        $busLibPerson->personId = $personId;
        return $busLibPerson->isOperationProcess($personId);
    }

    /**
     * Remove o processo de operação para uma pessoa
     *
     * @param unknown_type $personId
     * @return unknown
     */
    public function removeOperationProcess($personId)
    {
        $busLibPerson = $this->MIOLO->getBusiness($this->module, 'BusLibPerson');
        $busLibPerson->personId = $personId;
        return $busLibPerson->removeOperationProcess();
    }

    /**
     * Define o processo de operação para uma pessoa
     *
     * @param unknown_type $personId
     * @return unknown
     */
    public function setOperationProcess($personId)
    {
        $busLibPerson = $this->MIOLO->getBusiness($this->module, 'BusLibPerson');
        $busLibPerson->personId = $personId;
        return $busLibPerson->setOperationProcess($personId);
    }

    public function checkAccessLibraryUnit($personId, $libraryUnitId)
    {
        $className = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'CLASS_USER_ACCESS_IN_THE_LIBRARY');
        if (empty($className))
        {
            return true;
        }
        else
        {
            $bus = MIOLO::getInstance()->getBusiness($this->module, $className);
            return $bus->checkAccess($personId, $libraryUnitId);
        }
    }

    /**
     * Método que verifica se usuário existe no ldap e insere a pessoa no ldap
     *
     * @param (String) $login
     * @param (int) $baseId
     * @param (String) $password
     * @param (boolean) $verifyPersonInLdap
     * @return personId ou booleano 
     */
    public function insertLdapPerson($login, $baseId, $password = null, $verifyPersonInLdap = false)
    {
        //obtém a classe de autenticação configurada
        $class = strtolower($this->MIOLO->getConf('login.classUser'));

        if ($class && $baseId)
        {
            if (!( $this->MIOLO->import('classes::security::' . $class, $class) ))
            {
                $this->MIOLO->import('modules::' . $this->MIOLO->getConf('login.module') . '::classes::' . $class, $class, $this->MIOLO->php);
            }

            $authLdap = new $class($baseId);
        }
        else
        {
            return false;
        }

        //busca os dados no ldap
        $ldapData = $authLdap->searchData($login);


        //verifica se usuário existe no ldap
        if ($password)
        {
            $exists = $authLdap->authenticate($login, $password) && ( $ldapData['count'] > 0);
        }
        else
        {
            $exists = ($ldapData['count'] > 0);
        }

        //caso esse parametro seja passado como true, retorna a verficação se pessoa existe no ldap
        if ($verifyPersonInLdap)
        {
            return $exists;
        }

        //trata os dados da prerência MY_LIBRARY_LDAP_INSERT_USER
        $data = new stdClass();

        if ($exists)
        {
            //faz o parse da configuração, ja busca da base passada por parâmetro
            $lines = explode("\n", MY_LIBRARY_LDAP_INSERT_USER);
            $break = false;

            if (is_array($lines))
            {
                foreach ($lines as $i => $line)
                {
                    $conf = explode(';', $line);

                    if (is_array($conf))
                    {
                        $first = true;

                        foreach ($conf as $k => $val)
                        {
                            $values = explode('=', $val);

                            if ($first)
                            {
                                if ($values[0] == $baseId)
                                {
                                    $break = true;
                                }
                                $data->base = $values[0];
                            }
                            else
                            {
                                $data->$values[0] = $values[1];
                            }
                            $first = false;
                        }
                    }

                    if ($break)
                    {
                        break;
                    }
                }
            }

            $ldapData = $ldapData[0]; //obtém o primeiro registro
            //se não achar configuração para base, retorna false
            if (strlen($data->base) == 0)
            {
                return false;
            }

            $this->personName = $ldapData[strtolower($data->nome)][0]; //obtém o nome da pessoa do ldap

            if (strlen($this->personName) > 0)
            {
                $this->email = $ldapData[strtolower($data->email)][0]; //obtém o e-mail do ldap
                $this->login = $ldapData[strtolower($data->login)][0]; //obtém o login do ldap
                $this->baseLdap = $baseId;
                $this->locationTypeId = 35; //TODO Entender porque no gnuteca este campo da basperson é obrigatório. Vide ticket 
                $this->location = 'Não definida';

                //seta o vínculo da pessoa
                $bond = new stdClass();
                $bond->linkId = $data->vinculo;
                $bond->dateValidate = $data->validade;
                $this->bond = array($bond);

                //insere a pessoa
                $ok = $this->insertPerson(); //insere a pessoa no Gnuteca

                return $ok ? $this->personId : false;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * Retorna se a pessoa tem restrições no gnuteca.
     * Verifica multas, penalidades e empréstimos em aberto.
     * 
     * @return boolean
     */
    public function nothingInclude($personId)
    {
        if (!$personId)
        {
            throw new Exception(_M("É necessário um código de pessoa para obter suas restrições.", 'gnuteca3'));
        }

        $result = $this->query("SELECT * FROM gtcNadaConsta($personId);");

        return $result[0][0] == DB_TRUE;
    }

    /**
     * Obtem uma listagem com as restrições da pessoa com o Gnuteca
     * 
     * @param integer $personId
     * @return array
     */
    public function getRestrictions($personId)
    {
        if (!$personId)
        {
            throw new Exception(_M("É necessário um código de pessoa para obter suas restrições.", 'gnuteca3'));
        }

        return $this->query("SELECT * FROM gtcObterRestricoes($personId);");
    }

    /**
     * Método que avalia se é para aplicar criptografia na senha da pessoa
     * 
     * @return void
     */
    protected function encryptPassword()
    {
        if ($this->password)
        {
            //Se tiver configurado para encriptar senha do usuário em MD5
            if (PERSON_PASSWORD_ENCRYPT == self::PERSON_PASSWORD_ENCRYPT_TYPE_MD5)
            {
                $this->password = md5($this->password);
            }
        }
    }

    /**
     * Função que verifica se a autenticação é pelo LDAP ou não e insere a pessoa no gnuteca e retorna o código dela.
     * 
     * @param string $personId
     * @param string $baseLdap
     * @param string $password
     * @return string 
     * */
    public function insertPersonLdapWhenNeeded($personId, $baseLdap, $password = null)
    {
        //caso o tipo de autenticação seja login ou login/base, obtém o id do login
        if ((MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN))
        {
            $person = $this->getPersonIdFormLoginAndBase($personId, $baseLdap); //obtém a pessoa
            
            //Workaround para fazer funcionar login do moodle.
            if ( $this->MIOLO->getConf('login.classUser') == 'gAuthMoodle' && (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
            {
                if(!$person)
                {
                    return $personId;
                }
                else
                {
                    return $person->personId;
                }
            }

            //troca o login do usuário pelo personId
            if (strlen($person->personId) > 0) //se tiver a pessoa na base, troca o login pelo personId
            {
                $personId = $person->personId;
            }

            //Se for autenticação pelo LDAP
            if (strlen($person->personId) == 0 && MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE)
            {
                //Insere a pessoa
                $this->beginTransaction();
                $personId = $this->insertLdapPerson($personId, $baseLdap, $password);
                $this->commitTransaction();
            }
        }

        return $personId;
    }

    /**
     * Função que verifica realiza a união ente duas pessoas, chamando a função gtcPersonUnion() do Postgres
     * @param bigint $stayPerson
     * @param bigint $outPerson
     * @return boolean
     * */
    public function personUnion($stayPerson, $outPerson)
    {
        if (!$stayPerson || !$outPerson)
        {
            throw new Exception(_M('É necessário definir a pessoas que permanece e a que sai', 'gnuteca3'));
        }

        return $this->query("SELECT * FROM gtcPersonUnion({$stayPerson}, {$outPerson});");
    }
    
    /**
     * Obtem informacoes de quais abas podem ou nao ser editadas.
     * 
     * @param string $tabName nome da aba da qual voce queira pontualmente o valor da permissao
     * @return char (f ou t) ou stdClass no formato $stdClass->permissaoAba = (t|f)
     */
    public function getPersonChangePermissions($tabName = null)
    {
        $busPreference = $this->MIOLO->getBusiness('gnuteca3', 'BusPreference');
        $permission = $busPreference->getPreference('gnuteca3','CHANGE_WRITE_PERSON',false);

        if ( !empty($permission) )
        {
            $tabPermission = explode("\n", $permission);

            foreach ( $tabPermission as $tabPerm )
            {
                
                $permissionKeyValue = explode('=', $tabPerm);
                $permissionKeyValue[0] = trim($permissionKeyValue[0]);
                $permissionKeyValue[1] = trim($permissionKeyValue[1]);
                
                if ( !empty($permissionKeyValue[0]) )
                {
                    if ($permissionKeyValue[1] != 't' && $permissionKeyValue[1] != 'f' )
                    {
                        throw new Exception("Valor da permissão $tabPerm na preferência CHANGE_WRITE_PERSON deve ser t ou f e não $permissionKeyValue[1]!");
                    }
                    else
                    {
                        $permissionInfo[$permissionKeyValue[0]] = $permissionKeyValue[1];
                    }
                }

            }
            
            $permissionInfo = ((object) $permissionInfo);
        }
        
        if ( !is_null($tabName) )
        {
            if ( empty($permissionInfo->$tabName) )
            {
                throw new Exception("Permissão para tab $tabName não cadastrada na preferência CHANGE_WRITE_PERSON !");
            }
            
            $permissionInfo = $permissionInfo->$tabName;
        }
        
        return $permissionInfo;
    }
}

?>
