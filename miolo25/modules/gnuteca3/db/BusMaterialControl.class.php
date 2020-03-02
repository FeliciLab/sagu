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
 * This file handles the connection and actions for gtcMaterialControl table
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
 * Class created on 03/10/2008
 *
 **/


/**
 * Class to manipulate the reserveComposion table
 **/
class BusinessGnuteca3BusMaterialControl extends GBusiness
{
    public $cols;
    public $pkeys;
    public $pkeysWhere;
    public $fullColumns;

    public $controlNumber;
    public $entranceDate;
    public $lastChangeDate;
    public $lastChangeOperator;
    public $materialGenderId;
    public $materialTypeId;
    public $controlNumberFather;
    public $category;
    public $level;
    public $controlNumberS;

    const TYPE_BOOK                 = 1;
    const TYPE_COLLECTION           = 2;
    const TYPE_COLLECTION_FASCICLE  = 3;
    const TYPE_BOOK_ARTICLE         = 4;
    const TYPE_COLLECTION_ARTICLE   = 5;

    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();

        $this->table    = 'gtcMaterialControl';
        $this->pkeys    = 'controlNumber';
        $this->cols     = 'entranceDate,lastChangeDate,materialGenderId,controlNumberFather,category,level,lastChangeOperator';

        $this->fullColumns = $this->pkeys . ',' . $this->cols;
    }


    /**
     * Return a specific record from the database
     *
     * @param $controlNumber (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getMaterialControl($controlNumber, $extraInfo = false)
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $this->setWhere('controlNumber = ?');
        $sql = $this->select(array($controlNumber));
        $rs  = $this->query($sql, true);

        if ( !$rs[0] )
        {
        	return false;
        }

        //pega só o primeiro que é o que interesa
        $rs = $rs[0];
        //seta no objeto para ser usado após, em outras funções
        $this->setData( $rs );

        if ( $extraInfo)
        {
        	$busMaterialGender = $this->MIOLO->getBusiness( $this->module, 'BusMaterialGender' );
        	$materialGender    = $busMaterialGender->getMaterialGender( $rs->materialGenderId , true);
        	$rs->materialGenderDescription = $materialGender->description;
        	$busMaterialType   = $this->MIOLO->getBusiness( $this->module, 'BusMaterialType' );
            $materialType      = $busMaterialType->getMaterialType( $rs->materialTypeId , true);
            $rs->materialTypeDescription = $materialType->description;
        }

        return $rs;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $toObject (bool)
     *
     * @return (array): An array containing the search results
     **/
    public function searchMaterialControl($toObject = FALSE)
    {
        $this->clear();

        if ($this->controlNumberS)
        {
            $this->setWhere('controlNumber = ?');
            $data[] = $this->controlNumberS;
        }

        $this->setTables($this->table);
        $this->setColumns($this->cols);
        $sql = $this->select($data);
        $rs  = $this->query($sql, ($toObject) ? TRUE : FALSE);
        return $rs;
    }

    /**
     * Lista todos os números de controle
     * utilizado dentro da sincronzição do servidor Z3950
     * 
     * @return array
     */
    public function listALlControlNumbers()
    {
        return $this->query("SELECT controlNumber FROM gtcMaterialControl" ,false);;
    }
    
    /**
     * Lista quantidade de obras
     * 
     * @return array
     */
    public function countTotalObras()
    {
        $rs = $this->query("SELECT COUNT(controlNumber) FROM gtcMaterialControl");
        return $rs[0];
    }
    
    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertMaterialControl()
    {
        $this->clear();
        $columns = $this->fullColumns;

        if(is_null($this->materialGenderId))
        {
            $columns = str_replace(",materialGenderId", "", $columns);
        }
        if(is_null($this->controlNumberFather))
        {
            $columns = str_replace(",controlNumberFather", "", $columns);
        }

        $this->setColumns   ( $columns );
        $this->setTables    ( $this->table );

        $sql = $this->insert    ( $this->associateData( $columns ) );

        $rs  = $this->execute   ( $sql );
        
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
    public function updateMaterialControl()
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns($this->cols);
        $this->setWhere('controlNumber = ?');
        $sql = $this->update( $this->associateData($this->cols) );
        $rs  = $this->execute($sql);
        
        return  $rs;
    }


    /**
     * Delete a record
     *
     * @param $controlNumber (integer): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteMaterialControl($controlNumber)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setWhere('controlNumber = ?');
        $sql = $this->delete( array($controlNumber) );
        $rs  = $this->execute($sql);
        return $rs;
    }

    /**
     *
     **/
    public function getNextControlNumber()
    {
        $sql = "SELECT nextval('seq_controlnumber')";
        $rs  = $this->query($sql);

        return $rs[0][0];
    }


    /**
     *
     **/
    public function updateMaterialGender($controlNumber, $materialGender)
    {
        if(!strlen($materialGender))
        {
            return false;
        }

        $this->clear();
        $this->setTables($this->table);
        $this->setColumns("materialGenderId");
        $this->setWhere('controlNumber = ?');

        $args = array($materialGender, $controlNumber);

        $sql = $this->update( $args );
        $rs  = $this->execute($sql);
        return  $rs;
    }



    public function updateMaterialType($controlNumber, $materialType)
    {
        if(!strlen($materialType))
        {
            return false;
        }

        $this->clear();
        $this->setTables($this->table);
        $this->setColumns("materialTypeId");
        $this->setWhere('controlNumber = ?');

        $args = array($materialType, $controlNumber);

        $sql = $this->update( $args );
        $rs  = $this->execute($sql);
        return  $rs;
    }

    public function updateMaterialPhysicalType($controlNumber, $materialPhysicalType)
    {
        if(!strlen($materialPhysicalType))
        {
            return false;
        }

        $this->clear();
        $this->setTables($this->table);
        $this->setColumns("materialPhysicalTypeId");
        $this->setWhere('controlNumber = ?');

        $args = array($materialPhysicalType, $controlNumber);

        $sql = $this->update( $args );
        $rs  = $this->execute($sql);
        return  $rs;
    }

    /**
     *
     **/
    public function setControlNumberFather($controlNumber, $controlNumberFather)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns("controlNumberFather");
        $this->setWhere('controlNumber = ?');

        $args = array($controlNumberFather, $controlNumber);

        $sql = $this->update( $args );
        
        $rs  = $this->execute($sql);
        return  $rs;
    }



    /**
     * Get controls numbers with entranceDate is greater than date passed
     *
     * @param $date (String)
     * @param (array) librarys id
     *
     * @return (Array): List of control numbers
     */
    public function getControlNumbersByDate($date, $libraryUnitId)
    {
        $this->clear();

        //Não retornar Coleção, Artigo e nem Analítica de livro
        $this->setWhere("((A.category != 'SE' AND A.level != '#') OR (A.category != 'SA' AND A.level != '4') OR (A.category != 'BA' AND A.level != '4'))");

        //Materiais não baixados e com data de cadastro maior
        if ($date)
        {
            $this->setWhere("A.entranceDate >= ? AND C.isLowStatus = 'f'");
            $args[] =  $date;
        }

        if ($libraryUnitId)
        {
        	$libraries = implode("','",$libraryUnitId);
            $this->setWhere("B.libraryUnitId in ('{$libraries}')");
        }

        $this->setTables('    gtcMaterialControl  A
                    LEFT JOIN gtcExemplaryControl B
                           ON A.controlNumber = B.controlNumber
                    LEFT JOIN gtcExemplaryStatus  C
                           ON B.exemplaryStatusId = C.exemplaryStatusId');
        $this->setColumns('A.controlNumber');

        $sql = $this->select($args);
        $query = $this->query($sql);

        $list = array();

        for ($i=0; $i < count($query); $i++)
        {
            $list[] = $query[$i][0];
        }

        return $list;
    }


    /**
     *
     * @return boolean
     */
    function existsMaterial($controlNumber)
    {
        $this->clear();
        $this->setColumns("1");
        $this->setTables($this->table);
        $this->setWhere('controlNumber = ?');
        $sql = $this->select(array($controlNumber));
        $rs  = $this->query($sql);
        return isset($rs[0]) && count($rs[0]);
    }


    /**
     * Enter description here...
     *
     * @param integer $controlNumber
     */
    function getLastChangeDate($controlNumber)
    {
        $this->clear();
        $this->setColumns("lastchangedate");
        $this->setTables($this->table);
        $this->setWhere('controlNumber = ?');
        $sql = $this->select(array($controlNumber));
        $rs  = $this->query($sql, true);
        return isset($rs[0]) && count($rs[0]) ? $rs[0]->lastchangedate : false;
    }



    /**
     * Enter description here...
     *
     * @param integer $controlNumber
     */
    function getControlNumberFather($controlNumber)
    {
    	//caso tenha os dados no objeto, para não fazer mais sql otimizar, otimizar
    	if ( $this->controlNumber == $controlNumber )
    	{
    		return $this->controlNumberFather;
    	}

        $this->clear();
        $this->setColumns("controlNumberFather");
        $this->setTables($this->table);
        $this->setWhere('controlNumber = ?');
        $sql = $this->select(array($controlNumber));
        $rs  = $this->query($sql, true);
        return isset($rs[0]) && count($rs[0]) ? $rs[0]->controlNumberFather : false;
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $controlNumber
     * @return unknown
     */
    function getEntraceDate($controlNumber)
    {
        $this->clear();
        $this->setColumns("entrancedate");
        $this->setTables($this->table);
        $this->setWhere('controlNumber = ?');
        $sql = $this->select(array($controlNumber));
        $rs  = $this->query($sql, true);
        return isset($rs[0]) && count($rs[0]) ? $rs[0]->entrancedate : false;
    }

    /**
     * Define the last change date
     *
     * @param unknown_type $dbDate
     * @param unknown_type $controlNumber
     * @return unknown
     */
    function setLastChangeDate($dbDate, $controlNumber)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns("lastchangedate");
        $this->setWhere('controlNumber = ?');
        $sql = $this->update( array($dbDate, $controlNumber) );
        $rs  = $this->execute($sql);
        return  $rs;
    }
    
    function setLastChangeOperator($operator, $controlNumber)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns("lastchangeoperator");
        $this->setWhere('controlNumber = ?');
        $sql = $this->update( array($operator, $controlNumber) );
        $rs  = $this->execute($sql);
        return  $rs;
    }  

    /**
     * Enter description here...
     *
     * @param unknown_type $dbDate
     * @param unknown_type $controlNumber
     * @return unknown
     */
    function setCategory($category, $controlNumber)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns("category");
        $this->setWhere('controlNumber = ?');
        $sql = $this->update( array($category, $controlNumber) );
        $rs  = $this->execute($sql);
        return  $rs;
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $dbDate
     * @param unknown_type $controlNumber
     * @return unknown
     */
    function setLevel($level, $controlNumber)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns("level");
        $this->setWhere('controlNumber = ?');
        $sql = $this->update( array($level, $controlNumber) );
        $rs  = $this->execute($sql);
        return  $rs;
    }



    /**
     * Enter description here...
     *
     * @param unknown_type $controlNumber
     * @return unknown
     */
    function getCategory($controlNumber)
    {
        $this->clear();
        $this->setColumns("category");
        $this->setTables($this->table);
        $this->setWhere('controlNumber = ?');
        $sql = $this->select(array($controlNumber));
        $rs  = $this->query($sql, true);
        return isset($rs[0]) && count($rs[0]) ? $rs[0]->category : false;
    }



    /**
     * Enter description here...
     *
     * @param unknown_type $controlNumber
     * @return unknown
     */
    function getLevel($controlNumber)
    {
        $this->clear();
        $this->setColumns("level");
        $this->setTables($this->table);
        $this->setWhere('controlNumber = ?');
        $sql = $this->select(array($controlNumber));
        $rs  = $this->query($sql, true);
        return isset($rs[0]) && count($rs[0]) ? $rs[0]->level : false;
    }


    /**
     * Verify if a control number has the selected category and level
     *
     * @param integer $controlNumber
     * @param string $category
     * @param string $levespondl
     * @return boolean true if has
     */
    function verifyCategoryAndLevel($controlNumber, $category, $level = NULL)
    {
    	//se existir dados no objeto (para esse exemplar) , otimiza para não fazer mais um sql (muito útil na busca)
    	if ( $this->controlNumber == $controlNumber )
    	{
    		//se as categorias forem iguais
    		if ( $this->category == $category )
    		{
    			//se nao tiver level retorna true, então o exemplar é da categoria pedida
    			if ( !$level )
    			{
    				return true;
    			}
    			else //caso tenha level tem que comparar o level também
    			{
    				if ( $this->level == $level )
    				{
    					return true;
    				}
    			}
    		}

    		return false;
    	}

        $this->clear();
        $this->setColumns('controlNumber');
        $this->setTables($this->table);
        $this->setWhere('controlNumber = ?');
        $this->setWhere('category = ?');
        $data[] = $controlNumber;
        $data[] = $category;

        if ($level)
        {
            $data[] = $level;
            $this->setWhere('level = ?');
        }

        $sql = $this->select( $data );
        $rs  = $this->query($sql, true);

        if ($rs[0]->controlNumber)
        {
            return true;
        }

        return false;
    }

    /**
     * Verify if a Control Number is a Collection
     *
     * @param integer $controlNumber
     * @return boolean true or false
     */
    function isCollection($controlNumber)
    {
        return $this->verifyCategoryAndLevel($controlNumber, 'SE','#');
    }

    /**
     * Verify if a Control Number is a fascickle of a Collection
     *
     * @param integer $controlNumber
     * @return boolean true or false
     */
    function isCollectionFascicle($controlNumber)
    {
        $collection = $this->verifyCategoryAndLevel($controlNumber, 'SE');  // se faz parte de coleção
        $complete   = $this->verifyCategoryAndLevel($controlNumber, 'SE', '#'); //se é uma coleção completa

        //se faz parte de coleção e não é completo, então é um fascicle
        if ($collection & !$complete)
        {
        	return true;
        }
        else
        {
            return false;
        }
    }


    /**
     * Verify if a Control Number is a Book
     *
     * @param integer $controlNumber
     * @return boolean true or false
     */
    function isColletionArticle($controlNumber)
    {
        return $this->verifyCategoryAndLevel( $controlNumber, 'SA');
    }

    /**
     * Verify if a Control Number is a Book
     *
     * @param integer $controlNumber
     * @return boolean true or false
     */
    function isBook($controlNumber)
    {
        return $this->verifyCategoryAndLevel( $controlNumber, 'BK', null );
    }

    /**
     * Verify if a Control Number is a Article of a Book
     *
     * @param integer $controlNumber
     * @return boolean true or false
     */
    function isBookArticle($controlNumber)
    {
        return $this->verifyCategoryAndLevel( $controlNumber, 'BA', null );
    }


    /**
     * Retorna o tipo do material
     *
     * @param número de controle
     * @return código do tipo
     */
    public function getTypeOfMaterial($controlNumber)
    {
        $type = null;
        
        if ( $this->isBook($controlNumber) ) //livro
        {
            $type = self::TYPE_BOOK;
        }
        else if ( $this->isCollection($controlNumber) ) //coleção
        {
            $type = self::TYPE_COLLECTION;
        }
        else if ( $this->isCollectionFascicle($controlNumber) ) //fasciculo
        {
            $type = self::TYPE_COLLECTION_FASCICLE;
        }
        else if ( $this->isBookArticle($controlNumber)) //artigo de livro
        {
            $type = self::TYPE_BOOK_ARTICLE;
        }
        else if ( $this->isColletionArticle($controlNumber) )
        {
            $type = self::TYPE_COLLECTION_ARTICLE;
        }

        return $type;
    }


    /**
     * Retorna o nome do material conforme o tipo
     *
     * @param tipo
     * @return String com o nome
     */
    public function getNameOfTypeOfMaterial($type)
    {
        $name = '';
        if ( $type == self::TYPE_BOOK )
        {
            $name = _M('Livro', $this->module);
        }
        else if ( $type == self::TYPE_COLLECTION_FASCICLE )
        {
            $name = _M('Fascículo', $this->module);
        }
        else if ( $type == self::TYPE_COLLECTION )
        {
             $name = _M('Coleção', $this->module);
        }
        else if ( $type == self::TYPE_BOOK_ARTICLE)
        {
            $name = _M('Artigo de livro', $this->module);
        }
        else if ( $ype == self::TYPE_COLLECTION_ARTICLE )
        {
            $name = _M('Artigo de coleção', $this->module);
        }

        return $name;
    }


   /**
     * Retorna o titulo do material
     *
     * @param integer $controlNumber
     * @param integer $line
     * @return string
     */
    public function getMaterialSon($controlNumber, $libraryUnitId = null)
    {
        $this->clear();
        $this->setColumns("DISTINCT A.controlNumber");
        $this->setTables(is_null($libraryUnitId) ? " $this->table A " : " $this->table A INNER JOIN gtcExemplaryControl B USING (controlNumber) ");
        $this->setWhere('A.controlNumberFather = ?');

        if(!is_null($libraryUnitId))
        {
            $libraryUnitId = !is_array($libraryUnitId) ? array($libraryUnitId) : $libraryUnitId;
            $libraryUnitId = implode(",", $libraryUnitId);
            $this->setWhere("B.libraryUnitId IN ($libraryUnitId)");
        }

        $sql = $this->select(array($controlNumber));
        $rs  = $this->query($sql, true);

        return $rs;
    }


    /**
     * Return an array of controlNumber that has the controlNumberFather (children of father)
     *
     * @param array $controlNumber
     */
    public function getChildren( $controlNumberFather, $libraryUnitId = null)
    {
        /*if ( !is_null( $libraryUnitId) )
        {
            $this->libraryUnitIdS = explode( ',' , $this->libraryUnitIdS);
        }*/

    	$this->clear();
        $this->setColumns(  !strlen($libraryUnitId) ? 'controlNumber'           : "distinct {$this->table}.controlNumber");
        $this->setTables(   !strlen($libraryUnitId) ? $this->table              : "$this->table INNER JOIN gtcExemplaryControl USING(controlNumber)");
        $this->setWhere(    !strlen($libraryUnitId) ? 'controlNumberFather = ?' : "{$this->table}.controlNumberFather = ? AND gtcExemplaryControl.libraryUnitid in ( $libraryUnitId )");
        $this->setOrderBy('controlNumber');

        $data[] = $controlNumberFather;
        $sql = $this->select( $data );
        $rs  = $this->query($sql);
        //simplifica o array
        if ( is_array( $rs) )
        {
        	foreach ( $rs as $line => $info)
        	{
        		$result[] = $info[0];
        	}
        }
        return $result;
    }
    
    
    /**
     *  Método para obter todos números de controle.
     * 
     * @return array de objetos. Vetor com objetos stdClass contendo número de controle.
     */
    public function getAllControlNumbers()
    {
        $this->clear();
        $this->setColumns("DISTINCT controlNumber");
        $this->setTables("gtcmaterialcontrol");
        $sql = $this->select();
        $rs  = $this->query($sql, true);
        
        return $rs;
    }

    /**
     * Clean the class attributes
     *
     */
    function clean()
    {
        $this->controlNumber            =
        $this->entranceDate             =
        $this->lastChangeDate           =
        $this->materialGenderId         =
        $this->materialTypeId           =
        $this->controlNumberFather      =
        $this->category                 =
        $this->level                    = null;
    }
}
?>