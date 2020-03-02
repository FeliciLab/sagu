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
 * This file handles the connection and actions for basConfig table
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
 * Sandro Roberto Weisheimer[sandrow@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 29/08/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusRenew extends GBusiness
{
    public $pkeys;
    public $colsNoId;

    public $renewId;
    public $loanId;
    public $renewTypeId;
    public $renewDate;
    public $returnForecastDate;
    public $operator;
    public $personName;
    public $personNameS;
    public $renewIdS;
    public $loanIdS;
    public $renewTypeIdS;
    public $renewDateS;
    public $beginRenewDateS;
    public $endRenewDateS;
    public $beginReturnForecastDateS;
    public $endReturnForecastDateS;
    public $operatorS;
    public $libraryUnitIdS;
    public $itemNumberS;
    public $personIdS;
    public $personId;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcRenew';
        $this->pkeys    = 'renewId';
        $this->colsNoId = 'loanId,
                           renewTypeId,
                           renewDate,
                           returnForecastDate,
                           operator';
        $this->columns  = $this->pkeys . ',' . $this->colsNoId;
    }


    /**
     * List all records from the table handled by the class
     *
     * @param None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listRenew()
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
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
    public function getRenew($renewId)
    {
        $data[] = $renewId;

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('renewId = ?');
        $sql = $this->select($data);
        
        $rs  = $this->query($sql, TRUE);
        $this->setData($rs[0]);
        
        return $this;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchRenew($toObject = false)
    {
        $this->clear();

        if ($v = $this->renewIdS)
        {
            $this->setWhere('A.renewId = ?');
            $data[] = $v;
        }
        if ( $this->personIdS )
        {
            $this->setWhere('P.personId = ?');
            $data[] = $this->personIdS;
        }
        if ($v = $this->loanIdS)
        {
            $this->setWhere('A.loanId = ?');
            $data[] = $v;
        }
        if ($v = $this->renewTypeIdS)
        {
            $this->setWhere('A.renewTypeId = ?');
            $data[] = $v;
        }
        if ($v = $this->renewDateS)
        {
            $this->setWhere('date(A.renewDate) = ?');
            $data[] = $v;
        }
        if ($v = $this->beginRenewDateS)
        {
            $this->setWhere('date(A.renewDate) >= ?');
            $data[] = $v;
        }
        if ($v = $this->endRenewDateS)
        {
            $this->setWhere('date(A.renewDate) <= ?');
            $data[] = $v;
        }
        if ($v = $this->beginReturnForecastDateS)
        {
            $this->setWhere('A.returnForecastDate >= ?');
            $data[] = $v;
        }
        if ($v = $this->endReturnForecastDateS)
        {
            $this->setWhere('A.returnForecastDate <= ?');
            $data[] = $v;
        }
        if ($v = $this->operatorS)
        {
            $this->setWhere('lower(A.operator) LIKE lower(?)');
            $data[] = '%' . $v . '%';
        }
        if ($this->libraryUnitIdS)
        {
            $this->setWhere('L.libraryUnitId in (' . $this->libraryUnitIdS . ')' );
        }
        if ($this->itemNumberS)
        {
        	$this->setWhere('L.itemNumber = ?');
        	$data[] = $this->itemNumberS;
        }

        $tables = '     gtcRenew        A
             LEFT JOIN  gtcRenewType    B
                    ON  (A.renewTypeId = B.renewTypeId)
             LEFT JOIN  gtcLoan         L
                    ON  (A.loanId = L.loanId)
        LEFT JOIN ONLY  basPerson       P
                    ON  (P.personId = L.personId)';
        $columns = 'A.renewId,
                    A.loanId,
                    P.personId,
                    P.name AS personName,
                    L.itemNumber,
                    NULL AS tomo,
                    NULL AS data,
                    B.description AS renewType,
                    A.renewDate,
                    A.returnForecastDate,
                    A.operator';

        $this->setTables($tables);
        $this->setColumns($columns);
        $this->setOrderBy('renewId');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject);
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
    public function insertRenew()
    {
        $data = $this->associateData( $this->colsNoId );

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $sql = $this->insert($data) . ' RETURNING renewId';
        $rs  = $this->query($sql);
        
        $this->renewId = $rs[0][0];
        $this->renewIdS = $rs[0][0];

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
    public function updateRenew()
    {
        $data = $this->associateData( $this->colsNoId . ',' . $this->pkeys );

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('renewId = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);
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
    public function deleteRenew($renewId)
    {
        $data[] = $renewId;

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('renewId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }

    //Retorna quantidade de renovações de um empréstimo
    public function getCountRenew($loanId)
    {
    	if (!$loanId)
    	{
    		return false;
    	}
        $sql = "select count (loanid) from gtcrenew where loanid = '{$loanId}'";
        $rs  = $this->query($sql);
        return $rs[0][0];
    }
    
    /**
     * Obtém as renovações no empréstimo, com nova data futura de devolução
     * 
     * @param (integer) $loanId
     * @param (integer) $renewId
     * @return (object) 
     */
    public function getRenewsOfLoan($loanId, $renewId=null)
    {
        $this->clear();
        $this->setColumns("A.renewid, 
                           A.loanid, 
                           A.renewtypeid, 
                           C.description as renewType, 
                           A.returnforecastdate, 
                           A.renewdate,
                           (CASE WHEN (SELECT MIN(returnforecastdate) FROM gtcrenew I 
                                          WHERE A.loanid = I.loanid AND I.renewid > A.renewid) IS NOT NULL
                                 THEN
                                     (SELECT MIN(returnforecastdate) FROM gtcrenew I 
                                          WHERE A.loanid = I.loanid AND I.renewid > A.renewid)
                                 ELSE              
                                     (SELECT returnforecastdate 
                                          FROM gtcloan L WHERE L.loanid = A.loanid) 
                           END) as newreturnforecastdate, 
                           A.operator");
        
        $tables = "gtcrenew A 
                    INNER JOIN gtcloan B 
                        USING (loanid)
                    INNER JOIN gtcrenewtype C
                        USING(renewtypeid)
                        where loanid = {$loanId}";
        if ( $renewId )
        {
            $tables .= " AND A.renewId = {$renewId}";
        }
        
        $this->setTables($tables);
        $this->setOrderBy('A.renewId');

        $sql = $this->select();

        return  $this->query($sql, true);
    }
    
    /**
     * Obtém histórico de renovações por empréstimo
     * @param (integer) $loanId
     * @return (array) 
     */
    public function getHistoryOfLoan($loanId, $showOperator = true)
    {
        $renews = $this->getRenewsOfLoan($loanId);
        
        $newData = array();
        if ( is_array($renews) )
        {
            foreach( $renews as $i=>$value )
            {
                $returnForecastDate = new GDate($value->returnforecastdate);
                $newReturnForecastDate = new GDate($value->newreturnforecastdate);
                $newData[$i][] = $value->renewType;
                $newData[$i][] = $returnForecastDate->getDate(GDate::MASK_DATE_USER);
                $newData[$i][] = $value->renewdate;
                $newData[$i][] = $newReturnForecastDate->getDate(GDate::MASK_DATE_USER);
                
                if ( $showOperator )
                {
                    $newData[$i][] = $value->operator;
                }
            }
        }
        
        return $newData;
    }
    
}
?>
