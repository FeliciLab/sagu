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
 * Classe referente ao Historico De Equipamento Sip
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Tcharles Silva [tcharles@solis.coop.br]
 *
 * @since
 * Class created on 18/11/2013
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusSipEquipamentBinRules extends GBusiness
{
    public $table;
    public $pkeys;
    public $columns;
    public $fullColumns;

    public $sipEquipamentId;
    public $bin;
    public $exemplaryStatusId;
    public $exemplaryStatusDesc;
    
    public $sipEquipamentIdS;
    public $binS;
    public $exemplaryStatusIdS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        
        $this->pkeys   = 'sipEquipamentId,
                          exemplaryStatusId';
        
        $this->colsNoId = 'sipEquipamentId, 
                           exemplaryStatusId, 
                           bin';
        
        $this->columns  = 'sipEquipamentId, ' . $this->colsNoId;
        $this->tables   = 'gtcSipEquipamentBinRules';
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertSipEquipamentBinRules()
    {
        $this->tables   = 'gtcSipEquipamentBinRules';
        $data = array(
            $this->sipEquipamentId,
            $this->exemplaryStatusId,
            $this->bin
        );

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
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateSipEquipamentBinRules()
    {
    	if ($this->removeData)
    	{
    		return false;
    	}
        $data = $this->associateData( $this->pkeys );

        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->table);
        $this->setWhere('sipEquipamentId = ? AND exemplaryStatusId = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $libraryUnitId (integer)
     * @param $weekDayId (integer)
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function deleteSipEquipamentBinRules($sipEquipamentId, $exemplaryStatusId)
    {
        $data[] = $sipEquipamentId;
        $data[] = $exemplaryStatusId;

        $this->clear();
        $this->setTables($this->table);
        $this->setWhere('sipEquipamentId = ? AND exemplaryStatusId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Return if the libraryUnit is closed in some day or not
     *
     * @param $libraryUnitId (integer)
     * @param $weekDayId (integer)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getSipEquipamentBinRules($sipEquipamentId, $exemplaryStatusId)
    {
        $data[] = $sipEquipamentId;
        $data[] = $exemplaryStatusId;

        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->table);
        $this->setWhere('sipEquipamentId = ? AND exemplaryStatusId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);
        unset($this->sipEquipamentId);
        unset($this->exemplaryStatusId);
        $this->setData($rs[0]);
        return $rs[0] ? true : false;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @return (Array): An array containing the search results
     **/
    public function searchSipEquipamentBinRules($toObject = FALSE)
    {
        $this->clear();

        if ($v = $this->sipEquipamentId)
        {
            $this->setWhere('A.sipEquipamentId = ?');
            $data[] = $v;
        }

        /*
        if ($this->exemplaryStatusId)
        {
            $this->setWhere('A.exemplaryStatusId = ?');
            $data[] = $this->exemplaryStatusId;
        }
         */

        $this->setColumns('A.sipEquipamentId,
                           A.exemplaryStatusId,
                           A.bin,
                           C.description AS exemplaryStatusIdDesc');
        
        $this->setTables($this->tables . ' A 
                         INNER JOIN gtcSipEquipament B
                                 ON (A.sipEquipamentId = B.sipEquipamentId)
                         INNER JOIN gtcexemplarystatus C
                                 ON (A.exemplaryStatusId = C.exemplarystatusid)
                        ');
        
        $sql = $this->select($data);
        
        $res = $this->query($sql, $toObject);

        return $res;
    }

    
    /**
     * List all records from the table handled by the class
     *
     * @param None
     *
     * @return (Array): Return an array with the entire table
     *
     **/
    public function listSipEquipamentBinRules()
    {
        $this->clear();
        $this->setColumns($this->pkeys);
        $this->setTables($this->table);
        $sql = $this->select();
        
        $rs  = $this->query($sql);
        return $rs;
    }
    
    public function deleteSipEquipamentBinRulesForSipEquipamentId($sipEquipamentId)
    {
        $data[] = $sipEquipamentId;

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('sipEquipamentId = ?');
        $sql = $this->delete($data);

        $rs  = $this->execute($sql);
        return $rs;
    }
}
?>
