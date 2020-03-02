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
 * LoanBetweenLibraryStatus business
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
class BusinessGnuteca3BusLoanBetweenLibraryStatus extends GBusiness
{
    public $loanBetweenLibraryStatusId;
    

    public function __construct()
    {
        $table = 'gtcLoanBetweenLibraryStatus';
        $pkeys = 'loanBetweenLibraryStatusId';
        $cols  = 'description';
        parent::__construct($table, $pkeys, $cols);
    }


    public function insertLoanBetweenLibraryStatus()
    {
        return $this->autoInsert();
    }

    
    public function updateLoanBetweenLibraryStatus()
    {
        return $this->autoUpdate();
    }


    public function deleteLoanBetweenLibraryStatus($loanBetweenLibraryStatusId)
    {
        return $this->autoDelete($loanBetweenLibraryStatusId);
    }


    public function getLoanBetweenLibraryStatus($loanBetweenLibraryStatusId)
    {
        $this->clear();
        return $this->autoGet($loanBetweenLibraryStatusId);       
    }


    public function searchLoanBetweenLibraryStatus($object = false)
    {
        $this->clear();
        $filters = array(
            'loanBetweenLibraryId'  => 'equals',
            'description'           => 'ilike',
        );
        return $this->autoSearch($filters, $object);
    }


    public function listLoanBetweenLibraryStatus($filterPossiblesStatus = false)
    {
        //return $this->autoList();
        parent::clear();
        parent::setColumns('loanBetweenLibraryStatusId, description');
        parent::setTables('gtcLoanBetweenLibraryStatus');

        switch ($filterPossiblesStatus)
        {
            // SOLICITADO = pode cancelar, aprovar ou reprovar
            case 1 :
                parent::setWhere("loanBetweenLibraryStatusId IN (1, 2, 3, 4)");
                break;

            // CANCELADO = não pode mais trocar de estado
            case 2:
                parent::setWhere("loanBetweenLibraryStatusId IN (2)");
                break;

            // APROVADO = prode confirmar
            case 3:
                parent::setWhere("loanBetweenLibraryStatusId IN (3,5)");
                break;

            // REPROVADO = não pode mais trocar de estado
            case 4:
                parent::setWhere("loanBetweenLibraryStatusId IN (4)");
                break;

            // CONFIRMADO = pode devolver
            case 5:
                parent::setWhere("loanBetweenLibraryStatusId IN (5,6)");
                break;

            // DEVOLVIDO = não pode mais trocar de estado
            case 6:
                parent::setWhere("loanBetweenLibraryStatusId IN (6,7)");
                break;
                
            // FINALIZADO = não pode mais trocar de estado
            case 7:
                parent::setWhere("loanBetweenLibraryStatusId IN (7)");
                break;                

            default: break;
        }

        $sql = parent::select();
        $rs  = parent::query();

        $out = array();
        if ($rs)
        {
            foreach ($rs as $v)
            {
                list($id, $value) = $v;
                $out[ $id ] = $value;
            }
        }

        return $out;         
    }
}
?>
