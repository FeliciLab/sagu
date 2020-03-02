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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 02/04/2009
 *
 **/


class BusinessGnuteca3BusRequestChangeExemplaryStatus extends GOperation
{
    public  $requestChangeExemplaryStatusComposition;
    public  $requestChangeExemplaryStatusId,
            $futureStatusId,
            $personId,
            $observation,
            $date,
            $finalDate,
            $requestChangeExemplaryStatusStatusId,
            $libraryUnitId,
            $aproveJustOne,
            $discipline;

    public  $requestChangeExemplaryStatusIdS,
            $futureStatusIdS,
            $personIdS,
            $observationS,
            $dateS,
            $beginDateS,
            $endDateS,
            $finalDateS,
            $beginFinalDateS,
            $endFinalDateS,
            $requestChangeExemplaryStatusStatusIdS, 
            $libraryUnitIdS,
            $aproveJustOneS,
            $itemNumberS,
            $disciplineS,
            $endFinalDateSIgual;

    public  $userCodeLogged;

    public  $busReqChanExeStsHistory,
            $busReqChanExeStsComposition,
            $busReqChanExeStsStsComposition;
    
    private $sendMail, $gridData;
    
    //Dias antes que o aviso de vencimento do congelamento do material deve ser feito.
    const DEFAULT_ALERT_DAYS = 7; 

    function __construct()
    {
        parent::__construct();

        $this->pKey         = 'requestChangeExemplaryStatusId';
        $this->columns      = 'futureStatusId, personId, observation, date, finalDate, requestChangeExemplaryStatusStatusId, libraryUnitId, aproveJustOne, discipline';
        $this->columnsNoSts = 'futureStatusId, personId, observation, date, finalDate, requestChangeExemplaryStatusStatusId, libraryUnitId, aproveJustOne, discipline';
        $this->fullColumns  = "{$this->pKey}, {$this->columns}";
        $this->tables       = 'gtcRequestChangeExemplaryStatus';

        $this->busReqChanExeStsHistory        = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusStatusHistory');
        $this->busReqChanExeStsComposition    = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusComposition');
        $this->busReqChanExeStsStsComposition = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusStatusHistory');
        $this->sendMail = new GSendMail();
        $this->observationS = $this->observation;
    }

