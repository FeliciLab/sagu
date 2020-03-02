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
 * Classe Business para Equipamento SIP
 *
 * @author Lucas Rodrgo Gerhardt [lucas_gerhardt@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Lucas Rodrgo Gerhardt [lucas_gerhardt@solis.coop.br]
 *
 * @since
 * Class created on 26/11/2013
 * 
 **/

class BusinessGnuteca3BusSipCirculationStatus extends GBusiness
{
    
    public $sipCirculationStatusId;
    public $exemplaryStatusId;
    
    function __construct()
    {
        parent::__construct();
        $this->colsNoId = 'exemplaryStatusId';
        
        $this->id = 'sipCirculationStatusId';
        $this->columns  = $this->id . $this->colsNoId;
        $this->tables   = 'gtcSipCirculationStatus';
        
    }
    
    public function searchSipCirculationStatus()
    {
        $this->clear();

        if ($this->sipCirculationStatusId)
        {
            $this->setWhere('sipCirculationStatusId = ?');
            $data[] = $this->sipCirculationStatusId;
        }
        
        if ($this->exemplaryStatusId)
        {
            $this->setWhere('exemplaryStatusId = ?');
            $data[] = $this->exemplaryStatusId;
        }
        $this->setTables($this->tables);
        $this->setColumns($this->columns);
        $this->setOrderBy('sipCirculationStatusId DESC');
        $sql = $this->select($data);

        $rs  = $this->query($sql);
        return $rs;
    }
    
    public function getSipCirculationStatusId ($exemplaryStatus)
    {
        $this->clear();
        $this->setColumns('sipcirculationstatusid');
        $this->setTables('gtcsipcirculationstatus');
        $this->setWhere("exemplarystatusid = ?");
        $sql = $this->select(array($exemplaryStatus));
        $rs  = $this->query($sql);
        
        return $rs[0][0];
    }

                
}
?>
