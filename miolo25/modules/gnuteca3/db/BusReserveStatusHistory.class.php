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
 * This file handles the connection and actions for general reserveStatusHistory table
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
 * Class to manipulate the reserveStatusHistory table
 **/
class BusinessGnuteca3BusReserveStatusHistory extends GBusiness
{
    public $reserveId;
    public $reserveStatusId;
    public $date;
    public $operator;

    public $reserveIdS;
    public $reserveStatusIdS;
    public $dateS;
    public $operatorS;

    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->table    = 'gtcReserveStatusHistory';
        $this->colsNoId = 'reserveStatusId, date, operator';
        $this->colsId   = 'reserveId';
        $this->cols     = $this->colsId . ',' . $this->colsNoId;
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listReserveStatusHistory()
    {
        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->table);
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
    public function getReserveStatusHistory($privilegeGroupId, $linkId, $return = FALSE)
    {
        $data[] = $privilegeGroupId;
        $data[] = $linkId;
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns($this->cols);
        $sql = $this->select($data);
        $rs  = $this->query($sql, true);
        if ( !$return)
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
    public function searchReserveStatusHistory()
    {
        $this->clear();
        if ( $this->reserveId )
        {
            $this->setWhere('reserveId = ?');
            $data[] = $this->reserveId;
        }
        $this->setTables('gtcReserveStatusHistory RSH
                LEFT JOIN gtcReserveStatus        RS
                       ON (RSH.reserveStatusId = RS.reserveStatusId)');
        $this->setColumns('RS.description,
                           RSH.date,
                           RSH.operator');
        $this->setOrderBy('RSH.date');
        $sql = $this->select($data);
        $rs  = $this->query($sql);

        if ($rs)
        {
            foreach ($rs as $line => $info)
            {
                $rs[$line][1] = GDate::construct($rs[$line][1])->getDate(GDate::MASK_TIMESTAMP_USER);
            }
        }
        
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
    public function insertReserveStatusHistory()
    {
        $lastReserveStatusId = $this->getLastStatus($this->reserveId);
        if ($lastReserveStatusId != $this->reserveStatusId)
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
            return TRUE;
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
    public function updateReserveStatusHistory()
    {
        $this   ->clear();
        $this   ->setColumns($this->colsNoId);
        $this   ->setTables($this->table);
        $this   ->setWhere($this->id.' = ?');
        $data   = $this->associateData( $this->colsNoId . ',' . $this->colsId );
        $sql    = $this->update($data);
        $rs     = $this->execute($sql);
        return  $rs;
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
    public function deleteReserveStatusHistory($id)
    {
        $this       ->clear();
        $this       ->setTables($this->table);
        $this       ->setWhere($this->colsId . ' = ?');
        $sql        = $this->delete($id);
        $rs         = $this->execute($sql);
        return      $rs;
    }

    public function deleteByReserve($reserveId)
    {
        $this->clear();
        $this->setTables($this->table);
        $this       ->setWhere($this->colsId . ' = ?');
        $sql        = $this->delete($reserveId);
        $rs         = $this->execute($sql);
        return      $rs;
    }


    public function getLastStatus($reserveId)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns('reserveStatusId');
        $this->setWhere('reserveId = ?');
        $this->setOrderBy('date DESC LIMIT 1');
        $sql = $this->select(array($reserveId));
        $rs  = $this->query($sql);
        return $rs[0][0];
    }
}
?>
