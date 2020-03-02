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
class BusinessGnuteca3BusSearchFormatAccess extends GBusiness
{
    public $searchFormatId;
    public $linkId;
    public $links; //list of links separated by comma used in search

    public $searchFormatIdS;
    public $linkIdS;

    
    /**
     * Class constructor
     **/
    public function __construct()
    {
        $table = 'gtcSearchFormatAccess';
        $pkeys = 'searchFormatId,
                  linkId';
        parent::__construct($table, $pkeys);
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertSearchFormatAccess()
    {
        if ($this->removeData)
        {
            $this->deleteSearchFormatAccess($this->searchFormatId, $this->linkId);
            return false;
        }
        if ($this->getSearchFormatAccess($this->searchFormatId, $this->linkId))
        {
            return true;
        }
        return $this->autoInsert();
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateSearchFormatAccess()
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
    public function deleteSearchFormatAccess($searchFormatId, $linkId)
    {
        return $this->autoDelete($searchFormatId, $linkId);
    }


    /**
     * Return a specific record from the database
     *
     * @param $formatBackOfBookId (integer)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getSearchFormatAccess($searchFormatId, $linkId)
    {
        return $this->autoGet($searchFormatId, $linkId);
    }


    /**
     * Faz uma busca nos acessos de formato de pesquisa.
     *
     * @param boolean $object retorna como objeto caso sim, contrário como array
     * @param boolean $distintct se é para retornar somente os casos distintos
     *
     * @return array com os dados pesquisados
     **/
    public function searchSearchFormatAccess($object = false, $distinct = false )
    {
        $this->clear();
        if ($this->searchFormatId)
        {
        	$this->setWhere('A.searchFormatId = ?');
        	$args[] = $this->searchFormatId;
        }
        if ($this->linkId)
        {
        	$this->setWhere('A.linkId = ?');
        	$args[] = $this->linkId;
        }

        if ($this->links)
        {
            $this->setWhere('A.linkId in ('.$this->links.')');
        	//$args[] = $this->links;
        }

        //Caso seja distinto restringe a busca
        if ( $distinct )
        {
            $this->setWhere('C.isRestricted = true');
        }

        
        //TODO: Fix bug on GBusiness
        $tables  = $this->tables;
        $columns = $this->columns;
        
        $this->setTables('gtcSearchFormatAccess A
               INNER JOIN basLink               B
                       ON (A.linkId = B.linkId)
               INNER JOIN gtcSearchFormat C
                       ON (A.searchFormatId = C.searchFormatId) ');

        //caso sejá somente distintas as colunas devem ser diferentes
        $columns = $distinct ? 
                            ' distinct A.searchFormatId,C.description' :
                            'A.searchFormatId,
                            A.linkId,
                            B.description AS linkIdDescription,
                            C.description' ;

        $this->setColumns( $columns );
        $sql = $this->select($args);
        $rs  = $this->query($sql, $object);
       
        //TODO: Fix bug on GBusiness
        $this->setTables($tables);
        $this->setColumns($columns);
        
        return $rs;
    }


    /**
     * List all records from the table handled by the class
     *
     * @param None
     *
     * @return (Array): Return an array with the entire table
     *
     **/
    public function listSearchFormatAccess()
    {
        return $this->autoList();
    }
    
    
    public function deleteBySearchFormat($searchFormatId)
    {
    	$sql = new MSQL(null, $this->tables, 'searchFormatId = ?');
    	return $this->execute( $sql->delete($searchFormatId) );
    }
}
?>
