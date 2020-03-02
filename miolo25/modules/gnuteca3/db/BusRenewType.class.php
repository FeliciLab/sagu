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
 * This file handles the connection and actions for gtcRenewType table
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
 * Class created on 29/08/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusRenewType extends GBusiness
{
    public $pkeys;
    public $columns;
    public $fullColumns;

    public $renewTypeId;
    public $description;

    public $renewTypeIdS;
    public $descriptionS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcRenewType';
        $this->pkeys    = 'renewTypeId';
        $this->columns  = 'description';
        $this->fullColumns = $this->pkeys . ',' . $this->columns;
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return TRUE if succed, otherwise FALSE
     *
     **/
    public function insertRenewType()
    {
        $data = $this->associateData( $this->columns );

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->insert($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function updateRenewType()
    {
        $data = $this->associateData( $this->columns . ',' . $this->pkeys );

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('renewTypeId = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $renewTypeId (integer)
     *
     * @return (boolean) TRUE if succeed, otherwise FALSE
     *
     **/
    public function deleteRenewType($renewTypeId)
    {
        $data[] = $renewTypeId;

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('renewTypeId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * @param $moduleConfig (integer): Primary key of the record to be retrieved
     * @param $parameter (integer): Primary key of the record to be retrieved
     *
     * @return (Object): Return an object of the type handled by the class
     *
     **/
    public function getRenewType($renewTypeId)
    {
        $data[] = $renewTypeId;

        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $this->setWhere('renewTypeId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);
        $this->setData($rs[0]);
        return $this;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @return (Array) An array containing the search results
     **/
    public function searchRenewType()
    {
        $this->clear();

        if ($this->renewTypeIdS)
        {
            $this->setWhere('renewTypeId = ?');
            $data[] = $this->renewTypeIdS;
        }
        if ($this->description)
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = '%' . $this->description . '%';
        }

        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $this->setOrderBy('renewTypeId');
        $sql = $this->select($data);
        $rs  = $this->query($sql);
        return $rs;
    }


    /**
     * List all records from the table handled by the class
     *
     * @return (Array) Return an array with the entire table
     **/
    public function listRenewType()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }
}
?>
