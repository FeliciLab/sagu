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
 * @author Luiz G Gregory Filho [luiz@solis.coop.br]
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
 * Class created on 13/08/2008
 *
 **/
class BusinessGnuteca3BusExemplaryStatus extends GBusiness
{
    public  $MIOLO;
    public  $module;
    public  $_table;
    public  $_cols;
    public  $_colsId;

    public  $exemplaryStatusId,
            $description,
            $mask,
            $level,
            $executeLoan,
            $momentaryLoan,
            $daysOfMomentaryLoan,
            $executeReserve,
            $executeReserveInInitialLevel,
            $meetReserve,
            $isReserveStatus,
            $isLowStatus,
            $observation,
            $scheduleChangeStatusForRequest;

    public  $exemplaryStatusIdS,
            $descriptionS,
            $maskS,
            $levelS,
            $executeLoanS,
            $momentaryLoanS,
            $daysOfMomentaryLoanS,
            $executeReserveS,
            $executeReserveInInitialLevelS,
            $meetReserveS,
            $isReserveStatusS,
            $isLowStatusS,
            $observationS,
            $scheduleChangeStatusForRequestS;

    function __construct()
    {
        parent::__construct();

        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();

        $this->_table   = 'gtcExemplaryStatus';
        $this->_cols    = 'description,
                           mask,
                           level,
                           executeLoan,
                           momentaryLoan,
                           daysOfMomentaryLoan,
                           executeReserve,
                           executeReserveInInitialLevel,
                           meetReserve,
                           isReserveStatus,
                           isLowStatus,
                           observation,
                           scheduleChangeStatusForRequest';
        $this->id = 'exemplaryStatusId';
        $this->_colsId  = 'exemplaryStatusId,' . $this->_cols;

        $this->setData(null);
        $this->setColumns();
        $this->setTables();
    }

    /**
     * Seta as tabelas
     *
     * @param (String || Array) $tables
     */
    public function setTables($tables = null)
    {
        if(is_null($table))
        {
            $table = "gtcExemplaryStatus";
        }

        parent::setTables($table);
    }

    /**
     * Este método seta as colunas da tabela.
     *
     * @param (String || Array) $columns
     */
    public function setColumns($type = null)
    {
        switch($type)
        {
            case "list":
                $columns = array
                (
                    'exemplaryStatusId',
                    'description',
                    'mask',
                );
                break;

            case "update":
            case "insert" :
                $columns = array
                (
                    "description",
                    "mask",
                    "level",
                    "executeLoan",
                    "momentaryLoan",
                    "daysOfMomentaryLoan",
                    "executeReserve",
                    "executeReserveinInitialLevel",
                    "meetReserve",
                    "isReserveStatus",
                    "isLowStatus",
                    "observation",
                    "scheduleChangeStatusForRequest",
                );
                break;

            case "search":
            case "All":
            default:
                $columns = array
                (
                    "exemplaryStatusId",
                    "description",
                    "mask",
                    "level",
                    "executeLoan",
                    "momentaryLoan",
                    "daysOfMomentaryLoan",
                    "executeReserve",
                    "executeReserveInInitialLevel",
                    "meetReserve",
                    "isReserveStatus",
                    "isLowStatus",
                    "observation",
                    "scheduleChangeStatusForRequest",
                );
        }

        parent::setColumns($columns);
    }

    /**
     * Seta as condições do sql
     *
     * @return void
     */
    public function getWhereCondition()
    {
        $where = "";

        if ( ! empty( $this->exemplaryStatusIdS ) )
        {
            $where.= " exemplaryStatusId = ? AND ";
        }
        if( ! empty( $this->exemplaryStatusId ) )
        {
            $where.= " exemplaryStatusId = ? AND ";
        }
        if(!empty($this->descriptionS))
        {
            $where.= " lower(description) LIKE lower(?) AND ";
        }
        if(!empty($this->maskS))
        {
            $where.= " lower(mask) LIKE lower(?) AND ";
        }
        if(!empty($this->levelS) || !empty($this->level))
        {
            $where.= " level = ? AND ";
        }
        if(!empty($this->executeLoanS) || !empty($this->executeLoan))
        {
            $where.= " executeLoan = ? AND ";
        }
        if(!empty($this->momentaryLoanS) || !empty($this->momentaryLoan))
        {
            $where.= " momentaryLoan = ? AND ";
        }
        if(!empty($this->daysOfMomentaryLoanS) || !empty($this->daysOfMomentaryLoan))
        {
            $where.= " daysOfMomentaryLoan = ? AND ";
        }
        if(!empty($this->executeReserveS) || !empty($this->executeReserve))
        {
            $where.= " executeReserve = ? AND ";
        }
        if(!empty($this->executeReserveInInitialLevelS) || !empty($this->executeReserveInInitialLevel))
        {
            $where.= " executeReserveInInitialLevel = ? AND ";
        }
        if(!empty($this->meetReserveS) || !empty($this->meetReserve))
        {
            $where.= " meetReserve = ? AND ";
        }
        if(!empty($this->isReserveStatusS) || !empty($this->isReserveStatus))
        {
            $where.= " isReserveStatus = ? AND ";
        }
        if(!empty($this->isLowStatusS) || !empty($this->isLowStatus))
        {
            $where.= " isLowStatus = ? AND ";
        }
        if(!empty($this->scheduleChangeStatusForRequest) || !empty($this->scheduleChangeStatusForRequestS))
        {
            $where.= " scheduleChangeStatusForRequest = ? AND ";
        }

        if(strlen($where))
        {
            $where = substr($where, 0, strlen($where) - 4);
            parent::setWhere($where);
        }
    }

