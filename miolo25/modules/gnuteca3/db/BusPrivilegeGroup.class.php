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
 * This file handles the connection and actions for Privilege Group table
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
 * Class created on 30/07/2008
 *
 **/


/**
 * Class to manipulate the privilege group table
 **/
class BusinessGnuteca3BusPrivilegeGroup extends GBusiness
{
    public $MIOLO;
    public $db;
    public $MSQL;

    public $privilegeGroupId;
    public $description;

    public $privilegeGroupIdS;
    public $descriptionS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcPrivilegeGroup';
        $this->colsNoId = 'description';
        $this->id = 'privilegeGroupId';
        $this->columns  = 'privilegeGroupId, ' . $this->colsNoId;
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listPrivilegeGroup()
    {
        $this->clear();
        $data = array();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select($data);
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
    public function getPrivilegeGroup($privilegeGroupId, $return = FALSE)
    {
        $data = array($privilegeGroupId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('privilegeGroupId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject=true);
        if (!$return)
        {
        	$this->setData($rs[0]);
        	return $this;
        }
        else
        {
        	return $rs[0];
        }
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchPrivilegeGroup()
    {
        $this->clear();

        if ( $v = $this->privilegeGroupIdS )
        {
            $this->setWhere('privilegeGroupId = ?');
            $data[] = $v;
        }
        if ( $v = $this->descriptionS )
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = $v . '%';
        }

        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('privilegegroupid');
        $sql = $this->select($data);
        $rs  = $this->query($sql);

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
    public function insertPrivilegeGroup()
    {
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $sql = $this->insert( $this->associateData($this->colsNoId) );
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
    public function updatePrivilegeGroup()
    {
        $data = $this->associateData( $this->colsNoId . ', privilegeGroupId' );

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('privilegeGroupId = ?');
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
    public function deletePrivilegeGroup($privilegeGroupId)
    {
        $data = array($privilegeGroupId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('privilegeGroupId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);

        return $rs;
    }


    /**
     * Get constants for a specified module
     *
     * @param $moduleConfig (string): Name of the module to load values from
     *
     * @return (array): An array of key pair values
     *
     **/
    public function getPrivilegeGroupValues($privilegeGroupId)
    {
        $data = array($privilegeGroupId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('privilegegroupid = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql);

        return $rs;
    }
}
?>
