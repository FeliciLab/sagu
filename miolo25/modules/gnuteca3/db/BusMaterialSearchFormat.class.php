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
 * This file handles the connection and actions for gtcmaterialsearchformat table
 *
 * @author Jader Fiegenbaum [jader@solis.com.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Jader Fiegenbaum [jader@solis.com.br]
 *
 * @since
 * Class created on 30/04/2014
 *
 **/
class BusinessGnuteca3BusMaterialSearchFormat extends GBusiness
{
    public $MIOLO;
    public $module;
    public $colsNoId;
    
    public $controlNumber;
    public $searchFormatId;
    public $searchFormat;
    public $detailFormat;
    public $date;
    
    public $controlNumberS;
    public $searchFormatIdS;
    public $searchFormatS;
    public $detailFormatS;
    public $dateS;
    
    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->MIOLO  = MIOLO::getInstance();
        $this->tables   = 'gtcMaterialSearchFormat';
        $this->id       = 'controlNumber, searchFormatId';
        $this->colsNoId = 'searchFormat, detailFormat, date';
        $this->columns  = $this->id . ',' . $this->colsNoId;
     }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listMaterialSearchFormat()
    {
        $this->clear();

        $this->setColumns('controlnumber, searchformatid, detailformat, date');

        $this->setTables($this->tables);

        $this->setOrderBy($this->id);
        $sql = $this->select();
        $rs  = $this->query($sql);
        
        return $r;
    }

    public function getMaterialSearchFormatString($controlNumber, $searchFormatId, $type = 'search')
    {
        $format = $this->getMaterialSearchFormat($controlNumber, $searchFormatId);
        
        $return = '';
        
        if ( $format )
        {
            if ( $type == 'search' )
            {
                $return = $format->searchFormat;
            }
            else
            {
                $return = $format->detailFormat;
            }
        }
        
        return $return;
    }
    
    
    /**
     * Return a specific record from the database
     *
     * @param $controlNumber (integer): Primary key of the record to be retrieved
     * @param $searchFormatId (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getMaterialSearchFormat($controlNumber, $searchFormatId)
    {
        if ( !$controlNumber || !$searchFormatId )
        {
            return NULL;
        }
        
        $data = array($controlNumber, $searchFormatId );
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('controlnumber = ? AND searchformatid = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject=true);

        $data = null;
        
        if ($rs[0])
        {
            $data= $rs[0];
            $this->setData($data);	
        }

        return $this;
    }

   
    /**
     * Do a search on the database table handled by the class
     *
     * @return (array): An array containing the search results
     **/
    public function searchMaterialSearchFormat()
    {
        $this->clear();

        if ( $v = $this->controlNumberS )
        {
            $this->setWhere('controlNumber = ?' );
            $data[] = $this->controlNumberS;
        }
        
        if ( $v = $this->searchFormatIdS )
        {
            $this->setWhere('searchFormatId = ?' );
            $data[] = $this->searchFormatIdS;
        }
        
        if ( $v = $this->searchFormatS )
        {
            $this->setWhere('searchFormat = ?' );
            $data[] = $this->searchFormatS;
        }
        
        if ( $v = $this->detailFormatS )
        {
            $this->setWhere('detailFormat = ?' );
            $data[] = $this->detailFormatS;
        }
        
        if ( $v = $this->dateS )
        {
            $this->setWhere('date = ?' );
            $data[] = $this->dateS;
        }
        
        $columns = 'controlnumber, searchformatid, searchformat, detailformat, date';

        $this->setColumns($columns);
        $this->setTables($this->tables);
        $this->setOrderBy('searchformatid, controlnumber');
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
    public function insertMaterialSearchFormat()
    {
        $this->date = date(GDate::MASK_TIMESTAMP_USER);
        
        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns($this->columns);
        $sql = $this->insert( $this->associateData($this->columns) );
        $rs  = $this->query($sql);

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
    public function updateMaterialSearchFormat()
    {
        $this->date = date(GDate::MASK_TIMESTAMP_USER);
        $data = $this->associateData( $this->colsNoId . ', controlNumber, searchFormatId' );

        $this->clear();
        $this->setWhere('controlnumber = ?');
        $this->setWhere('searchformatid = ?');
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
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
    public function deleteMaterialSearchFormat($controlNumber, $searchFormatId)
    {
        $data = array($controlNumber, $searchFormatId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('controlnumber = ?');
        $this->setWhere('searchFormatId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }
    
    
    /**
     * Método para excluir todos formatos de pesquisa de um determinado número de controle.
     * 
     * @param int $controlNumber Número de controle.
     * @return boolean Retorno positivo caso tenha excluído com sucesso.
     */
    public function deleteAllSearchFormatForControlNumber($controlNumber)
    {
        $rs = FALSE;
        
        if ( $controlNumber )
        {
            $data = array($controlNumber);

            $this->clear();
            $this->setTables($this->tables);
            $this->setWhere('controlnumber = ?');
            $sql = $this->delete($data);
            $rs  = $this->execute($sql);
        }
        
        return $rs;
    }
    
    
    /**
     * Método para excluir todos formatos de pesquisa de um determinado formato de pesquisa.
     * 
     * @param int $searchFormatId Formato de pesquisa.
     * @return boolean Retorno positivo caso tenha excluído com sucesso.
     */
    public function deleteAllSearchFormatForSearchFormat($searchFormatId)
    {
        $rs = FALSE;
        
        if ( $searchFormatId )
        {
            $data = array($searchFormatId);

            $this->clear();
            $this->setTables($this->tables);
            $this->setWhere('searchformatid = ?');
            $sql = $this->delete($data);
            $rs  = $this->execute($sql);
        }
        
        return $rs;
    }
    
    /**
     * Atualiza o cache de um formato de pesquisa específico.
     * 
     * @param int $searchFormatId Format de pesquisa.
     * @param date $date Data que será utilizada para ser comparada com a data do cache.
     * @return boolean Retorna positivo caso tenha conseguido atualizar o cache com sucesso.
     */
    public function updateCacheOfSearchFormat($searchFormatId, $date)
    {
        $this->clear();
        
        $this->setColumns('controlnumber');
        $this->setTables('gtcMaterialControl A');
        $this->setWhere("A.controlNumber NOT IN (SELECT controlnumber FROM gtcMaterialSearchFormat WHERE searchFormatId = ? AND date > ?)");
        $sql = $this->select(array($searchFormatId, $date));
        
        $result = $this->query($sql, TRUE);
        
        if ( is_array($result) )
        {
            $MIOLO = MIOLO::getInstance();
            $busSearchFormat = $MIOLO->getBusiness('gnuteca3', 'BusSearchFormat');
            
            foreach ( $result as $value )
            {
                $busSearchFormat->getFormatedString($value->controlnumber, $searchFormatId);
            }
        }
        
        return true;
    }
    
    
    /**
     *  Atualiza todos formatos de pesquisa de um material.
     * 
     * @param int $controlNumber Número de controle do material.
     */
    public function updateAllSearchFormatOfMaterial($controlNumber)
    {
        $MIOLO = MIOLO::getInstance();
        $busSearchFormat = $MIOLO->getBusiness('gnuteca3', 'BusSearchFormat');
            
        $searchFormat = $busSearchFormat->listSearchFormat();
        
        if ( is_array($searchFormat) )
        {
            foreach ( $searchFormat as $k => $value )
            {
                $busSearchFormat->getFormatedString($controlNumber, $value[0]);
            }
        }
    }

}

?>