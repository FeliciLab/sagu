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
 *
 * @author Bruno E. Fuhr [bruno@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Bruno E. Fuhr [bruno@solis.com.br]
 * 
 * @since
 * Class created on 02/12/2014
 *
 **/

include("GnutecaWebServices.class.php");

class gnuteca3WebServicesPurchaseRequest extends GWebServices
{
    
    /**
     * Attributes
     */
    public $busPurchaseRequest;



    /**
     * Contructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->busPurchaseRequest = $this->MIOLO->getBusiness($this->module, 'BusPurchaseRequest');
    }
    
    /**
     * Obtém informações dos exemplares referentes aos números de solicitações de compra. 
     *
     * @param (int) $clientId
     * @param (String) $clientPassword base64 encoded
     * @param (Array) $solicitacoesDeCompra Array com todos os números de solicitação de compras
     * 
     * @return (Array) Retorna um array de objetos contendo informações sobre os exemplares referentes aos números de solicitações de compras.
     * 
     */
    public function getExemplariesFromPurchaseRequest($clientId, $clientPassword, $solicitacoesDeCompra = array())
    {        
        // CHECA ACESSO AO METHODO
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod('getExemplariesFromPurchaseRequest', false))
        {
            return parent::__getErrorStr();
        }
        
        return $this->busPurchaseRequest->getExemplariesFromPurchaseRequest($solicitacoesDeCompra);
    }
    
}
?>
