<?php
/**
 * <--- Copyright 2005-2014 de Solis - Cooperativa de Soluções Livres Ltda. e
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
 * Classe Business para BusBiometrySession
 *
 * @author Luis Augusto Weber Mercado
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * 
 */

class BusinessGnuteca3BusBiometrySession extends GBusiness {
    
    // Campos da tabela.
    public $sessionId;
    public $return;
    
    function __construct() {
        parent::__construct();
        $this->colsNoId = 'sessionId,
                           return';
        
        $this->id = 'sessionId';
        $this->columns  = $this->colsNoId;
        $this->tables   = 'basBiometrySession';
                
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
    public function getBiometrySession($sessionId)
    {
        $data = array($sessionId);
        $this->clear();
        $this->setColumns(' sessionId,
                            return');
        
        $this->setTables(' basBiometrySession ');
        
        $this->setWhere('sessionId = ?');
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
    public function searchBiometrySession($toObject = FALSE)
    {
        $this->clear();
        
        if ( $v = $this->sessionId )
        {
            $this->setWhere('sessionId = ?');
            $data[] = $v;
        }
                
        if ( $v = $this->return )
        {
            $this->setWhere('return = ?');
            $data[] = $v;
        }
        
        $this->setColumns(' sessionId,
                            return');
        
        $this->setTables('basBiometrySession');

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
    public function insertBiometrySession()
    {
        $sessionId = $this->query("SELECT NEXTVAL ('seq_biometrysessionid')");
        
        $data = array(
            $sessionId[0][0],
            $this->returnType
        );
        
        $this->setColumns(' sessionId,
                            return' );
        
        $this->setTables('basBiometrySession');
        
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->insert($data);
        
        $rs  = $this->query($sql.' RETURNING sessionId');
        
        // Atribui o valor ao sessionId conforme o do contador na tabela.
        $this->sessionId = $rs[0][0];
        
        return $rs[0][0];
        
    }
    
    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateBiometrySession()
    {
        $data = array(
            $this->sessionId,
            $this->return
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
    public function deleteBiometrySession($sessionId)
    {
        $this->clear();
        
        $tables  = 'basBiometrySession';
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
