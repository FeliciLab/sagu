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
 * This file handles the connection and actions for BasDocument table
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 17/08/2011
 *
 **/
class BusinessGnuteca3BusDocument extends GBusiness
{
    public $removeData;
    public $insertData;
    public $updateData;
    
    public $personId;
    public $documentTypeId;
    public $content;
    public $organ;
    public $dateExpedition;
    public $obs;
    public $cityId;
    public $isExcused;
    public $isDelivered;
    
    public $personIdS;
    public $documentTypeIdS;
    public $contentS;
    public $organS;
    public $dateExpeditionS;
    public $obsS;
    public $cityIdS;
    public $isExcusedS;
    public $isDeliveredS;

    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        
        $this->MIOLO    = MIOLO::getInstance();
        
        $this->tables   = 'basDocument';
        $this->colsNoId = 'content,organ,dateExpedition,obs,cityId,isExcused,isDelivered';
        $this->fullColumns = 'personId, documentTypeId, ' . $this->colsNoId;
    }

    
    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listDocument()
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
    public function getDocument($personId, $documentTypeId)
    {
        $this->clear();
        
        $data = array($personId, $documentTypeId);
        
        $this->setColumns( $this->fullColumns);
        $this->setTables($this->tables);
        $this->setWhere('personId = ? AND documentTypeId = ?');
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
    public function searchDocument($toObject = false, $orderBy = NULL)
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
        
        if ( !empty($this->documentTypeIdS) )
        {
            $this->setWhere('documentTypeId = ?');
            $data[] = $this->documentTypeIdS;
        }
        
        if ( !empty($this->contentS) )
        {
            $this->setWhere('lower(content) like lower(?)');
            $data[] = $this->contentS . '%';
        }
        
        if ( !empty($this->organS) )
        {
            $this->setWhere('organ = ?');
            $data[] = $this->organS;
        }
        
        if ( !empty($this->dateExpeditionS) )
        {
            $this->setWhere('dateExpedition = ?');
            $data[] = $this->dateExpeditionS;
        }
        
        if ( !empty($this->obsS) )
        {
            $this->setWhere('lower(obs) like lower(?)');
            $data[] = $this->obsS . '%';
        }

        if ( !empty($this->cityIdS) )
        {
            $this->setWhere('cityid = ?');
            $data[] = $this->cityIdS . '%';
        }        

        if ( !empty($this->isExcusedS) )
        {
            $this->setWhere('isExcused = ?');
            $data[] = $this->isExcusedS . '%';
        }

        if ( !empty($this->isDeliveredS) )
        {
            $this->setWhere('isDelivered = ?');
            $data[] = $this->isDeliveredS . '%';
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
    public function insertDocument()
    {
        $data = $this->associateData($this->fullColumns);
        
        $this->clear();
        
        $this->setColumns($this->fullColumns);
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
    public function updateDocument()
    {
        if ( !$this->personId || !$this->documentTypeId )
        {
            return false;
        }
        
        if ( $this->removeData )
        {
            return $this->deleteDocument($this->personId, $this->documentTypeId);
        }
        elseif ( $this->insertData )
        {
            return $this->insertDocument();
        }
        else
        {
            $data = $this->associateData($this->colsNoId);
            $data[] = $this->personId;
            $data[] = $this->documentTypeId;
            
            $this->clear();
            $this->setColumns( $this->colsNoId );
            $this->setTables($this->tables);
            $this->setWhere('personId = ? AND documentTypeId = ?');
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
    public function deleteDocument( $personId , $documentTypeId = null )
    {
        $this->clear();

        if ( is_null($personId) )
        {
        	return false;
        }
        
        $data = array($personId);

        $this->setWhere("personId = ? ");
        $data = array($personId);
        
        if ( $documentTypeId )
        {
            $this->setWhere("documentTypeId = ?");
            $data[] = $documentTypeId;
        }
        
        $this->setTables($this->tables);

        return $this->execute( $this->delete($data) );
    }
    
    /**
     * Método reescrito para limpar atributos
     * @param object stdClass $data 
     */
    public function setData($data)
    {
         $this->personId =
         $this->documentTypeId =
         $this->content =
         $this->organ = 
         $this->dateExpedition =
         $this->cityId =
         $this->isExcused =
         $this->isDelivered =
         $this->obs = null;
         
         parent::setData($data);
    }
    
}
?>
