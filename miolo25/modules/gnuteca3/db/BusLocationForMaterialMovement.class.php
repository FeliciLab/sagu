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
 * This file handles the connection and actions for gtcLocationForMaterialMovement table
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
 * Class created on 20/08/2008
 *
 **/
class BusinessGnuteca3BusLocationForMaterialMovement extends GBusiness
{
    public $colsNoId;

    public $locationForMaterialMovementId;
    public $description;
    public $observation;
    public $sendLoanReceiptByEmail;
    public $sendRenewReceiptByEmail;
    public $sendReturnReceiptByEmail;

    public $locationForMaterialMovementIdS;
    public $descriptionS;
    public $observationS;
    public $sendLoanReceiptByEmailS;
    public $sendRenewReceiptByEmailS;
    public $sendReturnReceiptByEmailS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcLocationForMaterialMovement';
        $this->colsNoId = 'description,
                           observation,
                           sendLoanReceiptByEmail,
                           sendRenewReceiptByEmail,
                           sendReturnReceiptByEmail';
        $this->id = $this->colsNoId;
        $this->columns  = 'locationForMaterialMovementId, ' . $this->colsNoId;
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listLocationForMaterialMovement()
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * @param $id (integer): Id of register
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getLocationForMaterialMovement($locationForMaterialMovementId, $return = FALSE)
    {
        $data[] = $locationForMaterialMovementId;
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('locationForMaterialMovementId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, true);
        if (!$return)
        {
        	$this->setData( $rs[0] );
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
    public function searchLocationForMaterialMovement()
    {
        $this->clear();

        if ( $v = $this->locationForMaterialMovementIdS )
        {
            $this->setWhere('locationForMaterialMovementId = ?');
            $data[] = $v;
        }
        if ( $v = $this->descriptionS )
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = '%' . $v . '%';
        }
        if ( $v = $this->observationS )
        {
            $this->setWhere('lower(observation) LIKE lower(?)');
            $data[] = '%' . $v . '%';
        }
        if ( $v = $this->sendLoanReceiptByEmailS )
        {
            $this->setWhere('sendLoanReceiptByEmail = ?');
            $data[] = $v;
        }
        if ( $v = $this->sendRenewReceiptByEmailS )
        {
            $this->setWhere('sendRenewReceiptByEmail = ?');
            $data[] = $v;
        }
        if ( $v = $this->sendReturnReceiptByEmailS )
        {
            $this->setWhere('sendReturnReceiptByEmail = ?');
            $data[] = $v;
        }

        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('locationForMaterialMovementId');
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
    public function insertLocationForMaterialMovement()
    {
        $data = $this->associateData($this->colsNoId);

        $this->setTables($this->tables);
        $this->setColumns($this->colsNoId);
        $sql    = $this->insert($data);
        $status = $this->execute($sql);
        return $status;
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateLocationForMaterialMovement()
    {
        $data = $this->associateData( $this->colsNoId . ', locationForMaterialMovementId' );

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('locationForMaterialMovementId = ?');
        $sql    = $this->update($data);
        $status = $this->execute($sql);
        return $status;
    }


    /**
     * Delete a record
     *
     * @param $id (integer): Id for delete
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteLocationForMaterialMovement($locationForMaterialMovementId)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('locationForMaterialMovementId = ?');
        $sql    = $this->delete(array($locationForMaterialMovementId));
        $status = $this->execute($sql);
        return $status;
    }


    /**
     * Get constants for a specified module
     *
     * @param $id (integer): Id
     *
     * @return (array): An array of key pair values
     *
     **/
    public function getLocationForMaterialMovementValues($locationForMaterialMovementId)
    {
        $data = array($locationForMaterialMovementId);
        $this->clear();
        $this->setColumns('locationForMaterialMovementId,
                           description');
        $this->setTables($this->tables);
        $this->setWhere('locationForMaterialMovementId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql);
        return $rs;
    }
}
?>
