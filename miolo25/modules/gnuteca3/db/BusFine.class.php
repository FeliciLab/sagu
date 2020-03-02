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
 * This file handles the connection and actions for busFine table
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
 * Class created on 01/08/2008
 *
 **/
class BusinessGnuteca3BusFine extends GBusiness
{
    public $pkeys;
    public $cols;
    public $table;
    public $fullColumns;
    public $busFineStatusHistory;
    public $busLibraryAssociation;
    public $isFormSearch = FALSE;
    public $busLoan;

    public $fineId;
    public $loanId;
    public $beginDate;
    public $value;
    public $fineStatusId;
    public $endDate;
    public $operator;
    public $observation;
    public $observationHistoric;
    public $date;
    public $personId;

    public $fineIdS;
    public $loanIdS;
    public $beginDateS;
    public $beginBeginDateS;
    public $endBeginDateS;
    public $startDateS;
    public $valueS;
    public $fineStatusIdS;
    public $beginEndDateS;
    public $endEndDateS;
    public $endDateS;
    public $itemNumberS;
    public $libraryUnitIdS;
    public $personIdS;
    public $observationS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->MIOLO   = MIOLO::getInstance();
        $this->module  = MIOLO::getCurrentModule();
        $this->busFineStatusHistory  = $this->MIOLO->getBusiness($this->module, 'BusFineStatusHistory');
        $this->busLibraryAssociation = $this->MIOLO->getBusiness($this->module, 'BusLibraryAssociation');
        $this->busLoan               = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $this->table   = 'gtcFine';
        $this->pkeys   = 'fineId';
        $this->cols    = 'loanId,
                          beginDate,
                          value,
                          fineStatusId,
                          endDate,
                          observation';
        $this->fullColumns = $this->pkeys . ',' . $this->cols;
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertFine()
    {
        $this->fineId = $this->getNextFineId();
        $data = $this->associateData( $this->fullColumns );
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $sql = $this->insert($data);
        $rs  = $this->execute($sql);

        //Pois o campo observationHistoric do formulário é para ser armazenado no campo observation do histórico
        $this->observation = $this->observationHistoric;
        $this->insertFineStatusHistory();

        return $rs;
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateFine()
    {
        $data = $this->associateData( $this->cols . ',' . $this->pkeys );
        $this->clear();

        //Na Circulação de material não deve gravar a observação no gtcFine. Mas, sim no gtcStatusHistory
        if (MIOLO::_REQUEST('action') == 'main:materialMovement')
        {
        	$this->observationHistoric = $data[5];
        	$this->setColumns('loanId,beginDate,value,fineStatusId,endDate');
            $this->setWhere('fineId = '.$data[6]);
        }
        //No Administrativo deve gravar todos os campos
        else
        {
            $this->setColumns($this->cols);
            $this->setWhere('fineId = ?');
        }

        $this->setTables($this->table);
        $sql = $this->update($data);
        $rs  = $this->execute($sql);

        //Pois o campo observationHistoric do formulário é para ser armazenado no campo observation do histórico
        //Na Circulação de material, como só tem o campo observação, deve armazenar este no histórico
        $this->observation = $this->observationHistoric;
        $this->insertFineStatusHistory();

        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $fineId (integer)
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function deleteFine($fineId)
    {
        //Delete all registers with this fineId code on gtcFineStatusHistory
        $delFSH = $this->busFineStatusHistory->deleteByFine($fineId);

        $this->clear();
        $this->setTables($this->table);
        $this->setWhere('fineId = ?');
        $sql = $this->delete(array($fineId));
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getFine($fineId, $returnThisObject = true)
    {
        $data[] = $fineId;

        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $this->setWhere('fineId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);

        if ($returnThisObject)
        {
            $this->setData($rs[0]);
            return $this;
        }
        
        return $rs[0];
    }

    /**
     * Return a specific record from gtcFine and gtcFineHistory
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getFineAndHistory($fineId)
    {
        $data[] = $fineId;

        $this->clear();
        $this->setColumns('A.fineId,A.loanId,A.beginDate,A.value,A.fineStatusId,A.endDate,A.observation,B.observation,B.date');
        $this->setTables('gtcFine A
                         LEFT JOIN gtcFineStatusHistory B
                         ON A.fineId = B.fineId');
        $this->setWhere('A.fineId = ?');
        $this->setOrderBy('B.date desc');
        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);

        return $rs[0];
    }


    /**
     *
     **/
    public function getLoanId($fineId)
    {
        $data[] = $fineId;

        $this->clear();
        $this->setColumns('loanid');
        $this->setTables($this->table);
        $this->setWhere('fineId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);

        return ($rs && isset($rs[0]->loanid)) ? $rs[0]->loanid : false;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @return (Array): An array containing the search results
     **/
    public function searchFine($toObject = FALSE , $orderBy='A.fineId DESC', $extraInfo= false )
    {
        $this->clear();

        if ($this->fineIdS)
        {
            $this->setWhere('A.fineId = ?');
            $data[] = $this->fineIdS;
        }

        if ($this->loanIdS)
        {
            $this->setWhere('A.loanId = ?');
            $data[] = $this->loanIdS;
        }

        if ($this->beginBeginDateS)
        {
            $this->setWhere('date(A.beginDate) >= ?');
            $data[] = $this->beginBeginDateS;
        }
        if ($this->endBeginDateS)
        {
            $this->setWhere('date(A.beginDate) <= ?');
            $data[] = $this->endBeginDateS;
        }

        if ($this->startDateS)
        {
            $this->setWhere('A.beginDate >= ?');
            $data[] = $this->startDateS;
        }

        if ($this->valueS)
        {
            $this->setWhere('A.value = ?');
            //converte a , para ponto para evitar erro na base de dados
            $data[] = str_replace(',','.' ,$this->valueS);
        }

        if ($this->fineStatusIdS)
        {
            $this->setWhere('A.fineStatusId = ?');
            $data[] = $this->fineStatusIdS;
        }

        if ($this->beginDateS)
        {
            $this->setWhere('date(A.beginDate) = ?');
            $data[] = $this->beginDateS;
        }
        if ($this->beginEndDateS)
        {
            $this->setWhere('date(A.endDate) >= ?');
            $data[] = $this->beginEndDateS;
        }
        if ($this->endDateS)
        {
            $this->setWhere('date(A.endDate) = ?');
            $data[] = $this->endDateS;
        }
        if ($this->endEndDateS)
        {
            $this->setWhere('date(A.endDate) <= ?');
            $data[] = $this->endEndDateS;
        }

        if ($this->itemNumberS)
        {
        	$this->setWhere('L.itemNumber = ?');
        	$data[] = $this->itemNumberS;
        }

        if ($this->itemNumber)
        {
            $this->setWhere('L.itemNumber = ?');
            $data[] = $this->itemNumber;
        }

        if ($this->libraryUnitIdS)
        {
            $this->setWhere('L.libraryUnitId in (' . $this->libraryUnitIdS . ')' );
        }

        if ($this->personIdS)
        {
            $this->setWhere('L.personId = ?');
            $data[] = $this->personIdS;
        }

        if ($this->isFormSearch) //Busca sendo feita a partir do FrmFineSearch
        {
            $columns = 'A.fineId,
                        A.loanId,
                        L.itemNumber,
                        NULL AS data,
                        A.beginDate,
                        A.value,
                        B.description,
                        A.endDate,
                        L.personId,
                        C.name,
                        L.LibraryUnitId,
                        LU.libraryName,
                        A.fineStatusId';
        }
        else
        {
	        $columns = 'A.fineId,
	                    A.loanId,
                        NULL AS tomo,
                        NULL AS data,
	                    A.beginDate,
	                    A.value,
	                    B.description,
	                    A.endDate,
                        L.LibraryUnitId,
                        LU.libraryName,
                        A.fineStatusId';
        }

        $tables = '     gtcFine         A
             LEFT JOIN  gtcFineStatus   B
                    ON  (A.fineStatusId = B.fineStatusId)
             LEFT JOIN  gtcLoan         L
                    ON  (A.loanId = L.loanId)
       LEFT JOIN  ONLY  basPerson    C
                    ON  (L.personId = C.personId)
             LEFT JOIN gtcLibraryUnit LU
                    ON (L.libraryUnitId = LU.libraryUnitId)';

        // Se for uma pesquisa por pessoa, faz um join com a tabela loan  e basperson
        if($this->personId)
        {
            $this->setWhere('P.personId = ?');
            $data[] = $this->personId;

            $tables.= 'INNER JOIN ONLY basPerson P ON (L.personId = P.personId)';

            $columns.= ", P.personId";
        }

        $this->setTables($tables);
        $this->setColumns($columns);
        $this->setOrderBy($orderBy);
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject);

        if ($toObject && $extraInfo)
        {
        	if (is_array($rs))

        	foreach( $rs as $line => $info)
        	{
        		//$loan = $this->busLoan->getLoan($info->loanId , true, FALSE);
        		$rs[$line]->loan = $this->busLoan->getLoan($info->loanId , true, true);
        	}
        }
        return $rs;
    }


    /**
     * List all records from the table handled by the class
     *
     * @param None
     *
     * @return (Array): Return an array with the entire table
     *
     **/
    public function listFine()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    public function getNextFineId()
    {
        $query = $this->query("SELECT NEXTVAL('seq_fineId')");
        return $query[0][0];
    }


    /**
     * Este metodo registra o historico de estado de uma determinada multa
     *
     * @param string $operator
     */
    public function insertFineStatusHistory($operator = null)
    {
    	$operator = is_null($operator) ? GOperator::getOperatorId() : $operator;

        //Insert data on gtcFineStatusHistory
        $this->busFineStatusHistory->fineId = $this->fineId;
        $this->busFineStatusHistory->fineStatusId = $this->fineStatusId;
        $this->busFineStatusHistory->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $this->busFineStatusHistory->operator = $operator;
        $this->busFineStatusHistory->observation = $this->observation;
        $this->busFineStatusHistory->insertFineStatusHistory();
    }



    /**
     *
     *
     * @param unknown_type $libraryUnitId
     * @param unknown_type $personId
     * @return unknown
     */
    public function getFineOpenOfAssociation($libraryUnitId, $personId)
    {
        $libraries = $this->busLibraryAssociation->getLibrariesAssociationOf($libraryUnitId);

        if ($libraries)
        {
            $this->clear();
            $this->setColumns('A.libraryUnitId,
                               A.linkId,
                               A.loanId,
                               B.fineId,
                               B.value,
                               B.beginDate,
                               B.endDate');
            $this->setTables('gtcloan   A
                INNER JOIN    gtcfine   B
                        ON    (A.loanId = B.loanId)');
            
            if ( VERIFY_FINES_AND_PENALTIES_PER_UNIT == DB_TRUE )
            {
                $this->setWhere('A.libraryUnitId IN ('.implode(',', $libraries).')
                             AND A.personId = ?
                             AND B.fineStatusId = ?');
            }
            else
            {
                $this->setWhere('A.personId = ? AND B.fineStatusId = ?');
            }
            
            $args[] = $personId;
            $args[] = ID_FINESTATUS_OPEN;
            $sql   = $this->select($args);
            $query = $this->query($sql, true);
            return $query;
        }
        else
        {
            return array();
        }
    }



    /**
     * Retorna as multas abertas de uma determinada biblioteca
     *
     * @param integer $libraryUnitId
     * @return array
     */
    public function getFinesOpen($libraryUnitId = null, $personId = null)
    {
        return $this->getFines($libraryUnitId, $personId, ID_FINESTATUS_OPEN);
    }


    /**
     * retorna todas multas com forma do pagamento via boleto.
     *
     * @param int $libraryUnitId
     * @param  array int $personId
     * @return array object
     */
    public function getFinePayRoll($libraryUnitId = null, $personId = null, $period = null, $offSet = null, $limit = null)
    {
        return $this->getFines($libraryUnitId, $personId, ID_FINESTATUS_BILLET, $period, $offSet, $limit);
    }



    /**
     * Retorna multas
     *
     * @param unknown_type $libraryUnitId
     * @param unknown_type $personId
     * @param unknown_type $fineStatus
     * @return unknown
     */
    public function getFines($libraryUnitId, $personId = null, $fineStatus = null, $period = null, $offSet = null, $limit = null)
    {
        $this->clear();

        $this->setColumns
        (
            'A.libraryUnitId,
             A.personId,
             B.fineId,
             B.value,
             A.itemNumber,
             A.loanDate,
             A.returnForecastDate,
             A.returnDate,
             B.fineStatusId,
             C.description as fineStatusDescription,
             D.libraryName'
        );

        $this->setTables('gtcloan A INNER JOIN gtcfine          B USING (loanId)
                                    INNER JOIN gtcFineStatus    C USING (fineStatusId)
                                    INNER JOIN gtcLibraryUnit   D USING (libraryUnitId) ');

        if(!is_null($libraryUnitId))
        {
            $libraryUnitId = !is_array($libraryUnitId) ? array($libraryUnitId) : $libraryUnitId;
            $this->setWhere("A.libraryUnitId IN ('". implode("', '", $libraryUnitId) ."')");
        }
        if(!is_null($personId))
        {
            $personId = !is_array($personId) ? array($personId) : $personId;
            $this->setWhere("A.personId IN ('". implode("', '", $personId) ."')");
        }
        if(!is_null($fineStatus))
        {
            $this->setWhere("B.fineStatusId = ?");
            $args[] = $fineStatus;
        }
        if(!is_null($period))
        {
            if(is_array($period))
            {
                $this->setWhere("A.returnDate BETWEEN '". GDate::construct($period[0])->getDate(GDate::MASK_TIMESTAMP_DB) ."' AND '". GDate::construct($period[1])->getDate(GDate::MASK_TIMESTAMP_DB) ."'");
            }
            else
            {
                $this->setWhere("A.returnDate::date = '". GDate::construct($period)->getDate(GDate::MASK_DATE_DB) ."' ");
            }
        }

        $this->setOrderBy("A.libraryUnitId, A.personId, A.returnDate");
        $sql   = $this->select($args);

        if(!is_null($offSet))
        {
            $sql.= " OFFSET $offSet ";
        }

        if(!is_null($limit))
        {
            $sql.= " LIMIT $limit ";
        }

        $query = parent::query($sql, true);
        return $query;
    }



    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getData()
    {
        $this->value = str_replace(',', '.', $this->value);
        return parent::getData();
    }


    /**
     * Seta uma determinada multa como paga
     *
     * @param int $fineId
     * @return boolean
     */
    public function setFinePay($fineId, $saveHistory=NULL)
    {
        $this->fineId       = $fineId;
        $this->fineStatusId = ID_FINESTATUS_PAYED;
        $this->endDate      = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);

        return $this->changeStatus($saveHistory);
    }

    /**
     * Seta uma determinada multa como aberta
     *
     * @param int $fineId
     * @return boolean
     */
    public function setFineOpen($fineId, $saveHistory=NULL)
    {
        $this->fineId       = $fineId;
        $this->fineStatusId = ID_FINESTATUS_OPEN;

        return $this->changeStatus($saveHistory);
    }



    /**
     * Seta uma determinada multa como paga por boleto
     *
     * @param int $fineId
     * @return boolean
     */
    public function setFinePayRoll($fineId, $saveHistory=NULL)
    {
        $this->fineId       = $fineId;
        $this->fineStatusId = ID_FINESTATUS_BILLET;
        $this->endDate      = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);

        return $this->changeStatus($saveHistory);
    }


    /**
     * Seta uma determinada multa como abonada
     *
     * @param int $fineId
     * @return boolean
     */
    public function setFineBonus($fineId, $obs = null, $saveHistory=NULL)
    {

        $this->fineId       = $fineId;
        $this->fineStatusId = ID_FINESTATUS_EXCUSED;
        $this->endDate      = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $this->observation  = $obs;

        return $this->changeStatus($saveHistory);
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function changeStatus($saveHistory=TRUE)
    {
        $this->clear();
        $this->setColumns("finestatusid, enddate");
        $this->setTables($this->table);
        $this->setWhere('fineId = ?');
        $sql = $this->update(array($this->fineStatusId, $this->endDate, $this->fineId));

        $rs  = $this->execute($sql);

        if ($saveHistory)
        {
            $operator = is_string($saveHistory) ? $saveHistory : null;
            $this->insertFineStatusHistory($operator);
        }

        return $rs;
    }


    /**
     * limpa todos atributos da classe
     *
     */
    public function clean()
    {
        $this->fineId=
        $this->loanId=
        $this->beginDate=
        $this->value=
        $this->fineStatusId=
        $this->endDate=
        $this->operator=
        $this->observation=
        $this->observationHistoric=
        $this->date=
        $this->personId=
        $this->fineIdS=
        $this->loanIdS=
        $this->beginDateS=
        $this->beginBeginDateS=
        $this->endBeginDateS=
        $this->startDateS=
        $this->valueS=
        $this->fineStatusIdS=
        $this->beginEndDateS=
        $this->endEndDateS=
        $this->endDateS=
        $this->itemNumberS=
        $this->libraryUnitIdS=
        $this->personIdS=
        $this->observationS= null;
    }
}
?>
