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
 * Class created on 28/11/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusSearchPresentationFormat extends GBusiness
{
    public $searchFormatId;
    public $category;
    public $searchFormat;
    public $detailFormat;

    public $searchFormatIdS;
    public $categoryS;
    public $searchFormatS;
    public $detailFormatS;
    

    function __construct()
    {
        $table = 'gtcSearchPresentationFormat';
        $pkeys = 'searchFormatId,
                  category';
        $cols  = 'searchFormat,
                  detailFormat';
        parent::__construct($table, $pkeys, $cols);
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertSearchPresentationFormat()
    {
    	if ($this->removeData)
    	{
    		$this->deleteSearchPresentationFormat($this->searchFormatId, $this->category);
    		return false;
    	}
    	if ($this->getSearchPresentationFormat($this->searchFormatId, $this->category))
    	{
    		return $this->updateSearchPresentationFormat();
    	}
        return $this->autoInsert();
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateSearchPresentationFormat()
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
    public function deleteSearchPresentationFormat($searchFormatId, $category)
    {
        return $this->autoDelete($searchFormatId, $category);
    }


    /**
     * Return a specific record from the database
     *
     * @param $formatBackOfBookId (integer)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getSearchPresentationFormat($searchFormatId, $category)
    {
        $this->clear();
        return $this->autoGet($searchFormatId, $category);
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $object (bool): Case TRUE return as Object, otherwise Array
     *
     * @return (Array): An array containing the search results
     **/
    public function searchSearchPresentationFormat($object = false)
    {
        $this->clear();
        $filters = array(
            'searchFormatId' => 'equals',
            'category'       => 'equals',
            'searchFormat'   => 'ilike',
            'detailFormat'   => 'ilike'
        );
        return $this->autoSearch($filters, $object);
    }


    /**
     * List all records from the table handled by the class
     *
     * @param None
     *
     * @return (Array): Return an array with the entire table
     *
     **/
    public function listSearchPresentationFormat()
    {
        return $this->autoList();
    }
    
    
    public function deleteBySearchFormat($searchFormatId)
    {
    	$sql = new MSQL(null, $this->tables, 'searchFormatId = ?');
    	return $this->execute( $sql->delete(array($searchFormatId)) );
    }
}
?>
