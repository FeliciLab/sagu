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
 *
 * This file handles the connection and actions for gtcLibraryUnit table
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/
class BusinessGnuteca3BusLibraryUnit extends GBusiness
{
    public $MIOLO;
    public $module;
    public $colsNoId;
    public $operatorAllLibraries;
    public $busLibraryUnitIsClosed;
    public $busLibraryUnitAccess;
    public $busOperatorLibraryUnit;
    public $busBond;

    public $libraryUnitIsClosed;
    public $group;

    public $libraryUnitIdS;
    public $libraryUnitIdSelect;
    public $libraryNameS;
    public $isRestrictedS;
    public $cityS;
    public $zipCodeS;
    public $locationS;
    public $numberS;
    public $complementS;
    public $emailS;
    public $urlS;
    public $privilegeGroupIdS;
    public $libraryGroupIdS;
    public $levelS;

    public $libraryUnitId;
    public $libraryName;
    public $isRestricted;
    public $city;
    public $zipCode;
    public $location;
    public $number;
    public $complement;
    public $email;
    public $url;
    public $level;
    public $privilegeGroupId;
    public $libraryGroupId;
    public $observation;
    public $acceptPurchaseRequest;
    //variables used by listLibraryUnit
    public $onlyWithAccess;
    public $filterOperator;
    public $linkId;

    public $labelAllLibrary;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->MIOLO  = MIOLO::getInstance();
        $this->tables   = 'gtcLibraryUnit';
        $this->id       = 'libraryUnitId';
        $this->colsNoId = 'libraryName,
                           isRestricted,
                           city,
                           zipCode,
                           location,
                           number,
                           complement,
                           email,
                           url,
                           privilegeGroupId,
                           libraryGroupId,
                           observation,
                           level,
                           acceptPurchaseRequest';
        $this->columns  = $this->id . ',' . $this->colsNoId;

