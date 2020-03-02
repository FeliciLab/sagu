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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/

/**
 * Class to manipulate
 **/

class BusinessGnuteca3BusAssociation extends GBusiness
{
    /**
     * Attributes
     */

    public $MIOLO;
    public $busLibraryAssociation;
    public $libraryAssociation;

    public $associationId;
    public $description;
    public $libraryUnitIdS;

    public  $associationIdS,
            $descriptionS;

    /**
     * Constructor Method
     */

    function __construct()
    {
        parent::__construct();

        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->busLibraryAssociation = $this->MIOLO->getBusiness($this->module, 'BusLibraryAssociation');

        $this->setData(null);
        $this->setColumns();
        $this->setTables();
    }

    /**
     * Seta as tabelas
     *
     * @param (String || Array) $tables
     */

    public function setTables($tables = null)
    {
        if(is_null($table))
        {
            $table = "gtcAssociation";
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
            case "update":
            case "insert" :
                $columns = array
                (
                    'description',
                );
                break;

            case "maxId":
                $columns = array
                (
                    ' MAX(associationId) as associationId ',
                );
                break;

            case 'search':
                $columns = array(
                    'DISTINCT A.associationId',
                    'A.description'
                );
                break;

            case "All":
            default:
                $columns = array
                (
                    'associationId',
                    'description',
                );
        }

        parent::setColumns($columns);
    }

    /**
     * Seta as condições do sql
     *
     * @return void
     */

    public function getWhereCondition()
    {
        $where = "";

        if(!empty($this->associationIdS) || !empty($this->associationId))
        {
            $where.= " associationId = ? AND ";
        }
        if(!empty($this->descriptionS))
        {
            $where.= " lower(description) LIKE lower(?) AND ";
        }
        if(!empty($this->libraryUnitIdS))
        {
            $where.= " B.libraryUnitId in (" . $this->libraryUnitIdS . ") AND ";
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

        if(!empty($this->associationIdS))
        {
            $args[] = $this->associationIdS;
        }
        else if(!empty($this->associationId))
        {
            $args[] = $this->associationId;
        }
        if(!empty($this->descriptionS))
        {
            $this->descriptionS = trim($this->descriptionS);
            $this->descriptionS = str_replace(" ", "%", $this->descriptionS);
            $args[] = "%{$this->descriptionS}%";
        }
        if(!empty($this->libraryUnitIdS))
        {
            $args[] = $this->libraryUnitIdS;
        }
        return $args;
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @return (array): An array containing the search results
     */

    public function searchAssociation()
    {
        parent::clear();
        parent::setTables("gtcAssociation A
             INNER JOIN gtcLibraryAssociation B
                  USING (associationid)");
        parent::setColumns('
        DISTINCT A.associationId,
        A.description'
        );

        $associationId = (!empty($this->associationIdS)) ? $this->associationIdS : $this->associationId;
        if(!empty($associationId))
        {
            parent::setWhere('associationId = ?');
            $args[] = $associationId;
        }
        if(!empty($this->descriptionS))
        {
            parent::setWhere('lower(description) LIKE lower(?)');
            $args[] = $this->descriptionS;
        }
        if(!empty($this->libraryUnitIdS))
        {
            parent::setWhere("B.libraryUnitId IN ('{$this->libraryUnitIdS}')");
            $args[] = $this->libraryUnitIdS;
        }

        $sql = parent::select($args);
        return parent::query();
    }

    /**
     * Insert a new record
     *
     * @return True if succed, otherwise False
     */

    public function insertAssociation()
    {
        parent::clear();

        $this->setTables();
        $this->setColumns("insert");

        $data = array
        (
            $this->description,
        );

        parent::insert($data);
        $ok = parent::Execute();

        if ($this->libraryAssociation)
        {
            $this->busLibraryAssociation->setLibraries( $this->libraryAssociation );
            $this->busLibraryAssociation->associationId = $this->getMaxAssociation();
            $this->busLibraryAssociation->insertLibraryAssociation();
        }
        
        return ($ok);
    }

    /**
     * Atualiza um determinado registro
     *
     * @return True if succed, otherwise False
     */

    public function updateAssociation()
    {
        parent::clear();

        $this->getWhereCondition();

        $this->setTables();
        $this->setColumns("update");

        $data = array
        (
            $this->description,
            $this->associationId
        );

        parent::update($data);
        $ok  = parent::Execute();

        if ($this->libraryAssociation)
        {
        	$this->busLibraryAssociation->setLibraries($this->libraryAssociation);
        	$this->busLibraryAssociation->associationId = $this->associationId;
        	$this->busLibraryAssociation->updateLibraryAssociation();
        }
        
        return ($ok);
    }

    /**
     * retorna um determinado registro
     *
     * @param (int) $associationId - Id do registro
     * @return (Array)
     */

    public function getAssociation($associationId)
    {
        parent::clear();

        $this->setTables();
        $this->setColumns("All");

        $this->associationId = $associationId;

        $this->getWhereCondition();
        parent::select($this->getDataConditionArray());

        $result = parent::query();

        if(!$result)
        {
            return false;
        }

        list
        (
            $this->associationId ,
            $this->description,

        ) = $result[0];
        
        $this->libraryAssociation = $this->busLibraryAssociation->getLibraries($associationId);
        return $this;
    }

    /**
     * retorna maior ID
     *
     * @return Integer
     */

    public function getMaxAssociation()
    {
        parent::clear();

        $this->setTables();
        $this->setColumns("maxId");

        parent::select();

        $result = parent::query();

        if(!$result)
        {
            return false;
        }

        return $result[0][0];
    }

    /**
     * Delete a record
     *
     * @param $associationId (int): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     */

    public function deleteAssociation($associationId)
    {
        parent::clear();

        $this->setTables();

        $this->associationId = $associationId;
        $this->getWhereCondition();

        $this->busLibraryAssociation->deleteLibraryAssociation($associationId);
        
        parent::delete(array($associationId));
        $ok = parent::Execute();
        
        return ($ok);
    }

    /**
     * List all records from the table handled by the class
     *
     * @param: (String) $orderBy - set order by selected records
     *
     * @return (array): Return an array with the entire table
     */

    public function listAssociation($orderBy = "description")
    {
        parent::clear();

        $this->setTables();
        $this->setColumns("All");

        parent::setOrderBy($orderBy);
        parent::select();

        return parent::query();
    }


}
?>
