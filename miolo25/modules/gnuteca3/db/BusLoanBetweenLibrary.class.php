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
 *
 * @since
 * Class created on 16/12/2008
 *
 **/
class BusinessGnuteca3BusLoanBetweenLibrary extends GBusiness
{
	public $busLibraryUnit;
    public $busLoanBetweenLibraryComposition;
    public $busLoanBetweenLibraryStatusHistory;
    public $libraryComposition;

    public $loanBetweenLibraryId;
    public $loanDate;
    public $returnForecastDate;
    public $returnDate;
    public $limitDate;
    public $libraryUnitId;
    public $personId;
    public $loanBetweenLibraryStatusId;
    public $observation;

    public $loanBetweenLibraryIdS;
    public $loanDateS;
    public $returnForecastDateS;
    public $beginLoanDateS;
    public $endLoanDateS;
    public $beginReturnForecastDateS;
    public $endReturnForecastDateS;
    public $limitDateS;
    public $beginLimitDateS;
    public $endLimitDateS;
    public $returnDateS;
    public $beginReturnDateS;
    public $endReturnDateS;
    public $libraryUnitIdS;
    public $personIdS;
    public $loanBetweenLibraryStatusIdS;
    public $observationS;
    public $itemNumberS;

    public function __construct()
    {
        $table = 'gtcLoanBetweenLibrary';
        $pkeys = 'loanBetweenLibraryId';
        $cols  = 'loanDate,
                  returnForecastDate,
                  returnDate,
                  limitDate,
                  libraryUnitId,
                  personId,
                  loanBetweenLibraryStatusId,
                  observation';

        parent::__construct($table, $pkeys, $cols);

        $this->busLibraryUnit                     = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busLoanBetweenLibraryComposition   = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibraryComposition');
        $this->busLoanBetweenLibraryStatusHistory = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibraryStatusHistory');
    }

