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


class BusinessGnuteca3BusWebService extends GBusiness
{
    public  $webServiceId,
            $serviceDescription,
            $class,
            $method,
            $enable,
            $needAuthentication,
            $checkClientIp;

    public $columns,
           $table       = 'gtcWebService',
           $pkeys       = 'webServiceId',
           $cols        = 'serviceDescription, class, method, enable, needAuthentication, checkClientIp';

    public function __construct()
    {
        $this->columns = "{$this->pkeys}, {$this->cols}";
        parent::__construct($this->table, $this->pkeys, $this->cols);
    }


    public function insertWebService()
    {
        return $this->autoInsert();
    }


    public function updateWebService()
    {
        return $this->autoUpdate();
    }


    public function deleteWebService($webService)
    {
        return $this->autoDelete($webService);
    }

    public function getWebServiceByClassAndMethod($className, $methodRequest, $enable = 't')
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->table);
        $this->setWhere('class = ?  AND method = ? AND enable = ?');
        $sql = $this->select(array($className, $methodRequest, $enable));
        $rs  = $this->query($sql, true);
        return $rs[0];
    }

}
?>
