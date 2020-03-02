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
 * This file handles the connection and actions for gtcWeekDay table
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
class BusinessGnuteca3BusWeekDay extends GBusiness
{
    public $pkeys;
    public $columns;
    public $fullColumns;

    public $weekDayId;
    public $description;

    public $weekDayIdS;
    public $descriptionS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables  = 'gtcWeekDay';
        $this->pkeys   = 'weekDayId';
        $this->columns = 'description';
        $this->fullColumns = $this->pkeys . ',' . $this->columns;
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertWeekDay()
    {
        $data = $this->associateData( $this->columns );

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->insert($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateWeekDay()
    {
        $data = $this->associateData( $this->columns . ',' . $this->pkeys );

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('weekDayId = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $weekDayId (integer)
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function deleteWeekDay($weekDayId)
    {
        $data[] = $weekDayId;

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('weekDayId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * @param $weekDayId (integer)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getWeekDay($weekDayId)
    {
        $data[] = $weekDayId;

        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $this->setWhere('weekDayId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);
        $this->setData($rs[0]);
        return $this;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @return (Array): An array containing the search results
     **/
    public function searchWeekDay($toObject = FALSE)
    {
        $this->clear();

        if ($this->weekDayIdS)
        {
            $this->setWhere('weekDayId = ?');
            $data[] = $this->weekDayIdS;
        }

        if ($this->descriptionS)
        {
            $this->setWhere('description = ?');
            $data[] = $this->descriptionS;
        }

        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
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
    public function listWeekDay()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }
}
?>