    /**
     * Trabalha o Data Object retornado do form
     *
     * transforma em um array para enviar para o where condition do sql
     *
     * @return (Array) $args
     */
    private function getDataConditionArray()
    {
        $args = array();

        if(!empty($this->exemplaryStatusIdS))
        {
            $args[] = $this->exemplaryStatusIdS;
        }
        if(!empty($this->exemplaryStatusId))
        {
            $args[] = $this->exemplaryStatusId;
        }
        if(!empty($this->descriptionS))
        {
            $this->descriptionS = trim($this->descriptionS);
            $this->descriptionS = str_replace(" ", "%", $this->descriptionS);
            $args[] = "{$this->descriptionS}%";
        }
        if(!empty($this->maskS))
        {
            $this->maskS = trim($this->maskS);
            $this->maskS = str_replace(" ", "%", $this->maskS);
            $args[] = "%{$this->maskS}%";
        }
        if(!empty($this->levelS))
        {
            $args[] = $this->levelS;
        }
        if(!empty($this->executeLoanS))
        {
            $args[] = $this->executeLoanS;
        }
        if(!empty($this->momentaryLoanS))
        {
            $args[] = $this->momentaryLoanS;
        }
        if(!empty($this->daysOfMomentaryLoanS))
        {
            $args[] = $this->daysOfMomentaryLoanS;
        }
        if(!empty($this->executeReserveS))
        {
            $args[] = $this->executeReserveS;
        }
        if(!empty($this->executeReserveInInitialLevelS))
        {
            $args[] = $this->executeReserveInInitialLevelS;
        }
        if(!empty($this->meetReserveS))
        {
            $args[] = $this->meetReserveS;
        }
        if(!empty($this->isReserveStatusS))
        {
            $args[] = $this->isReserveStatusS;
        }
        if(!empty($this->isLowStatusS))
        {
            $args[] = $this->isLowStatusS;
        }
        if(!empty($this->scheduleChangeStatusForRequestS))
        {
            $args[] = $this->scheduleChangeStatusForRequestS;
        }

        return $args;
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @return (array): An array containing the search results
     */
    public function searchExemplaryStatus( $asObject = false )
    {
        $this->clear();
        $this->getWhereCondition();
        $this->setColumns("search");
        $this->setTables();
        $this->setOrderBy("exemplaryStatusId");
        $sql = $this->select($this->getDataConditionArray());
        return $this->query(null, $asObject );
    }

    /**
     * Mount a list to use in MSelection
     *
     * @param $parseDescription verifica se tem mascara e coloca ela, caso contrário mostra o campo descrição
     *
     * @return (array): An array containing the search results
     */
    public function listExemplaryStatus( $object = false, $checkAccess = false, $parseDescription = false , $initialLevel = false, $idIn = false, $inOperator = "IN")
    {
        $this->clear();
        $this->setTables();

        $columns    = 'exemplaryStatusId as option, description';
        $order      = 'exemplaryStatusId';

        if($parseDescription)
        {
            $order      = 'CASE WHEN (char_length(mask) > 0) THEN mask ELSE description END';
            $columns    = 'exemplaryStatusId, CASE WHEN (char_length(mask) > 0) THEN mask ELSE description END as content';
        }

        parent::setColumns($columns);
        parent::setOrderBy($order);

        $where = '';

        $in = '';
        if($idIn && is_array($idIn))
        {
            $in = trim(implode(",", $idIn));
        }
        elseif($idIn && is_string($idIn))
        {
            $in = $idIn;
        }
        if(strlen($in))
        {
            $where.= " exemplaryStatusId $inOperator ($in) AND ";
        }

        if(strlen($where))
        {
            $where = substr($where, 0, strlen($where)-4);
            parent::setWhere($where);
        }

        if ($checkAccess)
        {
            $isLowStatus = array(DB_FALSE);
            $level = array(2);
            
        	if (GPerms::checkAccess('gtcMaterialMovementChangeStatusLow', null, false))
        	{
        		$isLowStatus[] = DB_TRUE;
        	}
            
           	if (GPerms::checkAccess('gtcMaterialMovementChangeStatusInitial', null, false))
        	{
        		$level[] = 1 ;
        	}
            
        	$level = implode ("','", $level);
        	$isLowStatus = implode ("','", $isLowStatus);
        	$where = " isLowStatus in ('$isLowStatus') and level in ('$level')";
            parent::setWhere($where);
        }

        $sql   = $this->select();
        $query = $this->query($sql, $object);

        return $query;
    }

    /**
     * Insert a new record
     *
     * @return True if succed, otherwise False
     */
    public function insertExemplaryStatus()
    {
        parent::clear();
        $this->setTables();
        $this->setColumns("insert");
        $data = array
        (
            $this->description,
            $this->mask,
            $this->level,
            $this->executeLoan,
            $this->momentaryLoan,
            $this->daysOfMomentaryLoan,
            $this->executeReserve,
            $this->executeReserveInInitialLevel,
            $this->meetReserve,
            $this->isReserveStatus,
            $this->isLowStatus,
            $this->observation,
            $this->scheduleChangeStatusForRequest
        );
        
        $sql = $this->insert($data);
        $sql .= ' returning exemplaryStatusId ;';
        $result = $this->query( $sql );
        
        $this->exemplaryStatusId = $result[0][0];
        
        return $result ? true : false;
    }

    /**
     * Atualiza um determinado registro
     *
     * @return True if succed, otherwise False
     */
    public function updateExemplaryStatus()
    {
        $data = array
        (
            $this->description,
            $this->mask,
            $this->level,
            $this->executeLoan,
            $this->momentaryLoan,
            $this->daysOfMomentaryLoan,
            $this->executeReserve,
            $this->executeReserveInInitialLevel,
            $this->meetReserve,
            $this->isReserveStatus,
            $this->isLowStatus,
            $this->observation,
            $this->scheduleChangeStatusForRequest,
            ($this->exemplaryStatusId == 0) ? '0' : $this->exemplaryStatusId
        );
        $msql = new MSQL($this->_cols, $this->_table, 'exemplaryStatusId = ?');
        $sql  = $msql->update($data);
        return $this->execute($sql);
    }

    /**
     * retorna um determinado registro
     *
     * @param (int) $exemplaryStatusId - Id do registro
     * @return (Array)
     */
    public function getExemplaryStatus( $exemplaryStatusId )
    {
        $msql   = new MSQL($this->_colsId, $this->_table, 'exemplaryStatusId = ?');
        $sql    = $msql->select(array($exemplaryStatusId));
        $result = $this->query($sql, true);

  		$this->setData($result[0]);

    	return $result[0];
    }

    /**
     * Delete a record
     *
     * @param $exemplaryStatusId (int): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     */
    public function deleteExemplaryStatus($exemplaryStatusId)
    {
        $this->clear();
        $this->setTables($this->_table);
        $this->setWhere('exemplaryStatusId = ?');
        $sql= $this->delete(array($exemplaryStatusId));
        $rs = $this->execute($sql);
        return $rs;

    }

    /**
     * verifica se um determinado estado de exemplary necessita agendamento para requisições de troca de estado
     *
     * @param int $exemplaryStatusId
     * @return boolean
     */
    public function checkScheduleChangeStatusForRequest($exemplaryStatusId)
    {
        if(!$exemplaryStatusId)
        {
            return false;
        }

        parent::clear();
        parent::setColumns("scheduleChangeStatusForRequest");
        $this->setTables();
        parent::setWhere("exemplaryStatusId = ?");
        $sql = parent::select(array($exemplaryStatusId));
        $result = parent::query();
        return (isset($result[0][0]) && $result[0][0] == 't');
    }

    public function getStatusLevel($exemplaryStatusId)
    {
        if(!$exemplaryStatusId)
        {
            return false;
        }

        parent::clear();
        parent::setColumns("level");
        $this->setTables();
        parent::setWhere("exemplaryStatusId = ?");
        $sql = parent::select(array($exemplaryStatusId));
        $result = parent::query();
        return $result[0][0];
    }

    public function getDescription($exemplaryStatusId)
    {
        if( strlen($exemplaryStatusId) == false )
        {
            return false;
        }

        parent::clear();
        parent::setColumns("description");
        $this->setTables();
        parent::setWhere("exemplaryStatusId = ?");
        $sql = parent::select(array($exemplaryStatusId));
        $result = parent::query();
        return $result[0][0];
    }
}
?>