    /**
     * Pesquisa e formata os dados das requisições de alteração de estado
     * @param (String) order
     * @return (array) de dados 
     */
    public function searchRequestChangeExemplaryStatus($order = 'requestChangeExemplaryStatusId desc')
    {
        $busComposition = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusComposition');
         
        $result = array();

        //caso seja pela minha biblioteca
        if ( $this->userCodeLogged )
        {
            $myRequests = $this->searchRequestChangeExemplaryStatusData('ordem, requestChangeExemplaryStatusId desc', true);
        }
        else //caso seja administrativo
        {
            $result = $this->searchRequestChangeExemplaryStatusData($order, false);
        }
        
        if ( is_array($myRequests) && is_array($result))
        {
            $result = array_merge($myRequests, $result);
        }
        else if ( is_array($myRequests) )
        {
            $result = $myRequests;
        }
        
        if ( is_array($result) )
        {
            foreach ($result as $i => $v)
            {
                $posCode        = 0;
                $posDate        = 6;
                $posFinalDate   = 7;
                $posComposition = 11;

                $code       = $v[$posCode];
                $date       = new GDate($v[$posDate]);
                $finalDate  = new GDate($v[$posFinalDate]);

                $result[$i][$posDate]       = $date->getDate(GDate::MASK_DATE_USER);
                $result[$i][$posFinalDate]  = $finalDate->getDate(GDate::MASK_DATE_USER);

                $composition = $busComposition->getRequestChangeExemplaryStatusComposition( $code );

                if ( $composition )
                {
                    $result[$i][$posComposition] = '';
                    
                    foreach ($composition as $itens)
                    {
                        $itens->applied = strlen($itens->applied) ? $itens->applied : "f";
                        $itens->confirm = strlen($itens->confirm) ? $itens->confirm : "f";
                        $itens->applied = ($itens->applied == DB_TRUE ? _M("Verdadeiro", $this->module) : _M("Falso", $this->module));
                        $itens->confirm = ($itens->confirm == DB_TRUE ? _M("Verdadeiro", $this->module) : _M("Falso", $this->module));
                        $result[$i][$posComposition].= "{$itens->itemNumber}|{$itens->confirm}|{$itens->applied};";
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Busca as requisições de estado de material
     * 
     * @param (String) $order
     * @param type $userRequest
     * @return type 
     */
    public function searchRequestChangeExemplaryStatusData($order, $userRequest = false)
    {
        $busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $busPerson      = $this->MIOLO->getBusiness($this->module, 'BusPerson');
        $busComposition = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusComposition');
        $busStatus      = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusStatus');
        $busExemplarySt = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        
        if ( $this->userCodeLogged )
        {
            $userLoggedOrder = "CASE WHEN B.personId = '{$this->userCodeLogged}' THEN 'A' ELSE 'B' END";
        }
        else
        {
            $userLoggedOrder = "''";
        }

        $sql = "SELECT DISTINCT A.requestChangeExemplaryStatusId,
                                '' AS data, /* campo da grid Congelados */
                                A.discipline,
                                B.name,
                                D.description,
                                E.description,
                                A.date,
                                A.finalDate,
                                C.libraryName,
                                CASE WHEN A.aproveJustOne IS TRUE THEN '". _M("Verdadeiro", $this->module)."' ELSE '". _M("Falso", $this->module)."' END,
                                A.requestChangeExemplaryStatusStatusId,
                                '' AS composition /* campo da grid Congelados */,
                                $userLoggedOrder as ordem
                        FROM                $this->tables           A
                                INNER JOIN  $busPerson->tables      B USING(personId)
                                INNER JOIN  $busLibraryUnit->tables C USING(libraryUnitId)
                                INNER JOIN  $busStatus->tables      D USING(requestChangeExemplaryStatusStatusId)
                                INNER JOIN  $busExemplarySt->_table E ON (A.futureStatusId = E.exemplaryStatusId)
                                INNER JOIN  $busComposition->tables F USING(requestChangeExemplaryStatusId)";

        $where = "";

        if($v = $this->requestChangeExemplaryStatusIdS)
        {
            $where.=" A.requestChangeExemplaryStatusId = '$v' AND ";
        }
        
        if($v = $this->futureStatusIdS)
        {
            $where.=" A.futureStatusId = '$v' AND ";
        }
       
        //chamado na minha biblioteca
        if ( $this->userCodeLogged)
        {
            $where .= " ( ( A.personId = '{$this->userCodeLogged}'
            OR A.requestChangeExemplaryStatusStatusId = '". REQUEST_CHANGE_EXEMPLARY_STATUS_CONFIRMED ."')";

            if ( $this->personId )
            {
                $where .= " AND ( A.personId = '{$this->personId}') ";
            }

            $where .= ' ) AND';
            
            if($v = $this->observationS)
            {
                $where.=" lower(A.observation) like lower('%{$v}%') AND ";
            }
        }
        else //chamado na administração
        {
            if ( $v = $this->personIdS )
            {
                $where.=" A.personId = '$v' AND";
            }
        }
        
        if($v = $this->dateS)
        {
            $where.=" A.date >= '$v' AND ";
        }
        
        if($v = $this->beginDateS)
        {
            $where.=" A.date >= '$v' AND ";
        }
        
        if($v = $this->endDateS)
        {
            $where.=" A.date <= '$v' AND ";
        }
        
        if($v = $this->finalDateS)
        {
            $where.=" A.finalDate <= '$v' AND ";
        }
        
        if($v = $this->beginFinalDateS)
        {
            $where.=" A.finalDate >= '$v' AND ";
        }
        
        if($v = $this->endFinalDateS)
        {
            $where.=" A.finalDate <= '$v' AND ";
        }
        
        if($v = $this->endFinalDateSIgual)
        {
            $where.=" A.finalDate = '$v' AND ";
        }
        
        if ($v = $this->requestChangeExemplaryStatusStatusIdS)
        {
            $where.=" A.requestChangeExemplaryStatusStatusId = '$v' AND ";
        }
        
        if($v = $this->libraryUnitIdS)
        {
            $where.=" A.libraryUnitId in ( ". $v . ") AND ";
        }
        
        if($v = $this->aproveJustOneS)
        {
            $where.=" A.aproveJustOne = '$v' AND ";
        }
        
        if($v = $this->itemNumberS)
        {
            $where.=" F.itemNumber = '$v' AND ";
        }
        
        if($v = $this->disciplineS)
        {
            $where.=" lower(A.discipline) like lower('%{$v}%') AND ";
        }

        if(strlen($where))
        {
            $where = substr($where, 0, strlen($where)-4);
            $sql.= " WHERE $where ";
        }
        if(!is_null($order))
        {
            $sql.= "ORDER BY $order ";
        }
        
        return parent::query($sql);
    }

    public function simpleSearchRequestChangeExemplaryStatus()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        if ($this->libraryUnitIdS)
        {
        	$this->setWhere("libraryunitid = ?");
        	$args[] = $this->libraryUnitIdS;
        }   

        if($this->endFinalDateSIgual)
        {
            $this->setWhere(" finalDate = to_date(?, 'yyyy-mm-dd') ");
            $args[] = $this->endFinalDateSIgual;
        }

        $sql = $this->select($args);
        return parent::query($sql, true);
    }


    /**
     * 
     */
    public function searchRequestChangeExemplaryStatusByStatus()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        if ($this->libraryUnitIdS)
        {
            $this->setWhere("libraryunitid = ?");
            $args[] = $this->libraryUnitIdS;
        }   

        if($this->endFinalDateSIgual)
        {
            $this->setWhere(" finalDate = to_date(?, 'yyyy-mm-dd') ");
            $args[] = $this->endFinalDateSIgual;
        }
                    
        if ($this->requestChangeExemplaryStatusStatusIdS)
        {
        	$this->setWhere("requestchangeexemplarystatusstatusid = ?");
        	$args[] = $this->requestChangeExemplaryStatusStatusIdS;
        }

        $sql = $this->select($args);
        return parent::query($sql, true);
    }
    
    
    /**
     *
     */
    public function insertRequestChangeExemplaryStatus()
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);
        $sql = parent::insert($this->associateData($this->columns));
        return parent::Execute();
    }


    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getCurrentId()
    {
        parent::clear();
        //$query = parent::query("SELECT currval('seq_requestchangeexemplarystatusid')");
        $query = parent::query("SELECT last_value FROM seq_requestchangeexemplarystatusid");
        if(!$query)
        {
            return 0;
        }

        $this->requestChangeExemplaryStatusId = $query[0][0];
        return $query[0][0];
    }


