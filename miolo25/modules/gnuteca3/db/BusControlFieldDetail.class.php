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
 * Class created on 13/10/2008
 *
 **/

/**
 * Class to manipulate
 **/

class BusinessGnuteca3BusControlFieldDetail extends GBusiness
{
    /**
     * Attributes
     */
    public  $MIOLO;


    public  $fieldId,
            $subfieldId,
            $beginPosition,
            $lenght,
            $description,
            $categoryId,
            $marcTagListId,
            $isActive;

    /**
     * Constructor Method
     */
    function __construct()
    {
        parent::__construct();

        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
    }

    /**
     * Seta as tabelas
     *
     * @param (String || Array) $tables
     */
    public function setTables($table = null)
    {
        if(is_null($table))
        {
            $table = "gtcControlFieldDetail";
        }

        parent::setTables($table);
    }


    /**
     * Este método seta as colunas da tabela.
     *
     * @param (String || Array) $columns
     */
    public function setColumns($type = "All")
    {
        switch($type)
        {
            default     :
            case "All"  :
                $columns = array
                (
                    'fieldid',
                    'subfieldid',
                    'beginposition',
                    'lenght',
                    'description',
                    'categoryid',
                    'marctaglistid',
                    'isactive',
                    'defaultvalue'
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

        if(!empty($this->fieldId))
        {
            $where.= " fieldId = ? AND ";
        }

        if(!empty($this->subfieldId))
        {
            $where.= " subfieldId = ? AND ";
        }

        if(!empty($this->categoryId))
        {
            $where.= " categoryId = ? AND ";
        }

        if(!empty($this->isActive))
        {
            $where.= " isActive = ? AND ";
        }

        if(!empty($this->marcTagListId))
        {
            $where.= " marcTagListId = ? AND ";
        }

        if(!empty($this->descriptionS))
        {
            $where.= " description = ? AND ";
        }

        if(!is_null($this->beginPosition))
        {
            $where.= " beginPosition = ? AND ";
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

        if(!empty($this->fieldId))
        {
            $args[] = $this->fieldId;
        }

        if(!empty($this->subfieldId))
        {
            $args[] = $this->subfieldId;
        }

        if(!empty($this->categoryId))
        {
            $args[] = $this->categoryId;
        }

        if(!empty($this->isActive))
        {
            $args[] = $this->isActive;
        }

        if(!empty($this->marcTagListId))
        {
            $args[] = $this->marcTagListId;
        }

        if(!empty($this->marcTagListIdS))
        {
            $this->marcTagListIdS = trim($this->marcTagListIdS);
            $this->marcTagListIdS = str_replace(" ", "%", $this->marcTagListIdS);
            $args[] = "%{$this->marcTagListIdS}%";
        }

        if(!empty($this->descriptionS))
        {
            $this->descriptionS = trim($this->descriptionS);
            $this->descriptionS = str_replace(" ", "%", $this->descriptionS);
            $args[] = "%{$this->descriptionS}%";
        }

        if(!is_null($this->beginPosition))
        {
            $args[] = $this->beginPosition;
        }


        return $args;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @return (array): An array containing the search results
     */
    public function searchControlFieldDetail()
    {
        parent::clear();
        $this->setTables();
        $this->setColumns();
        $this->getWhereCondition();
        parent::setOrderBy("beginposition");
        $sql = parent::select($this->getDataConditionArray());
        return parent::query();
    }


    /**
     * Insert a new record
     *
     * @return True if succed, otherwise False
     */
    public function insertControlFieldDetail()
    {
        parent::clear();

        $this->setTables();
        $this->setColumns();

        $data = array
        (
        );

        $sql = parent::insert($data);

        return parent::Execute();

    }


    /**
     * Atualiza um determinado registro
     *
     * @return True if succed, otherwise False
     */
    public function updateControlFieldDetail()
    {
        parent::clear();

        $this->marcTagListId = $this->marcTagListId;
        $this->getWhereCondition();

        $this->setTables();
        $this->setColumns("select");

        $data = array
        (
        );

        $sql = parent::update($data);

        return parent::Execute();
    }


    /**
     * retorna um determinado registro
     *
     * @return (Array)
     */
    public function getControlFieldDetail($fieldId = null, $subField = null, $categoryId = null, $isActive = null)
    {
        parent::clear();

        $this->clean();
        $this->setTables();
        $this->setColumns("All");

        $this->fieldId      = $fieldId;
        $this->subfieldId   = $subField;
        $this->categoryId   = $categoryId;
        $this->isActive     = $isActive;

        $this->getWhereCondition();
        parent::setOrderBy(" beginposition ");
        $sql = parent::select($this->getDataConditionArray());
        return parent::query($sql , true);
    }


 /**
     * retorna um determinado registro
     *
     * @return (Array)
     */
    public function getControlFieldDetailEmptyValue($fieldId, $subField = null, $beginPosition = '#')
    {
        parent::clear();
        $this->clean();

        $this->setTables();
        parent::setColumns("emptyValue");

        $this->fieldId          = $fieldId;
        $this->subfieldId       = $subField;
        $this->beginPosition    = $beginPosition;

        $this->getWhereCondition();
        $sql = parent::select($this->getDataConditionArray());

        return parent::query($sql , true);
    }


    /**
     * Delete a record
     *
     *
     * @return (boolean): True if succeed, otherwise False
     */
    public function deleteControlFieldDetail($fieldId)
    {
        parent::clear();

        $this->setTables();

        $this->fieldId = $fieldId;
        $this->getWhereCondition();

        parent::delete(array($fieldId));

        return parent::Execute();
    }


    public function clean()
    {
            $this->fieldId=
            $this->subfieldId=
            $this->beginPosition=
            $this->lenght=
            $this->description=
            $this->categoryId=
            $this->marcTagListId=
            $this->isActive =null;
    }

}
?>
