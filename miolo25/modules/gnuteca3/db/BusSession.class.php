<?php
/**
 * <--- Copyright 2005-2013 de Solis - Cooperativa de Soluções Livres Ltda. e
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
 * Classe Business para BusSession
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Silva [tcharles@solis.coop.br]
 *
 * @since
 * Class created on 25/11/2013
 * 
 **/

class BusinessGnuteca3BusSession extends GBusiness
{
    
    //Campos da tabela
    public $sessionId;
    public $sipequipamentId;
    public $personId;
    public $isClosed;
    
    public $busSessionOperation;
    

    function __construct()
    {
        parent::__construct();
        $this->colsNoId = 'sessionId,
                           sipequipamentId,
                           personId,
                           isClosed';
        
        $this->id = 'sessionId';
        $this->columns  = $this->colsNoId;
        $this->tables   = 'gtcSession';
        
        $this->busSessionOperation = $this->MIOLO->getBusiness($this->module, 'BusSessionOperation');
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
    public function getSession($sessionId)
    {
        $data = array($sessionId);
        $this->clear();
        $this->setColumns(' A.sessionId,
                            B.sipequipamentId,
                            C.personId,
                            A.isClosed ');
        
        $this->setTables('  gtcSession A 
                INNER JOIN  gtcSipEquipament B
                       ON   (A.sipequipamentid::int = B.sipEquipamentid)
                INNER JOIN  basperson C
                       ON   (A.personid = C.personid)');
        
        $this->setWhere('A.sessionId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, true);
        
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
    public function searchSession($toObject = FALSE)
    {
        $this->clear();
        
        if ( $v = $this->sessionId )
        {
            $this->setWhere('sessionId = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->sipequipamentId )
        {
            $this->setWhere('sipequipamentId = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->personId )
        {
            $this->setWhere('personId = ?');
            $data[] = $v;
        }

        $this->setWhere('isClosed = ?');
       	$data[] = $this->isClosed;
        
        $this->setColumns(' sessionId,
                            sipequipamentId,
                            personId,
                            isClosed ');
        
        $this->setTables('gtcSession');

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
    public function insertSession()
    {
        $data = array(
            $this->sipequipamentId,
            $this->personId,
            $this->isClosed
        );
        
        $this->setColumns(' sipequipamentId,
                            personId');
        
        $this->setTables('gtcSession');

        $this->clear();
        $this->setColumns($this->columns);
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
    public function updateSession()
    {
        $data = array(
            $this->sessionId,
            $this->sipequipamentId,
            $this->personId,
            $this->isClosed
        );
        
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('sessionId = '.$this->sessionId);
        
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
    public function deleteSession($sessionId)
    {
        $this->busSessionOperation->deleteSessionOperation($sessionId);
        $this->clear();

        $tables  = 'gtcSession';
        $where   = 'sessionId = ?';
        $data = array($sessionId);

        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->delete($data);
        
        $rs  = $this->execute($sql);
        return $rs;
    }
}
?>
