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
 * PrefixSuffix business
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini   [eduardo@solis.coop.br]
 * Jamiel Spezia        [jamiel@solis.coop.br]
 * Luiz Gregory Filho   [luiz@solis.coop.br]
 * Moises Heberle       [moises@solis.coop.br]
 *
 * @since
 * Class created on 06/01/2009
 *
 * */
class BusinessGnuteca3BusPrefixSuffix extends GBusiness
{

    public $prefixSuffixId;
    public $fieldId;
    public $subFieldId;
    public $content;
    public $type;
    public $prefixSuffixIdS;
    public $fieldIdS;
    public $subFieldIdS;
    public $contentS;
    public $typeS;

    const TYPE_PREFIX = 1;
    const TYPE_SUFFIX = 2;

    public function __construct()
    {
        parent::__construct();
        $this->tables = 'gtcPrefixSuffix';
        $this->colsNoId = 'fieldId,
                           subFieldId,
                           content,
                           type';
        $this->id = 'prefixSuffixId';
        $this->columns = $this->id . ', ' . $this->colsNoId;
    }

    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     * */
    public function listPrefixSuffix($object = FALSE)
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('prefixSuffixId');
        $sql = $this->select();
        $rs = $this->query($sql, $object);
        return $rs;
    }

    /**
     * Return a specific record from the database
     *
     * @param $moduleConfig (integer): Primary key of the record to be retrieved
     * @param $parameter (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     * */
    public function getPrefixSuffix($prefixSuffixId)
    {
        $data = array($prefixSuffixId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('prefixSuffixId = ?');
        $sql = $this->select($data);
        $rs = $this->query($sql, $toObject = true);
        $this->setData($rs[0]);

        return $this;
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     * */
    public function searchPrefixSuffix($toObject = false)
    {
        $this->clear();

        if ($v = $this->prefixSuffixIdS)
        {
            $this->setWhere('prefixSuffixId = ?');
            $data[] = $v;
        }
        if ($v = $this->fieldIdS)
        {
            $this->setWhere('fieldId = ?');
            $data[] = $v;
        }
        if ($v = $this->subFieldIdS)
        {
            $this->setWhere('subFieldId = ?');
            $data[] = $v;
        }
        if ($v = $this->contentS)
        {
            $this->setWhere('lower(content) LIKE lower(?)');
            $data[] = '%' . $v . '%';
        }
        if ($v = $this->typeS)
        {
            $this->setWhere('type = ?');
            $data[] = $v;
        }

        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('prefixSuffixId');
        $sql = $this->select($data);

        $rs = $this->query($sql, $toObject);

        return $rs;
    }

    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     * */
    public function insertPrefixSuffix()
    {
        $tags = $this->getTag($this->fieldId, $this->subFieldId);
        if (!$tags)
        {
            throw new Exception(_M("A tag {$this->fieldId}.{$this->subFieldId} é inválida."));
        }
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $data = array($this->fieldId,
            $this->subFieldId,
            $this->content,
            $this->type);
        $sql = $this->insert($data);
        $rs = $this->execute($sql);

        return $rs;
    }

    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     * */
    public function updatePrefixSuffix()
    {
        $tags = $this->getTag($this->fieldId, $this->subFieldId);
        if (!$tags)
        {
            throw new Exception(_M("A tag {$this->fieldId}.{$this->subFieldId} é inválida."));
        }
        $data = array($this->fieldId,
            $this->subFieldId,
            $this->content,
            $this->type,
            $this->prefixSuffixId);
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('prefixSuffixId = ?');
        $sql = $this->update($data);
        $rs = $this->execute($sql);

        return $rs;
    }

    /**
     * Delete a record
     *
     * @param $moduleConfig (string): Primary key for deletion
     * @param $parameter (string): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     * */
    public function deletePrefixSuffix($prefixSuffixId)
    {
        $data = array($prefixSuffixId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('prefixSuffixId = ?');
        $sql = $this->delete($data);
        $rs = $this->execute($sql);

        return $rs;
    }

    /**
     * Lista os tipo
     *
     * @return array
     */
    public function listTypes()
    {
        $listType = array
            (
            1 => _M('Prefixo', $this->module),
            2 => _M('Sufixo', $this->module)
        );
        return $listType;
    }

    /**
     * Verifica se exite prefixo ou sufixo para uma determinada tag
     *
     * @param char(5) $tag
     * @return bollean
     */
    public function getPrefixSuffixForTag($tag)
    {
        $this->clear();
        list($this->fieldId, $this->subFieldId) = explode(".", $tag);
        $filters = array
            (
            'fieldId' => 'like',
            'subFieldId' => 'like',
        );

        $d = $this->autoSearch($filters, true);

        return isset($d[0]) ? $d[0] : false;
    }

    /**
     * retorna os prefixos de uma determinada tag
     *
     * @param char(5) $tag
     * @return array
     */
    public function getPrefixForTag($tag, $forSelection = false)
    {
        $this->clear();
        list($this->fieldId, $this->subFieldId) = explode(".", $tag);
        $this->type = 1;

        $filters = array
            (
            'fieldId' => 'equals',
            'subFieldId' => 'equals',
            'type' => 'equals'
        );

        $prefix = $this->autoSearch($filters, true);

        if ($forSelection && $prefix)
        {
            $prefixFor = $prefix;
            $prefix = null;
            foreach ($prefixFor as $values)
            {
                $prefix[$values->prefixSuffixId] = $values->content;
            }
        }

        return $prefix;
    }

    /**
     * retorna os suffixos de uma determinada tag
     *
     * @param char(5) $tag
     * @return array
     */
    public function getSuffixForTag($tag, $forSelection = true)
    {
        $this->clear();
        list($this->fieldId, $this->subFieldId) = explode(".", $tag);
        $this->type = 2;

        $filters = array
            (
            'fieldId' => 'equals',
            'subFieldId' => 'equals',
            'type' => 'equals'
        );

        $suffix = $this->autoSearch($filters, true);

        if ($forSelection && $suffix)
        {
            $suffixFor = $suffix;
            $suffix = null;
            foreach ($suffixFor as $values)
            {
                $suffix[$values->prefixSuffixId] = $values->content;
            }
        }

        return $suffix;
    }

    public function getTag($fieldId, $subfieldId)
    {
        $gtcTag = $this->MIOLO->getBusiness($this->module, 'BusTag');
        $gtcTag->setTables('gtcTag');
        $gtcTag->setColumns('fieldid, subfieldid');
        $gtcTag->setWhere("fieldId = ? AND subfieldid = ?");
        $args[] = $fieldId;
        $args[] = $subfieldId;
        $gtcTag->select($args);
        $sql = $gtcTag->query();
        if (!$sql)
        {
            return null;
        }
        return ($sql[0][0] . '.' . $sql[0][1]);
    }

}

?>