        $this->busLibraryUnitIsClosed = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitIsClosed');
        $this->busLibraryUnitAccess   = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitAccess');
        $this->busOperatorLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusOperatorLibraryUnit');
        $this->busBond                = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->MIOLO->getClass($this->module, 'GOperator');
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listLibraryUnit($forCataloge = false, $filterOperator = TRUE )
    {
        $this->clear();

        $this->setColumns('DISTINCT
                           A.libraryUnitId,
                           A.libraryName,
                           A.isRestricted,
                           A.privilegeGroupId,
                           A.libraryGroupId,
                           A.level');

        $this->setTables('gtcLibraryUnit       A
                LEFT JOIN gtcLibraryUnitAccess B
                       ON A.libraryUnitId = B.libraryUnitId');

        if ( $this->filterOperator || $filterOperator || $operator )
        {
        	$operator = GOperator::getOperatorId();

            if ($operator)
            {
                $search = $this->busOperatorLibraryUnit->getOperatorLibraryUnit($operator);

                //Se existe operado cadastrado
                if ( $search->operatorLibrary )
                {
                    foreach ( $search->operatorLibrary as $val )
                    {
                        $libraries[] = $val->libraryUnitId;
                    }

                    //Define operator has all libraries access
                    $this->operatorAllLibraries = TRUE;

                    //se tem alguma permitida, coloca isso no where
                    if ( $libraries[0] )
                    {
                    	//USADO NA PESQUISA SIMPLES PARA SABER SE PODE OU NÃO POR O ALL LIBRARIES DE ACORDO COM A PERMISSAO
                    	$this->permissionToAllLibrary = false;
                    	$this->setWhere('A.libraryUnitId IN (' . implode(',', $libraries) . ')');
                    }
                    else
                    {
                    	//USADO NA PESQUISA SIMPLES PARA SABER SE PODE OU NÃO POR O ALL LIBRARIES DE ACORDO COM A PERMISSAO
                    	$this->permissionToAllLibrary = true;
                    }
                }
                //Quando não existe o operador cadastrado, mas retorna o gnuteca3
                else
                {
                    //Pega usuário logado
                    $this->busAuthenticate        = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
                    $personId   = $this->busAuthenticate->getUserCode();
                    //Se tiver usuário logado, que não é operador
                    if ( $personId )
                    {
                        //Pega todos os grupos(dentro da validade) que o usuário tem acesso
                        $groups      = $this->busBond->getAllPersonLink($personId);

                        foreach ( $groups as $g )
                        {
                            $group[] = $g->linkId;
                        }
                        
                        $group = ($group) ? implode(',', $group) : 'null';
                    	$this->setWhere( 'A.isRestricted = false OR B.linkId in ('.$group.')');
                    }
                    else
                    {
                        $this->setWhere('A.isRestricted = false');
                    }
                }
            }
        }
        else if ( ($this->onlyWithAccess) && !($this->operatorAllLibraries) )
        {
        	$this->setWhere( 'A.isRestricted = false');
        }

        $this->setOrderBy('A.level, A.libraryName');
        $sql = $this->select();
        $rs  = $this->query($sql, $forCataloge);

        if( ! $forCataloge || ! $rs )
        {
            if ( $this->labelAllLibrary )
            {
                if ( empty($libraries[0]) && empty($libraries[1]) ) // Se não veio nenhuma biblioteca
                {
                    $allLibraries = ''; //implode fica vazio, pois não tem nenhuma biblioteca para concatenar em $allLibraries
                }
                else
                {
                    $allLibraries = implode(',', $libraries); //concatena por implode com virgula todas as bibliotecas vindas do banco em $allLibraries.
                }

                $rs = array_merge(array(array($allLibraries, _M('Todas bibliotecas', $this->module))), $rs); //Concatena a opção Todas Bibliotecas com
            }
           
            return $rs;
        }

        foreach ( $rs as $i => $v )
        {
            $r[$i]->option      = $v->libraryUnitId;
            $r[$i]->description = $v->libraryName;
        }
        return $r;
    }


    /**
     * Método que busca todas unidades de biblioteca, usado em grids
     * 
     * @return array cujo indice é o id da biblioteca
     */
    public function listLibraryUnitAssociate()
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns('libraryunitid, libraryname');
        $sql = $this->select();
        $result = $this->query($sql);

        $data = array();
        if ( is_array($result) )
        {
            foreach($result as $i=>$val)
            {
                $data[$val[0]] = $val[1];
            }
        }

        return $data;
    }

    
    /**
     * Função usada na pesquisa e nos históricos
     *
     * @param $onlyPermittedForUser troca all libraryes de '' vazio para relação de bibliotecas possiveis
     * @return unknown_type
     */
    public function listLibraryUnitForSearch( $onlyPermittedForUser = false, $history = false )
    {
        if ( GOperator::isLogged() )
        {
            $this->operatorAllLibraries =true;
            //Liberar opção Todas bibliotecas para os Históricos da Minha biblioteca e Circulação de material
            if ($history)
            {
                $this->labelAllLibrary = MUtil::getBooleanValue( true );
            }
            else
            {
                $this->labelAllLibrary = MUtil::getBooleanValue(SIMPLE_SEARCH_ALL_LIBRARYS_OPERATOR);
            }

            //retorna tudo
        	return $this->listLibraryUnit(false, false );
        }

            //Liberar opção Todas bibliotecas para os Históricos da Minha biblioteca
        if ($history)
        {
            $this->labelAllLibrary = MUtil::getBooleanValue( true );
        }
        else
        {
            $this->labelAllLibrary = MUtil::getBooleanValue( SIMPLE_SEARCH_ALL_LIBRARYS_PERSON );
        }
        
    	//lista permitidas por usuário
    	$result =  $this->listLibraryUnit(false, true );

    	if ( ! $onlyPermittedForUser || MUtil::getBooleanValue( SIMPLE_SEARCH_ALL_LIBRARYS_PERSON ) )
    	{
    		return $result;
    	}

    	//passam por todas unidades montando relação de unidades permitidas
    	if ( is_array( $result ) )
    	{
    		foreach ( $result as $line => $info )
    		{
    			if ( $info[0] )
    			{
                    $allPermited[] = $info[0];
    			}
    		}
    	}

        //Só pesquisar em todas se a preferência estiver como Verdadeira MUtil::getBooleanValue(
        if ( MUtil::getBooleanValue(SIMPLE_SEARCH_ALL_LIBRARYS_PERSON) )
        {
    	    $result[0][0] = implode(',' , $allPermited );
        }

    	return $result;
    }
    
    /**
     * Lista todas unidades de biblioteca que aceitam sujestão de compras e que o usuário possui permissão
     * 
     * @param $personId (int) código de usuário
     * @return (array) com a lista de dados
     */
    public function listListLibraryUnitAcceptingPurchaseRequest($personId)
    {
        $bond = $this->busBond->getAllPersonLink($personId);
        
        $arrayBond = array();
        if ( is_array($bond) )
        {
            foreach( $bond as $key => $value )
            {
                $arrayBond[] = $value->linkId;
            }
        }
        
        $bond = implode(',', $arrayBond);
        if( strlen($bond) > 0 )
        {
            $this->clear();
            $this->setTables( "gtcLibraryUnit A 
                    LEFT JOIN gtclibraryunitaccess B 
                           ON (A.libraryunitid = B.libraryunitid)");
            $this->setColumns('distinct(A.libraryunitid), A.libraryname');
            $this->setWhere("A.acceptpurchaserequest = true 
                        AND (A.isrestricted = false OR (A.isrestricted = true AND B.linkid IN ({$bond})))");

            $sql = $this->select();

            $libraries = $this->query();

            if ( is_array($libraries) && ($this->labelAllLibrary) )
            {
                foreach( $libraries as $i=> $library )
                {
                    $all[] = $library[0];
                }

                 $all = implode(',', $all);
                 $libraries = array_merge(array($all => _M('Todas bibliotecas', $this->module)), $libraries );
            }

            return $libraries;
        }
        else
        {
            return null;
        }
    }


    /**
     * Return a specific record from the database
     *
     * @param $moduleConfig (integer): Primary key of the record to be retrieved
     * @param $parameter (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getLibraryUnit($libraryUnitId)
    {
        $data = array($libraryUnitId);
        $this->clear();
        $this->setColumns('libraryUnitId,
                           libraryName,
                           isRestricted,
                           privilegeGroupId,
                           libraryGroupId,
                           level');
        $this->setTables('gtcLibraryUnit');
        $this->setWhere('libraryUnitId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject=true);

        if ($rs[0])
        {
			$data= $rs[0];
        	$this->busLibraryUnitIsClosed->libraryUnitIdS = $libraryUnitId;
        	$data->libraryUnitIsClosed = $this->busLibraryUnitIsClosed->searchLibraryUnitIsClosed(TRUE);

        	$this->busLibraryUnitAccess->libraryUnitIdS = $libraryUnitId;
        	$data->group = $this->busLibraryUnitAccess->searchLibraryUnitAccess(TRUE);
        }

        $this->setData($data);
        return $data;
	}

    /**
     * Return a specific record from the database
     *
     * @param $moduleConfig (integer): Primary key of the record to be retrieved
     * @param $parameter (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getLibraryUnit1($libraryUnitId)
    {
        $data = array($libraryUnitId);
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('libraryUnitId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject=true);

        if ($rs[0])
        {
			$data= $rs[0];
        	$this->busLibraryUnitIsClosed->libraryUnitIdS = $libraryUnitId;
        	$data->libraryUnitIsClosed = $this->busLibraryUnitIsClosed->searchLibraryUnitIsClosed(TRUE);

        	$this->busLibraryUnitAccess->libraryUnitIdS = $libraryUnitId;
        	$data->group = $this->busLibraryUnitAccess->searchLibraryUnitAccess(TRUE);
        }

        $this->setData($data);
        return $data;
	}

	/**
	 * Return the name of the Library Unit
	 *
	 * @param int $libraryUnitId
	 * @return string the libraryName
	 */
    public function getLibraryName($libraryUnitId)
    {
        $this->clear();
        $this->setColumns("libraryName");
        $this->setTables('gtcLibraryUnit');
        $this->setWhere('libraryUnitId = ?');
        $sql = $this->select(array($libraryUnitId));
        $rs  = $this->query($sql, true);

        return ($rs[0]) && isset($rs[0]->libraryName) ? $rs[0]->libraryName : false;
	}


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchLibraryUnit()
    {
        $this->clear();

        if ( $v = $this->libraryUnitIdS )
        {
            $this->setWhere('libraryUnitId = ?' );
            $data[] = $this->libraryUnitIdS;
        }
        if ( $v = $this->libraryUnitIdSelect )
        {
            $this->setWhere('libraryUnitId in (' . $v . ')' );
        }
        if ( !empty($this->libraryNameS) )
        {
            $this->setWhere('lower(libraryName) LIKE lower(?)');
            $data[] = $this->libraryNameS . '%';
        }
        if ( !empty($this->isRestrictedS) )
        {
            $this->setWhere('isRestricted = ?');
            $data[] = $this->isRestrictedS;
        }
        if ( !empty($this->cityS) )
        {
            $this->setWhere('lower(city) LIKE lower(?)');
            $data[] = $this->cityS . '%';
        }
        if ( !empty($this->privilegeGroupIdS) )
        {
            $this->setWhere('privilegeGroupId = ?');
            $data[] = $this->privilegeGroupIdS;
        }
        if ( !empty($this->libraryGroupIdS) )
        {
            $this->setWhere('libraryGroupId = ?');
            $data[] = $this->libraryGroupIdS;
        }
        if ( !empty($this->observationS) )
        {
            $this->setWhere('lower(observation) LIKE lower(?)');
            $data[] = $this->observationS;
        }
        if ( !empty($this->emailS) )
        {
            $this->setWhere('lower(email) LIKE lower(?)');
            $data[] = $this->emailS. '%';
        }
        if ( !empty($this->levelS) )
        {
            $this->setWhere('level = ?');
            $data[] = $this->levelS;
        }

        $columns = 'A.libraryUnitId,
                    A.libraryName,
                    A.isRestricted,
                    A.city,
                    A.email,
                    (select C.description from gtcprivilegegroup C where C.privilegeGroupId = A.privilegeGroupId),
                    (select B.description from gtclibrarygroup B where B.libraryGroupId = A.libraryGroupId),
                    level';

        $this->setColumns($columns);
        $this->setTables('gtcLibraryUnit A');
        $this->setOrderBy('A.libraryUnitId');
        $sql = $this->select($data);
        $rs  = $this->query($sql);

        return $rs;
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertLibraryUnit()
    {
        $this->libraryUnitId = $this->getNextLibraryUnitId();

        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns($this->columns);
        $sql = $this->insert( $this->associateData($this->columns) );
        $rs  = $this->query($sql . 'RETURNING libraryUnitId');
        $this->libraryUnitId = $rs[0][0];

        if (is_array($this->libraryUnitIsClosed))
        {
            foreach ($this->libraryUnitIsClosed as $value)
            {
                $this->busLibraryUnitIsClosed->setData($value);
                $this->busLibraryUnitIsClosed->libraryUnitId = $this->libraryUnitId;
                $this->busLibraryUnitIsClosed->insertLibraryUnitIsClosed();
            }
        }

        if ($this->group)
        {
            foreach ($this->group as $value)
            {
                $this->busLibraryUnitAccess->setData($value);
                $this->busLibraryUnitAccess->libraryUnitId = $this->libraryUnitId;
                $this->busLibraryUnitAccess->insertLibraryUnitAccess();
            }
        }

        return $rs ? true : false ;
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateLibraryUnit()
    {
        $data = $this->associateData( $this->colsNoId . ', libraryUnitId' );

        $this->clear();
        $this->setWhere('libraryUnitId = ?');
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $sql = $this->update($data);
        $rs  = $this->execute($sql);

        //libraryUnitIsClosed
        if ($this->libraryUnitId)
        {
            $this->busLibraryUnitIsClosed->libraryUnitIdS = $this->libraryUnitId;
            $search = $this->busLibraryUnitIsClosed->searchLibraryUnitIsClosed(TRUE);
            if ($search)
            {
                foreach ($search as $value)
                {
                    $this->busLibraryUnitIsClosed->deleteLibraryUnitIsClosed($value->libraryUnitId, $value->weekDayId);
                }
            }
        }

        if (is_array($this->libraryUnitIsClosed))
        {
            foreach ($this->libraryUnitIsClosed as $value)
            {
                $this->busLibraryUnitIsClosed->setData($value);
                $this->busLibraryUnitIsClosed->insertLibraryUnitIsClosed();
            }
        }

        if ($this->group)
        {
            $this->busLibraryUnitAccess->deleteByLibrary($this->libraryUnitId);
            foreach ($this->group as $value)
            {
                $this->busLibraryUnitAccess->setData($value);
                $this->busLibraryUnitAccess->insertLibraryUnitAccess();
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
     **/
    public function deleteLibraryUnit($libraryUnitId)
    {
        $data = array($libraryUnitId);

        if ($libraryUnitId)
        {
            $this->busLibraryUnitIsClosed->libraryUnitIdS = $libraryUnitId;
            $search = $this->busLibraryUnitIsClosed->searchLibraryUnitIsClosed(TRUE);
            if ($search)
            {
                foreach ($search as $value)
                {
                    $this->busLibraryUnitIsClosed->deleteLibraryUnitIsClosed($value->libraryUnitId, $value->weekDayId);
                }
            }

            $this->busLibraryUnitAccess->deleteByLibrary($libraryUnitId);
        }

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('libraryUnitId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Get constants for a specified module
     *
     * @param $moduleConfig (string): Name of the module to load values from
     *
     * @return (array): An array of key pair values
     *
     **/
    public function getLibraryUnitValues($libraryUnitId)
    {
        $data = array($libraryUnitId);

        $this->clear();
        $this->setColumns('libraryUnitId');
        $this->setTables($this->tables);
        $this->setWhere('libraryUnitId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql);

        return $rs;
    }


    public function getNextLibraryUnitId()
    {
        $query = $this->query("SELECT NEXTVAL('seq_libraryUnitId')");
        return $query[0][0];
    }
}
?>