    /**
     * Ao atualizar por esta função, o status na sera atualizado.
     * para atualizar o status, utilize a função changeStatusRequestChangeExemplaryStatus()
     */
    public function updateRequestChangeExemplaryStatus()
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columnsNoSts);
        parent::setWhere("requestChangeExemplaryStatusId = ?");
        $sql = parent::update($this->associateData("{$this->columns}, {$this->pKey}"));
        return parent::Execute($sql);
    }


    /**
     * verifica se é para aprovar apenas um item number da requisição
     *
     * @param int $requestChangeExemplaryStatusId
     * @return  boolean
     */
    public function checkAproveJustOne($requestChangeExemplaryStatusId)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("aproveJustOne");
        parent::setWhere("requestChangeExemplaryStatusId = ?");
        parent::select(array($requestChangeExemplaryStatusId));
        $result = parent::query();
        return (isset($result[0][0]) && $result[0][0] == 't');
    }


    /**
     * verifica se é para aprovar apenas um item number da requisição
     *
     * @param int $requestChangeExemplaryStatusId
     * @return  boolean
     */
    public function setAproveJustOne($aprove, $requestChangeExemplaryStatusId)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("aproveJustOne");
        parent::setWhere("requestChangeExemplaryStatusId = ?");
        parent::update(array($aprove, $requestChangeExemplaryStatusId));
        return parent::execute();
    }


    /**
     * retorna o estado atual
     *
     * @param int $requestChangeExemplaryStatusId
     * @return integer
     */
    public function getCurrentStatus($requestChangeExemplaryStatusId)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("requestChangeExemplaryStatusStatusId");
        parent::setWhere("requestChangeExemplaryStatusId = ?");
        parent::select(array($requestChangeExemplaryStatusId));
        $result = parent::query();
        return $result[0][0];
    }



    /**
     * retorna o estado atual
     *
     * @param int $requestChangeExemplaryStatusId
     * @return integer
     */
    public function getFutureStatus($requestChangeExemplaryStatusId)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns("futureStatusId");
        parent::setWhere("requestChangeExemplaryStatusId = ?");
        parent::select(array($requestChangeExemplaryStatusId));
        $result = parent::query();
        return $result[0][0];
    }








    /**
     * Altera o status de uma requisição.
     * Este metodo controla o historico dos status
     */
    public function changeStatusRequestChangeExemplaryStatus($operator)
    {

        parent::clear       ();
        parent::setTables   ($this->tables);
        parent::setColumns  ("requestChangeExemplaryStatusStatusId");
        parent::setWhere    ("requestChangeExemplaryStatusId = ?");
        parent::update      (array($this->requestChangeExemplaryStatusStatusId, $this->requestChangeExemplaryStatusId));
        $ok = parent::Execute();

        if(!$ok)
        {
            return false;
        }

        // VERIFICA SE O ULTIMO STATUS ADICIONADO É IGUAL AO PROXIMO
        // CASO SEJA, RETORNA
        if($this->busReqChanExeStsHistory->compareLastStatus($this->requestChangeExemplaryStatusId, $this->requestChangeExemplaryStatusStatusId))
        {
            return true;
        }

        $date = GDate::now();
        // INSERE O HISTORICO DO ESTADO DA REQUISIÇÃO
        $this->busReqChanExeStsHistory->clean();
        $this->busReqChanExeStsHistory->requestChangeExemplaryStatusId = $this->requestChangeExemplaryStatusId;
        $this->busReqChanExeStsHistory->requestChangeExemplaryStatusStatusId = $this->requestChangeExemplaryStatusStatusId;
        $this->busReqChanExeStsHistory->date = $date->getDate(GDate::MASK_TIMESTAMP_DB);
        $this->busReqChanExeStsHistory->operator = $operator;
        $this->busReqChanExeStsHistory->insertRequestChangeExemplaryStatusHistory();

        return true;
    }


    /**
     * retorna uma determinada requisição
     */
    public function getRequestChangeExemplaryStatus($requestChangeExemplaryStatusId, $object = true, $return = false)
    {
        if(!strlen($requestChangeExemplaryStatusId))
        {
            return false;
        }

        parent::clear();

        $busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $busPerson      = $this->MIOLO->getBusiness($this->module, 'BusPerson');
        $busComposition = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusComposition');
        $busStatus      = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusStatus');
        $busExemplarySt = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');

        $sql = "SELECT  A.requestChangeExemplaryStatusId,
                        A.requestChangeExemplaryStatusStatusId,
                        D.description,
                        A.futureStatusId,
                        E.description,
                        A.libraryUnitId,
                        C.libraryName,
                        A.personId,
                        A.observation,
                        B.name,
                        A.date,
                        A.finalDate,
                        A.aproveJustOne,
                        A.discipline
                FROM                $this->tables           A
                        INNER JOIN  $busPerson->tables      B USING(personId)
                        INNER JOIN  $busLibraryUnit->tables C USING(libraryUnitId)
                        INNER JOIN  $busStatus->tables      D USING(requestChangeExemplaryStatusStatusId)
                        INNER JOIN  $busExemplarySt->_table E ON (A.futureStatusId = E.exemplaryStatusId)
                WHERE   A.requestChangeExemplaryStatusId = '$requestChangeExemplaryStatusId' ";

        $result = parent::query($sql);

        if(!$result || !$object)
        {
            return $result;
        }

        $r = null;
        $r->requestChangeExemplaryStatusId          = $result[0][0];
        $r->requestChangeExemplaryStatusStatusId    = $result[0][1];
        $r->requestChangeExemplaryStatusStatusDesc  = $result[0][2];
        $r->futureStatusId                          = $result[0][3];
        $r->futureStatusDesc                        = $result[0][4];
        $r->libraryUnitId                           = $result[0][5];
        $r->libraryName                             = $result[0][6];
        $r->personId                                = $result[0][7];
        $r->observation                             = $result[0][8];
        $r->personNane                              = $result[0][9];
        $r->date                                    = $result[0][10];
        $r->finalDate                               = $result[0][11];
        $r->aproveJustOne                           = $result[0][12];
        $r->discipline                              = $result[0][13];
        $r->requestChangeExemplaryStatusComposition = $busComposition->getRequestChangeExemplaryStatusComposition($requestChangeExemplaryStatusId);

        if($return)
        {
            return $object ? $r : $result;
        }

        $this->setData($r);

        return $r;
    }

    /**
     * Este método retorna as requisições semalhantes a uma determinada
     * os periodos do tempo devem se cruzar para que seja válidado.
     *
     * @param obj $objectRequest
     */
    public function getSimilarRequest($objectRequest, $notApplied = true, $requestStatus = false)
    {
        $busComposition     = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusComposition');
        $busFutureStatus    = $this->MIOLO->getBusiness($this->module, 'BusExemplaryFutureStatusDefined');
        $compTable          = $busComposition->tables;
        $futTable           = $busFutureStatus->tables;

        $itemNumbers = implode("', '", $objectRequest->itemNumber);

        $sql = "SELECT A.requestChangeExemplaryStatusId, A.date, A.finalDate
                  FROM $this->tables A
            INNER JOIN $compTable B
                 USING (requestChangeExemplaryStatusId) ";

        $sql.= " WHERE A.requestchangeexemplarystatusid != {$objectRequest->requestChangeExemplaryStatusId}
                   AND A.libraryUnitId = {$objectRequest->libraryUnitId}
                   AND B.itemnumber IN ('{$itemNumbers}')
                   AND B.confirm is true";

        if($notApplied === true)
        {
            $sql.= " AND B.applied is false ";
        }
        elseif($notApplied === false)
        {
            $sql.= " AND B.applied is true ";
        }

        if($requestStatus)
        {
            $sql.= " AND A.requestchangeexemplarystatusstatusid = '$requestStatus'";
        }

        $result = parent::query($sql);
        if(!$result)
        {
            return false;
        }

        foreach ($result as $v)
        {
            $r[] = $v[0];
        }
        return $r;
       
    }


    /**
     * deleta uma determinada requisição
     */
    public function deleteRequestChangeExemplaryStatus($requestChangeExemplaryStatusId)
    {
        $this->busReqChanExeStsComposition->deleteRequestChangeExemplaryStatusComposition($requestChangeExemplaryStatusId);
        $this->busReqChanExeStsStsComposition->deleteRequestChangeExemplaryStatusStatusHistory($requestChangeExemplaryStatusId);

        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);
        parent::setWhere("requestChangeExemplaryStatusId = ?");
        parent::delete(array($requestChangeExemplaryStatusId));
        return parent::execute();
    }

    /**
    * Renova a requisição para o semestre
    */
    public function getLibraryUnit($requestId)
    {
        settype($requestId, "integer");

        $sql = "SELECT      a.libraryUnitId,
                            b.libraryname
                FROM        gtcRequestChangeExemplaryStatus a
                INNER JOIN  gtclibraryunit                  b
                USING       (libraryUnitId)
                WHERE       a.requestChangeExemplaryStatusId = {$requestId}";

        $rs  = $this->query($sql);

        if(!$rs)
        {
            return false;
        }

        $r = new StdClass();
        $r->libraryUnitId   = $rs[0][0];
        $r->libraryname     = $rs[0][1];
        return $r;
    }




    /**
    * Renova a requisição para o semestre
    */
    public function renewRequest($requestId, $dateRenew)
    {
        
        if (!$dateRenew)
        {
            $dateRenew = $this->getPeriodInterval();
            $dateRenew = new GDate($dateRenew->finalDate);
            $dateRenew = $dateRenew->getDate(GDate::MASK_DATE_DB);
        }
    	if (!$requestId)
    	{
    		return false;
    	}

        $sql = "UPDATE gtcRequestChangeExemplaryStatus SET finalDate = '{$dateRenew}' WHERE requestChangeExemplaryStatusId = {$requestId}";
        $rs  = $this->execute($sql);
        return $rs;
    }



    /**
     * Altera os registro de um usuário por outro
     *
     * @param integer $currentPersonId
     * @param integer $newPersonId
     * @return boolean
     */
    public function updatePersonId($currentPersonId, $newPersonId)
    {
        $this->clear();
        $this->setColumns("personId");
        $this->setTables($this->tables);
        $this->setWhere(' personId = ?');
        $sql = $this->update(array($newPersonId, $currentPersonId));
        $rs  = $this->execute($sql);
        return $rs;
    }




    /**
     * limpa attributos da classe
     *
     */
    public function clean()
    {
        $this->requestChangeExemplaryStatusId =         //  | integer                       | not null default nextval('seq_requestchangeexemplarystatusid'::regclass)
        $this->futureStatusId =                         //  | integer                       | not null
        $this->personId =                               //  | integer                       | not null
        $this->observation =                            //  | text                          |
        $this->date =                                   //  | timestamp without time zone   | not null
        $this->finalDate =                              //  | timestamp without time zone   | not null
        $this->requestChangeExemplaryStatusStatusId =   //  | integer                       | not null
        $this->libraryUnitId =                          //  | integer                       | not null
        $this->requestChangeExemplaryStatusIdS =        //  | integer                       | not null default nextval('seq_requestchangeexemplarystatusid'::regclass)
        $this->futureStatusIdS =                        //  | integer                       | not null
        $this->personIdS =                              //  | integer                       | not null
        $this->observationS =                           //  | text                          |
        $this->dateS =                                  //  | timestamp without time zone   | not null
        $this->finalDateS =                             //  | timestamp without time zone   | not null
        $this->requestChangeExemplaryStatusStatusIdS =  //  | integer                       | not null
        $this->aproveJustOne =
        $this->aproveJustOneS =
        $this->libraryUnitIdS =
        $this->discipline =
        $this->endFinalDateSIgual =
        $this->disciplineS                            = null;
    }
    
    
    public function notifyEndRequest( $advance, $libraryUnitId=null)
    {
    	$libraryUnitIds = is_array($libraryUnitId) ? $libraryUnitId : array($libraryUnitId);
        
        foreach( $libraryUnitIds as $libraryUnitId )
        {
                $this->clear();
                $this->setColumns($this->fullColumns);
                $this->setTables($this->tables);            
                $args = null;
                
	        $busLibraryUnitConfig = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitConfig');
	        $hojeMais = GDate::now();
                $periodInterval = $this->getPeriodInterval();
                
                //Data de hoje for maior que o inicio do período de renovação
                $this->setWhere("(finalDate - interval '{$periodInterval->requestChangeDays}' day)::date = ?");
                $args[] = $hojeMais->getDate(GDate::MASK_DATE_DB);
                $this->setWhere('libraryUnitId = ?');
	        $args[] = $libraryUnitId;
                //Somente avisa quem tem exemplares com estado Confirmado
                $this->setWhere('requestChangeExemplaryStatusStatusId = ?');
	        $args[] = REQUEST_CHANGE_EXEMPLARY_STATUS_CONFIRMED;
                
                $sql = $this->select($args);
                
                $content = $this->query($sql, true);                
	        
	        if(!$content)
	        {
	        	continue;
	        }
	        
	        $notifyEndRequestMessage = array();
	        foreach ($content as $requestObject)
	        {
	        	$busPerson = $this->MIOLO->getBusiness($this->module, 'BusPerson');
		        $busRequestChangeExemplaryStatusComposition = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusComposition');
		
		        $personId       = $requestObject->personId;
		        $requestId      = $requestObject->requestChangeExemplaryStatusId;
		        $libraryUnitId  = $requestObject->libraryUnitId;
		        $finalDate      = new GDate($requestObject->finalDate);
		        $person         = $busPerson->getBasicPersonInformations($personId);
		
		        if(!$person || !strlen($person->email))
		        {
		            continue;
		        }
		        
		        //mensagem
	            $notifyEndRequestMessage[$requestId][0] = $requestId;
	            $notifyEndRequestMessage[$requestId][1] = "{$personId} - {$person->name}";
	            $notifyEndRequestMessage[$requestId][4] = $person->email;
	            $notifyEndRequestMessage[$requestId][5] = $finalDate->getDate(GDate::MASK_DATE_USER);
	            
	            if (!$person->email)
	            {
	                $notifyEndRequestMessage[$requestId][2] = DB_FALSE;
	                $notifyEndRequestMessage[$requestId][3] = _M("E-mail da pessoa está em branco", $this->module);
	                continue;
	            }
	                        
	            //testa se e-mail é no formato conta@domínio.extensão
	            if (!preg_match("/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+\.([a-zA-Z]{2,4})$/", $person->email))
	            {
	                $notifyEndRequestMessage[$requestId][2] = DB_FALSE;
	                $notifyEndRequestMessage[$requestId][3] = _M("Person mail is invalid.", $this->module);
	                continue;
	            }
		
		        $subject = $busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_COMUNICA_SOLICITANTE_TERMINO_REQUISICAO_SUBJECT');
		        $content = $busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_COMUNICA_SOLICITANTE_TERMINO_REQUISICAO_CONTENT');
		        $materials = $busRequestChangeExemplaryStatusComposition->getRequestChangeExemplaryStatusCompositionExemplaryDetails($requestId, "array", true);
		
		        $columns = array
		        (
		            _M("Número de controle",    $this->module),
		            _M("Número do exemplar",       $this->module),
		            _M("Título",             $this->module),
		            _M("Autor",            $this->module),
		        );
		
		        $materialContents = $this->sendMail->mountTable(_M("Materiais", $this->module), $columns, $materials);
		
		        //Obtem o conteudo do email
		        $gf = new GFunction();
		        $gf->setVariable('$REQUESTOR_NAME',     $person->name);
		        $gf->setVariable('$MATERIALS',          '--MATERIAIS--');
		        $gf->setVariable('$FINAL_DATE',         $finalDate->getDate(GDate::MASK_DATE_USER));
		        $gf->setVariable('$REQUEST_ID',         $requestObject->requestChangeExemplaryStatusId);
		        $content = $gf->interpret($content);
		        $content = str_replace('--MATERIAIS--', $materialContents, $content);
	            
		        if ( !$this->sendMail->informaSolicitanteTerminoRequisicao($person, $subject, $content) )
		        {
		        	$notifyEndRequestMessage[$requestId][2] = DB_FALSE;
	                $notifyEndRequestMessage[$requestId][3] = _M("Falha no envio do e-mail", $this->module);
		        }
		        else 
		        {
		        	$notifyEndRequestMessage[$requestId][2] = DB_TRUE;
	                $notifyEndRequestMessage[$requestId][3] = _M("Sucesso!", $this->module);
		        }
	        }
	        
	         //envia e-mail para admin
            $this->sendMail->sendMailToAdminResultOfNotifyEndRequest($notifyEndRequestMessage, $libraryUnitId);
        }
        
        // MONTA GRID COM OS DADOS DOS ENVIOS DE EMAIL.
        foreach ($notifyEndRequestMessage as $content)
        {
            $this->addGridData($content);
        }
        
        return true;
    }
    
    public function addGridData($gridData)
    {
        $this->gridData[] = $gridData;
    }


    public function getGridData()
    {
        return $this->gridData;
    }
    
  
    public function getPeriodInterval()
    {
        $periods = explode("\n", REQUEST_CHANGE_EXEMPLARY_STATUS_SEMESTER_PERIOD);

        $periods = array_filter($periods); //limpa linhas vazias no array
        $today = GDate::now();
        $periodsInformation = array();
        
        //Se tiver ao menos um periodo definido com um NOME={Parametros do periodo}
        if ( strpos($periods[0],'=') )
        {
            foreach ($periods as $period)
            {
                $periodData = new stdClass();
                //Separa as informações do periodo
                $periodInfo = explode("=",$period);
                $periodData->periodId = $period[0];
                /*Informações da inicio/fim e dias de "janela", dias de janela são
                 * os dias antes do inicio do período que os exemplares podem ter 
                 * seu estado solicitado.
                 */
                $periodInfo = explode(";",$periodInfo[1]);

                //Separa informação do dia e mes para definir a data inicial de solicitação.
                $beginDate = GDate::now();
                $dayMonth = explode("/", $periodInfo[0]);
                $beginDate->setDay($dayMonth[0]);
                $beginDate->setMonth($dayMonth[1]);
                $periodData->beginDate = $beginDate;

                //Separa informação do dia e mes para definir a data final de solicitação.
                $finalDate = GDate::now();
                $dayMonth = explode("/", $periodInfo[1]);
                $finalDate->setDay($dayMonth[0]);
                $finalDate->setMonth($dayMonth[1]);
                $periodData->finalDate = $finalDate;

                //Cria atributo no stdClass para usar a quantidade de dias de aviso de e-mail
                $periodData->requestChangeDays = $periodInfo[2];

                //Reduz os dias a partir da data inicial para definir a data com a janela de solicitacao.
                $requestBeginDate = clone $beginDate;
                $requestBeginDate->addDay(-$periodInfo[2]);
                $periodData->requestBeginDate = $requestBeginDate;

                //Reduz os dias a partir da data inicial para definir a data com a janela de solicitacao.            
                $requestFinalDate = clone $finalDate;
                $requestFinalDate->addDay(-$periodInfo[2]);
                $periodData->requestFinalDate = $requestFinalDate;

                //Se a data de hoje estiver dentro do período então permite solicitação.
                if ( $today->compare($requestBeginDate, '>=') && $today->compare($requestFinalDate, '<=') )
                {
                    return $periodData;
                }            

                $periodsInformation[] = $periodData;
            }
        }
        
        //Se chegar até aqui é porque não encontrou um período válido.
        if ( !empty($periodsInformation) )
        {
            //Adiciona 1 ano ao primeiro período
            $periodsInformation[0]->beginDate->addYear(1);
            $periodsInformation[0]->finalDate->addYear(1);
            $periodsInformation[0]->requestBeginDate->addYear(1);
            $periodsInformation[0]->requestFinalDate->addYear(1);

            //Retorna a data do primeiro período no próximo ano.
            return $periodsInformation[0];
        }
        else
        {
            //Se nao tiver nenhum periodo valido entao os dias para aviso sao 7 por padrao.
            $periodData->requestChangeDays = self::DEFAULT_ALERT_DAYS;
            //Se chegar aqui é porque não tem período válido.
            return $periodData;            
        }

    }    
}
?>
