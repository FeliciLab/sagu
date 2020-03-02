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
 * This file handles the connection and actions for general reserve table
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 04/08/2008
 *
 **/


/**
 * Class to manipulate the reserve table
 **/
class BusinessGnuteca3BusReserve extends GBusiness
{
	public $isFormSearch = FALSE;
    public $reserveId;
    public $libraryUnitId;
    public $personId;
    public $requestedDate;
    public $limitDate;
    public $reserveStatusId;
    public $reserveTypeId;
    public $reserveComposition;
    public $isConfirmed;

    public $reserveIdS;
    public $libraryUnitIdS;
    public $personIdS;
    public $requestedDateS;
    public $limitDateS;
    public $beginRequestedDateSDate;
    public $beginRequestedDateS;
    public $beginRequestedDateSTime;
    public $endRequestedDateSDate;
    public $endRequestedDateS;
    public $endRequestedDateSTime;
    public $beginLimitDateS;
    public $endLimitDateS;
    public $reserveStatusIdS;
    public $reserveTypeIdS;
    public $itemNumberS;

    public $busRSH;
    public $busRC;
    public $busLibraryAssociation;
    public $busReserveStatusHistory;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->table    = 'gtcReserve';
        $this->colsNoId = 'libraryUnitId,
                           personId,
                           requestedDate,
                           limitDate,
                           reserveStatusId,
                           reserveTypeId';
        $this->colsId   = 'reserveId';
        $this->cols     = $this->colsId . ',' . $this->colsNoId;

        $this->busRSH                   = MIOLO::getInstance()->getBusiness($this->module, 'BusReserveStatusHistory');
        $this->busRC                    = MIOLO::getInstance()->getBusiness($this->module, 'BusReserveComposition');
        $this->busLibraryAssociation    = MIOLO::getInstance()->getBusiness($this->module, 'BusLibraryAssociation');
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listReserve()
    {
        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
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
    public function getReserve($id, $return = FALSE, $libraryUnitid)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns($this->cols);
        $this->setWhere($this->colsId . ' = ? ');
        $sql = $this->select(array($id));
        
        $rs  = $this->query($sql, true);
        $this->busRC->reserveId = $id;
        $this->reserveComposition = $this->busRC->getReserveComposition(TRUE); //pega detalhes extras da composição
        $rs[0]->reserveComposition = $this->reserveComposition; 
        if ( !$return)
        {
            $this->setData( $rs[0] );
            return $this;
        }
        else
        {
            return $rs[0];
        }

    }

