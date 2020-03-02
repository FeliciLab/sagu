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
 * This file handles the connection and actions for gtcTag table
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
 * Class created on 01/08/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusTag extends GBusiness
{
    public $removeData;
    public $cols;
    public $pkeys;
    public $pkeysWhere;
    public $fullColumns;
    public $tag;

    public $fieldId;
    public $subfieldId;
    public $description;
    public $observation;
    public $isRepetitive;
    public $hasSubfield;
    public $isActive;
    public $inDemonstration;
    public $isObsolete;
    public $help;
    public $helpX;

    public $fieldIdS;
    public $subfieldIdS;
    public $descriptionS;
    public $observationS;
    public $isRepetitiveS;
    public $hasSubfieldS;
    public $isActiveS;
    public $inDemonstrationS;
    public $isObsoleteS;
    public $helpS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcTag';
        $this->pkeys    = 'fieldId,
                           subfieldId';
        $this->id = $this->pkeys; //FIXME Feito deste modo para fazer funcionar teste unitario sem complicar com o resto do bus.
        $this->cols     = 'description,
                           observation,
                           isRepetitive,
                           hasSubfield,
                           isActive,
                           inDemonstration,
                           isObsolete,
                           help';
        $this->fullColumns = $this->pkeys . ',' . $this->cols;
        $this->pkeysWhere = 'fieldId = ? AND subfieldId = ?';
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertTag()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $sql = $this->insert( $this->associateData($this->fullColumns) );
        $rs  = $this->execute($sql);

        if (($this->subfieldId == MARC_SPACE) && ($this->tag))
        {
            foreach ($this->tag as $value)
            {
                $data = $value;
                $data->fieldId  = $this->fieldId;
                $this->setData($data);
                $this->insertTag();
            }
        }

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
    public function updateTag()
    {
        //$aux = clone($this);
        $get = $this->getTag($this->fieldId, $this->subfieldId, false);
        if ($get)
        {
            $this->clear();
            $this->setTables($this->tables);
            $this->setColumns($this->cols);
            $this->setWhere($this->pkeysWhere);
            $sql = $this->update( $this->associateData($this->cols . ',' . $this->pkeys) );
            $rs  = $this->execute($sql);

            if (($this->subfieldId == MARC_SPACE) && ($this->tag))
            {
                foreach ($this->tag as $value)
                {
                    if ($value->removeData)
                    {
                        $this->deleteTag($this->fieldId, $value->subfieldId);
                    }
                    else
                    {
                        $data = $value;
                        $data->fieldId = $this->fieldId;
                        $this->setData($data);
                        $this->updateTag();
                    }
                }
            }
            return $rs;
        }
        else
        {
            $this->insertTag();
        }
    }

    /**
     * Return a specific record from the database
     *
     * @param $fieldId (integer): Primary key of the record to be retrieved
     * @param $subfieldId (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getTag($fieldId, $subfieldId = null, $setData = true)
    {
        $data[] = $fieldId;
        
        if(!is_null($subfieldId))
        {
            $data[] = $subfieldId;
        }

        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns($this->fullColumns);

        $this->pkeysWhere = 'fieldId = ? ';
        if(!is_null($subfieldId))
        {
            $this->pkeysWhere.= ' AND subfieldId = ?';
        }

        $this->setWhere($this->pkeysWhere);
        $this->setOrderBy("subfieldId");

        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);

        if ($rs)
        {
            $rs[0]->helpX = $rs[0]->help;
            if ($subfieldId == MARC_SPACE)
            {
                $opts->toObject         = TRUE;
                $opts->excludeMarcSpace = TRUE;
                $this->fieldIdS = $fieldId;
                $search = $this->searchTag($opts);
                $rs[0]->tag = $search ? $search : array();
            }
            if ($setData)
            {
                $this->setData( $rs[0] );
            }
            return (!is_null($subfieldId) ? $rs[0] : $rs);
        }
        else
        {
            return false;
        }
    }

     /**
     * Retorna um array de objetos das tags solicitadas
     *
     * @param $fieldId (array): array com as tags a serem retornadas
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getTags($fields)
    {

        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns($this->fullColumns);

        unset($where);
        foreach ($fields as $field=>$subfields)
        {
            $where[$field] = "( fieldId = '{$field}' ";

            if (is_array($subfields))
            {
                unset($whereA);
                foreach ($subfields as $subfield)
                {
                    $whereA[] = " subfieldId = '{$subfield}' ";
                }
                $where[$field] .= ' AND ( ' . implode(' OR ', $whereA) . ') ';
            }

            $where[$field] .= ')';
        }

        $where = implode(' OR ', $where);


        $this->setWhere($where);
        $this->setOrderBy("subfieldId");

        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);

        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $fieldId (integer): Primary key for deletion
     * @param $subfieldId (integer): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteTag($fieldId, $subfieldId)
    {
        $data = array($fieldId, $subfieldId);
        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere($this->pkeysWhere);
        $sql = $this->delete($data);
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
    public function searchTag($options = NULL)
    {
        $this->clear();

        if ($this->fieldIdS)
        {
            $this->setWhere('fieldId = ?');
            $data[] = $this->fieldIdS;
        }

        if ($options->excludeMarcSpace)
        {
            $this->setWhere('subfieldId != ?');
            $data[] = MARC_SPACE;
        }
        else if ($this->subfieldIdS)
        {
            $this->setWhere('subfieldId = ?');
            $data[] = $this->subfieldIdS;
        }

        if ($this->descriptionS)
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = '%' . $this->descriptionS . '%';
        }
        if ($this->observationS)
        {
            $this->setWhere('lower(observation) LIKE lower(?)');
            $data[] = '%' . $this->observationS . '%';
        }
        if ($this->isRepetitiveS)
        {
            $this->setWhere('isRepetitive = ?');
            $data[] = $this->isRepetitiveS;
        }
        if ($this->hasSubfieldS)
        {
            $this->setWhere('hasSubfield = ?');
            $data[] = $this->hasSubfieldS;
        }
        if ($this->isActiveS)
        {
            $this->setWhere('isActive = ?');
            $data[] = $this->isActiveS;
        }
        if ($this->inDemonstrationS)
        {
            $this->setWhere('inDemonstration = ?');
            $data[] = $this->inDemonstrationS;
        }
        if ($this->isObsoleteS)
        {
            $this->setWhere('isObsolete = ?');
            $data[] = $this->isObsoleteS;
        }
        if ($this->helpS)
        {
            $this->setWhere('lower(help) LIKE lower(?)');
            $data[] = $this->helpS;
        }


        if ($this->subfieldIdS == MARC_SPACE)
        {
            $this->setOrderBy('fieldId');
        }
        else
        {
            $this->setOrderBy('subfieldId');
        }

        $this->setTables($this->tables);
        $this->setColumns($this->fullColumns);
        $sql = $this->select($data);
        $rs  = $this->query($sql, $options->toObject ? TRUE : FALSE);
        return $rs;
    }


    /**
     * List all records from the table handled by the class
     *
     * @return (array): Return an array with the entire table
     *
     **/
    public function listTag()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    public function setData($data)
    {
        $this->fieldId          = NULL;
        $this->subfieldId       = NULL;
        $this->description      = NULL;
        $this->observation      = NULL;
        $this->isRepetitive     = NULL;
        $this->hasSubfield      = NULL;
        $this->isActive         = NULL;
        $this->inDemonstration  = NULL;
        $this->isObsolete       = NULL;
        $this->help             = NULL;
        parent::setData($data);
    }




    /**
     *
     **/
    public function isRepetitive($fieldId, $subfieldId = null)
    {
        $data[] = $fieldId;
        if(!is_null($subfieldId))
        {
            $data[] = $subfieldId;
        }

        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns('isRepetitive');

        $this->pkeysWhere = 'fieldId = ? ';
        if(!is_null($subfieldId))
        {
            $this->pkeysWhere.= ' AND subfieldId = ?';
        }

        $this->setWhere($this->pkeysWhere);
        $this->setOrderBy("subfieldId");

        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);

        if ($rs)
        {
            return $rs[0]->isRepetitive == 't';
        }

        return false;
    }



    /**
     *
     **/
    public function getHelp($fieldId, $subfieldId = null)
    {
        $data[] = $fieldId;
        if(!is_null($subfieldId))
        {
            $data[] = $subfieldId;
        }

        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns('help');

        $this->pkeysWhere = 'fieldId = ? ';
        if(!is_null($subfieldId))
        {
            $this->pkeysWhere.= ' AND subfieldId = ?';
        }

        $this->setWhere($this->pkeysWhere);
        $this->setOrderBy("subfieldId");

        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);

        if ($rs)
        {
            return $rs[0]->help;
        }

        return false;
    }







    public function getTagName($fieldId, $subfieldId = '#')
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns("description");


        $data[] = $fieldId;
        $where = 'fieldId = ? ';

        $data[] = $subfieldId;
        $where.= ' AND subfieldId = ?';

        $this->setWhere($where);

        $sql = $this->select($data);
        $rs  = $this->query($sql);

        if (!$rs)
        {
            return null;
        }

        return $rs[0][0];
    }


    public function getTagNameByTag($tag)
    {
        list($f, $s) = explode(".", $tag);
        return $this->getTagName($f,$s);
    }

    
    /**
     * Busca os campos Marc
     * @param $tag
     */
    public function searchFieldId($tag)
    {
    	$this->clear();
    	$this->setColumns('distinct(fieldid) as fieldid');
    	$this->setTables($this->tables);
    	$this->setWhere("fieldid LIKE '{$tag}%'");
    	$this->setOrderBy('fieldId');
    	
    	$sql = $this->select();
    	$rs = $this->query($sql, true);
        
    	return $rs;
    }
    
    /**
     *  Método estático para testar se o campo é um campo de controle.
     * 
     * @param String $fieldId Campo MARC.
     * @return boolean Retorna positivo caso seja um campo de controle.
     */
    public static function isControlField($fieldId)
    {
         return in_array($fieldId, array('000', '001', '003', '005', '008'));
    }

}

?>
