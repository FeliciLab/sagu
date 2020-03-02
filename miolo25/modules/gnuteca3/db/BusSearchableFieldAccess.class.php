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
 * SearchableFieldAccess
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
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
 * Class created on 18/03/2009
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusSearchableFieldAccess extends GBusiness
{
    public $cols;
    public $pkeys;
    public $pkeysWhere;
    public $table;

    public $searchableFieldId;
    public $linkId;

    public $searchableFieldIdS;
    public $linkIdS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->table      = 'gtcSearchableFieldAccess';
        $this->pkeys      = 'searchableFieldId,
                             linkId';
        $this->pkeysWhere = 'searchableFieldId = ? AND linkId = ?';
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertSearchableFieldAccess()
    {
    	if ($this->removeData)
    	{
    		return $this->deleteSearchableFieldAccess($this->searchableFieldId, $this->linkId);
    	}
        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->table);
        $sql = $this->insert( $this->associateData($this->pkeys) );
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
    public function updateSearchableFieldAccess()
    {
    }


    /**
     * Return a specific record from the database
     *
     * @param $libraryUnitId (integer): Primary key of the record to be retrieved
     * @param $linkId (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getSearchableFieldAccess($searchableFieldId, $linkId)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns($this->pkeys);
        $this->setWhere($this->pkeysWhere);
        $sql = $this->select( array($searchableFieldId, $linkId) );
        $rs  = $this->query($sql, TRUE);
        return $rs ? $rs[0] : FALSE;
    }


    /**
     * Return a specific record from the database
     *
     * @param $linkId (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getSearchableFieldAccessByLinkId($linkId)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns($this->pkeys);
        $this->setWhere("linkId in (" . $linkId . ")");
        $sql = $this->select();
        $rs  = $this->query($sql, TRUE);
        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $libraryUnitId (integer): Primary key for deletion
     * @param $linkId (integer): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteSearchableFieldAccess($searchableFieldId, $linkId)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setWhere($this->pkeysWhere);
        $sql = $this->delete( array($searchableFieldId, $linkId) );
        $rs  = $this->execute($sql);
        return $rs;
    }


    public function deleteByGroup($searchableFieldId)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setWhere('searchableFieldId = ?');
        $sql = $this->delete( array($searchableFieldId) );
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $toObject (boolean): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchSearchableFieldAccess($toObject = FALSE)
    {
        $this->clear();

        if ($this->searchableFieldIdS)
        {
            $this->setWhere('A.searchableFieldId = ?');
            $data[] = $this->searchableFieldIdS;
        }

        if ($this->linkIdS)
        {
            $this->setWhere('A.linkId = ?');
            $data[] = $this->linkIdS;
        }

        $this->setTables('gtcSearchableFieldAccess  A
            INNER JOIN    basLink                   B
                    ON    (A.linkId = B.linkId)');
        $this->setColumns('A.searchableFieldId,
                           A.linkId,
                           B.description');
        $this->setOrderBy('A.linkId');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject ? TRUE : FALSE);
        return $rs;
    }


    /**
     * List all records from the table handled by the class
     *
     * @return (array): Return an array with the entire table
     *
     **/
    public function listSearchableFieldAccess()
    {
        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }
}
?>
