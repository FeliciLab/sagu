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
 * LoanBetweenLibraryStatusHistory business
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
 * Class created on 16/12/2008
 *
 **/
class BusinessGnuteca3BusLoanBetweenLibraryStatusHistory extends GBusiness
{
    public $loanBetweenLibraryId;
    public $loanBetweenLibraryStatusId;
    public $date;
    public $operator;


    public function __construct()
    {
        $table = 'gtcLoanBetweenLibraryStatusHistory';
        $pkeys = 'loanBetweenLibraryId,
                  loanBetweenLibraryStatusId';
        $cols  = 'date,
                  operator';
        parent::__construct($table, $pkeys, $cols);
    }


    public function insertLoanBetweenLibraryStatusHistory()
    {
        $lastStatus = $this->getLastStatus($this->loanBetweenLibraryId);
        if ($lastStatus != $this->loanBetweenLibraryStatusId)
        {
            return $this->autoInsert();
        }
        return FALSE;
    }


    public function updateLoanBetweenLibraryStatusHistory()
    {
        return $this->autoUpdate();
    }


    public function deleteLoanBetweenLibraryStatusHistory($loanBetweenLibraryId, $loanBetweenLibraryStatusId)
    {
        return $this->autoDelete($loanBetweenLibraryId, $loanBetweenLibraryStatusId);
    }


    public function getLoanBetweenLibraryStatusHistory($loanBetweenLibraryId, $loanBetweenLibraryStatusId)
    {
        $this->clear();
        return $this->autoGet($loanBetweenLibraryId, $loanBetweenLibraryStatusId);
    }


    public function searchLoanBetweenLibraryStatusHistory($object = false)
    {
        $this->clear();
        $filters = array(
            'loanBetweenLibraryId'          => 'equals',
            'loanBetweenLibraryStatusId'    => 'equals',
            'date'                          => 'equals',
            'operator'                      => 'ilike',
        );
        return $this->autoSearch($filters, $object);
    }


    public function listLoanBetweenLibraryStatusHistory()
    {
        return $this->autoList();
    }


    public function getLastStatus($loanBetweenLibraryId)
    {
        $msql = new MSQL('loanBetweenLibraryStatusId', $this->tables, 'loanBetweenLibraryId = ?', 'date DESC LIMIT 1');
        $sql  = $msql->select(array($loanBetweenLibraryId));
        $rs   = $this->query($sql);
        return $rs[0][0];
    }
}
?>