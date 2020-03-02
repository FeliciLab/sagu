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
 * This file handles the connection and actions for gtcEmailControlDelayedLoan table
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
 * Class created on 17/11/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusEmailControlDelayedLoan extends GBusiness
{
    public $loanId;
    public $lastSent;
    public $amountSent;

    public $loanIdS;
    public $lastSentS;
    public $amountSentS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        $table = 'gtcEmailControlDelayedLoan';
        $pkeys = 'loanId';
        $cols  = 'lastSent,
                  amountSent';
        parent::__construct($table, $pkeys, $cols);
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertEmailControlDelayedLoan()
    {
        return $this->autoInsert();
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateEmailControlDelayedLoan()
    {
        return $this->autoUpdate();
    }


    /**
     * Delete a record
     *
     * @param $loanId (integer)
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function deleteEmailControlDelayedLoan($loanId)
    {
        return $this->autoDelete($loanId);
    }


    /**
     * Return a specific record from the database
     *
     * @param $loanId (integer)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getEmailControlDelayedLoan($loanId)
    {
        $this->clear();
        return $this->autoGet($loanId);
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $object (bool): Case TRUE return as Object, otherwise Array
     *
     * @return (Array): An array containing the search results
     **/
    public function searchEmailControlDelayedLoan($object = false)
    {
        $this->clear();
        $filters = array(
            'loanId'     => 'equals',
            'lastSent'   => 'equals',
            'amountSent' => 'equals'
        );
        return $this->autoSearch($filters, $object);
    }


    /**
     * List all records from the table handled by the class
     *
     * @param None
     *
     * @return (Array): Return an array with the entire table
     *
     **/
    public function listEmailControlDelayedLoan()
    {
        return $this->autoList();
    }


    /**
     * Return a specific record from the database
     *
     * @param $loanId (integer)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getLastSent($loanId)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns($this->columns);
        $this->setWhere('loanId = ?');
        $this->setOrderBy('lastSent DESC');
        $rs = $this->query($this->select(array($loanId)), true);
        return $rs[0];
    }
}
?>
