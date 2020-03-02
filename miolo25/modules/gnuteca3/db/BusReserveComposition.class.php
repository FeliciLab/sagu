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
 * This file handles the connection and actions for reserveComposition table
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
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
 * Class to manipulate the reserveComposition table
 **/
class BusinessGnuteca3BusReserveComposition extends GBusiness
{
    public $reserveId;
    public $itemNumber;
    public $isConfirmed;

    public $reserveIdS;
    public $itemNumberS;
    public $isConfirmedS;

    public $module;
    public $removeData; //used to detect if is to delete or not


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->table    = 'gtcReserveComposition';
        $this->cols     = 'reserveId, itemNumber, isConfirmed';
        $this->module   = MIOLO::getInstance()->getCurrentModule();
    }


    /**
     * Return a specific record from the database
     *
     * @param $parameter (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getReserveComposition( $extraInfo = false )
    {
        $this->clear();
        
        $columns = $this->cols;
        
        if ( $extraInfo )
        {
            $columns = '        	
        	reserveId,
            RC.itemNumber,
			isConfirmed,
			controlNumber,
			MT.materialTypeId,
			MT.description as materialType,
			MG.materialGenderId,
			MG.description as materialGender,
			EC.exemplaryStatusId';
        }
        
        $this->setColumns( $columns );
        
        $tables = $this->table;
        
        if ( $extraInfo)
        {
        	
            $tables =' gtcReserveComposition RC
             LEFT JOIN gtcExemplaryControl EC
                    ON (RC.itemNumber = EC.itemNumber)
             LEFT JOIN gtcMaterialType MT
                    ON (MT.materialTypeId = EC.materialTypeId)
             LEFT JOIN gtcMaterialGender MG
                    ON (MG.materialGenderId = EC.materialGenderId)
                    ';
            $this->setOrderBy('isConfirmed desc');
        }
        
        $this->setTables($tables);
        
        if ($this->reserveId)
        {
        	if ( is_array( $this->reserveId ) )
        	{
                $this->setWhere('reserveId in (' . implode(',', $this->reserveId ) .')') ;
        	}
        	else
        	{
        		$this->setWhere("reserveId = ?");
                $args[] = $this->reserveId;
        	}
        }
        else
        {
        	return false; // para não retornar todas as reservas, pode ser pesado e dar acesso ao usuário a informações que não são dele
        }

        if ($this->itemNumber)
        {
            
            //Existe RC. somente quando mostra informacoes extras ($extraInfo).
            if ( $extraInfo )
            {
               $where = 'RC.itemnumber = ?'; 
            }
            else
            {
               $where = 'itemnumber = ?'; 
            }
            
            $this->setWhere($where);
            $args[] = $this->itemNumber;
        }

        $sql = $this->select($args);
        $rs  = $this->query($sql,true);

        if ($rs)
        {
            foreach ($rs as $line =>$info)
            {
                $rs[$line]->isConfirmedLabel = $info->isConfirmed == 't' ? _M('Sim', $this->module) : _M('Não', $this->module);
            }
        }

        return $rs;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchReserveComposition($toObject = FALSE)
    {
        $this->clear();
        if ( $this->reserveIdS )
        {
            $this->setWhere('reserveId = ?');
            $data[] = $this->reserveIdS;
        }

        if ( $this->itemNumberS )
        {
            $this->setWhere('itemNumber = ?');
            $data[] = $this->itemNumberS;
        }

        if ( $this->isConfirmedS )
        {
            $this->setWhere('isConfirmed = ?');
            $data[] = $this->isConfirmedS;
        }

        $this->setTables('    gtcReserveComposition ');
        $this->setColumns(' reserveId,
                            itemNumber,
                            isConfirmed');
        $sql = $this->select($data);
        $rs  = $this->query($sql, ($toObject) ? TRUE : FALSE);
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
    public function insertReserveComposition()
    {
        if ($this->reserveId && $this->itemNumber && $this->isConfirmed)
        {
            $this->clear();
            $this->setColumns($this->cols);
            $this->setTables($this->table);
            $sql = $this->insert( $this->associateData($this->cols) );
            $rs  = $this->execute($sql);
            return $rs;
        }
        else
        {
            return $ok;
        }
    }

    
    public function updateComposition()
    {
        if ($this->reserveId && $this->itemNumber && $this->isConfirmed)
        {
            $this   ->clear();
            $this   ->setColumns('isConfirmed');
            $this   ->setTables($this->table);
            $this   ->setWhere('reserveId = ?');
            $this   ->setWhere('itemNumber = ?');
            $data   = $this->associateData('isConfirmed,reserveId, itemNumber');
            $sql    = $this->update($data);
            $rs     = $this->execute($sql);
            $rs     = true;
            return  $rs;
        }
        else
        {
            return false;
        }
    }
    

    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateReserveComposition()
    {
        $temp   = $this->getReserveComposition();
        if ($temp)
        {
            if ($this->removeData)
            {
                $this->deleteReserveComposition();
            }
            else
            {
                $this->_updateReserveComposition();
            }
        }
        else
        {
            $this->insertReserveComposition();
        }
    }


    /**
     * Delete a record
     *
     * @param $parameter (string): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteReserveComposition($reserveId, $itemNumber=null)
    {
        $this       ->clear();
        $this       ->setTables($this->table);
        $data[]     = $reserveId;
        $this       ->setWhere('reserveId = ?');
        if ($itemNumber)
        {
            $data[]     = $itemNumber;
            $this       ->setWhere('itemNumber = ?');
        }
        $sql        = $this->delete($data);
        $rs         = $this->execute($sql);

        return      $rs;
    }
}
?>