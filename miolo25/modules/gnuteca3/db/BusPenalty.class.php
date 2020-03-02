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
 * Class created on 23/09/2008
 *
 **/
class BusinessGnuteca3BusPenalty extends GBusiness
{
    public $pkeys;
    public $columns;
    public $fullColumns;
    public $busLibraryAssociation;
    public $busLibraryUnit;
    public $busPolicy;
    public $removeData;

    public $penaltyId;
    public $personId;
    public $libraryUnitId;
    public $observation;
    public $internalObservation;
    public $penaltyDate;
    public $penaltyEndDate;
    public $operator;

    public $penaltyIdS;
    public $personIdS;
    public $libraryUnitIdS;
    public $observationS;
    public $internalObservationS;
    public $penaltyDateS;
    public $penaltyEndDateS;
    public $beginBeginPenaltyDateS;
    public $endBeginPenaltyDateS;
    public $beginEndPenaltyDateS;
    public $endEndPenaltyDateS;
    public $operatorS;
    public $onlyActive;

    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables  = 'gtcPenalty';
        $this->pkeys   = 'penaltyId';
        $this->columns = 'personId,
                          libraryUnitId,
                          observation,
                          internalObservation,
                          penaltyDate,
                          penaltyEndDate,
                          operator';
        $this->fullColumns = $this->pkeys . ',' . $this->columns;
        $this->busLibraryAssociation = $this->MIOLO->getBusiness($this->module, 'BusLibraryAssociation');
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busPolicy = $this->MIOLO->getBusiness($this->module, 'BusPolicy');
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertPenalty()
    {
        $data = $this->associateData( $this->columns );
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->insert($data) . ' RETURNING penaltyId';
        $rs  = $this->query($sql);
        
        $this->penaltyId = $rs[0][0];

        return $rs[0][0];
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updatePenalty()
    {
        if ($this->penaltyId)
        {
            if ($this->removeData)
            {
                return $this->deletePenalty($this->penaltyId);
            }
            else
            {
                $data = $this->associateData( $this->columns . ',' . $this->pkeys );
                $this->clear();
                $this->setColumns($this->columns);
                $this->setTables($this->tables);
                $this->setWhere('penaltyId = ?');
                $sql = $this->update($data);
                $rs  = $this->execute($sql);
                return $rs;
            }
        }
        else
        {
            return $this->insertPenalty();
        }
    }


    /**
     * Delete a record
     *
     * @param $penaltyId (integer)
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function deletePenalty($penaltyId)
    {
        $data[] = $penaltyId;

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('penaltyId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * @param $penaltyId (integer)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getPenalty($penaltyId)
    {
        $data[] = $penaltyId;

        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $this->setWhere('penaltyId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);
        if ($rs)
        {
        	$this->setData($rs[0]);
            return $rs[0];
        }
        else
        {
            return false;
        }
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @return (Array): An array containing the search results
     **/
    public function searchPenalty($toObject = FALSE, $orderBy = 'A.penaltyId DESC', $filterOperator = FALSE)
    {
        $this->clear();

        if ($this->penaltyIdS)
        {
            $this->setWhere('A.penaltyId = ?');
            $data[] = $this->penaltyIdS;
        }
        if ($this->personIdS)
        {
            $this->setWhere('A.personId = ?');
            $data[] = $this->personIdS;
        }
        if ($this->libraryUnitIdS)
        {
            $this->setWhere('A.libraryUnitId in (' . $this->libraryUnitIdS . ')' );
        }
        if ($this->observationS)
        {
            $this->observationS = str_replace(' ','%', $this->observationS);
            $this->setWhere('lower(A.observation) LIKE lower(?)');
            $data[] = '%' . strtolower($this->observationS) . '%';
        }
        if ($this->internalObservationS)
        {
            $this->internalObservationS = str_replace(' ','%', $this->internalObservationS);
            $this->setWhere('lower(A.internalObservation) LIKE lower(?)');
            $data[] = '%' . strtolower($this->internalObservationS) . '%';
        }
        if ($this->penaltyDateS)
        {
            $this->setWhere('date(A.penaltyDate) = ?');
            $data[] = $this->penaltyDateS;
        }
        if ($this->penaltyEndDateS)
        {
            $this->setWhere('date(A.penaltyDate) = ?');
            $data[] = $this->penaltyEndDateS;
        }
        if ($this->beginBeginPenaltyDateS)
        {
            $this->setWhere('date(A.penaltyDate) >= ?');
            $data[] = $this->beginBeginPenaltyDateS;
        }
        if ($this->endBeginPenaltyDateS)
        {
            $this->setWhere('date(A.penaltyDate) <= ?');
            $data[] = $this->endBeginPenaltyDateS;
        }
        if ($this->beginEndPenaltyDateS)
        {
            $this->setWhere('date(A.penaltyEndDate) >= ?');
            $data[] = $this->beginEndPenaltyDateS;
        }
        if ($this->endEndPenaltyDateS)
        {
            $this->setWhere('date(A.penaltyEndDate) <= ?');
            $data[] = $this->endEndPenaltyDateS;
        }
        if ($this->operatorS)
        {
            $this->setWhere('lower(A.operator) LIKE lower(?)');
            $data[] = '%'.strtolower($this->operatorS) . '%';
        }

        if ($filterOperator)
        {
        	$this->busLibraryUnit->filterOperator = TRUE;
        	$libs = $this->busLibraryUnit->listLibraryUnit();
        	if ($libs)
        	{
        		$_libs = array();
                foreach ($libs as $lib)
                {
                	$_libs[] = $lib[0];
                }
                $this->setWhere('A.libraryUnitId IN (' . implode(',', $_libs) . ')');
        	}
        }
        
        //mostra somente ativas
        if ( $this->onlyActive )
        {
            $this->setWhere('coalesce(penaltyEndDate > now(), penaltyEndDate is null)' );
        }

        $this->setTables('  gtcPenalty      A
            LEFT JOIN ONLY  basPerson       B
                        ON  (A.personId = B.personId)
                 LEFT JOIN  gtcLibraryUnit  LU
                        ON  (A.libraryunitid = LU.libraryunitid)
                        ');
        $this->setColumns('A.penaltyId,
                           A.personId,
                           B.name,
                           A.observation,
                           A.internalObservation,
                           A.penaltyDate::date as penaltyDate,
                           A.penaltyEndDate::date as penaltyEndDate,
                           A.operator,
                           LU.libraryName,
                           LU.libraryUnitId');
        $this->setOrderBy($orderBy);
        $rs  = $this->query( $this->select($data) , $toObject ? TRUE : FALSE);
        return $rs;
    }


    /**
     * List all records from the table handled by the class
     *
     * @return (Array): Return an array with the entire table
     *
     **/
    public function listPenalty()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    /**
    * Get an array of penaltys, filter by library unit code and person code,
    * note that this function will search in penalry by this library unit association
    *
    * @param $libraryUnitId the code of library unit
    * @param $personId      the code of person
    *
    * @return an array of objects of penalty
    */
    public function getPenaltyOfAssociation($libraryUnitId, $personId)
    {
        $libraries = $this->busLibraryAssociation->getLibrariesAssociationOf($libraryUnitId);

        if ($libraries)
        {
        	//Get current columns
        	$cols = $this->columns;

            $this->clear();
            $this->setTables($this->tables);
            $this->setColumns('penaltyId, libraryUnitId');
            if ( VERIFY_FINES_AND_PENALTIES_PER_UNIT == DB_TRUE )
            {
                $this->setWhere('(libraryUnitId IN (' . implode(',', $libraries) . ') OR libraryUnitId IS NULL)');
            }            
            $this->setWhere('coalesce(penaltyEndDate > ?, penaltyEndDate is null)');
            $this->setWhere('personId = ?');
            $args[] = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
            $args[] = $personId;
            $sql   = $this->select($args);
            $query = $this->query($sql, true);

            //Back to original columns
            $this->columns = $cols;

            return $query;
        }
        else
        {
            return array();
        }
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


    /**
     * Enter description here...
     *
     * @param unknown_type $data
     */
    public function setData($data)
    {
        $this->removeData = NULL;
        $this->personId = NULL;
        $this->libraryUnitId = NULL;
        $this->penaltyId = NULL;
        $this->observation = NULL;
        $this->internalObservation = NULL;
        $this->penaltyDate = NULL;
        $this->penaltyEndDate = NULL;
        $this->operator = NULL;
        parent::setData($data);
    }

    /**
     * Função que calcula os dias de penalidade para materiais entregues em atraso.
     * Se tiver penalidade por atraso no empréstimo retorna um GDate com a data
     * calculada de termino da penalidade, se não retorna falso.
     * 
     * @param stdClass $loan
     * @return boolean
     */
    public function calcultesPenalty($loan)
    {
        $policy = $this->busPolicy->getUserPolicy($loan->libraryUnitId,$loan->loan->personId,$loan->loan->linkId,$loan->materialGenderId); //Pega politica do material para a pessoa

        if ( MUtil::getBooleanValue(ROUND_PENALTY_BY_DELAY) ) //Se tiver true na variável de arredondamento
        {
            $policy[0]->penaltyByDelay = ceil($policy[0]->penaltyByDelay); //Arredonda pra cima
        }
        else
        {
            $policy[0]->penaltyByDelay = floor($policy[0]->penaltyByDelay); //Arredonda pra baixo
        }

        if ( $policy[0]->penaltyByDelay > 0 ) //Se existe penalidade por atraso
        {
            $penaltyEndDate = GDate::now(); //Dia atual
            $penaltyEndDate->addDay($loan->loan->delayDays * $policy[0]->penaltyByDelay); //Calcula o dia em que a penalidade terminará = Dia atual + (Dias de atraso * penalidade por dias de atraso)
        }
        else
        {
            $penaltyEndDate = false;
        }
        
        return $penaltyEndDate;
    }
}
?>
