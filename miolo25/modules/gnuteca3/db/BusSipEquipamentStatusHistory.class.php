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
class BusinessGnuteca3BusSipEquipamentStatusHistory extends GBusiness
{
    public $pkeys;
    public $table;
    public $columns;
    public $fullColumns;

    public $sipEquipamentId;
    public $dateTime;
    public $sipEquipamentLogId;
    public $status;
    
    public $sipEquipamentIdS;
    public $dateTimeS;
    public $sipEquipamentLogIdS;
    
    /*
    const SIPEQUIPAMENTLOGOK = '0';
    const SIPEQUIPAMENTLOGSEMPAPEL = '1';
    const SIPEQUIPAMENTLOGDESLIGADO = '2';
     */


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->table   = 'gtcSipEquipamentLog';
        
        $this->pkeys   = 'sipEquipamentId';
        
        $this->columns = 'status';
        $this->fullColumns = $this->pkeys . ',' . $this->columns;
    }
    
    /*
     * Método para auxiliar na hora de colocar no historico
     */
   
    public static function getConstants($option)
    {
        if ($option == 0)
        {
            return "OK";
        }
        else if ($option == 1)
        {
            return "SEM PAPEL";
        }
        else if ($option == 2)
        {
            return "DESLIGADO";
        }
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertSipEquipamentStatusHistory()
    {
        $data = $this->associateData( $this->fullColumns);
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $sql = $this->insert($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateSipEquipamentStatusHistory()
    {
        $data = $this->associateData( $this->columns . ',' . $this->pkeys );

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->table);
        $this->setWhere('sipEquipamentId = ? AND sipEquipamentLogId = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $fineId (integer)
     * @param $fineStatusId (integer)
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function deleteSipEquipamentStatusHistory($sipEquipamentId, $sipEquipamentLogId)
    {
        $data[] = $sipEquipamentId;
        $data[] = $sipEquipamentLogId;

        $this->clear();
        $this->setTables($this->table);
        $this->setWhere('sipEquipamentId = ? AND sipEquipamentLogId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * @param $fineId (integer)
     * @param $fineStatusId (integer)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getSipEquipamentStatusHistory($sipEquipamentId, $sipEquipamentLogId)
    {
        $data[] = $sipEquipamentId;
        $data[] = $sipEquipamentLogId;

        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $this->setWhere('sipEquipamentId = ? AND sipEquipamentLogId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);
        $this->setData($rs[0]);
        return $this;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @return (Array): An array containing the search results
     **/
    public function searchSipEquipamentStatusHistory($toObject = FALSE)
    {
        $this->clear();

        if ($this->sipEquipamentId)
        {
            $this->setWhere('sipEquipamentId = ?');
            $data[] = $this->sipEquipamentId;
        }
        
        if ($this->dateTime)
        {
            $this->setWhere('datetime = ?');
            $data[] = $this->dateTime;
        }

        if ($this->sipEquipamentLogId)
        {
            $this->setWhere('sipEquipamentLogId = ?');
            $data[] = $this->sipEquipamentLogId;
        }
        

        $this->setColumns('sipEquipamentId,
                           datetime,
                           sipEquipamentLogId,
                           status');
        
        $this->setTables('gtcSipEquipamentLog');
        $this->setOrderBy('sipEquipamentId');
        $sql = $this->select($data);
        
        return $this->query($sql, ($toObject ? TRUE : FALSE));
    }


    /**
     * List all records from the table handled by the class
     *
     * @param None
     *
     * @return (Array): Return an array with the entire table
     *
     **/
    public function listSipEquipamentStatusHistory()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    public function getLastStatus($sipEquipamentId)
    {
        $args[] = $sipEquipamentId;
        $this->setTables($this->table);
        $this->setColumns('sipEquipamentLogId');
        $this->setOrderBy('dateTime DESC LIMIT 1');
        $this->setWhere('sipEquipamentId = ?');
        $query = $this->query($this->select($args));
        return $query[0][0];
    }


    /**
     * Delete fine status history by fineId
     *
     * @param $fineId (integer)
     *
     * @return (boolean): TRUE if sucessfully deleted, otherwise FALSE
     */
    public function deleteBySipEquipament($sipEquipamentId)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setWhere('sipEquipamentId = ?');
        return $this->execute( $this->delete(array($sipEquipamentId)) );
    }
}
?>
