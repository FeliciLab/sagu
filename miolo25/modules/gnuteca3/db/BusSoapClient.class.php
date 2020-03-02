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


class BusinessGnuteca3BusSoapClient extends GBusiness
{
    public  $soapClientId,//"    integer,
            $clientDescription,
            $ip,//"              varchar,
            $password,//"        varchar,
            $enable;//"          boolean

    public $columns,
           $table       = 'gtcSoapClient',
           $pkeys       = 'soapClientId',
           $cols        = 'clientDescription, ip, password, enable';

    public function __construct()
    {
        $this->columns = "{$this->pkeys}, {$this->cols}";
        parent::__construct($this->table, $this->pkeys, $this->cols);
    }


    public function insertSoapClient()
    {
        return $this->autoInsert();
    }


    public function updateSoapClient()
    {
        return $this->autoUpdate();
    }


    public function deleteSoapClient($soapClient)
    {
        return $this->autoDelete($soapClient);
    }


    /**
    * Autentica um cliente
    *
    * @param integer $clientId
    * @param string $clientPassword
    */
    public function authenticate($clientId, $clientPassword)
    {
        $this->clear();
        $this->setColumns("1");
        $this->setTables($this->table);
        $this->setWhere('soapClientId = ?  AND password = ?');
        $sql = $this->select(array($clientId, base64_decode($clientPassword)));
        $rs = $this->query($sql);
        return isset($rs[0]) && $rs[0][0] == '1';
    }



    /**
     * retorna um cliente soap
     *
     * @param integer $clientId
     * @return object
     */
    public function getSoapClient($clientId)
    {
        $this->clear();
        $this->setColumns("{$this->pkeys}, {$this->cols}");
        $this->setTables($this->table);
        $this->setWhere('soapClientId = ?');
        $sql = $this->select(array($clientId));
        $rs = $this->query($sql, true);
        return isset($rs[0]) ? $rs[0] : false;
    }

}
?>
