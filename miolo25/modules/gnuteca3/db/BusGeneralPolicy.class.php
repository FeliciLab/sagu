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
 * This file handles the connection and actions for general generalPolicy table
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 04/08/2008
 *
 **/
class BusinessGnuteca3BusGeneralPolicy extends GBusiness
{
    public $loanGeneralLimit;
    public $reserveGeneralLimit;
    public $privilegeGroupId;
    public $linkList;
    public $linkId;
    public $privilegeGroupDescription;
    public $linkDescription;
    public $reserveGeneralLimitIninitialLevel;


    public $loanGeneralLimitS;
    public $linkIdS;
    public $reserveGeneralLimitS;
    public $reserveGeneralLimitIninitialLevelS;
    public $privilegeGroupIdS;



    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->table    = 'gtcGeneralPolicy';
        $this->colsNoId = 'loanGeneralLimit, reserveGeneralLimit, reserveGeneralLimitIninitialLevel';
        $this->colsId   = $this->id = 'privilegeGroupId, linkId';
        $this->cols     = $this->colsId . ',' . $this->colsNoId;
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listGeneralPolicy()
    {
        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs  = $this->query($sql);
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
    public function getGeneralPolicy($privilegeGroupId, $linkId, $return = FALSE)
    {
        $data[] = $privilegeGroupId;
        $data[] = $linkId;
        $this->clear();
        $this->setTables('gtcGeneralPolicy GP , gtcPrivilegeGroup PG, basLink L');
        $this->setColumns(' GP.privilegegroupid,
                            PG.description as privilegeGroupDescription,
                            GP.linkid,
                            L.description as linkDescription,
                            GP.loanGeneralLimit,
                            GP.reserveGeneralLimit,
                            GP.reserveGeneralLimitIninitialLevel
                            ');
        $this->setWhere('GP.privilegeGroupId = ? ');
        $this->setWhere('GP.linkId = ?');
        $this->setWhere('GP.privilegegroupid   = PG. privilegegroupid');
        $this->setWhere('GP.linkid         = L.linkId');
        $sql = $this->select($data);
        $rs  = $this->query($sql, true);
        
        if ( !$return)
        {
            $this->setData( $rs[0] );
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
    public function searchGeneralPolicy()
    {
        $this->clear();
        if ( !empty($this->privilegeGroupIdS) )
        {
            $this->setWhere('GP.privilegegroupid = ?');
            $data[] = $this->privilegeGroupIdS;
        }
        if ( !empty($this->linkIdS) )
        {
            $this->setWhere('GP.linkid = ?');
            $data[] = $this->linkIdS;
        }
        if ( !empty($this->loanGeneralLimitS) )
        {
            $this->setWhere('GP.loanGeneralLimit = ?');
            $data[] = $this->loanGeneralLimitS;
        }
        if ( !empty($this->reserveGeneralLimitS) )
        {
            $this->setWhere('GP.reserveGeneralLimit = ?');
            $data[] = $this->reserveGeneralLimitS;
        }
        if ( !empty($this->reserveGeneralLimitIninitialLevelS) )
        {
            $this->setWhere('GP.reserveGeneralLimitIninitialLevel = ?');
            $data[] = $this->reserveGeneralLimitIninitialLevelS;
        }
        $this->setTables('gtcGeneralPolicy GP , gtcPrivilegeGroup PG, basLink L');
        $this->setColumns('GP.privilegegroupid, PG.description, GP.linkid, L.description, GP.loangenerallimit, GP.reservegeneralLimit, GP.reserveGeneralLimitIninitialLevel');
        $this->setOrderBy('privilegegroupid, linkId');
        $this->setWhere('GP.privilegegroupid   = PG. privilegegroupid');
        $this->setWhere('GP.linkid         = L.linkId');
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
    public function insertGeneralPolicy()
    {
        foreach ($this->linkList as $linkId)
        {
            $this->linkId = $linkId;
            $getGeneralPolicy = $this->getGeneralPolicy($this->privilegeGroupId , $this->linkId, true);
            if ( !$getGeneralPolicy )
            {
                $this->clear();
                $this->setColumns($this->cols);
                $this->setTables($this->table);
                $sql = $this->insert( $this->associateData($this->cols) );
                $rs  = $this->execute($sql);
            }
            else
            {
                $rs = $this->updateGeneralPolicy();
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
    public function updateGeneralPolicy()
    {
        $this   ->clear();
        $this   ->setColumns($this->colsNoId);
        $this   ->setTables($this->table);
        $this   ->setWhere('privilegegroupid = ?');
        $this   ->setWhere('linkid = ?');
        $data   = $this->associateData( $this->colsNoId . ',' . $this->colsId );
        $sql    = $this->update($data);
        $rs     = $this->execute($sql);
        return  $rs;
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
    public function deleteGeneralPolicy($privilegeGroupId, $linkId)
    {
        $data[]     = $privilegeGroupId;
        $data[]     = $linkId;
        $this       ->clear();
        $this       ->setTables($this->table);
        $this       ->setWhere('privilegegroupid = ?');
        $this       ->setWhere('linkid = ?');
        $sql        = $this->delete($data);
        $rs         = $this->execute($sql);
        return      $rs;
    }
}
?>
