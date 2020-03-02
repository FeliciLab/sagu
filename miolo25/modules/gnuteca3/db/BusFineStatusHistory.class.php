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
 * This file handles the connection and actions for busFineStatusHistory table
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
 *
 * @since
 * Class created on 02/08/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusFineStatusHistory extends GBusiness
{
    public $pkeys;
    public $table;
    public $columns;
    public $fullColumns;

    public $fineId;
    public $fineStatusId;
    public $date;
    public $operator;
    public $observation;

    public $fineIdS;
    public $fineStatusIdS;
    public $dateS;
    public $operatorS;
    public $observationS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->table   = 'gtcFineStatusHistory';
        $this->pkeys   = 'fineId,
                          fineStatusId';
        $this->columns = 'date,
                          operator,
                          observation';
        $this->fullColumns = $this->pkeys . ',' . $this->columns;
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertFineStatusHistory()
    {
        $data = $this->associateData( $this->fullColumns );
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $sql = $this->insert($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateFineStatusHistory()
    {
        $data = $this->associateData( $this->columns . ',' . $this->pkeys );

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->table);
        $this->setWhere('fineId = ? AND fineStatusId = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $fineId (integer)
     * @param $fineStatusId (integer)
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function deleteFineStatusHistory($fineId, $fineStatusId)
    {
        $data[] = $fineId;
        $data[] = $fineStatusId;

        $this->clear();
        $this->setTables($this->table);
        $this->setWhere('fineId = ? AND fineStatusId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * @param $fineId (integer)
     * @param $fineStatusId (integer)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getFineStatusHistory($fineId, $fineStatusId)
    {
        $data[] = $fineId;
        $data[] = $fineStatusId;

        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $this->setWhere('fineId = ? AND fineStatusId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);
        $this->setData($rs[0]);
        return $this;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @return (Array): An array containing the search results
     **/
    public function searchFineStatusHistory($toObject = FALSE)
    {
        $this->clear();

        if ($this->fineIdS)
        {
            $this->setWhere('A.fineId = ?');
            $data[] = $this->fineIdS;
        }

        if ($this->fineStatusIdS)
        {
            $this->setWhere('A.fineStatusId = ?');
            $data[] = $this->fineStatusIdS;
        }

        if ($this->dateS)
        {
            $this->setWhere('A.date = ?');
            $data[] = $this->dateS;
        }

        if ($this->operatorS)
        {
            $this->setWhere('A.operator = ?');
            $data[] = $this->operatorS;
        }

        if ($this->observation)
        {
            $this->setWhere('A.observation = ?');
            $data[] = $this->observation;
        }

        $this->setColumns('A.fineId,
                           B.description AS fineStatus,
                           A.date,
                           A.operator,
                           A.observation,
                           A.fineStatusId');
        $this->setTables('  gtcFineStatusHistory    A
                INNER JOIN  gtcFineStatus           B
                        ON  (A.fineStatusId = B.fineStatusId)');
        $this->setOrderBy('A.fineId');
        $sql = $this->select($data);
        return $this->query($sql, ($toObject ? TRUE : FALSE));
    }


    /**
     * List all records from the table handled by the class
     *
     * @param None
     *
     * @return (Array): Return an array with the entire table
     *
     **/
    public function listFineStatusHistory()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    public function getLastStatus($fineId)
    {
        $args[] = $fineId;
        $this->setTables($this->table);
        $this->setColumns('fineStatusId');
        $this->setOrderBy('date DESC LIMIT 1');
        $this->setWhere('fineId = ?');
        $query = $this->query($this->select($args));
        return $query[0][0];
    }


    /**
     * Delete fine status history by fineId
     *
     * @param $fineId (integer)
     *
     * @return (boolean): TRUE if sucessfully deleted, otherwise FALSE
     */
    public function deleteByFine($fineId)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setWhere('fineId = ?');
        return $this->execute( $this->delete(array($fineId)) );
    }
}
?>
