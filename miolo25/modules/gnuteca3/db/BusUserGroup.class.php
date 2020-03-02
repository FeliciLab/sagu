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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/
class BusinessGnuteca3BusUserGroup extends GBusiness
{
    public $linkId;
    public $description;
    public $level;
    public $isVisibleToPerson;
    public $isOperator;

    public $linkIdS;
    public $descriptionS;
    public $levelS;
    public $isVisibleToPersonS;
    public $isOperatorS;

    public $busGeneralPolicy;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->colsNoId = 'description,
                           level,
                           isVisibleToPerson,
                           isOperator';
        $this->id = 'linkId';
        $this->columns  = 'linkId, ' . $this->colsNoId;
        $this->tables   = 'baslink';

        $this->busGeneralPolicy   = $this->MIOLO->getBusiness($this->module, 'BusGeneralPolicy');
    }


    /**
     * List all records from the table handled by the class
     *
     * @DEPRECATED usar o método BusBond::listBond() 
     * 
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listUserGroup($forCataloge = false)
    {
        //return 'Not implemented';
        $data = array();

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select($data);
        $rs  = $this->query($sql, $forCataloge);

        if(!$forCataloge || !$rs)
        {
            return $rs;
        }

        foreach ($rs as $i => $v)
        {
            $r[$i]->option      = $v->linkId;
            $r[$i]->description = $v->description;
        }

        return $r;
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
    public function getUserGroup($linkId)
    {
        $data = array($linkId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('linkId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, true);
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
    public function searchUserGroup()
    {
        $this->clear();

        if ( $v = $this->linkIdS )
        {
            $this->setWhere('linkId = ?');
            $data[] = $v;
        }
        if ( $v = $this->descriptionS )
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = '%' . $v . '%';
        }
        if ( $v = $this->levelS )
        {
            $this->setWhere('level = ?');
            $data[] = $v;
        }
        if ( $this->isVisibleToPersonS )
        {
        	$this->setWhere('isVisibleToPerson = ?');
        	$data[] = $this->isVisibleToPersonS;
        }
        
        if ( $this->isOperatorS )
        {
        	$this->setWhere('isOperator = ?');
        	$data[] = $this->isOperatorS;
        }

        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('linkId');
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
    public function insertUserGroup()
    {
        $data = array(
            $this->description,
            $this->level,
            $this->isVisibleToPerson,
            $this->isOperator
        );

        $this->clear();
        $this->setColumns($this->colsNoId);
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
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateUserGroup()
    {
        $data = array(
            $this->description,
            $this->level,
            $this->isVisibleToPerson,
            $this->isOperator,
            $this->linkId
        );

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('linkId = ?');
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
    public function deleteUserGroup($linkId)
    {
    	$this->busGeneralPolicy->deleteGeneralPolicy($linkId);

        $this->clear();

        $tables  = 'baslink';
        $where   = 'linkId = ?';
        $data = array($linkId);

        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
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
    public function getModuleValues($moduleConfig)
    {
        $this->clear();

        $columns = 'A.parameter,
                    A.value';
        $tables  = 'basConfig A';

        $where   = 'A.moduleConfig = ?';
        $data    = array(strtoupper($moduleConfig));

        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->select($data);
        $rs  = $this->query($sql);

        return $rs;
    }
}
?>
