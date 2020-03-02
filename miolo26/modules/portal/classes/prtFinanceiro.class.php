<?php

/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Solu��es Livres Ltda.
 *
 * Este arquivo � parte do programa Sagu.
 *
 * O Sagu � um software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * dentro dos termos da Licen�a P�blica Geral GNU como publicada pela Funda��o
 * do Software Livre (FSF); na vers�o 2 da Licen�a.
 *
 * Este programa � distribu�do na esperan�a que possa ser �til, mas SEM
 * NENHUMA GARANTIA; sem uma garantia impl�cita de ADEQUA��O a qualquer MERCADO
 * ou APLICA��O EM PARTICULAR. Veja a Licen�a P�blica Geral GNU/GPL em
 * portugu�s para maiores detalhes.
 *
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral GNU, sob o t�tulo
 * "LICENCA.txt", junto com este programa, se n�o, acesse o Portal do Software
 * P�blico Brasileiro no endere�o www.softwarepublico.gov.br ou escreva para a
 * Funda��o do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 * Usuario portal
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 * Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * @since
 * Class created on 25/09/2012
 *
 */
class PrtFinanceiro extends MForm
{
    public function __construct(){}
    
    public function obterTitulosEmAberto($personId)
    {
        $MIOLO = MIOLO::getInstance();
        
        $filter = new stdClass();
        $filter->onlyOpen = true;
        $filter->personId = $personId;
        
        $busReceivableInvoice = $MIOLO->getBusiness('finance', 'BusReceivableInvoice');        
        $data = $busReceivableInvoice->listInvoicesForPerson($filter, true);
        
        return $data;
    }
    
    public function obterTitulosFechados($personId)
    {
        $MIOLO = MIOLO::getInstance();
        
        $filter = new stdClass();
        $filter->onlyClose = true;
        $filter->personId = $personId;
        
        $busReceivableInvoice = $MIOLO->getBusiness('finance', 'BusReceivableInvoice');        
        $data = $busReceivableInvoice->listInvoicesForPerson($filter);
        
        return $data;
    }
    
    public function isInadimplente($personId)
    {
        $sql = "SELECT isDefaulter($personId)";
        $query =  bBaseDeDados::obterInstancia()->_db->query($sql);
        
        return MUtil::getBooleanValue($query[0][0]);
    }
    
    public function obterPrimeiroTituloVencido($invoiceIds)
    {
        $sql = new MSQL('invoiceid', 'finreceivableinvoice');
        $sql->setWhere('invoiceid IN (\'' . implode("','", $invoiceIds) . '\')');
        $sql->setOrderBy('maturitydate ASC');
        $sql->setLimit(1);
        
        $data = bBaseDeDados::consultar($sql);
        
        return $data[0][0];
    }

}


?>