    /**
     * FunÃ§Ã£o utilizada para listar as reservas por biblioteca, para avisar os usuÃ¡rios da sua chegada
     **/
    public function getReservesLibrary($reserveStatusId, $isConfirmed, $libraryUnitId, $returnType = 'array')
    {
        $this->clear();

        if ($reserveStatusId)
        {
        	$this->setWhere('R.reserveStatusId = ?');
            $args[] =  $reserveStatusId;
        }

        if ($isConfirmed)
        {
        	$this->setWhere('RC.isConfirmed = ? ');
            $args[] =  $isConfirmed;
        }

        if ($libraryUnitId)
        {
        	$this->setWhere('R.libraryUnitId = ?');
            $args[] =  $libraryUnitId;
        }

        $this->setTables('    gtcReserve R
                    LEFT JOIN gtcReserveComposition RC
                           ON R.reserveId = RC.reserveId');

        $this->setColumns(' R.reserveId,
                            R.libraryUnitId,
                            R.personId,
                            R.requestedDate,
                            R.limitDate,
                            R.reserveStatusId,
                            R.reserveTypeId,
                            RC.itemNumber,
                            RC.isConfirmed');

        $sql = $this->select($args);


        $rs  = $this->query($sql, ($returnType == 'object'));

        return $rs;
    }



    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchReserve($orderBy = 'R.requestedDate DESC', $object = FALSE, $getComposition = false )
    {
        $this->clear();
        
        if ( $this->reserveIdS )
        {
            $this->setWhere('R.reserveId = ?');
            $data[] = $this->reserveIdS;
        }

        if ( $this->libraryUnitIdS )
        {
            $this->setWhere('R.libraryUnitId in (' . $this->libraryUnitIdS . ')' );
        }

        if ( $this->personIdS )
        {
            $this->setWhere('R.personId = ?');
            $data[] = $this->personIdS;
        }

        if ( $this->personId )
        {
            $this->setWhere('R.personId = ?');
            $data[] = $this->personId;
        }

        if ( $this->requestedDateS )
        {
            $this->setWhere('date(R.requestedDate) = ?');
            $data[] = $this->requestedDateS;
        }

        if ( $this->limitDateS )
        {
            $this->setWhere('date(R.limitDate)= ?');
            $data[] = $this->limitDateS;
        }

        if ( $this->beginRequestedDateSDate )
        {
            $this->setWhere('date(R.requestedDate)::date >= ?');
            $data[] = $this->beginRequestedDateSDate;
        }
        
        if ( $this->beginRequestedDateSTime )
        {
            $this->setWhere('R.requestedDate::time >= ?');
            $data[] = $this->beginRequestedDateSTime;
        }

        if ( $this->endRequestedDateSTime )
        {
            $this->setWhere('R.requestedDate::time <= ?');
            $data[] = $this->endRequestedDateSTime;
        }
        
        if ( $this->endRequestedDateSDate )
        {
            $this->setWhere('date(R.requestedDate)::date <= ?');
            $data[] = $this->endRequestedDateSDate;
        }

        if ( $this->beginLimitDateS )
        {
            $this->setWhere('date(R.limitDate)>= ?');
            $data[] = $this->beginLimitDateS;
        }
        if ( $this->endLimitDateS )
        {
            $this->setWhere('date(R.limitDate)<= ?');
            $data[] = $this->endLimitDateS;
        }

        if ( $this->reserveStatusIdS )
        {
            if(!is_array($this->reserveStatusIdS))
            {
                $this->setWhere('R.reserveStatusId = ?');
                $data[] = $this->reserveStatusIdS;
            }
            elseif(is_array($this->reserveStatusIdS))
            {
                $in = implode(",", $this->reserveStatusIdS);
                $this->setWhere('R.reserveStatusId IN ('. $in .')');
            }
        }

        if ( $this->reserveTypeIdS )
        {
            $this->setWhere('R.reserveTypeId = ?');
            $data[] = $this->reserveTypeIdS;
        }

        if ( $this->itemNumberS )
        {
        	$this->setWhere('(SELECT COUNT(*) FROM gtcReserveComposition WHERE reserveId=R.reserveId AND itemNumber=?) > 0');
            $data[] = $this->itemNumberS;
        }

        $this->setTables('    gtcReserve R
                    LEFT JOIN gtcLibraryUnit LU
                           ON R.libraryUnitId = LU.libraryUnitID
               LEFT JOIN ONLY basPerson P
                           ON R.personId = P.personId
                    LEFT JOIN gtcReserveStatus RS
                           ON R.reserveStatusId = RS.reserveStatusId
                    LEFT JOIN gtcReserveType RT
                           ON R.reserveTypeId = RT.reserveTypeId');
        
        $this->setColumns(' R.reserveId,
                            P.personId,
                            P.name AS person,
                            NULL AS title,
                            NULL AS author,
                            R.requestedDate,
                            R.limitDate,
                            RS.description AS reserveStatus,
                            RT.description AS reserveType,
                            LU.libraryName AS libraryUnit,
                            RS.reserveStatusId,
                            LU.libraryUnitId');

        if(!is_null($orderBy))
        {
            parent::setOrderBy($orderBy);
        }

        $sql = $this->select($data);
        $rs  = $this->query($sql, $object);
        
        if ($object && $getComposition)
        {
        	if ( is_array( $rs ))
        	{
        		foreach ( $rs as $line => $info )
        		{
        			$this->busRC->reserveId = $info->reserveId;
                    $rs[$line]->composition = $this->busRC->getReserveComposition( true );
        		}
        	}
        }        
        
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
    public function insertReserve()
    {
        $data = new StdClass();
        $this->reserveId = $this->getNextReserveId();
        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->table);
        $sql = $this->insert( $this->associateData($this->cols) );
        $rs  = $this->execute($sql);

        $data->reserveId        = $this->reserveId;
        $data->operator         = $this->MIOLO->getLogin()->id;

        if (is_null($data->operator))
        {
            $data->operator     = 'gnuteca3';
        }

        $data->date             = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $data->reserveStatusId  = $this->reserveStatusId;
        $this->busRSH->setData($data);
        $this->busRSH->insertReserveStatusHistory();

        if ($this->reserveComposition)
        {
            foreach ($this->reserveComposition as $line => $info)
            {
            	$this->BusRC->reserveId    = '';
            	$this->BusRC->itemNumber   = '';
                $this->busRC->setData($info);
                $this->busRC->reserveId = $this->reserveId;
                $this->busRC->insertReserveComposition();
            }
        }

        return $rs;
    }


    /**
    * Return the next value to be inserted.
    * If you want a cross Database function you need treat this in other way.
    *
    */
    public function getNextReserveId()
    {
        $sql = 'select nextval(\'seq_reserveid\');';
        $rs  = $this->query($sql);
        return $rs[0][0];
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateReserve()
    {
        $this   ->clear();
        $this   ->setColumns($this->colsNoId);
        $this   ->setTables($this->table);
        $this   ->setWhere($this->colsId . ' = ?');
        $array   = $this->associateData( $this->colsNoId . ',' . $this->colsId );
        $sql    = $this->update($array);
        $rs     = $this->execute($sql);

        $data = new StdClass();
        $data->reserveId        = $this->reserveId;
        $data->operator         = $this->MIOLO->getLogin()->id;
        if (is_null($data->operator))
        {
            $data->operator     = 'gnuteca3';
        }

        $data->date             = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $data->reserveStatusId  = $this->reserveStatusId;
        $this->busRSH->setData($data);
        $this->busRSH->insertReserveStatusHistory();

        if ($this->reserveComposition)
        {
            $this->busRC->deleteReserveComposition($this->reserveId);
            foreach ($this->reserveComposition as $line => $info)
            {
                if (!($info->removeData))
                {
                    $this->busRC->setData($info);
                    $this->busRC->insertReserveComposition();
                }
            }
        }

        return  $rs;
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
    public function deleteReserve($reserveId)
    {
        $this->busRSH->deleteByReserve($reserveId);
        $this->busRC->deleteReserveComposition($reserveId);

        $this       ->clear();
        $this       ->setTables($this->table);
        $this       ->setWhere($this->colsId . ' = ?');
        $sql        = $this->delete(array($reserveId));
        $rs         = $this->execute($sql);
        return      $rs;
    }


    /**
     * Verifica se tem reserva para o exemplar dentro dos filtros que sao os parametros.
     * 
     * @param type $itemNumber
     * @param type $reserveStatusId
     * @param type $personId
     * @param type $isConfirmed
     * @return boolean
     */
    public function hasReserve($itemNumber, $reserveStatusId=null, $personId=null, $isConfirmed=null)
    {
        $this->clear();
        $this->setColumns('A.reserveId,
                           A.libraryUnitId,
                           A.personId,
                           A.limitDate,
                           A.reserveStatusId,
                           A.reserveTypeId,
                           B.itemNumber,
                           B.isConfirmed');
        $this->setTables('          gtcReserve A
                          LEFT JOIN gtcReserveComposition B
                                 ON (A.reserveId = B.reserveId)');

        $this->setWhere('B.itemNumber = ?');
        $data[] =  $itemNumber;

        if ($personId)
        {
            $this->setWhere('A.personId = ?');
            $data[] = $personId;
        }

        if ($reserveStatusId)
        {
            if (!is_array($reserveStatusId))
            {
                $reserveStatusId = array($reserveStatusId);
            }
            $this->setWhere('A.reserveStatusId IN ('.implode(',', $reserveStatusId).')');
        }
        
        if ( $isConfirmed )
        {
            $this->setWhere('B.isConfirmed = true');
        }

        $sql = $this->select($data);
        $rs = $this->query($sql);

        if ($rs)
        {
        	return true;
        }
        else
        {
        	return false;
        }
    }


    /**
     * Cancela uma determinada Reserve
     *
     * @return Boolean
     */
    function cancelReserve($reserveId)
    {
		if (!$reserveId)
		{
			return false;
		}
        return $this->changeReserveStatus($reserveId, ID_RESERVESTATUS_CANCELLED);
    }


    /**
     * Altera o status de uma determinada reserva
     * 
     * Pode também atualizar data limite, caso for passado o parametro
     *
     * @return Boolean
     */
    public function changeReserveStatus($reserveId, $reserveStatusId, $operator = null, $limitDate = null )
    {
    	$data = new StdClass();
        $data->reserveId        = $reserveId;
        $data->operator         = $operator;
        $data->date             = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $data->reserveStatusId  = $reserveStatusId;

        if (is_null($data->operator) || empty($data->operator))
        {
            $op                 = $this->MIOLO->getLogin()->id;
            $data->operator     = $op ? $op : 'gnuteca3';
        }

        $this->busRSH->setData($data);
        $this->busRSH->insertReserveStatusHistory();

        $this->clear();
        $this->setTables    ('gtcReserve');
        
        if ( !$limitDate )
        {
            $this->setColumns   ('reservestatusid');
        }
        else
        {
        	$this->setColumns   ('reservestatusid,limitDate');
        }
         
        $this->setWhere     ('reserveId = ?');
        
        $args[] = $reserveStatusId;

        if ( $limitDate )
        {
        	$args[] = $limitDate;
        }
        
        $args[] = $reserveId;

        $sql = parent::update($args);
        
        return parent::Execute();
    }


    /**
     * Return the Reserve of an association
     *
     * @param integer $libraryUnitId
     * @param integer $personId
     * @param array   $reserveStatusIs
     * @param boolean $extraInfo
     * @return array of objects
     */
    public function getReservesOfAssociation( $libraryUnitId = null , $personId = null, $reserveStatusId = array(ID_RESERVESTATUS_REQUESTED, ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REPORTED), $extraInfo = false)
    {
        $libraries = $this->busLibraryAssociation->getLibrariesAssociationOf($libraryUnitId);

        if ($libraries)
        {
            $this->clear();
            $this->setTables($this->table);
            $this->setColumns($this->cols);

            if ( $libraryUnitId )
            {
                $this->setWhere('libraryUnitId IN (' . implode(',', $libraries) . ')');
            }

            if ( $reserveStatusId )
            {
                $this->setWhere('reserveStatusId IN (' . implode(',', $reserveStatusId) . ')');
            }

            if ($personId)
            {
                $this->setWhere('personId = ?');
            }

            $sql   = $this->select(array($personId));
            $query = $this->query($sql, true);

            if ( $query && is_array($query)  && $extraInfo )
            {
            	foreach ( $query as $line => $info )
            	{
            		$this->busRC->reserveId = $info->reserveId;
            		$result = $this->busRC->getReserveComposition(true);
            		$query[$line]->reserveComposition = $result;
            	}
            }

            return $query;
        }
        else
        {
            return array();
        }
    }


    /**
     * Função privada utilizada no getReservesOfMaterial e getReservesOfExemplar
     * 
     * @param $reserveStatusId
     * @param $isConfirmed
     * @param $limit
     * @param $orderBy
     * @return unknown_type
     */
    private function _getReservesOf($reserveStatusId, $isConfirmed=null, $limit = null, $orderBy  = 'A.reserveId' )
    {
    //transforma reserveStatusId em array
        if (!is_array($reserveStatusId))
        {
            $reserveStatusId = array($reserveStatusId);
        }
        
        $this->clear();
        $this->setColumns('A.reserveId,
                           A.libraryUnitId,
                           A.personId,
                           A.requestedDate,
                           A.limitDate,
                           A.reserveStatusId,
                           A.reserveTypeId,
                           B.itemNumber,
                           B.isConfirmed,
                           C.name as personName,
                           D.description as reserveStatus,
                           E.description as reserveType,
                           F.libraryName as libraryUnitName ');

        $this->setTables('           gtcReserve A
                          INNER JOIN gtcReserveComposition B
                                  ON (A.reserveId = B.reserveId)
                     INNER JOIN ONLY basPerson C
                                  ON (A.personId = c.personId)
                          INNER JOIN gtcReserveStatus D
                                  ON ( A.reserveStatusId = d.reserveStatusId )
                          INNER JOIN gtcReserveType E
                                  ON ( A.reserveTypeId = e.reserveTypeId )
                          INNER JOIN gtcLibraryUnit F
                                  ON ( A.libraryUnitId = F.libraryUnitId)');

        $this->setWhere('A.reserveStatusId IN (' . implode(',', $reserveStatusId) . ')');
        $this->setOrderBy( $orderBy );

        if ($isConfirmed)
        {
            $this->setWhere('B.isConfirmed = true');

            $sql = new MSql(); //pra que serve isso??
            $sql->setRange(1);
        }

        if ( $limit )
        {
            $this->setLimit( $limit );
        }
    }

    public function getReservesOfMaterial( $controlNumber, $reserveStatusId, $isConfirmed=null, $limit = null, $orderBy  = 'A.reserveId', $libraryUnitId = null )
    {
        $this->_getReservesOf($reserveStatusId, $isConfirmed, $limit, $orderBy );
        
        //transforma itemNumber em array
        if ( !is_array( $controlNumber ) )
        {
            $controlNumber = array($controlNumber);
        }
        
        $controlNumber = implode(',' , $controlNumber);
        
        $controlNumberSelect = "SELECT itemNumber FROM gtcExemplaryControl WHERE controlNumber IN ($controlNumber)";

        if ( $libraryUnitId )
        {
            $controlNumberSelect .= "AND libraryUnitId = {$libraryUnitId}";
        }
        
        $this->setWhere("B.itemNumber IN ($controlNumberSelect)");
        
        $this->select( );
        
        return $this->query( null , true);
    }
    
    /**
     * Return reserve of the exemplary
     *
     *
     */
    public function getReservesOfExemplary( $itemNumber, $reserveStatusId, $isConfirmed=null, $limit = null, $orderBy  = 'A.reserveId', $personId = null )
    {
    	$this->_getReservesOf($reserveStatusId, $isConfirmed, $limit, $orderBy );
    	
        //transforma itemNumber em array
        if (!is_array($itemNumber))
        {
            $itemNumber = array($itemNumber);
        }
        
        $this->setWhere('B.itemNumber IN (\'' . implode('\',\'', $itemNumber) . '\')');
        
        if ( $personId )
        {
        	$this->setWhere( "A.personId = '$personId'");
        }
        
        $this->select( );
        
        return $this->query( null , true);
    }

    /**
     * Lista uma serie de reservas, filtrando pelo estado, confirmação ou código
     * Esta função também obtem o número de controle e o nome da pessoa com a reserva.
     *
     * @param mixed $reserveStatusId suporta integer (= ) ou array (in)
     * @param boolean $isConfirmed
     * @param integer $reserveId
     * @return array de objetos
     */
    public function getReserves($reserveStatusId, $isConfirmed = null, $reserveId = null)
    {
        $this->clear();
        $this->setTables('gtcReserve R
                LEFT JOIN gtcReserveComposition RC
                       ON RC.reserveId = R.reserveId
           LEFT JOIN ONLY basPerson b  ON ( r.personId = b.personId )
                LEFT JOIN gtcExemplaryControl e ON ( RC.itemNumber = e.itemNumber )');
        $this->setColumns(' R.reserveId,
                            R.libraryUnitId,
                            R.personId,
                            R.requestedDate,
                            R.limitDate,
                            R.reserveStatusId,
                            R.reserveTypeId,
                            RC.itemNumber,
                            RC.isConfirmed,
                            B.name ,
                            E.controlNumber
                            ');

        if ($this->libraryUnitId)
        {
        	$this->setWhere('R.libraryUnitId = ?');
        	$args[] = $this->libraryUnitId;
        }

        if (is_array($reserveStatusId) )
        {
            foreach ($reserveStatusId as $line => $info)
            {
                $string .= '?,';
                $args[] = $info;
            }

            $string = substr($string, 0, strlen($string)-1 );
            $this->setWhere('R.reserveStatusId in ('.$string.')');
        }
        elseif($reserveStatusId)
        {
            $this->setWhere('R.reserveStatusId = ?');
            $args[] = $reserveStatusId;
        }

        if ($isConfirmed)
        {
            $this->setWhere('RC.isConfirmed = ?');
            $args[] = DB_TRUE;
        }

        if($reserveId)
        {
            $this->setWhere('R.reserveId = ?');
            $args[] = $reserveId;
        }

        $sql   = $this->select($args);
        $query = $this->query($sql, true);

        return $query;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $reserveId
     * @param unknown_type $limitDate
     * @return unknown
     */
    public function updateLimitDate($reserveId, $limitDate)
    {
        $this   ->clear();
        $this   ->setColumns('limitDate');
        $this   ->setTables($this->table);
        $this   ->setWhere($this->colsId . ' = ?');
        $array   = array($limitDate, $reserveId );
        $sql    = $this->update($array);
        $rs     = $this->execute($sql);

        return  $rs;
    }


    /**
     * Get total reserves in queue by library unit
     *
     * @return unknown
     */
    public function getTotalQueueByLibrary($libraryUnitId = NULL)
    {
    	$this->clear();
    	$this->setColumns('COUNT(*)');

    	$this->setWhere('reserveStatusId = ?');
    	$args[] = ID_RESERVESTATUS_REQUESTED;

    	if ($libraryUnitId)
    	{
    		$this->setWhere('libraryUnitId = ?');
    		$args[] = $libraryUnitId;
    	}
    	$this->setTables($this->table);
    	$sql   = $this->select($args);
        $query = $this->query($sql);
        return $query[0][0];
    }


    /**
     * Get total reserves in queue by itemNumber
     *
     * @param unknown_type $itemNumber
     * @return unknown
     */
    public function getTotalQueueByItemNumber($itemNumber)
    {
        $this->clear();
        $this->setColumns('COUNT(*)');
        $this->setTables('gtcReserve            A
               LEFT JOIN  gtcReserveComposition B
                      ON  (A.reserveId = B.reserveId)');
        $this->setWhere('A.reserveStatusId = ?', ID_RESERVESTATUS_REQUESTED);
        $this->setWhere('B.itemNumber = ?', $itemNumber);
        $sql   = $this->select();
        $query = $this->query($sql);
        return $query[0][0];
    }


    /**
     * Get reserve queue position of reserveId
     *
     * @param unknown_type $reserveId
     * @return unknown
     */
    public function getQueuePosition($reserveId, $libraryUnitId)
    {
        $this->clear();
        $this->setColumns('reserveId');
        $this->setTables($this->table);
        $this->setOrderBy('requestedDate');
        $this->setWhere('reserveStatusId = ?');
        $this->setWhere('libraryUnitId = ?');
        $sql = $this->select(array(ID_RESERVESTATUS_REQUESTED, $libraryUnitId));
        $query = $this->query($sql, TRUE);
        if ($query)
        {
            foreach ($query as $i => $v)
            {
                if ($v->reserveId == $reserveId)
                {
                	return $i;
                }
            }
        }
        return NULL;
    }

    /**
     * retorna informações necessárias para ver a posição do usuário na lista de reserva. Utilizado no MyReserves, FrmSimpleSearch (aba Reserves), FrmVerifyUser e BusOperationReserve (Queue reserve)
     *
     * @param unknown_type $reserveId
     * @return unknown
     */
    public function getReservePosition( $reserveId )
    {
        $sql = "SELECT reserveposition(" . $reserveId . ")"; //Função de banco para as posições de reserva
        $rs  = $this->query($sql);
        
        return $rs;
    }



    /**
     * Altera os registro de um usuário por outro
     *
     * @param integer $currentPersonId
     * @param integer $newPersonId
     * @return boolean
     */
    public function updatePersonId($currentPersonId, $newPersonId)
    {
        $this->clear();
        $this->setColumns("personId");
        $this->setTables($this->table);
        $this->setWhere('personId = ?');
        $sql = $this->update(array($newPersonId, $currentPersonId));
        $rs  = $this->execute($sql);
        return $rs;
    }
    
    /*
     * Desatende uma reserva que esteja com estado atendida passando-a para
     * solicitada.
     * 
     * @param integer $reserveId
     * @param boolean $returnLastStatus flag que define se deve voltar para o ultimo estado inicial.
     */
    public function neglectReserve($reserveId, $returnLastInitialStatus = true)
    {
        
       $this->beginTransaction();
       
       //Obter ultimo estado inicial do material
       $busExemplaryStatusHistory = MIOLO::getInstance()->getBusiness($this->module, 'BusExemplaryStatusHistory');
       
       //Registrar a operação no histórico de estados da reserva;
       $this->busRSH->reserveId = $reserveId;
       $this->busRSH->reserveStatusId = ID_RESERVESTATUS_REQUESTED;
       $this->busRSH->date = GDate::now()->getDate();
       $this->busRSH->operator = GOperator::getOperatorId();
       $this->busRSH->insertReserveStatusHistory();
       
       //Limpar o campo data limite para vazio;
       $this->updateLimitDate($reserveId, NULL);
       //- Alterar o estado para “Solicitada”;       
       $this->changeReserveStatus($reserveId, ID_RESERVESTATUS_REQUESTED);

       $this->busRC->reserveId = $reserveId;
       $reserveCompostion = $this->busRC->getReserveComposition();
       
       $busExemplaryControl = MIOLO::getInstance()->getBusiness($this->module, 'BusExemplaryControl');
       
       foreach ($reserveCompostion as $reserve )
       {
           //Se o exemplar esta confirmado a reserva dele deve ser desconfirmada
           if ( $reserve->isConfirmed == 't' )
           {
                //Alterar o campo da composição “está confirmado” para “não”;
                $this->busRC->setData($reserve);
                $this->busRC->isConfirmed = 'f';
                $this->busRC->updateComposition();
                
                //Se for para voltar o estado do material para o ultimo estado inicial
                if ( $returnLastInitialStatus )
                {
                    //Alterar o estado do exemplar para disponível.
                    $busExemplaryControl->controlNumber = $busExemplaryControl->getControlNumber($reserve->itemNumber);
                    $lastInitialStatus = $busExemplaryStatusHistory->getLastStatus($reserve->itemNumber,1);
                    $busExemplaryControl->changeStatus($reserve->itemNumber,$lastInitialStatus,  GOperator::getOperatorId());
                }
           }
       }
       
       $this->commitTransaction();
    }

}
?>
