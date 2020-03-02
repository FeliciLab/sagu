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
 * Separator business
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini   [eduardo@solis.coop.br]
 * Jamiel Spezia        [jamiel@solis.coop.br]
 * Luiz Gregory Filho   [luiz@solis.coop.br]
 * Moises Heberle       [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 25/05/2009
 *
 **/
class BusinessGnuteca3BusSeparator extends GBusiness
{
   // public $colsNoId;

    public $separatorId;
    public $cataloguingFormatId;
	public $fieldId;
    public $subFieldId;
	public $content;
	public $fieldId2;
    public $subFieldId2;

    public $separatorIdS;
    public $cataloguingFormatIdS;
	public $fieldIdS;
    public $subFieldIdS;
	public $contentS;
	public $fieldId2S;
    public $subFieldId2S;


    public function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcSeparator';
        $this->colsNoId = 'cataloguingFormatId,
                           fieldId,
                           subFieldId,
                           content,
                           fieldId2,
                           subFieldId2';
        $this->id = 'separatorId';
        $this->columns  =  $this->id . ', ' . $this->colsNoId;

    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listSeparator($object=FALSE)
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('separatorId');
        $sql = $this->select();
        $rs  = $this->query($sql, $object);
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
     **/
    public function getSeparator($separatorId)
    {
        $data = array($separatorId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('separatorId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject=true);
        $this->setData($rs[0]);

        return $this;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchSeparator($toObject=false)
    {
        $this->clear();

        if ( $this->separatorIdS )
        {
            $this->setWhere('separatorId = ?');
            $data[] = $this->separatorIdS;
        }
        if ( $this->cataloguingFormatIdS )
        {
            $this->setWhere('cataloguingFormatId = ?');
            $data[] = $this->cataloguingFormatIdS;
        }
        if ( $this->fieldIdS )
        {
            $this->setWhere('fieldId = ?');
            $data[] = $this->fieldIdS;
        }
        if ( $this->subFieldIdS )
        {
            $this->setWhere('subFieldId = ?');
            $data[] = $this->subFieldIdS;
        }
        if ( $this->contentS )
        {
            $this->setWhere('lower(content) LIKE lower(?)');
            //TODO neste Where existe a necessidade de buscar algumas vezes por :, no entanto o MIOLO não trata bem isso. Esta pendência está sendo vista no ticket #11835.
            $data[] = str_replace(':', '\:', $this->contentS) . '%';
        }
        if ( $this->fieldId2S )
        {
            $this->setWhere('fieldId2 = ?');
            $data[] = $this->fieldId2S;
        }
        if ( $this->subFieldId2S )
        {
            $this->setWhere('subFieldId2 = ?');
            $data[] = $this->subFieldId2S;
        }

        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('separatorId');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject);

        return $rs;
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertSeparator()
    {
        $this->clear();

        $sql = "INSERT INTO gtcSeparator ( cataloguingFormatId,fieldId,subFieldId,content,fieldId2,subFieldId2 ) VALUES ( '{$this->cataloguingFormatId}','{$this->fieldId}','{$this->subFieldId}',$$" . $this->content . "$$,'{$this->fieldId2}','{$this->subFieldId2}' )";
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
    public function updateSeparator()
    {        
        $sql  = "UPDATE gtcSeparator SET cataloguingFormatId= '{$this->cataloguingFormatId}',fieldId= '{$this->fieldId}',subFieldId= '{$this->subFieldId}',content= $$" . $this->content . "$$,fieldId2= '{$this->fieldId2}',subFieldId2= '{$this->subFieldId2}' WHERE separatorId = '{$this->separatorId}'";
        $rs  = $this->execute($sql);

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
     **/
    public function deleteSeparator($separatorId)
    {
        $data = array($separatorId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('separatorId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);

        return $rs;
    }


    /**
     * Retona uma separador para um determinada $tag
     *
     * @param char(5) $tag
     * @return array
     */
    public function getSeparatorByTag($tag)
    {
        $this->clean();
        list($this->fieldIdS, $this->subFieldIdS) = explode(".", $tag);

        $r = $this->searchSeparator();

        if(!$r)
        {
            return false;
        }

        $separator = null;

        foreach ($r as $index => $content)
        {
            $separator[$content[0]] = $content[4];
        }

        return $separator;
    }



    public function clean()
    {
        $this->separatorId          =
        $this->cataloguingFormatId  =
        $this->fieldId              =
        $this->subFieldId           =
        $this->content              =
        $this->fieldId2             =
        $this->subFieldId2          =
        $this->separatorIdS         =
        $this->cataloguingFormatIdS =
        $this->fieldIdS             =
        $this->subFieldIdS          =
        $this->contentS             =
        $this->fieldId2S            =
        $this->subFieldId2S         = null;
    }


} // final da classe
?>
