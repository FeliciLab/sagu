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
 * This file handles the connection and actions for Material Gender table
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
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/
class BusinessGnuteca3BusMaterialGender extends GBusiness
{
    public $colsNoId;

    public $materialGenderId;
    public $description;

    public $materialGenderIdS;
    public $descriptionS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcMaterialGender';
        $this->colsNoId = 'description';
        $this->id       = 'materialGenderId';
        $this->columns  = $this->id.', ' . $this->colsNoId;
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listMaterialGender($forCataloge = false)
    {
        $data = array();

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select($data);
        $rs  = $this->query($sql, $forCataloge);

        if(!$forCataloge || !$rs)
        {
            return $rs;
        }

        foreach ($rs as $i => $v)
        {
            $r[$i]->option      = $v->materialGenderId;
            $r[$i]->description = $v->description;
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
    public function getMaterialGender($materialGenderId, $return = FALSE)
    {
        $data = array($materialGenderId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('materialGenderId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, true);
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
    public function searchMaterialGender()
    {
        $this->clear();

        //pode ter 0 em função de importações
        //if ( isset( $this->materialGenderIdS ) )
		if ( ($this->materialGenderIdS) || ($this->materialGenderIdS == '0') )
		{
		    $this->setWhere('materialgenderid = ?');
		    $data[] = $this->materialGenderIdS;
		}

        if ( $v = $this->descriptionS )
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = $v . '%';
        }

        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('materialGenderId');
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
    public function insertMaterialGender()
    {
        $data = array($this->description);

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
    public function updateMaterialGender()
    {
        $data = array(
            $this->description,
            $this->materialGenderId
        );

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('materialGenderId = ?');
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
    public function deleteMaterialGender($materialGenderId)
    {
        $data = array($materialGenderId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('materialGenderId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);

        return $rs;
    }
}
?>
