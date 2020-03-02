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
 * Class created on 05/ago/2008
 *
 **/
class BusinessGnuteca3BusLibraryGroup extends GBusiness
{
    public $MIOLO;
    public $module;

    public  $libraryGroupId,
            $description,
            $observation;

    public  $libraryGroupIdS,
            $descriptionS;

    /**
     * Constructor Method
     */

    function __construct()
    {
        parent::__construct();

        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();

        $this->id = 'libraryGroupId';

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
            $table = "gtclibrarygroup";
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
            case 'insert':
            case 'update':
                $columns = array
                (
                    'description',
                    'observation'
                );
                break;

            case 'list':
                $columns = array
                (
                    'librarygroupid',
                    'description',
                );
                break;

            case 'select':
            default:
                $columns = array
                (
                    'librarygroupid',
                    'description',
                    'observation'
                );
                break;
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

        if(!empty($this->libraryGroupIdS) || !empty($this->libraryGroupId))
        {
            $where.= " libraryGroupId = ? AND ";
        }
        if(!empty($this->descriptionS))
        {
            $where.= " lower(description) LIKE lower(?) AND ";
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

        if(!empty($this->libraryGroupIdS))
        {
            $args[] = $this->libraryGroupIdS;
        }
        if(!empty($this->libraryGroupId))
        {
            $args[] = $this->libraryGroupId;
        }
        if(!empty($this->descriptionS))
        {
            $this->descriptionS = trim($this->descriptionS);
            $this->descriptionS = str_replace(" ", "%", $this->descriptionS);
            $args[] = "%{$this->descriptionS}%";
        }

        return $args;
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @return (array): An array containing the search results
     */

    public function searchLibraryGroup()
    {
        unset($this->libraryGroupId); //estava ocorrendo erro pos-busca no formulario
        $this->clear();
        $filters = array(
            'libraryGroupId'     => 'equals',
            'description'        => 'ilike',
            'ttagsags'           => 'ilike'
        );
        $result = $this->autoSearch($filters, $object);

        return $result;

    }

    /**
     * Insert a new record
     *
     * @return True if succed, otherwise False
     */

    public function insertLibraryGroup()
    {
        parent::clear();

        $this->setTables();
        $this->setColumns('insert');

        $data = array
        (
            $this->description,
            $this->observation
        );

        $sql = parent::insert($data);

        return parent::Execute();
    }

    /**
     * Atualiza um determinado registro
     *
     * @return True if succed, otherwise False
     */

    public function updateLibraryGroup()
    {
        parent::clear();

        $this->getWhereCondition();

        $this->setTables();
        $this->setColumns('update');

        $data = array
        (
            $this->description,
            $this->observation,
            $this->libraryGroupId
        );

        $sql = parent::update($data);

        return parent::Execute();
    }

    /**
     * retorna um determinado registro
     *
     * @param (int) $labelLayoutId - Id do registro
     * @return (Array)
     */

    public function getLibraryGroup($libraryGroupId)
    {
        parent::clear();

        $this->setTables();
        $this->setColumns('update');

        $this->libraryGroupId = $libraryGroupId;

        $this->getWhereCondition();
        parent::select($this->getDataConditionArray());

        $result = parent::query();

        if(!$result)
        {
            return false;
        }

        list
        (
            $this->description,
            $this->observation
        ) = $result[0];

        return $result[0];
    }

    /**
     * Delete a record
     *
     * @param $labelLayoutId (int): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     */

    public function deleteLibraryGroup($libraryGroupId)
    {
        parent::clear();

        $this->setTables();

        $this->libraryGroupId = $libraryGroupId;
        $this->getWhereCondition();

        parent::delete(array($libraryGroupId));
        
        return parent::Execute();
    }

    /**
     * List all records from the table handled by the class
     *
     * @param: (String) $orderBy - set order by selected records
     *
     * @return (array): Return an array with the entire table
     */

    public function listLibraryGroup($orderBy = "description")
    {
        parent::clear();

        $this->setTables();
        $this->setColumns("list");

        parent::setOrderBy($orderBy);
        parent::select();

        return parent::query();
    }

    public function clear()
    {
        $this->libraryGroupId =
        $this->description =
        $this->observation = null;
        parent::clear();
    }

}
?>
