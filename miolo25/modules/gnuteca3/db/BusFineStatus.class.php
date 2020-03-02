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
 * This file handles the connection and actions for busFineStatus table
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


/**
 * Class to manipulate the busFineStatus table
 **/
class BusinessGnuteca3BusFineStatus extends GBusiness
{
    public $colsNoId;

    public $fineStatusId;
    public $description;

    const FINE_STATUS_OPEN = 1;
    
    public $fineStatusIdS;
    public $descriptionS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcFineStatus';
        $this->colsNoId = 'description';
        $this->columns  = 'fineStatusId, ' . $this->colsNoId;
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listFineStatus()
    {
        $this->clear();
        $this->setColumns('fineStatusId, description');
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * @param $fineStatusId (integer): Id of the record
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getFineStatus($fineStatusId)
    {
        $data = array($fineStatusId);
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('fineStatusId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, true);
        $this->setData( $rs[0] );
        return $rs[0];
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchFineStatus()
    {
        $this->clear();

        if ($v = $this->fineStatusIdS)
        {
            $this->setWhere('fineStatusId = ?');
            $data[] = $v;
        }

        if ($v = $this->descriptionS)
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = $v . '%';
        }

        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('fineStatusId');
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
    public function insertFineStatus()
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
    public function updateFineStatus()
    {
        $data = $this->associateData( $this->colsNoId . ', fineStatusId' );

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('fineStatusId = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $fineStatusId (integer): Id of record
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteFineStatus($fineStatusId)
    {
        $data = array($fineStatusId);
        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('fineStatusId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }
    
    
    /**
     * Retorna o id do estado do exemplar "Em aberto"
     */
    public function getFineStatusOpen()
    {
    	return self::FINE_STATUS_OPEN;
    }
}

?>
