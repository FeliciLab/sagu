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
 * 
 *
 * @author Luiz G Gregory Filho [luiz@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 11/ago/2008
 *
 **/

/**
 * Class to manipulate 
 **/

class BusinessGnuteca3BusLibraryAssociation extends GBusiness
{
    /**
     * Attributes
     */

    public $MIOLO;

    public $associationId;
    public $libraryUnitId;

    public $libraries;

    public  $descriptionS,
            $libraryUnitIdS;
       
    /**
     * Constructor Method
     */
    
    function __construct()
    {
        parent::__construct();
        $this->setData(null);
        $this->setColumns();
        $this->setTables();
    }

    /**
     * Seta as tabelas
     *
     * @param (String || Array) $tables
     */
    
    public function setTables($type = null)
    {
        switch($type)
        {
            case "get":
                $table = " gtcLibraryAssociation LA ";
                $table.= "  LEFT JOIN gtcLibraryUnit LU ON (LA.libraryUnitId = LU.libraryUnitId) ";
                break;

            case "delete" :
            case "insert" :
                $table = " gtcLibraryAssociation ";
                break;

            case "select":
                $table = " gtcLibraryAssociation LA ";
                $table.= "  LEFT JOIN gtcLibraryUnit LU ON (LA.libraryUnitId = LU.libraryUnitId) ";
                $table.= "  LEFT JOIN gtcAssociation A  ON (LA.associationId = A.associationId) ";
                break;

            default:
                $table = 'gtcLibraryAssociation';
        }

        parent::setTables($table);
    }
        
    /**
     * Este método seta as colunas da tabela.     
     *
     * @param (String || Array) $columns
     */
    
    public function setColumns($type = null)
    {
        switch($type)
        {
            case "search" :
                $columns = array
                (
                    'LA.associationId',
                    ' A.description',
                    'LA.libraryUnitId',
                    'LU.libraryname',
                );                      
                break;

            case "getAssociationId":
                $columns = array
                (
                    ' distinct LA.associationId'
                );
                break;

            case "get":
                $columns = array
                (
                    'LA.libraryUnitId as libraryUnitId ',
                    'LU.libraryname as libraryUnitDescription ',
                );                      
                break;                

            case "insert":
                $columns = array
                (
                    'associationId',
                    'libraryunitId',
                );
                break;

            case "associationId":
                $columns = 'associationId';
                break;

            case "libraryUnitId":
                $columns = 'DISTINCT libraryUnitId';
                break;

            case "All":
            default:
                $columns = array
                (
                    'LA.associationId',
                    'LA.libraryunitId',
                );            
        }

        parent::setColumns($columns);
    }      

    /**
     * Recebe a lista de bibliotecas de repetitive field.
     *
     * @param Array Object
     */

    public function setLibraries($libraries)
    {
        $this->libraries = $libraries;
    }

    /**
     * Seta as condições do sql
     *
     * @return void
     */
    
    public function getWhereCondition()
    {
        $where = "";
        
        if(!empty($this->associationIdS))
        {
            if(is_string($this->associationIdS))
            {
                $where.= " LA.associationId = ? AND ";
            }
            elseif(is_array($this->associationIdS))
            {
                $ex = implode(", ", $this->associationIdS);
                $where.= " LA.associationId IN ($ex) AND";
            }
        }
        if(!empty($this->associationId))
        {
            $where.= " associationId = ?    AND ";
        }
        if(!empty($this->libraryUnitId))
        {
            $where.= " libraryUnitId = ?    AND ";
        }
        if(!empty($this->libraryUnitIdS))
        {
            $where.= " LU.libraryUnitId = ? AND ";
        }
        if(!empty($this->descriptionS))
        {
            $where.= " lower(A.description) LIKE lower(?) AND ";
        }
        if(!empty($this->librarynameS))
        {
            $where.= " lower(LU.libraryname) LIKE lower(?) AND ";
        }

        if(strlen($where))
        {
            $where = substr($where, 0, strlen($where) - 4);
            parent::setWhere($where);
        }
    }
        
    /**
     * Trabalha o Data Object retornado do form
     * 
     * transforma em um array para enviar para o where condition do sql
     *
     * @return (Array) $args
     */
    
    private function getDataConditionArray()
    {
        $args = array();
        
        if(!empty($this->associationIdS) && is_string($this->associationIdS))
        {
            $args[] = $this->associationIdS;
        }          
        if(!empty($this->associationId))
        {
            $args[] = $this->associationId;
        }     
        if(!empty($this->libraryUnitIdS))
        {
            $args[] = $this->libraryUnitIdS;
        }
        if(!empty($this->descriptionS))
        {
            $this->descriptionS = trim($this->descriptionS);
            $this->descriptionS = str_replace(" ", "%", $this->descriptionS);
            $args[] = "%{$this->descriptionS}%";
        }
        if(!empty($this->librarynameS))
        {
            $this->librarynameS = trim($this->librarynameS);
            $this->librarynameS = str_replace(" ", "%", $this->librarynameS);
            $args[] = "%{$this->librarynameS}%";
        }

        return $args;
    }

    /**
     * Retorna o id de uma determinada associação
     *
     * @return (int)
     */

    public function getAssociationId()
    {
        parent::clear();

        $this->getWhereCondition();
        $this->setTables("select");
        $this->setColumns("getAssociationId");

        $sql = parent::select($this->getDataConditionArray());

        $result = parent::query();

        if(!$result)
        {
            return false;
        }

        $return = array();

        foreach($result as $v)
        {
            $return[] = $v[0];
        }

        return $return;
    }   


    /**
     * Do a search on the database table handled by the class
     *
     * @return (array): An array containing the search results
     */
    
    public function searchAssociation()
    {
        $assId = $this->getAssociationId();

        if(!$assId)
        {
            return false;
        }

        parent::clear();

        $this->dataCondition = null;
        $this->associationIdS = $assId;

        $this->getWhereCondition();
        $this->setTables("select");
        $this->setColumns("search");
        
        $sql = parent::select($this->getDataConditionArray());
        $result = parent::query();;
        if(!$result)        
        {
            return false;
        }
        
        $return = array();

        foreach($result as $i => $v)
        {
            $return[$v[0]][0] = $v[0];
            $return[$v[0]][1] = $v[1];
            $return[$v[0]][2].= "{$v[2]} - {$v[3]}<br>";
        }

        return $return;
    }
    
    
    public function searchLibraryAssociation($toObject = false)
    {
        $this->clear();
        $this->setTables('select');
        $this->setColumns('search');
    	
    	if ($this->associationId)
    	{
    		$this->setWhere('LA.associationId = ?');
    		$args[] = $this->associationId;
    	}
    	if ($this->libraryUnitId)
    	{
    		$this->setWhere('LA.libraryUnitId = ?');
    		$args[] = $this->libraryUnitId;
    	}

    	$sql   = $this->select($args);
    	$query = $this->query($sql, $toObject);
    	return $query;
    }
    

    /**
     * Insert a new record
     * 
     * @return True if succed, otherwise False     
     */
    
    public function insertLibraryAssociation()
    {
        $ok         = array();
        $libraries  = array();
        
        foreach($this->libraries as $i => $values)
        {
            // filtra unidades reperidas para
            if(array_search($values->libraryUnitId, $libraries) !== false)
            {
                continue;
            }
            
            //verifica se eh para remover
            if ($values->removeData)
            {
            	continue;
            }
            
            $libraries[] = $values->libraryUnitId;

            parent::clear();
            
            $this->setTables("insert");
            $this->setColumns("insert");
                    
            $data = array
            (
                $this->associationId,
                $values->libraryUnitId,
            );
                    
            $sql = parent::insert($data);

            $ok[$i] = parent::Execute();
        }

        return (array_search(false, $ok) === false);
    }

    /**
     * Atualiza um determinado registro
     * 
     * @return True if succed, otherwise False     
     */
    
    public function updateLibraryAssociation()
    {
        $ok = $this->deleteLibraryAssociation($this->associationId);

        if(!$ok)
        {
            return false;
        }

        return $this->insertLibraryAssociation();
    }   
    
    /**
     * retorna um determinado registro
     *
     * @param (int) $associationId - Id do registro
     * @return (Array)
     */
    
    public function getLibraries($associationId)
    {
        parent::clear();
        
        $this->setTables("get");
        $this->setColumns("get");
        
        $this->associationIdS    = $associationId;
        
        $this->getWhereCondition();
        $sql = parent::select($this->getDataConditionArray());
        
        $result = parent::query(null, 1);                  

        return $result;
    }
    
    /**
     * Delete a record
     *
     * @param $associationId (int): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     */
    
    public function deleteLibraryAssociation($associationId, $libraryUnitId = null)
    {        
        parent::clear();               

        $this->setTables("delete");      
        
        $this->associationId = $associationId;
        $args[] = $associationId;

        if(!is_null($libraryUnitId))
        {
            $this->libraryUnitId = $libraryUnitId;
            $args[] = $libraryUnitId;
        }

        $this->getWhereCondition();    
        
        $sql = parent::delete($args);

        return parent::Execute();
    }


    public function getLibrariesAssociationOf($libraryUnitId)
    {
        //Get associations
        $this->clear();
        $this->setTables('gtcLibraryAssociation');
        $this->setColumns('associationId');
        $this->setWhere('libraryUnitId = ?');
        $sql    = $this->select(array($libraryUnitId));
        $query  = $this->query($sql);

        if ($query)
        {
	        $associations = array();
	        for ($i=0; $i < count($query); $i++)
	        {
	            $associations[] = $query[$i][0];
	        }
	        //Get libraries
	        if (count($associations) > 0)
	        {
	            $this->clear();
	            $this->setTables('gtcLibraryAssociation');
	            $this->setColumns('libraryUnitId');
	            $this->setWhere('associationId IN ('.implode(',', $associations).')');
	            $sql   = $this->select();
	            $query = $this->query($sql);
	
	            if ($query)
	            {
			        $libraries = array();
			        for ($i=0; $i < count($query); $i++)
			        {
			            $libraries[] = $query[$i][0];
			        }
                    return $libraries;
	            }
	        }
        }
        
        return array($libraryUnitId);
    }
}
?>
