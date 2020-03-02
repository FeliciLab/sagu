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
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 02/04/2009
 *
 **/


class BusinessGnuteca3BusRequestChangeExemplaryStatusComposition extends GBusiness
{
    /**
     * Attributes
     */

    public  $requestChangeExemplaryStatusId,// | integer                     | not null
            $itemNumber,                    // | character varying(20)       | not null
            $oldItemNumber,                    // | character varying(20)       | not null
            $confirm,                       // | boolean                     | default false
            $date,                          // | timestamp without time zone | not null
            $applied,                       // | boolean                     | default false
            $exemplaryFutureStatusDefinedId;

    public  $requestChangeExemplaryStatusIdS,// | integer                     | not null
            $itemNumberS,                    // | character varying(20)       | not null
            $confirmS,                       // | boolean                     | default false
            $dateS,                          // | timestamp without time zone | not nul
            $appliedS,                       // | boolean                     | default false
            $exemplaryFutureStatusDefinedIdS;

    public $compostion;

    /**
     * Constructor Method
     */

    function __construct()
    {
        parent::__construct();

        $this->pKey         = 'requestChangeExemplaryStatusId, itemNumber';
        $this->columns      = 'confirm, date, applied, exemplaryFutureStatusDefinedId';
        $this->fullColumns  = "{$this->pKey}, {$this->columns}";
        $this->tables       = 'gtcRequestChangeExemplaryStatusComposition';
    }



