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
 * This file handles the connection and actions for gtcExemplaryFutureStatusDefined table
 *
 * @author Moises Heberle [moises@solis.coop.br]
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
 * Class created on 21/10/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusExemplaryFutureStatusDefined extends GBusiness
{
    public $pkeys;
    public $cols;
    public $fullColumns, $localTable;

    public $exemplaryFutureStatusDefinedId;
    public $exemplaryStatusId;
    public $itemNumber;
    public $applied;
    public $date;
    public $operator;
    public $observation;
    public $cancelReserveEmailObservation;

    public $exemplaryFutureStatusDefinedIdS;
    public $exemplaryStatusIdS;
    public $itemNumberS;
    public $appliedS;
    public $dateS;
    public $beginDateS;
    public $endDateS;
    public $operatorS;
    public $libraryUnitIdS;
    public $observationS;
    public $cancelReserveEmailObservationS;

    public $busExemplaryStatusHistory;


    /**
     * Class constructor
     **/
    function __construct()
    {
    	$this->MIOLO  = MIOLO::getInstance();
    	$this->module = MIOLO::getCurrentModule();
    	$this->busExemplaryStatusHistory = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatusHistory');

        parent::__construct();
        $this->tables = $this->localTable = 'gtcExemplaryFutureStatusDefined';
        $this->pkeys  = 'exemplaryFutureStatusDefinedId';
        $this->cols   = 'exemplaryStatusId,
                         itemNumber,
                         applied,
                         date,
                         operator,
                         observation,
                         cancelReserveEmailObservation';
        $this->fullColumns = $this->pkeys . ',' . $this->cols;
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertExemplaryFutureStatusDefined()
    {
        $this->clear();
        $data = $this->associateData( $this->cols );
        $this->setColumns($this->cols);
        $this->setTables($this->localTable);

        //Se for Estado Anterior
        if ($data[0] == 'level0')
        {
            $futureStatus   = $this->busExemplaryStatusHistory->getLastStatus( $data[1]);
            $data[0] = $futureStatus;
        }
        //Se for Estado Inicial
        if ($data[0] == '0')
        {
            $futureStatus   = $this->busExemplaryStatusHistory->getLastStatus( $data[1], ID_EXEMPLARYSTATUS_INITIAL);
            $data[0] = $futureStatus;
        }

        $sql = $this->insert($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateExemplaryFutureStatusDefined()
    {
        $data = $this->associateData( $this->cols . ',' . $this->pkeys );
        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->tables);

        //Se for Estado Anterior
        if ($data[0] == 'level0')
        {
            $futureStatus   = $this->busExemplaryStatusHistory->getLastStatus( $data[1]);
            $data[0] = $futureStatus;
        }

        $this->setWhere('exemplaryFutureStatusDefinedId = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * @param $exemplaryFutureStatusDefinedId (integer): Primary key for deletion
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getExemplaryFutureStatusDefined($exemplaryFutureStatusDefinedId)
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $this->setWhere('exemplaryFutureStatusDefinedId = ?');
        $sql = $this->select( array($exemplaryFutureStatusDefinedId) );
        $rs  = $this->query($sql, true);
        $this->setData($rs[0]);
        return $this;
    }


    /**
     * Obtém o estado futuro para um itemNumber
     *
     * @param unknown_type $itemNumber
     * @return unknown
     */
    public function getStatusDefined($itemNumber, &$exemplaryFutureStatusDefinedId = null, $returnAll = FALSE)
    {
    	//se é para retornar como array ou simples
    	//isto foi feito em funçao de compatibilidade
    	$returnArray = is_array( $itemNumber );

    	//transforma itemNumber em array
    	if ( !is_array( $itemNumber ) )
    	{
    		$itemNumber = array ( $itemNumber );
    	}

    	//converte para formato sql
    	$itemNumber = implode("','", $itemNumber );

    	$this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $this->setWhere("itemNumber IN ( '{$itemNumber}' )");
        $this->setWhere('applied = ?');
        $this->setOrderBy('exemplaryFutureStatusDefinedId asc');
        $sql = $this->select( array(DB_FALSE) );

        $rs  = $this->query($sql, true);
        $exemplaryFutureStatusDefinedId = $rs[0]->exemplaryFutureStatusDefinedId;

        //se tipo de retorno não é array
        if ( $returnAll && !$returnArray )
        {
        	return $rs[0];
        }

        if ( $returnAll && $returnArray )
        {
            return $rs;
        }

        return $rs[0]->exemplaryStatusId;
    }


    /**
     * Delete a record
     *
     * @param $exemplaryFutureStatusDefinedId (integer): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteExemplaryFutureStatusDefined($exemplaryFutureStatusDefinedId)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('exemplaryFutureStatusDefinedId = ?');
        $sql = $this->delete( array($exemplaryFutureStatusDefinedId) );
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $toObject (bool): Result as array of object
     *
     * @return (array): An array containing the search results
     **/
    public function searchExemplaryFutureStatusDefined($toObject = false)
    {
        $this->clear();

        if ($this->exemplaryFutureStatusDefinedIdS)
        {
            $this->setWhere('A.exemplaryFutureStatusDefinedId = ?');
            $args[] = $this->exemplaryFutureStatusDefinedIdS;
        }
        if ($this->exemplaryStatusIdS)
        {
            $this->setWhere('A.exemplaryStatusId = ?');
            $args[] = $this->exemplaryStatusIdS;
        }
        if ($this->itemNumberS)
        {
            $this->setWhere('A.itemNumber = ?');
            $args[] = $this->itemNumberS;
        }
        if ($this->appliedS)
        {
            $this->setWhere('A.applied = ?');
            $args[] = $this->appliedS;
        }
        if ($this->dateS)
        {
            $this->setWhere('date(A.date) = ?');
            $args[] = $this->dateS;
        }
        if ($this->beginDateS)
        {
            $this->setWhere('date(A.date) >= ?');
            $args[] = $this->beginDateS;
        }
        if ($this->endDateS)
        {
            $this->setWhere('date(A.date) <= ?');
            $args[] = $this->endDateS;
        }
        if ($this->operatorS)
        {
            $this->setWhere('lower(A.operator) LIKE lower(?)');
            $args[] = '%' . $this->operatorS . '%';
        }
        if ($this->observationS)
        {
            $this->setWhere('lower(A.observation) LIKE lower(?)');
            $args[] = '%' . $this->observationS . '%';
        }
        if ($this->cancelReserveEmailObservationS)
        {
            $this->setWhere('lower(A.cancelReserveEmailObservation) LIKE lower(?)');
            $args[] = '%' . $this->cancelReserveEmailObservationS . '%';
        }
        if ($this->libraryUnitIdS)
        {
            $this->setWhere('EC.libraryUnitId in (' . $this->libraryUnitIdS . ')' );
        }
        
        $this->setOrderBy('exemplaryFutureStatusDefinedId asc');
        
        $this->setTables('gtcExemplaryFutureStatusDefined     A
                LEFT JOIN gtcExemplaryStatus                  B
                       ON (A.exemplaryStatusId = B.exemplaryStatusId)
                LEFT JOIN gtcExemplaryControl                 EC
                       ON (A.itemNumber = EC.itemNumber)');
        $this->setColumns('A.exemplaryFutureStatusDefinedId,
                           B.description,
                           A.itemNumber,
                           A.applied,
                           A.date,
                           A.operator,
                           A.observation,
                           A.cancelReserveEmailObservation');
        $sql = $this->select($args);
        $rs  = $this->query($sql, $toObject);
        return $rs;
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listExemplaryFutureStatusDefined()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }




    function clean()
    {
        $this->exemplaryFutureStatusDefinedId =
        $this->exemplaryStatusId =
        $this->itemNumber =
        $this->applied =
        $this->date =
        $this->operator =
        $this->observation =
        $this->cancelReserveEmailObservation =
        $this->exemplaryFutureStatusDefinedIdS =
        $this->exemplaryStatusIdS =
        $this->itemNumberS =
        $this->appliedS =
        $this->dateS =
        $this->operatorS =
        $this->cancelReserveEmailObservationS =
        $this->observationS = null;
    }


    /**
     * seta um agendamento como aplicado
     *
     * @param varchar $itemNumber
     * @param int $exemplaryFutureStatusDefinedId
     * @return boolean
     */
    public function setApplied($itemNumber, $exemplaryFutureStatusDefinedId = null)
    {
        $data = array(DB_TRUE, $itemNumber);
        $this->clear();
        $this->setColumns('applied');
        $this->setTables($this->tables);
        $this->setWhere('itemNumber = ?');

        if(!empty($exemplaryFutureStatusDefinedId))
        {
            $this->setWhere('exemplaryFutureStatusDefinedId = ?');
            $data[] = $exemplaryFutureStatusDefinedId;
        }

        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * retorna o ID atual
     *
     * @return int
     */
    public function getCurrentId()
    {
        parent::clear();
        //$query = parent::query("SELECT currval('seq_exemplaryfuturestatusdefinedid')");]
        $query = parent::query("SELECT last_value FROM seq_exemplaryfuturestatusdefinedid");
        if(!$query)
        {
            return 0;
        }

        $this->requestChangeExemplaryStatusId = $query[0][0];
        return $query[0][0];
    }


}
?>
