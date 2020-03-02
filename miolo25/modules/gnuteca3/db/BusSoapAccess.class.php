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
 * gtcTask business
 *
 * @author Luiz Gilberto Gregory F [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 06/08/2009
 *
 **/


class BusinessGnuteca3BusSoapAccess extends GBusiness
{
    public  $webServiceId, $soapClientId;

    public $columns,
           $table       = 'gtcSoapAccess';


    public $busWebService, $busSoapClient;

    public function __construct()
    {
        parent::__construct($this->table, "webServiceId, soapClientId");

        $this->busWebService = $this->MIOLO->getBusiness($this->module, 'BusWebService');
        $this->busSoapClient = $this->MIOLO->getBusiness($this->module, 'BusSoapClient');
    }


    public function checkAccess($webServiceId, $soapClientId)
    {
        $this->clear();
        $this->setColumns("1");
        $this->setTables($this->table);
        $this->setWhere('webServiceId = ? AND soapClientId = ?');
        $sql = $this->select(array($webServiceId, $soapClientId));
        $rs = $this->query($sql);
        return isset($rs[0][0]) && $rs[0][0] == '1';
    }


    /**
     * Retorna um determiando servico pelo id
     *
     * @param integer $webServiceId
     */
    public function getWebService($webServiceId)
    {

    }


    /**
     * Retorna um determiando servico pelo id
     *
     * @param integer $webServiceId
     */
    public function getWebServiceByClassAndMethod($className, $methodRequest)
    {
        return $this->busWebService->getWebServiceByClassAndMethod($className, $methodRequest);
    }


    /**
     * Autentica um cliente
     *
     * @param integer $clientId
     * @param string $clientPassword
     */
    public function authenticate($clientId, $clientPassword)
    {
        return $this->busSoapClient->authenticate($clientId, $clientPassword);
    }


    /**
     * retorna o objeto de uma cliente
     *
     * @param integer $clientId
     */
    public function getClient($clientId)
    {
        return $this->busSoapClient->getSoapClient($clientId);
    }
}
?>
