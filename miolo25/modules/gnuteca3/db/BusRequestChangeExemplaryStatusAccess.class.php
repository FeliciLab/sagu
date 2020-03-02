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
 * @author Luiz G Gregory Filho [luiz@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 02/04/2009
 *
 **/
class BusinessGnuteca3BusRequestChangeExemplaryStatusAccess extends GBusiness
{

    public  $basLinkId,        
            $exemplaryStatusId,
            $basLinkIdS,
            $exemplaryStatusIdS,
            $busExemplaryStatus,
            $exemplaryStatus,
            $busAuthenticate,
            $busBond;

    /**
     * Constructor Method
     */

    function __construct()
    {
        parent::__construct();

        $this->busExemplaryStatus   = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        $this->busAuthenticate      = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $this->busBond              = $this->MIOLO->getBusiness($this->module, 'BusBond');

        $this->pKey         = 'basLinkId, exemplaryStatusId';
        $this->columns      = $this->pKey;
        $this->fullColumns  = $this->pKey;
        $this->tables       = 'gtcRequestChangeExemplaryStatusAccess';
    }


    /**
     *
     */
    public function searchRequestChangeExemplaryStatusAccess($order = null)
    {
        parent::clear();

        if($v = $this->basLinkIdS)
        {
            $this->setWhere("R.basLinkId = ?");
            $data[] = $v;
        }
        if($v = $this->exemplaryStatusIdS)
        {
            $this->setWhere("R.exemplaryStatusId = ?");
            $data[] = $v;
        }

        if(!is_null($order))
        {
            $this->setOrderBy($order);
        }

        $this->setTables('    gtcRequestChangeExemplaryStatusAccess R
                    LEFT JOIN basLink B
                           ON R.basLinkId = B.linkID
                    LEFT JOIN gtcExemplaryStatus E
                           ON R.exemplaryStatusId = E.exemplaryStatusId');

            $this->setColumns(' B.linkId,
                                B.description,
                                E.exemplaryStatusId,
                                E.description');

        $sql = parent::select($data);
        return parent::query();
    }


    /**
     * Insere a permissão no banco
     *
     * Só retorna true se tiver inserido todos dados
     */
    public function insertRequestChangeExemplaryStatusAccess()
    {
        if ( is_array($this->exemplaryStatus) )
        {
            foreach($this->exemplaryStatus as $i=> $exemplaryStatus)
            {
                $this->exemplaryStatusId = $exemplaryStatus->exemplaryStatusId;
                
                $this->clear();
                $this->setTables($this->tables);
                $this->setColumns($this->columns);
                $sql = $this->insert($this->associateData($this->columns));
                $ok = $this->execute($sql);
            }

            if ( !$ok )
            {
                return false;
            }
        }

        return true;
    }


    public function updateRequestChangeExemplaryStatusAccess()
    {
        //apaga todos registros
        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('baslinkid = ?');
        $sql = $this->delete($this->basLinkId);

        //testa a deleção dos dados
        if ( $this->execute($sql) )
        {
            if ( is_array($this->exemplaryStatus) )
            {
                foreach($this->exemplaryStatus as $i=> $exemplaryStatus)
                {
                    if ( !$exemplaryStatus->removeData )
                    {
                        $this->exemplaryStatusId = $exemplaryStatus->exemplaryStatusId;

                        $this->clear();
                        $this->setTables($this->tables);
                        $this->setColumns($this->columns);
                        $sql = $this->insert($this->associateData($this->columns));
                        $ok = $this->execute($sql);
                    }
                }

                if ( !$ok )
                {
                    return false;
                }
            }

        }
        
        return true;
    }

    /**
     *
     */
    public function deleteRequestChangeExemplaryStatusAccess($basLinkId, $exemplaryStatusId = null)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns($this->columns);
        $this->setWhere('basLinkId = ?');
                
        if ( $exemplaryStatusId )
        {
            $this->setWhere('exemplaryStatusId = ?');
        }
         
        $sql = $this->delete(array($basLinkId, $exemplaryStatusId));

        return $this->execute($sql);
    }


    /*
     * checa se uma determinado grupo tem acesso a um determinado status de exemplar
     *
     * @param int $basLinkId
     * @param int $exemplaryStatusId
     * @return boolean
     */
    public function checkAccess($basLinkId, $exemplaryStatusId)
    {
        if (!$basLinkId || !$exemplaryStatusId)
        {
            return false;
        }

        $basLinkId          = is_array($basLinkId)          ? $basLinkId            : array($basLinkId);
        $exemplaryStatusId  = is_array($exemplaryStatusId)  ? $exemplaryStatusId    : array($exemplaryStatusId);

        $basLinkId          = implode("','", $basLinkId);
        $exemplaryStatusId  = implode("','", $exemplaryStatusId);

        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);
        parent::setWhere("basLinkId IN ('$basLinkId') AND exemplaryStatusId IN ('$exemplaryStatusId')");
        parent::select();
        return parent::query();
    }


    /**
     * Verifica se o grupo tem acesso a troca de exemplar
     *
     * @param array $basLinkId
     * @return boolean
     */
    public function checkGroupAccess($basLinkId)
    {
        if(!$basLinkId)
        {
            return false;
        }

        $basLinkId = is_array($basLinkId) ? $basLinkId : array($basLinkId);
        $basLinkId = implode("','", $basLinkId);

        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("1");
        parent::setWhere("basLinkId IN ('$basLinkId')");
        parent::select();
        return parent::query();
    }


    /**
     * Verifica se a pessoa logada tem acesso ao congelamento.
     *
     * @return boolean
     */
    public function checkPersonAccess($personId = null, $exemplaryStatusId  = null)
    {
        //Pega usuário logado
        $personId   = !is_null($personId) ? $personId : $this->busAuthenticate->getUserCode();

        //Se tiver usuário logado, que não é operador
        if (!$personId)
        {
            return false;
        }

        //Pega todos os grupos(dentro da validade) que o usuário tem acesso
        $groups      = $this->busBond->getAllPersonLink($personId);

        foreach ($groups as $g)
        {
            $group[] = $g->linkId;
        }

        if(is_null($exemplaryStatusId))
        {
            return $this->checkGroupAccess($group);
        }

        return $this->checkAccess($group, $exemplaryStatusId);
    }


    /**
     * Método que obtém todos os estados do grupo
     *
     * @param (int) Id do grupo
     * @return (array) com os grupos
     */
    public function getRequestAcces($basLinkId)
    {
        $this->clear();
        $this->setTables('gtcrequestchangeexemplarystatusaccess A
                          INNER JOIN gtcexemplarystatus B
                                  ON (A.exemplarystatusid = B.exemplarystatusid)');
        
        $this->setColumns('A.exemplaryStatusId, B.description');
        $this->setWhere('A.baslinkid = ?');

        $sql = $this->select($basLinkId);
        
        $result = $this->query($sql, true);

        $this->exemplaryStatus = $result;

        return $result;
    }


    /**
     * FIXME função muito confusa, pode ser simplificada
     */
    public function getRequestAccessForBasLinkId($basLinkId, $forSelect = false)
    {

        $basLinkId = is_array($basLinkId) ? $basLinkId : array($basLinkId);
        $basLinkId = implode(",", $basLinkId);

        parent::clear       ();
        parent::setTables   ($this->tables);
        parent::setColumns  ("exemplaryStatusId");
        parent::setWhere    ("basLinkId IN ($basLinkId)");

        $sql    = parent::select();
        $result = parent::query($sql, true);

        $msql = new MSQL('exemplaryStatusId, description', 'gtcExemplaryStatus');
        $idIn = array();

        foreach($result as $obj)
        {
            $idIn[] = $obj->exemplaryStatusId;
        }

        $idIn = trim(implode(',', $idIn));

        if(strlen($idIn))
        {
            $msql->setWhere("exemplaryStatusId IN ($idIn)");
        }
        $sql = $msql->select();
        $res = $this->db->query($sql, true);

        $out = array();
        if ($res->result)
        {
            foreach ($res->result as $i => $v)
            {
                list($out[$i]->exemplaryStatusId,
                     $out[$i]->description) = $v;
            }
        }

        $this->exemplaryStatus = $out;

        if(!$result || !$forSelect)
        {
            return $result;
        }

        $busExemplaryStatus = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        return $busExemplaryStatus->listExemplaryStatus(false, false, true, false, $idIn);
    }


    /**
     * Limpa os atributos
     *
     */
    public function clean()
    {
        $this->basLinkId =
        $this->exemplaryStatusId =
        $this->basLinkIdS =
        $this->exemplaryStatusIdS = null;
    }

}

?>
