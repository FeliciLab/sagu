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
 * This file handles the connection and actions for Material Type table
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
 * Class created on 29/07/2008
 *
 **/
class BusinessGnuteca3BusMaterialType extends GBusiness
{
    public $colsNoId;

    public $materialTypeId;
    public $description;
    public $isRestricted;
    public $level;
    public $observation;

    public $materialTypeIdS;
    public $descriptionS;
    public $isRestrictedS;
    public $levelS;
    public $observationS;


    /**
     * Class constructor
     **/
    public function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcMaterialType';
        $this->colsNoId = 'description,
                           isRestricted,
                           level,
                           observation';
        $this->id = 'materialTypeId';
        $this->columns  = $this->id . ', ' . $this->colsNoId;
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listMaterialType( $forCataloge = false, $restricted = false )
    {
        $data = array();

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('level');

        if ($restricted)
        {
        	$this->setWhere('isRestricted = false');
        }

        $sql = $this->select($data);
        $rs  = $this->query($sql, $forCataloge);

        if(!$forCataloge || !$rs)
        {
            return $rs;
        }

        foreach ($rs as $i => $v)
        {
            $r[$i]->option      = $v->materialTypeId;
            $r[$i]->description = $v->description;
            $r[$i]->level = $v->level;
        }

        return $r;
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
    public function getMaterialType($materialTypeId, $return = FALSE)
    {
        $data = array($materialTypeId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('materialtypeid = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject=true);
        if ($return  == FALSE )
        {
        	$this->setData($rs[0]);
	        return $this;
        }
        else
        {
        	return $rs[0];
        }
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchMaterialType()
    {
        $this->clear();
        if ( $v = $this->materialTypeIdS )
        {
            $this->setWhere('materialtypeid = ?');
            $data[] = $v;
        }
        if ( $v = $this->descriptionS )
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = $v . '%';
        }
        if ( $v = $this->observationS )
        {
            $this->setWhere('lower(observation) LIKE lower(?)');
            $data[] = $v . '%';
        }
        if ( $v = $this->isRestrictedS )
        {
        	$this->setWhere('isRestricted = ?');
        	$data[] = $v;
        }
        if ( $v = $this->levelS )
        {
        	$this->setWhere('level = ?');
        	$data[] = $v;
        }

        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('materialTypeId');
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
    public function insertMaterialType()
    {
        $data = array($this->description,
                      $this->isRestricted,
                      $this->level,
                      $this->observation);

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
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
    public function updateMaterialType()
    {
        $data = array(
            $this->description,
            $this->isRestricted,
            $this->level,
            $this->observation,
            $this->materialTypeId
        );

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('materialTypeId = ?');
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
     */
    public function deleteMaterialType($materialTypeId)
    {
        $data = array($materialTypeId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('materialTypeId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);

        return $rs;
    }
}
?>
