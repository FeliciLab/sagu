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
 * Classe Business para BusSessionOperation
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Silva [tcharles@solis.coop.br]
 *
 * @since
 * Class created on 22/11/2013
 * 
 **/

class BusinessGnuteca3BusSessionOperation extends GBusiness
{
    
    //Campos da tabela
    public $sessionOperationId;
    public $sessionId;
    public $dateTime;
    public $operation;
    public $loanId;
    public $returnRegisterId;
    public $renewId;
    
    public $sessionOperationIdS;
    public $sessionIdS;
    public $dateTimeS;
    public $operationS;
    public $loanIdS;
    public $returnRegisterIdS;
    public $renewIdS;

    function __construct()
    {
        parent::__construct();
        $this->colsNoId = 'sessionOperationId, 
                           sessionId, 
                           dateTime, 
                           operation, 
                           loanId, 
                           returnRegisterId, 
                           renewId';
        
        $this->id = 'sessionOperationId';
        $this->columns  = 'sessionOperationId, ' . $this->colsNoId;
        $this->tables   = 'gtcSessionOperation';
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
    public function getSessionOperation($sessionOperationId)
    {
        $data = array($sessionOperationId);
        $this->clear();
        $this->setColumns('  A.sessionOperationId,
                             B.sessionId, 
                             A.dateTime,
                             A.operation, 
                             C.loanId, 
                             D.returnRegisterId, 
                             E.renewId  ');
        
        $this->setTables('gtcSessionOperation A 
                LEFT JOIN gtcSession B
                       ON (A.sessionid = B.sessionid)
                LEFT JOIN gtcLoan C
                       ON (A.loanid = C.loanid)
                LEFT JOIN gtcReturnRegister D
                       ON (A.returnregisterid = D.returnregisterid)
                LEFT JOIN gtcRenew E
                       ON (A.renewid = E.renewid)');
        
        $this->setWhere('A.sessionOperationId = ?');
        
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
    public function searchSessionOperation($toObject = FALSE)
    {
        $this->clear();
        
        if ( $v = $this->sessionOperationId )
        {
            $this->setWhere('A.sessionOperationId = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->sessionId )
        {
            $this->setWhere('A.sessionId = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->dateTime )
        {
            $this->setWhere('A.dateTime = ?');
            $data[] = $v;
        }
        if ( $v = $this->operation )
        {
        	$this->setWhere('A.operation = ?');
        	$data[] = $v;
        }

        if ( $v = $this->loanId )
        {
        	$this->setWhere('A.loanId = ?');
        	$data[] = $v;
        }
        
        if ( $v = $this->returnRegisterId )
        {
        	$this->setWhere('A.returnRegisterId = ?');
        	$data[] = $v;
        }
        
        if ( $v = $this->renewId )
        {
        	$this->setWhere('A.renewId = ?');
        	$data[] = $v;
        }
        
        
        $this->setColumns('  A.sessionOperationId,
                             B.sessionId, 
                             A.dateTime,
                             A.operation, 
                             C.loanId, 
                             D.returnRegisterId, 
                             E.renewId'  );
        
        $this->setTables('gtcSessionOperation A 
                LEFT JOIN gtcSession B
                       ON (A.sessionid = B.sessionid)
                LEFT JOIN gtcLoan C
                       ON (A.loanid = C.loanid)
                LEFT JOIN gtcReturnRegister D
                       ON (A.returnregisterid = D.returnregisterid)
                LEFT JOIN gtcRenew E
                       ON (A.renewid = E.renewid)');

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
    public function insertSessionOperation()
    {        
        $data = array(
            $this->sessionId,
            $this->operation,
            $this->loanId,
            $this->returnRegisterId,
            $this->renewId
        );
        
        $this->setColumns(' sessionId, 
                            operation, 
                            loanId, 
                            returnRegisterId, 
                            renewId');

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
    public function updateSessionOperation()
    {
        $data = array(
            $this->sessionId,
            $this->dateTime,
            $this->operation,
            $this->loanId,
            $this->returnRegisterId,
            $this->renewId
        );
        
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('sessionOperationId = '.$this->sessionOperationId);

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
    public function deleteSessionOperation($sessionOperationId)
    {
        $this->clear();

        $tables  = 'gtcSessionOperation';
        $where   = 'sessionOperationId = ?';
        $data = array($sessionOperationId);

        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->delete($data);
        
        $rs  = $this->execute($sql);
        
        return $rs;
    }
}
?>
