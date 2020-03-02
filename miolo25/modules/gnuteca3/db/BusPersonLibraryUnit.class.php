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
 * Class created on 30/10/2008
 *
 **/


class BusinessGnuteca3BusPersonLibraryUnit extends GBusiness
{
    public $table;
    public $pkeys;

    public $libraryUnitId;
    public $personId;

    public $libraryUnitIdS;
    public $personIdS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->table  = 'gtcPersonLibraryUnit';
        $this->pkeys  = 'libraryUnitId,
                         personId';
    }


    /**
     * Insert a new record. It verifies if register already exist, and if exists return true, but don't really insert
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertPersonLibraryUnit()
    {
        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->table);
        $sql = $this->insert(array($this->libraryUnitId, $this->personId));
        $rs  = $this->execute($sql);
        return $rs;
    }



    /**
     * Get record
     *
     * @param int $libraryUnitId
     * @param int $personId
     * @return object
     */
    public function getPersonLibraryUnit($libraryUnitId, $personId)
    {
        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->table);
        $this->setWhere('libraryUnitId = ?');
        $this->setWhere('personId = ?');
        $sql = $this->select(array($libraryUnitId, $personId));
        $rs  = $this->query($sql, true);
        if ($rs[0])
        {
            $this->setData($rs[9]);
            return $rs[0];
        }
        return false;
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
    public function deletePersonLibraryUnit($libraryUnitId, $personId)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setWhere('libraryUnitId = ?');
        $this->setWhere('personId = ?');
        $sql = $this->delete(array($libraryUnitId, $personId));
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchPersonLibraryUnit($toObject = false)
    {
        $this->clear();

        if ( $this->libraryUnitIdS )
        {
            $this->setWhere('A.libraryUnitId = ?');
            $args[] = $this->libraryUnitIdS;
        }
        if ( $this->personIdS )
        {
            $this->setWhere('A.personId = ?');
            $args[] = $this->personIdS;
        }

        $this->setTables('gtcPersonLibraryUnit      A
              INNER JOIN  gtcLibraryUnit            B
                      ON  (A.libraryUnitId = B.libraryUnitId)');
        $this->setColumns('A.libraryUnitId,
                           A.personId,
                           B.libraryName');
        $sql = $this->select($args);
        $rs  = $this->query($sql, $toObject);
        return $rs;
    }


    public function checkAccess($personId, $libraryUnitId)
    {
    	return $this->getPersonLibraryUnit($libraryUnitId, $personId) ? TRUE : FALSE;
    }



    /**
     * Altera os registro de um usuário por outro
     *
     * @param integer $currentPersonId
     * @param integer $newPersonId
     * @return boolean
     */
    public function updatePersonId($currentPersonId, $newPersonId)
    {
        $this->clear();
        $this->setColumns("personId");
        $this->setTables($this->table);
        $this->setWhere(' personId = ?');
        $sql = $this->update(array($newPersonId, $currentPersonId));
        $rs  = $this->execute($sql);
        return $rs;
    }


}
?>
