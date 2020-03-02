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
 * Class created on 04/11/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusFormatBackOfBook extends GBusiness
{
    public $formatBackOfBookId;
    public $description;
    public $format;
    public $internalFormat;

    public $formatBackOfBookIdS;
    public $descriptionS;
    public $formatS;
    public $internalFormatS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        $table = 'gtcFormatBackOfBook';
        $pkeys = 'formatBackOfBookId';
        $cols  = 'description,
                  format,
                  internalFormat';
        parent::__construct($table, $pkeys, $cols);
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertFormatBackOfBook()
    {
        return $this->autoInsert();
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateFormatBackOfBook()
    {
        return $this->autoUpdate();
    }


    /**
     * Delete a record
     *
     * @param $formatBackOfBookId (integer)
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function deleteFormatBackOfBook($formatBackOfBookId)
    {
        return $this->autoDelete($formatBackOfBookId);
    }


    /**
     * Return a specific record from the database
     *
     * @param $formatBackOfBookId (integer)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getFormatBackOfBook($formatBackOfBookId)
    {
        $this->clear();
        return $this->autoGet($formatBackOfBookId);
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $object (bool): Case TRUE return as Object, otherwise Array
     *
     * @return (Array): An array containing the search results
     **/
    public function searchFormatBackOfBook($object = false)
    {
        $this->clear();
        $this->setColumns('formatBackOfBookId, description, format, internalFormat');
        $this->setTables('gtcFormatBackOfBook');
        if ( $this->formatBackOfBookIdS )
        {
            $this->setWhere('formatBackOfBookId = ?');
            $data[] = $this->formatBackOfBookIdS;
        }
        if ($this->descriptionS)
        {
            $this->descriptionS = str_replace(' ','%', $this->descriptionS);
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = '%' . strtolower($this->descriptionS) . '%';
        }
        if ($this->formatS)
        {
            $this->formatS = str_replace(' ','%', $this->formatS);
            $this->setWhere('lower(format) LIKE lower(?)');
            $data[] = '%' . strtolower($this->formatS) . '%';
        }
        if ($this->internalFormatS)
        {
            $this->internalFormatS = str_replace(' ','%', $this->internalFormatS);
            $this->setWhere('lower(internalFormat) LIKE lower(?)');
            $data[] = '%' . strtolower($this->internalFormatS) . '%';
        }
        $sql = $this->select($data);
        $rs  = $this->query($sql);
        return $rs;
        /*$this->clear();
        $filters = array(
            'formatBackOfBookId' => 'equals',
            'description'        => 'ilike',
            'format'             => 'ilike',
            'internalFormat'     => 'ilike'
        );
        return $this->autoSearch($filters, $object);*/
    }


    /**
     * List all records from the table handled by the class
     *
     * @param None
     *
     * @return (Array): Return an array with the entire table
     *
     **/
    public function listFormatBackOfBook()
    {
        return $this->autoList();
    }
}
?>
