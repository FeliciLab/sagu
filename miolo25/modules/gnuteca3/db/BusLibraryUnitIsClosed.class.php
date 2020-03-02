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
 * This file handles the connection and actions for gtcLibraryUnitIsClosed table
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
 * Class created on 18/09/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusLibraryUnitIsClosed extends GBusiness
{
    public $table;
    public $pkeys;
    public $columns;
    public $fullColumns;

    public $libraryUnitId;
    public $weekDayId;

    public $libraryUnitIdS;
    public $weekDayIdS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->table   = 'gtcLibraryUnitIsClosed';
        $this->pkeys   = 'libraryUnitId,
                          weekDayId';
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertLibraryUnitIsClosed()
    {
    	if ($this->removeData)
    	{
    		return false;
    	}
        $data = $this->associateData( $this->pkeys );

        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->table);
        $sql = $this->insert($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateLibraryUnitIsClosed()
    {
    	if ($this->removeData)
    	{
    		return false;
    	}
        $data = $this->associateData( $this->pkeys );

        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->table);
        $this->setWhere('libraryUnitId = ? AND weekDayId = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $libraryUnitId (integer)
     * @param $weekDayId (integer)
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function deleteLibraryUnitIsClosed($libraryUnitId, $weekDayId)
    {
        $data[] = $libraryUnitId;
        $data[] = $weekDayId;

        $this->clear();
        $this->setTables($this->table);
        $this->setWhere('libraryUnitId = ? AND weekDayId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Return if the libraryUnit is closed in some day or not
     *
     * @param $libraryUnitId (integer)
     * @param $weekDayId (integer)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getLibraryUnitIsClosed($libraryUnitId, $weekDayId)
    {
        $data[] = $libraryUnitId;
        $data[] = $weekDayId;

        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->table);
        $this->setWhere('libraryUnitId = ? AND weekDayId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);
        unset($this->libraryUnitId);
        unset($this->weekDayId);
        $this->setData($rs[0]);
        return $rs[0] ? true : false;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @return (Array): An array containing the search results
     **/
    public function searchLibraryUnitIsClosed($toObject = FALSE)
    {
        $this->clear();

        if ($this->libraryUnitIdS)
        {
            $this->setWhere('A.libraryUnitId = ?');
            $data[] = $this->libraryUnitIdS;
        }

        if ($this->weekDayIdS)
        {
            $this->setWhere('A.weekDayId = ?');
            $data[] = $this->weekDayIdS;
        }

        $this->setColumns('A.libraryUnitId AS libraryUnitId,
                           A.weekDayId     AS weekDayId,
                           B.description   AS weekDescription');
        $this->setTables('gtcLibraryUnitIsClosed    A
            INNER JOIN    gtcWeekDay                B
                    ON    (A.weekDayId = B.weekDayId)');
        $sql = $this->select($data);
        return $this->query($sql, ($toObject ? TRUE : FALSE));
    }


    /**
     * List all records from the table handled by the class
     *
     * @param None
     *
     * @return (Array): Return an array with the entire table
     *
     **/
    public function listLibraryUnitIsClosed()
    {
        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    public function amountDays($beginDate, $endDate, $libraryUnitId = null )
    {
        
        $beginDate = GDate::construct($beginDate)->getTimestampUnix();
        $endDate   = GDate::construct($endDate)->getTimestampUnix();

        $this->clear();
        $this->setTables($this->table);
        $this->setColumns('weekDayId');
        
        if ( $libraryUnitId )
        {
            $this->setWhere('libraryUnitId = ?');
            $args[] = $libraryUnitId;
        }
        
        $sql = $this->select($args);
        $rs  = $this->query($sql);

        $weekDays = array();
        
        for ( $i=0; $i < count($rs); $i++ )
        {
            $weekDays[] = $rs[$i][0];
        }

        $amountDays = 0;
        
        for ($i=0; $beginDate <= $endDate; $i++)
        {
            if (in_array(date('N', $beginDate), $weekDays))
            {
                $amountDays ++;
            }

            //Add +1 day
            $beginDate += 86400;
        }

        return $amountDays;
    }


    public function isClosed($libraryUnitId, $weekDayId)
    {
        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->table);
        $this->setWhere('libraryUnitId = ? AND weekDayId = ?');
        $sql = $this->select( array($libraryUnitId, $weekDayId) );
        $rs  = $this->query($sql);
        return ($rs[0][0]) ? TRUE : FALSE;
    }
}
?>