    public function getCurrentStatus($loanBetweenLibraryId)
    {
        $data = array($loanBetweenLibraryId);

        $this->clear();
        $this->setColumns("loanBetweenLibraryStatusId");
        $this->setTables($this->tables);
        $this->setWhere('loanBetweenLibraryId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql);

        return $rs[0][0];
    }

    public function insertLoanBetweenLibrary()
    {
    	$this->loanBetweenLibraryId = $this->db->getNewId('seq_loanbetweenlibraryid');
        
        if ($this->autoInsert())
        {
        	if (count($this->libraryComposition))
        	{
        		foreach ($this->libraryComposition as $ex)
        		{
        			$ex->loanBetweenLibraryId = $this->loanBetweenLibraryId;
		            $ex->isConfirmed          = DB_FALSE;
		            $this->busLoanBetweenLibraryComposition->setData($ex);
		            $this->busLoanBetweenLibraryComposition->insertLoanBetweenLibraryComposition();
        		}
        	}
            $this->insertLoanBetweenLibraryStatusHistory();
        	return $this->loanBetweenLibraryId;
        }
        else
        {
        	return false;
        }
    }

    public function getLoanBetweenLibrary($loanBetweenLibraryId)
    {
        $this->clear();
        $data = $this->autoGet($loanBetweenLibraryId);

        if ($loanBetweenLibraryId)
        {
            $this->busLoanBetweenLibraryComposition->loanBetweenLibraryIdS = $loanBetweenLibraryId;
            $data->busLoanBetweenLibraryComposition->loanBetweenLibraryIdS = $loanBetweenLibraryId;
            $this->libraryComposition = $this->busLoanBetweenLibraryComposition->searchLoanBetweenLibraryComposition(TRUE);
            $data->libraryComposition = $this->libraryComposition;
        }

        return $data;
    }

    /**
     * retorna o código da biblioteca
     */
    public function getLibraryId($loanBetweenLibraryId)
    {
        $data = array($loanBetweenLibraryId);

        $this->clear();
        $this->setColumns("libraryUnitId");
        $this->setTables($this->tables);
        $this->setWhere('loanBetweenLibraryId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql);

        return $rs[0][0];
    }

    public function updateLoanBetweenLibrary()
    {
        $this->busLoanBetweenLibraryComposition->deleteLoanBetweenLibraryComposition($this->loanBetweenLibraryId);
        foreach ($this->libraryComposition as $line => $info)
        {
            if (!($info->removeData))
            {
                $this->busLoanBetweenLibraryComposition->setData($info);
                $this->busLoanBetweenLibraryComposition->insertLoanBetweenLibraryComposition();
            }
        }
        $this->insertLoanBetweenLibraryStatusHistory();
        
        return $this->autoUpdate();
    }

    public function deleteLoanBetweenLibrary($loanBetweenLibraryId)
    {
    	$this->busLoanBetweenLibraryComposition->deleteLoanBetweenLibraryComposition($loanBetweenLibraryId);
        return $this->autoDelete($loanBetweenLibraryId);
    }

    public function searchLoanBetweenLibrary($toObject = false)
    {
    	//Get all library units with current operator has access, and put id's to array
        $list = $this->busLibraryUnit->listLibraryUnit(NULL, TRUE);
        $libraries = array();

        if ($list)
        {
        	foreach ($list as $lib)
        	{
        		$libraries[] = $lib[0];
        	}
        }
        
        $libraries = ($libraries) ? implode(',', $libraries) : 'null';

        $this->clear();
        $this->setTables('gtcLoanBetweenLibrary            A
                LEFT JOIN gtcLibraryUnit                   B
                       ON (A.libraryUnitId = B.libraryUnitId)
                LEFT JOIN gtcLoanBetweenLibraryStatus      C
                       ON (A.loanBetweenLibraryStatusId = C.loanBetweenLibraryStatusId)
                LEFT JOIN gtcLoanBetweenLibraryComposition D
                       ON (A.loanBetweenLibraryId = D.loanBetweenLibraryId)
                LEFT JOIN gtcExemplaryControl              E
                       ON (D.itemNumber = E.itemNumber)');
        
        $columns = "A.loanBetweenLibraryId,
                           A.loanDate,
                           A.returnForecastDate,
                           A.returnDate,
                           A.limitDate,
                           B.libraryUnitId AS libraryUnitId,
                           B.libraryName AS libraryName,
                           A.personId,
                           C.loanBetweenLibraryStatusId,
                           C.description AS status,
                           '' AS exemplaryes,";

        if ( $this->libraryUnitIdS )
        {
            $columns .= "  CASE WHEN ((A.libraryUnitId = '{$this->libraryUnitIdS}' ) AND (A.loanBetweenLibraryStatusId = '".ID_LOANBETWEENLIBRARYSTATUS_REQUESTED."')) THEN TRUE ELSE FALSE END AS showCancel,
                           CASE WHEN ((A.loanBetweenLibraryStatusId = '".ID_LOANBETWEENLIBRARYSTATUS_REQUESTED."')
                                 AND ((SELECT COUNT(*) FROM gtcExemplaryControl WHERE libraryUnitId = '{$this->libraryUnitIdS}' AND itemNumber IN ( SELECT itemNumber FROM gtcLoanbetweenLibraryComposition WHERE loanBetweenLibraryId = A.loanBetweenLibraryId)) > 0))
                                THEN TRUE ELSE FALSE END AS showAcceptDisaccept,
                           CASE WHEN ((A.libraryUnitId = ({$this->libraryUnitIdS}) AND E.originalLibraryUnitId NOT IN ({$this->libraryUnitIdS})) AND (A.loanBetweenLibraryStatusId = '".ID_LOANBETWEENLIBRARYSTATUS_APPROVED."')) THEN TRUE ELSE FALSE END AS showConfirmReceipt,
                           CASE WHEN ((A.loanBetweenLibraryStatusId = '".ID_LOANBETWEENLIBRARYSTATUS_CONFIRMED."')
                                 AND (A.libraryUnitId = ({$this->libraryUnitIdS}) )) THEN TRUE ELSE FALSE END AS showReturnMaterial,
                           CASE WHEN ((A.loanBetweenLibraryStatusId = '".ID_LOANBETWEENLIBRARYSTATUS_DEVOLUTION."')
                                 AND ((SELECT COUNT(*) FROM gtcExemplaryControl WHERE originalLibraryUnitId = ({$this->libraryUnitIdS}) AND itemNumber IN ( SELECT itemNumber FROM gtcLoanbetweenLibraryComposition WHERE loanBetweenLibraryId = A.loanBetweenLibraryId AND isconfirmed IS TRUE)) > 0))
                                THEN TRUE ELSE FALSE END AS showConfirmReturn,
                           CASE WHEN (A.libraryUnitId = '{$this->libraryUnitIdS}' ) THEN TRUE ELSE FALSE END AS EditRecord,
                           '{$this->libraryUnitIdS}' as LibrarySearch";
        }
        else
        {
            $columns .= "false as showCancel,
                         false as showAcceptDisaccept,
                         false as showConfirmReceipt,
                         false as showReturnMaterial,
                         false as showConfirmReturn,
                         false as EditRecord";
        }

        $this->setColumns($columns);
        $this->MSQL->setGroupBy('1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17');

    	if ($this->loanBetweenLibraryIdS)
    	{
    		$this->setWhere('A.loanBetweenLibraryId = ?');
    		$args[] = $this->loanBetweenLibraryIdS;
    	}
    	if ($this->loanDateS)
    	{
    		$this->setWhere('A.loanDate = ?');
    		$args[] = $this->loanDateS;
    	}
    	if ($this->beginLoanDateS)
    	{
    		$this->setWhere('date(A.loanDate) >= ?');
    		$args[] = $this->beginLoanDateS;
    	}
    	if ($this->endLoanDateS)
    	{
    		$this->setWhere('date(A.loanDate) <= ?');
    		$args[] = $this->endLoanDateS;
    	}
    	if ($this->returnForecastDateS)
    	{
    		$this->setWhere('date(A.returnForecastDate) = ?');
    		$args[] = $this->returnForecastDateS;
    	}
    	if ($this->beginReturnForecastDateS)
    	{
    		$this->setWhere('date(A.returnForecastDate) >= ?');
    		$args[] = $this->beginReturnForecastDateS;
    	}
    	if ($this->endReturnForecastDateS)
    	{
    		$this->setWhere('date(A.returnForecastDate) <= ?');
    		$args[] = $this->endReturnForecastDateS;
    	}
    	if ($this->limitDateS)
    	{
    		$this->setWhere('A.limitDate = ?');
    		$args[] = $this->limitDateS;
    	}
    	if ($this->beginLimitDateS)
    	{
    		$this->setWhere('date(A.limitDate) >= ?');
    		$args[] = $this->beginLimitDateS;
    	}
    	if ($this->endLimitDateS)
    	{
    		$this->setWhere('date(A.limitDate) <= ?');
    		$args[] = $this->endLimitDateS;
    	}
    	if ($this->returnDateS)
    	{
    		$this->setWhere('date(A.returnDate) = ?');
    		$args[] = $this->returnDateS;
    	}
    	if ($this->beginReturnDateS)
    	{
    		$this->setWhere('date(A.ReturnDate) >= ?');
    		$args[] = $this->beginReturnDateS;
    	}
    	if ($this->endReturnDateS)
    	{
    		$this->setWhere('date(A.returnDate) <= ?');
    		$args[] = $this->endReturnDateS;
    	}
    	if ($this->personIdS)
    	{
    		$this->setWhere('A.personId = ?');
    		$args[] = $this->personIdS;
    	}
    	if ($this->loanBetweenLibraryStatusIdS)
    	{
    		$this->setWhere('A.loanBetweenLibraryStatusId = ?');
    		$args[] = $this->loanBetweenLibraryStatusIdS;
    	}
    	if ($this->itemNumberS)
    	{
    		$this->setWhere('D.itemNumber = ?');
    		$args[] = $this->itemNumberS;
    	}

    	if ($this->libraryUnitIdS)
    	{
    		$this->setWhere("(B.libraryUnitId IN ({$this->libraryUnitIdS}) OR E.originalLibraryUnitId IN ({$this->libraryUnitIdS}))");
    	}

    	$this->setOrderBy('A.loanBetweenLibraryId');
        $sql = $this->select($args);

        $rs  = $this->query($sql, $toObject);
        return $rs;
    }

    public function listLoanBetweenLibrary()
    {
        return $this->autoList();
    }

    public function changeStatus($loanBetweenLibraryId, $loanBetweenLibraryStatusId)
    {
        $cols[] = 'loanBetweenLibraryStatusId';
        $args[] = $loanBetweenLibraryStatusId;

        //Se o status for Confirmacao de Retorno, salva o returnDate como a data atual
        if ($loanBetweenLibraryStatusId == ID_LOANBETWEENLIBRARYSTATUS_FINALIZED)
        {
        	$cols[] = 'returnDate';
        	$args[] = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        }

    	$this->clear();
    	$this->setTables($this->tables);
    	$this->setColumns($cols);

    	$this->setWhere('loanBetweenLibraryId = ?');
        $args[] = $loanBetweenLibraryId;

    	$sql = $this->update($args);
    	return $this->execute($sql);
    }

    public function insertLoanBetweenLibraryStatusHistory()
    {
        if ($this->loanBetweenLibraryId)
        {
	        //Insert gtcLoanBetweenLibraryStatusHistory
	        $this->busLoanBetweenLibraryStatusHistory->loanBetweenLibraryId = $this->loanBetweenLibraryId;
	        $this->busLoanBetweenLibraryStatusHistory->loanBetweenLibraryStatusId = $this->loanBetweenLibraryStatusId;
	        $this->busLoanBetweenLibraryStatusHistory->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
	        $this->busLoanBetweenLibraryStatusHistory->operator = GOperator::getOperatorId();
	        $this->busLoanBetweenLibraryStatusHistory->insertLoanBetweenLibraryStatusHistory();
        }
    }

    /**
     * Função utilizada em LoanBetweenLibrary que não deixa requisitar itemNumber já solicitado por outra unidade.
     */
    public function getLoanBetweenLibraryConfirm($itemNumber)
    {
        $confirmado = ID_LOANBETWEENLIBRARYSTATUS_CONFIRMED;
        $aprovado   = ID_LOANBETWEENLIBRARYSTATUS_APPROVED;
    	if (!$itemNumber)
    	{
    		return false;
    	}
        $sql = "SELECT A.loanBetweenLibraryId FROM gtcLoanBetweenLibrary A, gtcLoanBetweenLibraryComposition B WHERE A.loanBetweenLibraryId = B.loanBetweenLibraryId AND itemNumber = '{$itemNumber}' AND isConfirmed = 't' AND (A.loanBetweenlibraryStatusId = {$confirmado} OR A.loanBetweenlibraryStatusId = {$aprovado})";
        $rs  = $this->query($sql);
        return $rs;
    }

    /**
     * Retorna a composição de um determinado emprestimo entre bibliotecas
     *
     * @param optional integer $loanBetweenLibraryId
     * @return object or false
     */
    public function getComposition($loanBetweenLibraryId = null)
    {
        $loanBetweenLibraryId = is_null($loanBetweenLibraryId) ? $this->loanBetweenLibraryId : $loanBetweenLibraryId;

        if(is_null($loanBetweenLibraryId))
        {
            return false;
        }

        return $this->busLoanBetweenLibraryComposition->getComposition($loanBetweenLibraryId);
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
        $this->setTables($this->tables);
        $this->setWhere(' personId = ?');
        $sql = $this->update(array($newPersonId, $currentPersonId));
        $rs  = $this->execute($sql);
        return $rs;
    }
}
?>