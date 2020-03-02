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
 * This file handles the connection and actions for Operation table
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
 * Class created on 04/08/2008
 *
 **/
class BusinessGnuteca3BusOperation extends GBusiness
{
    public $colsNoId;

    public $operationId;
    public $description;
    public $defineRule;

    public $operationIdS;
    public $descriptionS;
    public $defineRuleS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();

        $this->id = 'operationId';
        $this->colsNoId = 'description,
                           defineRule';
        $this->columns  = 'operationId, ' . $this->colsNoId;
        $this->tables   = 'gtcOperation';
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listOperation($defineRuleFilter = false)
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        if ($defineRuleFilter)
        {
            $this->setWhere('defineRule = true');
        }
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
    public function getOperation($operationId)
    {
        $data = array($operationId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('operationId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, true);
        $this->setData( $rs[0] );
        return $this;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchOperation()
    {
        $this->clear();

        if ( !empty($this->operationIdS) )
        {
            $this->setWhere('operationId = ?');
            $data[] = $this->operationIdS;
        }
        if ( !empty($this->descriptionS) )
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = $this->descriptionS . '%';
        }
        if ( !empty($this->defineRuleS) )
        {
            $this->setWhere('defineRule = ?');
            $data[] = $this->defineRuleS;
        }

        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('operationId');
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
    public function insertOperation()
    {
        $data = $this->associateData( $this->colsNoId );

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
    public function updateOperation()
    {
        $data = $this->associateData( $this->colsNoId . ', operationId' );

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('operationId = ?');
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
    public function deleteOperation($operationId)
    {
        $data = array($operationId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('operationId = ?');
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
    public function getOperationValues($operationId)
    {
        $data = array($operationId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('operationId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql);
        return $rs;
    }
}
?>
