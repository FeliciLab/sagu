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
 * Return type business
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
 **/
class BusinessGnuteca3BusReturnType extends GBusiness
{
   // public $colsNoId;

    public $returnTypeId;
    public $description;
    public $sendMailReturnReceipt;
    public $returnTypeIdS;
    public $typeIdS;
    public $descriptionS;
    public $sendMailReturnReceiptS;


    public function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcReturnType';
        $this->colsNoId = 'description,sendMailReturnReceipt';
        $this->id = 'returnTypeId';
        $this->columns  = $this->id . ', ' . $this->colsNoId;

    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listReturnType($object=FALSE)
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('returnTypeId');
        $sql = $this->select();
        $rs  = $this->query($sql, $object);
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
    public function getReturnType($returnTypeId)
    {
        $data = array($returnTypeId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('returnTypeId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject=true);
        $this->setData($rs[0]);

        return $this;
    }

    /**
     * @param int $returnTypeId
     * @return string $description
     *
     * Função retorna a descrição do tipo de retorno de material.
     * Function returns the description of loan return type.
     */

    public function getReturnTypeDescription($returnTypeId)
    {
        $data = array($returnTypeId);
        $this->clear();
        $this->setColumns('description');
        $this->setTables($this->tables);
        $this->setWhere('returnTypeId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject=true);

        return $rs[0]->description;
    }
    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchReturnType()
    {
        $this->clear();

        if ( $this->typeIdS )
        {
            $this->setWhere('returnTypeId = ?');
            $data[] = $this->typeIdS;
        }

        if ( $this->returnTypeIdS )
        {
            $this->setWhere('returnTypeId = ?');
            $data[] = $this->returnTypeIdS;
        }

        if ( $this->descriptionS )
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = $this->descriptionS . '%';
        }

        if ( $this->sendMailReturnReceiptS )
        {
            $this->setWhere('sendMailReturnReceipt = ?');
            $data[] = $this->sendMailReturnReceiptS;
        }
        
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('returnTypeId');

        return $this->query( $this->select($data) );
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertReturnType()
    {
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $data= array($this->description, $this->sendMailReturnReceipt);
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
    public function updateReturnType()
    {
        $data= array($this->description,
                     $this->sendMailReturnReceipt,
                     $this->returnTypeId);
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('returnTypeId = ?');
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
    public function deleteReturnType($returnTypeId)
    {
        $data = array($returnTypeId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('returnTypeId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);

        return $rs;
    }

}
?>
