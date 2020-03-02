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
 * This file handles the connection and actions for busPenalty table
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
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
 * Class created on 04/05/2009
 *
 **/


class BusinessGnuteca3BusMyPenalty extends GBusiness
{
    public $pkeys;
    public $columns;
    public $fullColumns;
    public $busLibraryAssociation;
    public $busLibraryUnit;
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
    public $beginBeginPenaltyDateS;
    public $endBeginPenaltyDateS;
    public $beginEndPenaltyDateS;
    public $endEndPenaltyDateS;
    public $operatorS;

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
                          penaltyDate,
                          penaltyEndDate';
        $this->fullColumns = $this->pkeys . ',' . $this->columns;
        $this->busLibraryAssociation = $this->MIOLO->getBusiness($this->module, 'BusLibraryAssociation');
        $this->busLibraryUnit        = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
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
            $this->setWhere('A.libraryUnitId = ?');
            $data[] = $this->libraryUnitIdS;
        }
        if ($this->observationS)
        {
            $this->observationS = str_replace(' ','%', $this->observationS);
            $this->setWhere('lower(A.observation) LIKE lower(?)');
            $data[] = '%' . strtolower($this->observationS) . '%';
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

        $this->setTables('  gtcPenalty      A
            LEFT JOIN ONLY  basPerson       B
                        ON  (A.personId = B.personId)
                 LEFT JOIN  gtcLibraryUnit  LU
                        ON  (A.libraryunitid = LU.libraryunitid)
                        ');
        $this->setColumns('A.penaltyId,
                           A.personId,
                           A.observation,
                           A.penaltyDate,
                           A.penaltyEndDate,
                           LU.libraryName');
//                           LU.libraryUnitId');
        $this->setOrderBy($orderBy);
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject ? TRUE : FALSE);
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
            $this->clear();
            $this->setTables($this->tables);
            $this->setColumns('penaltyId, libraryUnitId');
            $this->setWhere('(libraryUnitId IN (' . implode(',', $libraries) . ') OR libraryUnitId IS NULL)');
            $this->setWhere('coalesce(penaltyEndDate > ?, penaltyEndDate is null)');
            $this->setWhere('personId = ?');
            $args[] = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
            $args[] = $personId;
            $sql   = $this->select($args);
            $query = $this->query($sql, true);
            return $query;
        }
        else
        {
            return array();
        }
    }


    public function setData($data)
    {
        $this->removeData       = NULL;
        $this->personId         = NULL;
        $this->libraryUnitId    = NULL;
        $this->penaltyId        = NULL;
        $this->observation      = NULL;
        $this->penaltyDate      = NULL;
        $this->penaltyEndDate   = NULL;
        $this->operator         = NULL;
        parent::setData($data);
    }
}
?>
