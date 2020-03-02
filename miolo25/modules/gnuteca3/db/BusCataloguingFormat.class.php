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
 * Cataloguing format business
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
 * Class created on 30/07/2009
 *
 **/
class BusinessGnuteca3BusCataloguingFormat extends GBusiness
{
   // public $colsNoId;

    public $cataloguingFormatId;
	public $description;
    public $observation;

    public $cataloguingFormatIdS;
	public $descriptionS;
    public $observationS;


    public function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcCataloguingFormat';
        $this->colsNoId = 'description,
                           observation';
        $this->columns  = 'cataloguingFormatId, ' . $this->colsNoId;

    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listCataloguingFormat($object=FALSE, $associate = FALSE)
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('cataloguingFormatId');
        $sql = $this->select();
        if ($associate)
        {
            $rs  = $this->query($sql, true);
            if ($rs)
            {
                foreach ($rs as $v)
                {
                    $out[$v->cataloguingFormatId] = $v->description;
                }
            }
            return $out;            
        }
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
    public function getCataloguingFormat($cataloguingFormatId)
    {
        $data = array($cataloguingFormatId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('cataloguingFormatId = ?');
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
    public function searchCataloguingFormat()
    {
        $this->clear();

        if ($v = $this->cataloguingFormatIdS)
        {
            $this->setWhere('cataloguingFormatId = ?');
            $data[] = $v;
        }
        if ($v = $this->descriptionS)
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = $v . '%';
        }
        if ($v = $this->observationS)
        {
            $this->setWhere('lower(observation) LIKE lower(?)');
            $data[] = $v . '%';
        }

        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('cataloguingFormatId');
        $sql = $this->select($data);
        $rs  = $this->query($sql);

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
    public function insertCataloguingFormat()
    {
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $data= array($this->description,
                     $this->observation);
        $sql = $this->insert($data);
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
    public function updateCataloguingFormat()
    {
        $data= array($this->description,
                     $this->observation);
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('cataloguingFormatId = ?');
        $sql = $this->update($data);
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
    public function deleteCataloguingFormat($cataloguingFormatId)
    {
        $data = array($cataloguingFormatId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('cataloguingFormatId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);

        return $rs;
    }



    public function clean()
    {
        $this->cataloguingFormatId  =
        $this->description          =
        $this->observation          = null;
    }


} // final da classe
?>
