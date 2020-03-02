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
 * This file handles the connection and actions for Rules for Material Movement Table
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
 *
 * @since
 * Class created ON 04/08/2008
 *
 *
 **/
class BusinessGnuteca3BusRulesForMaterialMovement extends GBusiness
{
    public $currentState;
    public $operationId;
    public $locationForMaterialMovementId;
    public $futureState;

    public $currentStateS;
    public $operationIdS;
    public $locationForMaterialMovementIdS;
    public $futureStateS;

    public $busExemplaryStatusHistory;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->table    = 'gtcRulesForMaterialMovement';
        $this->colsNoId = 'futureState';
        $this->id   = 'currentState, operationId, locationForMaterialMovementId';
        $this->cols     = $this->id . ',' . $this->colsNoId;
        $this->busExemplaryStatusHistory = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatusHistory');
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
    public function getRulesForMaterialMovement($currentState, $operationId, $locationForMaterialMovementId, $return = FALSE)
    {
        $this   ->clear();
        $this   ->setTables($this->table);
        $this   ->setColumns($this->cols);
        $this   ->setColsIdWhere();
        $sql    = $this->select(array($currentState, $operationId, $locationForMaterialMovementId));
        $rs     = $this->query($sql, true);
        
        // caso não encontre a regra , tenta pegar do local genérico (todos lugares) = location = 0 
        if ( !$rs )
        {
        	$sql    = $this->select(array($currentState, $operationId, 0 ) );
        	$rs     = $this->query($sql, true);
        }
        
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
     * Do a search ON the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchRulesForMaterialMovement()
    {
        $this->clear();
        if ( $this->currentStateS )
        {
            $this->setWhere('RMM.currentState = ?');
            $data[] = $this->currentStateS;
        }
        if ( $this->operationIdS)
        {
            $this->setWhere('RMM.operationId= ?');
            $data[] = $this->operationIdS;
        }
        if ( $this->locationForMaterialMovementIdS || $this->locationForMaterialMovementIdS === 0 )
        {
            $this->setWhere('RMM.locationForMaterialMovementId = ?');
            $data[] = $this->locationForMaterialMovementIdS;
        }
        if ( $this->futureStateS != "" )
        {
            $this->setWhere('RMM.futureState= ?');
            $data[] = $this->futureStateS;
        }
        $this->setTables('gtcRulesForMaterialMovement RMM
                LEFT JOIN gtcExemplaryStatus ES
                       ON RMM.currentState = ES.exemplaryStatusId
                LEFT JOIN gtcExemplaryStatus ES2
                       ON RMM.futureState = ES2.exemplaryStatusId
                LEFT JOIN gtcOperation OP
                       ON RMM.operationId = OP.operationId
                LEFT JOIN gtcLocationForMaterialMovement LMM
                       ON RMM.locationForMaterialMovementId = LMM.locationForMaterialMovementId');
        $this->setColumns(' RMM.currentState,
                            ES.description,
                            RMM.operationId,
                            OP.description,
                            RMM.locationForMaterialMovementId,
                            LMM.description,
                            RMM.futureState,
                            ES2.description');
        $this->setOrderBy(' RMM.currentState,
                            RMM.operationId,
                            RMM.locationForMaterialMovementId
                            ');
        $this->select($data);
        $rs  = $this->query();
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
    public function insertRulesForMaterialMovement()
    {
        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->table);
        if (!$this->futureState)
        {
            $this->futureState = 0;
        }
        $sql = $this->insert( $this->associateData($this->cols) );
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
    public function updateRulesForMaterialMovement()
    {
        $this   ->clear();
        $this   ->setColumns($this->colsNoId);
        $this   ->setTables($this->table);
        $this   ->setColsIdWhere();
        $data   = $this->associateData( $this->colsNoId . ',' . $this->id );
        if (!$this->futureState)
        {
            $this->futureState = 0;
        }
        $sql    = $this->update($data);
        $rs     = $this->execute($sql);
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
    public function deleteRulesForMaterialMovement($currentState, $operationId, $locationForMaterialMovementId)
    {
        $this       ->clear();
        $this       ->setTables($this->table);
        $this       ->setColsIdWhere();
        $sql        = $this->delete(array($currentState, $operationId, $locationForMaterialMovementId));
        $rs         = $this->execute($sql);
        return      $rs;
    }


    /**
    * Make a few setWhere calls using this->colsId values
    *
    */
    public function setColsIdWhere()
    {
        $colsIdArray = explode(',', $this->id);
        foreach ($colsIdArray as $line => $info)
        {
            $this->setWhere($info . ' = ?');
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
    public function getFutureStatus($itemNumber, $currentState, $operationId, $locationForMaterialMovementId)
    {
    	$rs = $this->getRulesForMaterialMovement($currentState, $operationId, $locationForMaterialMovementId, true);

    	//Se o estado tiver definido como estado inicial
    	if ( is_numeric($rs->futureState) && $rs->futureState == 0 )
    	{
    		$level = 1; //Estado inicial. 2 é transição
    		$futureStatus = $this->busExemplaryStatusHistory->getLastStatus($itemNumber, $level);
	   		return $futureStatus;
    	}
    	else
    	{
    		return $rs->futureState;
    	}
    }
}
?>
