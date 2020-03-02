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
 * This file handles the connection and actions for gtcRight table
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
 * Class created on 13/10/2008
 *
 **/
class BusinessGnuteca3BusRight extends GBusiness
{
    public $pkeys;
    public $cols;
    public $fullColumns;
    public $busLibraryUnit;

    public $privilegeGroupId;
    public $linkId;
    public $materialGenderId;
    public $operationId;

    public $privilegeGroupIdS;
    public $linkIdS;
    public $materialGenderIdS;
    public $operationIdS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables = 'gtcRight';
        $this->id = 'privilegeGroupId';
        $this->pkeys  = 'privilegeGroupId,
                         linkId,
                         materialGenderId,
                         operationId';
        $this->busLibraryUnit = MIOLO::getInstance()->getBusiness($this->module, 'BusLibraryUnit');
    }


    /**
     * Insert a new record. It verifies if register already exist, and if exists return true, but don't really insert
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertRight()
    {
        // verifies if register already exists
        $tempRight = $this->verifySpecificRight();
        // and only insert if no exists
        if (!$tempRight)
        {
            $data = array(
                $this->privilegeGroupId,
                $this->linkId,
                $this->materialGenderId,
                $this->operationId
            );

            $this->clear();
            $this->setColumns($this->pkeys);
            $this->setTables($this->tables);
            $sql = $this->insert($data);

            $rs  = $this->execute($sql);

            return $rs;
        }
        else
        {
            return true;
        }
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateRight()
    {
        $data = array($this->privilegeGroupId,
                      $this->linkId,
                      $this->materialGenderId,
                      $this->operationId);

        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->tables);
        $this->setWhere('privilegeGroupId = ?');
        $this->setWhere('linkId = ?');
        $this->setWhere('materialGenderId = ?');
        $this->setWhere('operationId = ?');
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
    public function deleteRight($privilegeGroupId, $linkId, $materialGenderId, $operationId)
    {
        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->tables);
        $this->setWhere('privilegeGroupId = ?');
        $this->setWhere('linkId = ?');
        $this->setWhere('materialGenderId = ?');
        $this->setWhere('operationId = ?');
        $sql = $this->delete( array($privilegeGroupId, $linkId, $materialGenderId, $operationId) );
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * @param $privilegeGroupId (integer)
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getRight($privilegeGroupId)
    {
        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->tables);
        $this->setWhere('privilegeGroupId = ?');
        $sql = $this->select(array($privilegeGroupId));
        $rs  = $this->query($sql, true);
        $this->setData( $rs[0] );
        return $this;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchRight($toObject = false)
    {
        $this->clear();

        if ( !empty($this->privilegeGroupIdS) )
        {
            $this->setWhere('A.privilegeGroupId = ?');
            $args[] = $this->privilegeGroupIdS;
        }
        if ( !empty($this->linkIdS) )
        {
            $this->setWhere('A.linkId = ?');
            $args[] = $this->linkIdS;
        }
        if ( !empty($this->materialGenderIdS) )
        {
            $this->setWhere('A.materialGenderId = ?');
            $args[] = $this->materialGenderIdS;
        }
        if ( !empty($this->operationIdS) )
        {
            $this->setWhere('A.operationId = ?');
            $args[] = $this->operationIdS;
        }

        $this->setColumns('
            A.privilegeGroupId,
            B.description,
            A.linkId,
            C.description,
            A.materialGenderId,
            D.description,
            A.operationId,
            E.description
        ');
        $this->setTables('
                        gtcRight            A
            INNER JOIN  gtcPrivilegeGroup   B
                    ON  (A.privilegeGroupId = B.privilegeGroupId)
            INNER JOIN  basLink             C
                    ON  (A.linkId = C.linkId)
            INNER JOIN  gtcMaterialGender     D
                    ON  (A.materialGenderId = D.materialGenderId)
            INNER JOIN  gtcOperation        E
                    ON  (A.operationId = E.operationId)
        ');
        $this->setOrderBy('
            B.description,
            C.description,
            D.description,
            E.description
        ');
        $sql = $this->select($args);
        $rs  = $this->query($sql, $toObject);
        return $rs;
    }


    /**
     * Get constants for a specified module
     *
     * @param $moduleConfig (string): Name of the module to load values from
     *
     * @return (array): An array of key pair values
     *
     **/
    public function getRightValues($privilegeGroupId)
    {
        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->tables);
        $this->setWhere('privilegeGroupId = ?');
        $sql = $this->select( array($privilegeGroupId) );
        $rs  = $this->query($sql);
        return $rs;
    }


    /**
     * Check if has right
     *
     * @param $libraryUnitId (integer)
     * @param $linkId (integer)
     * @param $materialGenderId (integer)
     * @param $operationId (integer)
     *
     * @return (boolean): TRUE if has right, otherwise FALSE
     */
    public function hasRight($libraryUnitId, $linkId, $materialGenderId, $operationId)
    {
        $library = $this->busLibraryUnit->getLibraryUnit($libraryUnitId);

        $args = array($library->privilegeGroupId,
                      $linkId,
                      $materialGenderId,
                      $operationId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns($this->pkeys);
        $this->setWhere('privilegeGroupId = ?');
        $this->setWhere('linkId = ?');
        $this->setWhere('materialGenderId = ?');
        $this->setWhere('operationId = ?');
        $sql   = $this->select($args);
        $query = $this->query($sql);
        return $query[0][0] ? true : false;
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
    public function verifySpecificRight()
    {
        $data[] = $this->privilegeGroupId;
        $data[] = $this->linkId;
        $data[] = $this->materialGenderId;
        $data[] = $this->operationId;

        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->tables);
        $this->setWhere('privilegeGroupId = ?');
        $this->setWhere('linkId = ?');
        $this->setWhere('materialGenderId = ?');
        $this->setWhere('operationId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, true);
        return $rs;
    }
    
    public function verifyRightSip()
    {
        $data[] = $this->linkId;
        $data[] = $this->operationId;

        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->tables);
        $this->setWhere('linkId = ?');
        $this->setWhere('operationId = ?');
        $sql = $this->select($data);
        
        $rs  = $this->query($sql, true);
        return $rs;
    }
    
}
?>