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
 * This file handles the connection and actions for gtcLibraryUnitConfig table
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
 * Class created on 02/10/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusLibraryUnitConfig extends GBusiness
{
    public $MIOLO;
    public $module;

    public $cols;
    public $pkeys;
    public $pkeysWhere;
    public $fullColumns;

    public $libraryUnitId;
    public $parameter;
    public $value;

    public $libraryUnitIdS;
    public $parameterS;
    public $valueS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->MIOLO    = MIOLO::getInstance();
        $this->tables   = 'gtcLibraryUnitConfig';
        $this->pkeys    = 'libraryUnitId,
                           parameter';
        $this->cols     = 'value';
        $this->fullColumns = $this->pkeys . ',' . $this->cols;
        $this->pkeysWhere  = 'libraryUnitId = ? AND parameter = ?';
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertLibraryUnitConfig()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $sql = $this->insert( $this->associateData($this->fullColumns) );
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
    public function updateLibraryUnitConfig()
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns($this->cols);
        $this->setWhere($this->pkeysWhere);
        $sql = $this->update( $this->associateData($this->cols . ',' . $this->pkeys) );
        $rs  = $this->execute($sql);
        return $rs;
    }

    
    /**
     * Delete a record
     *
     * @param $libraryUnitId (integer): Primary key for deletion
     * @param $parameter (integer): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteLibraryUnitConfig($libraryUnitId, $parameter)
    { 
        $this->clear();
        $this->setTables($this->tables);
        if (is_array($libraryUnitId)) //Apagar apenas quando libraryUnitId estiver na lista passada
        {
            $libraryList = implode(',', $libraryUnitId);
            $this->setWhere("libraryUnitId IN({$libraryList}) AND parameter = ?");
            $sql = $this->delete( array($parameter) );
        }
        else
        {
            $this->setWhere($this->pkeysWhere);
            $sql = $this->delete( array($libraryUnitId, $parameter) );
        }
        $rs  = $this->execute($sql);
        return $rs;
    }

    public function deleteLibraryUnitConfigByParameter($parameter)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('parameter = ?');
        $sql = $this->delete( array($parameter) );
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
    public function searchLibraryUnitConfig($toObject = FALSE)
    {
        $this->clear();

        if ($this->libraryUnitIdS)
        {
            $this->setWhere('libraryUnitId = ?');
            $data[] = $this->libraryUnitIdS;
        }

        if ($this->parameterS)
        {
            $this->setWhere('parameter = ?');
            $data[] = $this->parameterS;
        }

        if ($this->valueS)
        {
            $this->setWhere('lower(value) LIKE lower(?)');
            $data[] = '%' . $this->valueS . '%';
        }

        $this->setTables($this->tables);
        $this->setColumns($this->fullColumns);
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject ? TRUE : FALSE);
        return $rs;
    }


    /**
     * List all records from the table handled by the class
     * 
     * @return (array): Return an array with the entire table
     * @param $libraryUnitId (int): Número da unidade, obtém as configurações apenas daquela unidade.
     *
     **/
    public function listLibraryUnitConfig( $libraryUnitId = null )
    {
        $this->clear();
        $this->setColumns( $this->fullColumns );
        $this->setTables( $this->tables );
        $args = array();
        
        if( $libraryUnitId )
        {
            $args[] = $libraryUnitId;
	    $this->setWhere( "libraryUnitId = ?" );
            $sql = $this->select( $args );
            $rs  = $this->query( $sql );
            return $rs;
        }
        
        $sql = $this->select();
        $rs  = $this->query( $sql );
        return $rs;
    }


    /**
     * Obtem um ou mais parametros da biblioteca, caso não encontre, pega preferência padrão
     * 
     * @param $libraryUnitId unidade de biblioteca, se nao encontrar parametro para ela, retorna o parametro geral.
     * @param escalar/array $parameter se passar variavel escalar retorna somente o valor,senao retorna array com os valores.
     * @return array ou escalar com o valor da preferencia/preferencias.
     */
    public function getValueLibraryUnitConfig($libraryUnitId = null, $parameter = null)
    {
    	$result = null;
        
        //Se foi passado parametro
    	if ( $parameter )
    	{
    		$args = array();
	        $this->clear();
                
                //Caso o conteudo da gtclibraryunitconfig seja nulo, retorna o valor padrao
	        $this->setColumns('b.parameter,
	                         (CASE WHEN g.value IS NOT NULL THEN g.value ELSE b.value END) as value'); 
	        $this->setTables('basconfig b 
	                          LEFT JOIN gtclibraryunitconfig g
	                                 ON (b.parameter = g.parameter 
	                                AND g.libraryunitid = ? )');
	        
	        $args[] = $libraryUnitId;
                //Quando um array com parametros for passado
	        if ( is_array($parameter) )
	        {
                    //Faz busca pelos parametros passados dentro do array
                    $arg = implode('\',\'', $parameter);
                    $this->setWhere("b.parameter in ('{$arg}')");
	        }
	        else 
	        {
                    //Obtem o parametro passado pontualmente.
                    $args[] = $parameter;
                    $this->setWhere("b.parameter = ?");
	        }
	        
                //efetua a busca
	        $sql = $this->select($args);

	        $rs  = $this->query($sql, true);
	        
                //se tiver sido passado um array de parametros
	        if ( is_array($parameter) )
	        {
                    //Prepara o retorno com um array associativo
                    $result = array();
                    foreach( $rs as $i=>$value )
                    {
                        $result[$value->parameter] = $value->value;
                    }
	        }
	        else 
	        {
                    //Retorna o parametro solicitado.
                    $result = $rs[0]->value;
	        }
    	}

        return $result;
    }
}
?>
