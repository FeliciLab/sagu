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
 * This file handles the connection and actions for basPhone table
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 12/11/2010
 *
 **/
class BusinessGnuteca3BusPhone extends GBusiness
{
    public $removeData;
    public $insertData;
    public $updateData;
    
    public $personId;
    public $type;
    public $phone;
    public $phoneId;
    
    public $personIdS;
    public $typeS;
    public $phoneS;
    public $phoneIdS;

    function __construct()
    {
        parent::__construct();
        $this->MIOLO    = MIOLO::getInstance();
        $this->tables   = 'basPhone';
        $this->colsNoId = 'personId, type,phone';
        $this->fullColumns = 'phoneId,' . $this->colsNoId;
    }
    
    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listPhone()
    {
        $this->clear();

        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs = $this->query($sql);
        
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
    public function getPhone($phoneId)
    {
        $this->clear();

        if ( empty($phoneId) )
        {
            return false;
        }
        
        $data = array($phoneId);
        
        $this->setColumns( $this->fullColumns);
        $this->setTables($this->tables);
        $this->setWhere('phoneId = ?');
        $sql = $this->select($data);

        $rs = $this->query($sql, TRUE);
        
        if ( $rs )
        {
            $this->setData($rs[0]);
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
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchPhone($toObject = false, $orderBy = NULL)
    {
        $this->clear();

        //ordenação padrão
        if ( !$orderBy )
        {
            $orderBy = 'personId';
        }
        
        if ( !empty($this->personIdS) )
        {
            $this->setWhere('personId = ?');
            $data[] = $this->personIdS;
        }
        if ( !empty($this->typeS) )
        {
            $this->setWhere('type = ?');
            $data[] = $this->typeS;
        }
        if ( !empty($this->phoneS) )
        {
            $this->setWhere('phone = ?');
            $data[] = $this->phoneS;
        }
        if ( !empty($this->phoneIdS) )
        {
            $this->setWhere('phoneId = ?');
            $data[] = $this->phoneIdS;
        }
        
        $this->setColumns( $this->fullColumns );
        $this->setTables($this->tables);
        $this->setOrderBy($orderBy);
        $sql = $this->select($data);
        $rs = $this->query($sql, $toObject);

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
    public function insertPhone()
    {
        if ( !$this->personId || !$this->type || !$this->phone )
        {
            return false;
        }

        $data = array( $this->personId, $this->type, $this->phone);
        $this->clear();

        $this->setColumns('personId,type,phone');
        $this->setTables($this->tables);
        $sql = $this->insert($data);
        $rs = $this->execute($sql);
        
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
    public function updatePhone()
    {
        //Se não for insert e não tiver phoneId e faltar algum outro atributo
        if ( (!$this->phoneId && !$this->insertData) || !$this->personId || !$this->type || !$this->phone )
        {
            //Não pode fazer update ou remover
            return false;
        }
        
        if ( $this->removeData )
        {
            return $this->deletePhone($this->personId, $this->phoneId, $this->type);
        }
        elseif ( $this->insertData )
        {
            return $this->insertPhone();
        }
        else
        {
            $data = array($this->phone, $this->phoneId, $this->personId, $this->type);
            $this->clear();
            $this->setColumns( 'phone');
            $this->setTables($this->tables);
            $this->setWhere('phoneId = ? AND personId = ? AND type = ?');
            $sql = $this->update($data);
            $rs = $this->execute($sql);
            
            return $rs;
        }
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
    public function deletePhone( $personId, $phoneId = null, $type = null )
    {
        $this->clear();

        $data = null;

        if ( is_null($personId) )
        {
        	return false;
        }
        else
        {
            $this->setWhere("personId = ? ");
            $data[] = $personId;            
        }

        if ( $phoneId )
        {        
            $this->setWhere("phoneId = ? ");
            $data[] = $phoneId;
        }
        
        if ( $type )
        {
            $this->setWhere("type = ? ");
            $data[] = $type;
        }
        
        $this->setTables($this->tables);
        
        return $this->execute( $this->delete($data) );
    }
}
?>