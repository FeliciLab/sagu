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
 * Return register business
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini   [eduardo@solis.coop.br]
 * Jamiel Spezia        [jamiel@solis.coop.br]
 * Luiz Gregory Filho   [luiz@solis.coop.br]
 * Moises Heberle       [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 25/05/2009
 *
 * */
class BusinessGnuteca3BusReturnRegister extends GBusiness
{

    // public $colsNoId;

    public $returnRegisterId;
    public $returnTypeId;
    public $itemNumber;
    public $date;
    public $operator;
    public $returnRegisterIdS;
    public $returnTypeIdS;
    public $itemNumberS;
    public $dateS;
    public $beginDateS;
    public $endDateS;
    public $operatorS;

    public function __construct()
    {
        parent::__construct();
        $this->tables = 'gtcReturnRegister';
        $this->colsNoId = 'returnTypeId,
                           itemNumber,
                           date,
                           operator';
        $this->columns = 'returnRegisterId, ' . $this->colsNoId;
    }

    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     * */
    public function listReturnRegister($object = FALSE)
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('returnRegisterId');
        $sql = $this->select();
        $rs = $this->query($sql, $object);
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
     * */
    public function getReturnRegister($returnRegisterId, $toObj = FALSE)
    {
        $data = array($returnRegisterId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('returnRegisterId = ?');
        $sql = $this->select($data);
        $rs = $this->query($sql, $toObject = true);
        $this->setData($rs[0]);
        
        if($toObj)
        {
            return $rs[0];
        }

        return $this;
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     * */
    public function searchReturnRegister()
    {
        $this->clear();

        if ($v = $this->returnRegisterIdS)
        {
            $this->setWhere('A.returnRegisterId = ?');
            $data[] = $v;
        }
        if ($v = $this->returnTypeIdS)
        {
            $this->setWhere('A.returnTypeId = ?');
            $data[] = $v;
        }
        if ($v = $this->itemNumberS)
        {
            $this->setWhere('A.itemNumber = ?');
            $data[] = $v;
        }

        if ($this->dateS)
        {
            $this->setWhere('date(A.date) = ?');
            $data[] = $this->dateS;
        }
        if ($this->beginDateS)
        {
            $this->setWhere('date(A.date) >= ?');
            $data[] = $this->beginDateS;
        }
        if ($this->endDateS)
        {
            $this->setWhere('date(A.date) <= ?');
            $data[] = $this->endDateS;
        }

        if ($v = $this->operatorS)
        {
            $this->setWhere('lower(A.operator) LIKE lower(?)');
            $data[] = $v . '%';
        }
        $this->setTables('gtcReturnRegister  A
                LEFT JOIN gtcReturnType      B
                       ON (A.returnTypeId = B.returnTypeId)');
        $this->setColumns(' A.returnRegisterId,
                            A.returnTypeId,
                            B.description,
                            A.itemNumber,
                            A.date,
                            A.operator');
        $this->setOrderBy('returnRegisterId');
        $sql = $this->select($data);
        $rs = $this->query($sql);

        return $rs;
    }

    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     * */
    public function insertReturnRegister()
    {
        if (!$material = $this->getItemNumber($this->itemNumber))
        {
            throw new Exception(_M("O número de exemplar {$this->itemNumber} não existe."));
        }
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $data = array($this->returnTypeId,
            $this->itemNumber,
            $this->date,
            $this->operator);
        $sql = $this->insert($data);
        $rs = $this->query($sql . ' RETURNING returnRegisterId');
        $this->returnRegisterId = $rs[0][0];
        $this->returnRegisterIdS = $rs[0][0];
        
        return $rs;
    }

    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     * */
    public function updateReturnRegister()
    {
        if (!$material = $this->getItemNumber($this->itemNumber))
        {
            throw new Exception(_M("O número de exemplar {$this->itemNumber} não existe."));
        }
        $data = array($this->returnTypeId,
            $this->itemNumber,
            $this->date,
            $this->operator,
            $this->returnRegisterId);
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('returnRegisterId = ?');
        $sql = $this->update($data);
        $rs = $this->execute($sql);

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
     * */
    public function deleteReturnRegister($returnRegisterId)
    {
        $data = array($returnRegisterId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('returnRegisterId = ?');
        $sql = $this->delete($data);
        $rs = $this->execute($sql);

        return $rs;
    }
    
     public function getItemNumber($itemNumber)
    {
        $busMateriaControl = $this->MIOLO->getBusiness($this->module, 'BusMaterialControl');
        $busMateriaControl->setColumns('itemNumber');
        $busMateriaControl->setTables('gtcexemplarycontrol');
        $busMateriaControl->setWhere('itemNumber = ?');
        $args[] = $itemNumber;
        $sql = $busMateriaControl->select($args);
        if (!$sql)
        {
            return null;
        }
        $rs = $busMateriaControl->query($sql, true);
        return $rs;
    }

}

?>
