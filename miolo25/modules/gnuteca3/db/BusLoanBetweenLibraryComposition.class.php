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
class BusinessGnuteca3BusLoanBetweenLibraryComposition extends GBusiness
{
    public $loanBetweenLibraryId;
    public $itemNumber;
    public $isConfirmed;
    public $removeData;

    public function __construct()
    {
        $table = 'gtcLoanBetweenLibraryComposition';
        $pkeys = 'loanBetweenLibraryId,
                  itemNumber';
        $cols  = 'isConfirmed';
        parent::__construct($table, $pkeys, $cols);
    }

    public function insertLoanBetweenLibraryComposition()
    {
    	if ($this->removeData)
    	{
    		$this->deleteLoanBetweenLibraryComposition($this->loanBetweenLibraryId, $this->itemNumber);
    		return true;
    	}
        return $this->autoInsert();
    }

    public function updateLoanBetweenLibraryComposition()
    {
        return $this->autoUpdate();
    }
    
    public function deleteLoanBetweenLibraryComposition($loanBetweenLibraryId, $itemNumber = null)
    {
    	$this->clear();
        $this->setWhere('loanBetweenLibraryId = ?');
        $args[] = $loanBetweenLibraryId;
        if ($itemNumber)
        {
        	$this->setWhere('itemNumber = ?');
        	$args[] = $itemNumber;
        }
        $this->setTables($this->tables);
        return $this->execute( $this->delete($args) );
    }

    public function getLoanBetweenLibraryComposition($loanBetweenLibraryId)
    {
        $this->clear();
        return $this->autoGet($loanBetweenLibraryId);
    }

    public function searchLoanBetweenLibraryComposition($object = false)
    {
        $this->clear();
        $filters = array(
            'loanBetweenLibraryId'  => 'equals',
            'itemNumber'            => 'equals',
            'isConfirmed'           => 'equals',
        );
        return $this->autoSearch($filters, $object);
    }

    public function listLoanBetweenLibraryComposition()
    {
        return $this->autoList();
    }

    public function getComposition($loanBetweenLibraryId, $libraryUnitId = null)
    {
        settype($loanBetweenLibraryId, "integer");

        $this->clear();
        $this->setColumns   ("a.loanBetweenLibraryId, a.itemNumber, a.isConfirmed, b.libraryUnitId");
        $this->setTables    ("gtcloanbetweenlibrarycomposition a inner join gtcexemplarycontrol b using(itemnumber)");
        $this->setWhere     ("a.loanbetweenlibraryid = ?");

        if($libraryUnitId)
        {
            $this->setWhere("b.originalLibraryUnitId = '$libraryUnitId'");
        }

        $sql = $this->select(array($loanBetweenLibraryId));

        return $this->query ($sql, true);
    }

    public function getCompositionExemplaryStatus($loanBetweenLibraryId)
    {
        settype($loanBetweenLibraryId, "integer");

        $this->clear();
        $this->setColumns   ("a.itemNumber, a.isConfirmed, D.libraryname, C.description");
        $this->setTables    ("gtcloanbetweenlibrarycomposition A INNER JOIN gtcexemplarycontrol B USING(itemnumber) INNER JOIN gtcExemplaryStatus C USING(exemplaryStatusId) LEFT JOIN gtcLibraryUnit D ON (B.originallibraryunitid = D.libraryUnitId)");
        $this->setWhere     ("a.loanbetweenlibraryid = ?");
        $sql = $this->select(array($loanBetweenLibraryId));

        return $this->query ($sql, true);
    }
}
?>