    /**
     *
     */
    public function searchRequestChangeExemplaryStatusComposition($order = null)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);

        if($v = $this->requestChangeExemplaryStatusIdS)
        {
            $this->setWhere("requestChangeExemplaryStatusId = ?");
            $data[] = $v;
        }
        if($v = $this->itemNumberS)
        {
            $this->setWhere("itemNumber = ?");
            $data[] = $v;
        }
        if($v = $this->confirmS)
        {
            $this->setWhere("confirm = ?");
            $data[] = $v;
        }
        if($v = $this->dateS)
        {
            $this->setWhere("date = ?");
            $data[] = $v;
        }

        if(!is_null($order))
        {
            $this->setOrderBy($order);
        }

        $sql = parent::select($data);
        return parent::query();
    }


    /**
     *
     */
    public function insertRequestChangeExemplaryStatusComposition()
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->fullColumns);
        $sql = parent::insert($this->associateData($this->fullColumns));
        return parent::Execute();
    }


    /**
     *
     */
    public function updateRequestChangeExemplaryStatusComposition()
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);
        
        if($this->itemNumber != $this->oldItemNumber)
        {
            parent::clear();
            parent::setTables($this->tables);
            $this->columns = 'confirm, date, applied, exemplaryFutureStatusDefinedId, itemNumber';
            parent::setColumns($this->columns);
            parent::setWhere("requestChangeExemplaryStatusId = ? AND itemNumber = '$this->oldItemNumber'");
            parent::update($this->associateData("{$this->columns}, {$this->pKey}"));
        }
        
        else 
        {
            parent::setWhere("requestChangeExemplaryStatusId = ? AND itemNumber = ?");
            parent::update($this->associateData("{$this->columns}, {$this->pKey}"));
        }
        
        return parent::Execute();
    }



    /**
     *
     */
    public function getRequestChangeExemplaryStatusComposition($requestChangeExemplaryStatusId, $confirm = null, $applied = null)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->fullColumns);
        parent::setWhere("requestChangeExemplaryStatusId = ?");
        if (!is_null($confirm))
        {
            parent::setWhere("confirm = '$confirm'");
        }
        if (!is_null($applied))
        {
            parent::setWhere("applied = '$applied'");
        }
        $sql = parent::select(array($requestChangeExemplaryStatusId));
        $result = parent::query(null, true);

        if ( is_array($result) )
        {
            foreach ($result as $line => $info )
            {
                $result[$line]->confirmDesc = $info->confirm == DB_TRUE ? "Sim":"Não";
                $result[$line]->confirmDescription = $info->confirm == DB_TRUE ? "Sim":"Não";
            }
        }

        return $result;
    }



    /**
     *
     */
    public function getRequestChangeExemplaryStatusCompositionExemplaryDetails($requestChangeExemplaryStatusId, $returnType = "object", $confirm = false)
    {
        parent::clear();
        parent::setTables("$this->tables A INNER JOIN gtcExemplaryControl B USING (itemNumber)");
        parent::setColumns("B.controlNumber, A.itemNumber");
        parent::setWhere("requestChangeExemplaryStatusId = ?");
        $data = array();
        $data[] = $requestChangeExemplaryStatusId;
        if ( $confirm )
        {
            parent::setWhere("confirm = ?");
            $data[] = DB_TRUE;
        }
        parent::select($data);
        $result = parent::query(null, ($returnType == "object"));

        if(!$result)
        {
            return false;
        }

        $busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');

        foreach ($result as $index => $content)
        {
            if($returnType == "object")
            {
                $content->title     = $busMaterial->getMaterialTitle($content->controlNumber);
                $content->author    = $busMaterial->getMaterialAuthor($content->controlNumber);
            }
            else
            {
                $content[] = $busMaterial->getMaterialTitle($content[0]);
                $content[] = $busMaterial->getMaterialAuthor($content[0]);
            }

            $result[$index] = $content;
        }

        return $result;
    }



    /**
     *
     */
    public function deleteRequestChangeExemplaryStatusComposition($requestChangeExemplaryStatusId)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);
        parent::setWhere("requestChangeExemplaryStatusId = ?");
        parent::delete(array($requestChangeExemplaryStatusId));
        return parent::execute();
    }


    /**
     *
     */
    public function deleteRequestChangeExemplaryStatusItemComposition($requestChangeExemplaryStatusId, $itemNumber)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);
        parent::setWhere("requestChangeExemplaryStatusId = ? AND itemNumber = ?");
        parent::delete(array($requestChangeExemplaryStatusId, $itemNumber));
        return parent::execute();
    }




    /**
     *
     */
    public function checkCompositionExists($requestChangeExemplaryStatusId)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);
        parent::setWhere("requestChangeExemplaryStatusId = ?");
        parent::select(array($requestChangeExemplaryStatusId));
        return parent::query();
    }



    /**
     * aprova um itemNumber de uma determinada requisição
     *
     * @param int $requestId
     * @param int $itemNumber
     * @return boolean
     */
    public function aproveItemNumberForRequest($requestId, $itemNumber)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("confirm");
        parent::setWhere("requestChangeExemplaryStatusId = ? AND itemNumber = ?");
        parent::update(array('t', $requestId, $itemNumber));
        return parent::Execute();
    }



    /**
     * Seta relação do item com um agendamento para troca de estado
     *
     * @param int $itemNumber
     * @param int $futureStatus
     */
    public function setFutureStatusForItemNumber($requestId, $itemNumber, $futureStatus)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("exemplaryFutureStatusDefinedId");
        parent::setWhere("requestChangeExemplaryStatusId = ? AND itemNumber = ?");
        parent::update(array($futureStatus, $requestId, $itemNumber));
        return parent::Execute();
    }



    /**
     * retorna a relação do item com um agendamento para troca de estado
     *
     * @param int $itemNumber
     * @param int $futureStatus
     */
    public function getFutureStatusForItemNumber($requestId, $itemNumber)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("exemplaryFutureStatusDefinedId");
        parent::setWhere("requestChangeExemplaryStatusId = ? AND itemNumber = ?");
        $sql = parent::select(array($requestId, $itemNumber));
        $result = parent::query($sql);

        if(!$result)
        {
            return false;
        }

        return $result[0][0];
    }




    /**
     * Verifica se tem algum item da composição aprovado
     *
     */
    public function checkOneApproved($requestId)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("1");
        parent::setWhere("requestChangeExemplaryStatusId = ? AND confirm is true AND applied is true");
        $sql = parent::select(array($requestId));
        $result = parent::query($sql);

        if(!$result)
        {
            return false;
        }

        return isset($result[0][0]);

    }


    /**
     * Verifica se tem algum item da composição aprovado
     *
     */
    public function checkApplied($requestId, $itemNumber)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("applied");
        parent::setWhere("requestChangeExemplaryStatusId = ? AND itemNumber = ?");
        $sql = parent::select(array($requestId, $itemNumber));
        $result = parent::query($sql);

        if(!$result)
        {
            return false;
        }

        return (isset($result[0][0]) && ($result[0][0] == 't'));
    }


    /**
     * retorna as requisições que tem relação com algum future status
     *
     * @param int $itemNumber
     * @param int $futureStatus
     */
    public function getRequestIdFromFutureStatus($futureStatus)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("requestChangeExemplaryStatusId");
        parent::setWhere("exemplaryFutureStatusDefinedId = ?");
        $sql = parent::select(array($futureStatus));
        return parent::query($sql);
    }



    /**
     * applica a requisição a um determinado item number
     *
     * @param int $requestId
     * @param int $itemNumber
     * @return boolean
     */
    public function appliedItemNumberForRequest($requestId, $itemNumber)
    {
        $date = GDate::now();
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("applied, date");
        parent::setWhere("requestChangeExemplaryStatusId = ? AND itemNumber = ?");
        parent::update(array('t', $date->getDate(GDate::MASK_TIMESTAMP_DB), $requestId, $itemNumber));
        return parent::Execute();
    }



    /**
     * applica os outros items
     *
     * @param int $requestId
     * @param int $itemNumber
     * @return boolean
     */
    public function applyOthers($requestId, $itemNumber)
    {
        $date = GDate::now();
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("applied, date");
        parent::setWhere("requestChangeExemplaryStatusId = ? AND itemNumber != ?");
        parent::update(array('t', $date->getDate(GDate::MASK_TIMESTAMP_DB), $requestId, $itemNumber));
        return parent::Execute();
    }



    /**
     * retorna os outros
     *
     * @param int $requestId
     * @param int $itemNumber
     * @return boolean
     */
    public function getOthers($requestId, $itemNumber)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("itemNumber, requestChangeExemplaryStatusId");
        parent::setWhere("requestChangeExemplaryStatusId = ? AND itemNumber != ?");
        parent::select(array($requestId, $itemNumber));
        return parent::query();
    }



    /**
     * Enter description here...
     *
     * @param unknown_type $requestId
     * @return unknown
     */
    public function disapproveCompositionForItemNumber($requestId, $itemNumber)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("confirm, exemplaryFutureStatusDefinedId");//, applied");
        parent::setWhere("requestChangeExemplaryStatusId = ? AND itemNumber = ?");
        parent::update(array('f', null, $requestId, $itemNumber));
        return parent::Execute();
    }



    /**
     * Enter description here...
     *
     * @param unknown_type $requestId
     * @return unknown
     */
    public function disapproveCompositionForRequest($requestId)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("confirm, applied, exemplaryFutureStatusDefinedId");
        parent::setWhere("requestChangeExemplaryStatusId = ?");
        parent::update(array('f', 't', null, $requestId));
        return parent::Execute();
    }



    /**
     * Enter description here...
     *
     */
    public function clean()
    {
        $this->requestChangeExemplaryStatusId=  // | integer                     | not null
        $this->itemNumber=                      // | character varying(20)       | not null
        $this->confirm=                         // | boolean                     | default false
        $this->date=                            // | timestamp without time zone | not null
        $this->requestChangeExemplaryStatusIdS= // | integer                     | not null
        $this->itemNumberS=                     // | character varying(20)       | not null
        $this->confirmS=                        // | boolean                     | default false
        $this->dateS=null;                      // | timestamp without time zone | not nul

        $this->compostion = null;
    }



} // final da classe
?>
