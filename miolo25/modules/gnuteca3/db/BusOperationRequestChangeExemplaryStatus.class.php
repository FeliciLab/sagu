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
 * @author Luiz G Gregory Filho [luiz@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 02/04/2009
 *
 * */
class BusinessGnuteca3BusOperationRequestChangeExemplaryStatus extends GMessages
{

    private $busReqChanExeStatus,
            $busReqChanExeStsAccess,
            $busReqChanExeStsComposition,
            $busReqChanExeStsHistory,
            $busReqChanExeStsStatus,
            $busAuthenticate,
            $busBond,
            $busExemplaryControl,
            $busExemplaryStatus,
            $busOperationChangeStatus,
            $busExemplaryFutureStatusDefined,
            $busExemplaryStatusHistory;

    private $msgCode,
            $operator;

    /**
     * Constructor Method
     */
    function __construct($requestChangeExemplaryStatusId = null)
    {
        parent::__construct();

        $this->busReqChanExeStatus              = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatus');
        $this->busReqChanExeStsAccess           = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusAccess');
        $this->busReqChanExeStsComposition      = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusComposition');
        $this->busReqChanExeStsHistory          = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusStatusHistory');
        $this->busReqChanExeStsStatus           = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusStatus');
        $this->busAuthenticate                  = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $this->busBond                          = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->busExemplaryControl              = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busExemplaryStatus               = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        $this->busExemplaryFutureStatusDefined  = $this->MIOLO->getBusiness($this->module, 'BusExemplaryFutureStatusDefined');
        $this->busOperationChangeStatus         = $this->MIOLO->getBusiness($this->module, 'BusOperationChangeStatus');
        $this->busExemplaryStatusHistory        = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatusHistory');

        $this->setRequestChangeExemplaryStatusId($requestChangeExemplaryStatusId);
    }

    /**
     * Seta o ID da requisição
     *
     * @param unknown_type $requestChangeExemplaryStatusId
     */
    public function setRequestChangeExemplaryStatusId($requestChangeExemplaryStatusId)
    {
        $this->busReqChanExeStatus->requestChangeExemplaryStatusId = $requestChangeExemplaryStatusId;
        $this->requestChangeExemplaryStatusId = $requestChangeExemplaryStatusId;
    }

    /**
     * Seta o estado futuro desejado para o exemplar
     *
     * @param int $futureStatus
     */
    public function setFutureStatusId($futureStatusId)
    {
        $this->busReqChanExeStatus->futureStatusId = $futureStatusId;
    }

    /**
     * Seta a pessoa solicitante
     *
     * @param int $personId
     */
    public function setPersonId($personId)
    {
        $this->busReqChanExeStatus->personId = $personId;
    }

    /**
     * Seta a data que foi feita a solicitação
     *
     * @param timestamp $date
     */
    public function setDate($date = null)
    {
        if ($date)
        {
            $date = new GDate($date);
            $date = $date->getDate(GDate::MASK_TIMESTAMP_DB);
        }
        else
        {
            $date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        }

        $this->busReqChanExeStatus->date = $date;
    }

    /**
     * Seta a data final da solicitação;
     *
     * @param timestamp $date (caso seja nullo, seta a data atual automaticamente)
     * @param integer $sumDays (permite somar dias os parametro $date)
     */
    public function setFinalDate($date = null, $sumDays = null)
    {
        if ($date)
        {
            $date = new GDate($date);
        }
        else
        {
            $date = GDate::now();
        }

        if (is_integer($sumDays))
        {
            $date->addDay($sumDays);
        }

        $date = $date->getDate(GDate::MASK_TIMESTAMP_DB);
        $this->busReqChanExeStatus->finalDate = $date;
    }

    /**
     * Seta o estado que se encontra a solicitação
     *
     * @param integer $requestChangeExemplaryStatusStatusId (referente a registros da tabela gtcRequestChangeExemplaryStatusStatus)
     */
    public function setRequestChangeExemplaryStatusStatusId($requestChangeExemplaryStatusStatusId)
    {
        $this->busReqChanExeStatus->requestChangeExemplaryStatusStatusId = $requestChangeExemplaryStatusStatusId;
    }

    /**
     * Seta a biblioteca referente a solicitação
     *
     * @param int $libraryUnitId
     */
    public function setLibraryUnit($libraryUnitId)
    {
        $this->busReqChanExeStatus->libraryUnitId = $libraryUnitId;
    }

    /**
     * Seta um observação para a requisiçao
     *
     * @param text $observation
     */
    public function setObservation($observation)
    {
        $this->busReqChanExeStatus->observation = $observation;
    }

    /**
     * Seta se a requisição aprovara apena um item number ou mais de um item number
     *
     * @param boolean $aproveJustOne
     */
    public function setAproveJustOne($aproveJustOne)
    {
        $this->busReqChanExeStatus->aproveJustOne = $aproveJustOne;
    }

    /**
     * Seta o nome da disciplina
     *
     * @param boolean $aproveJustOne
     */
    public function setDiscipline($discipline)
    {
        $this->busReqChanExeStatus->discipline = $discipline;
    }

    /**
     * Este metodo seta a composição da requisição
     *
     * O primeiro parametro recebe um array simples com itemNumbers de exemplares.
     * @param simple array $composition
     */
    private function setRequestComposition($composition)
    {
        $this->busReqChanExeStsComposition->compostion = $composition;
    }

    /**
     * seta o id do operador
     *
     * @param int $operator
     */
    public function setOperatorId($operator)
    {
        $this->operator = $operator;
    }

    /**
     * retorna o estado futuro da requisição
     *
     * @return int
     */
    public function getFutureStatusId()
    {
        return $this->busReqChanExeStatus->futureStatusId;
    }

    /**
     * retorna a pessoa que efetuou a solicitação
     *
     * @return int
     */
    public function getPersonId()
    {
        return $this->busReqChanExeStatus->personId;
    }

    /**
     * retorna o id do operador
     *
     * @return int
     */
    public function getOperatorId()
    {
        return strlen($this->operator) ? $this->operator : GOperator::getOperatorId();
    }

    /**
     * returna a data que foi feita a solicitação
     *
     * @param boolean $objectGDate (Permite retornar em objeto GDate)
     * @return date
     */
    public function getDate($object = false)
    {
        return $object ? new GDate($this->busReqChanExeStatus->date) : $this->busReqChanExeStatus->date;
    }

    /**
     * returna a data de termino de validade da solicitação
     *
     * @param boolean $objectGDate (permite retorna em objeto GDate)
     * @return date
     */
    public function getFinalDate($object = false)
    {
        return $object ? new GDate($this->busReqChanExeStatus->finalDate) : $this->busReqChanExeStatus->finalDate;
    }

    /**
     * retorna o estado da operação
     *
     * @return int
     */
    public function getRequestChangeExemplaryStatusStatus()
    {
        return $this->busReqChanExeStatus->requestChangeExemplaryStatusStatusId;
    }

    /**
     * Allias para o method getRequestChangeExemplaryStatusStatus
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getRequestChangeExemplaryStatusStatus();
    }

    /**
     * retorna a biblioteca
     *
     * @return int
     */
    public function getLibraryUnit()
    {
        return $this->busReqChanExeStatus->libraryUnitId;
    }

    /**
     * Retorna o vinculo da pessoa, caso a pessoa na seja setada, busca a pessoa da autenticação;
     *
     * @return int linkId
     */
    public function getPersonLink()
    {
        $personId = $this->getPersonId();

        if ($personId)
        {
            $bond = $this->busBond->getPersonLink($personId);
            return isset($bond->linkId) ? $bond->linkId : false;
        }

        return $this->busAuthenticate->getPersonLink();
    }

    /**
     * Retorna a composição da requisição;
     *
     * @return array
     */
    public function getRequestComposition()
    {
        return $this->busReqChanExeStsComposition->compostion;
    }

    /**
     * retorna se a requisição deve aprovar apenas um item number ou mais que um
     */
    public function getAproveJustOne()
    {
        return $this->busReqChanExeStatus->aproveJustOne;
    }

    /**
     * retorna a disciplina
     *
     * @return int
     */
    public function getDiscipline()
    {
        return $this->busReqChanExeStatus->discipline;
    }

    /**
     * Verifica se o solicitante pode requisitar um determinado futuro para o exemplar
     *
     * @return boolean
     */
    public function checkAccess($personLinkId = null, $futureStatusId = null)
    {
        $futureStatusId = is_null($futureStatusId) ? $this->getFutureStatusId() : $futureStatusId;
        return $this->busReqChanExeStsAccess->checkPersonAccess($this->getPersonId(), $futureStatusId);
    }

    /**
     * Este metodo checa a composição e seta a compição valida.
     *
     * @param simple array $composition
     * @return boolean
     */
    public function checkComposition($composition = null)
    {
        $composition = is_null($composition) ? $this->getRequestComposition() : $composition;

        if (!is_array($composition))
        {
            return false;
        }

        $allExemplatiesOK = true;
        $newComposition = false;
        $libraryUnitId = $this->getLibraryUnit();

        foreach ($composition as $index => $v)
        {
            if (!is_object($v))
            {
                $tmpV = new stdClass();
                $tmpV->itemNumber = $v;
                $tmpV->confirm = DB_FALSE;
                $tmpV->$delete = false;
                $tmpV->$update = false;
                $tmpV->$insert = true;

                $v = $tmpV; //restaura o v como objeto
            }

            //Exemplares que estao para ser removidos nao devem ser validados.
            if ($v->delete)
            {
                $v->update =false;
                $v->insert = false;
                $newComposition[$index] = $v;
                continue;
            }

            if ((boolean)$ok = $this->busExemplaryControl->checkExemplaryExists($v->itemNumber, $libraryUnitId))
            {
                if ($v->exemplaryStatusId)
                {
                    $exemplaryStatus = $this->busExemplaryStatus->getExemplaryStatus($v->exemplaryStatusId, true);
                    $canSchedule = MUtil::getBooleanValue($exemplaryStatus->scheduleChangeStatusForRequest);

                    if (!$canSchedule)
                    {
                        $this->addError(_M('Materiais em estado @1 não podem ser congelados', 'gnuteca', $exemplaryStatus->description));
                        continue;
                    }
                }

                $newComposition[$index] = $v;
            }
            
            if( empty($ok) )
            {
                $this->addError(_M('Exemplar @1 não existe.', 'gnuteca', $v->itemNumber));
                return FALSE;
            }
        }

        $this->setRequestComposition($newComposition);

        return $allExemplatiesOK;
    }

    /**
     * Insere uma nova requisição de alteração de status de exemplar
     *
     * @return boolean
     */
    public function insertRequest()
    {
        //verifica se tudo que é necessário foi preenchido corretamente
        if (!$this->checkAttributes())
        {
            return false;
        }

        $this->setRequestChangeExemplaryStatusStatusId(REQUEST_CHANGE_EXEMPLARY_STATUS_REQUESTED);

        // INSERE A REQUISIÇÂO
        $ok = $this->busReqChanExeStatus->insertRequestChangeExemplaryStatus();

        if (!$ok)
        {
            $this->addError(107);
            return false;
        }

        // BUSCA O ID DA REQUISIÇÂO
        $requestId = $this->busReqChanExeStatus->getCurrentId();

        if (!$requestId)
        {
            return false;
        }

        $this->requestChangeExemplaryStatusId = $requestId;

        // PERCORRE OS ITENS INSERINDO A COMPOSIÇÂO
        foreach ($this->getRequestComposition() as $itemNumber)
        {
            if (!is_object($itemNumber))
            {
                $this->busReqChanExeStsComposition->clean();
                $this->busReqChanExeStsComposition->requestChangeExemplaryStatusId = $requestId;
                $this->busReqChanExeStsComposition->itemNumber = $itemNumber;
                $this->busReqChanExeStsComposition->confirm = DB_FALSE;
                $this->busReqChanExeStsComposition->date = $this->getDate();
                $this->busReqChanExeStsComposition->insertRequestChangeExemplaryStatusComposition();
            }
            else
            {
                $this->busReqChanExeStsComposition->clean();
                $this->busReqChanExeStsComposition->requestChangeExemplaryStatusId = $requestId;
                $this->busReqChanExeStsComposition->itemNumber = $itemNumber->itemNumber;
                $this->busReqChanExeStsComposition->confirm = strlen($itemNumber->confirm) ? $itemNumber->confirm : DB_FALSE;
                $this->busReqChanExeStsComposition->date = $this->getDate();
                $this->busReqChanExeStsComposition->insertRequestChangeExemplaryStatusComposition();
            }
        }

        // CHECA SE EXISTE COMPOSIÇÃO, CASO NÃO EXISTE UMA COMPOSIÇÃO, REMOVE A REQUISIÇÃO
        if (!$this->busReqChanExeStsComposition->checkCompositionExists($requestId))
        {
            $this->busReqChanExeStatus->deleteRequestChangeExemplaryStatus($requestId);
            return false;
        }

        // INSERE O HISTORICO DO ESTADO DA REQUISIÇÂO
        $this->busReqChanExeStsHistory->clean();
        $this->busReqChanExeStsHistory->requestChangeExemplaryStatusId = $requestId;
        $this->busReqChanExeStsHistory->requestChangeExemplaryStatusStatusId = REQUEST_CHANGE_EXEMPLARY_STATUS_REQUESTED;
        $this->busReqChanExeStsHistory->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $this->busReqChanExeStsHistory->operator = $this->getOperatorId();
        $this->busReqChanExeStsHistory->insertRequestChangeExemplaryStatusHistory();

        return true;
    }

    /**
     * Atualiza um requisição;
     *
     * @return boolean
     */
    public function updateRequest()
    {
        if (!$this->checkAttributes())
        {
            return false;
        }

        if(!$this->checkUpdateStatus($this->getStatus(), $this->busReqChanExeStatus->getCurrentStatus($this->requestChangeExemplaryStatusId))){
            $this->addError(114);
            return false;
        }

        // UPDATE A REQUISIÇÂO
        $ok = $this->busReqChanExeStatus->updateRequestChangeExemplaryStatus();

        if (!$ok)
        {
            $this->addError(107);
            return false;
        }

        $confirmeExemplaries = null;
        foreach ($this->getRequestComposition() as $itemNumber)
        {
            if (is_object($itemNumber))
            {

                $this->busReqChanExeStsComposition->clean();
                $this->busReqChanExeStsComposition->requestChangeExemplaryStatusId = $this->requestChangeExemplaryStatusId;
                $this->busReqChanExeStsComposition->itemNumber = $itemNumber->itemNumber;
                $this->busReqChanExeStsComposition->oldItemNumber = $itemNumber->oldItemNumber;
                $this->busReqChanExeStsComposition->confirm = strlen($itemNumber->confirm) ? $itemNumber->confirm : 'f';
                $this->busReqChanExeStsComposition->date = $this->getDate();

                if ($itemNumber->delete)
                {
                    $this->busReqChanExeStsComposition->deleteRequestChangeExemplaryStatusItemComposition($this->requestChangeExemplaryStatusId, $itemNumber->itemNumber);
                }
                elseif ($itemNumber->insert)
                {
                    $this->busReqChanExeStsComposition->insertRequestChangeExemplaryStatusComposition();
                }
                elseif ($itemNumber->update)
                {
                    $this->busReqChanExeStsComposition->updateRequestChangeExemplaryStatusComposition();
                }

                if ($itemNumber->confirm == 't' && !$itemNumber->delete)
                {
                    $confirmeExemplaries[] = $itemNumber->itemNumber;
                }
                elseif ($itemNumber->confirm == 'f' || $itemNumber->delete)
                {
                    $this->disapproveCompositionForRequest($this->requestChangeExemplaryStatusId, $itemNumber->itemNumber);
                }
            }
        }

        //se o estado é o mesmo não precisa fazer chamada de alteração/ação de estado
        if ($this->checkEqualStatus())
        {
            return true;
        }

        // ATUALIZA STATUS DA REQUISIÇÂO
        switch ($this->getStatus())
        {
            case REQUEST_CHANGE_EXEMPLARY_STATUS_APROVED :
                return $this->aproveRequest($this->requestChangeExemplaryStatusId);

            case REQUEST_CHANGE_EXEMPLARY_STATUS_REPROVED :
                return $this->reproveRequest($this->requestChangeExemplaryStatusId, false);

            case REQUEST_CHANGE_EXEMPLARY_STATUS_CONCLUDE :
                return $this->concludeRequest($this->requestChangeExemplaryStatusId, false);

            case REQUEST_CHANGE_EXEMPLARY_STATUS_CANCEL :
                return $this->cancelRequest(false);
        }


        return true;
    }

    /**
     * Compara se o estado do banco é o mesmo que esta sendo testando troca, por exemplo, se esta cancelado e quer cancelar.
     *
     * @return boolean
     */
    public function checkEqualStatus($estadoAtual = null, $estadoFuturo = null, $requestId = null)
    {
        //se for passaso por parametro usa ele, se não pega da classe
        $requestId = $requestId ? $requestId : $this->requestChangeExemplaryStatusId;

        //se não tiver estado atual, pega da classe
        if (!$estadoAtual)
        {
            $request = $this->getRequest($requestId, true);
            $estadoAtual = $request->requestChangeExemplaryStatusStatusId;
        }

        // se não tiver estado futuro pega da classe
        if (!$estadoFuturo)
        {
            $estadoFuturo = $this->getStatus();
        }

        return $estadoAtual == $estadoFuturo;
    }

    /**
     * aprova uma determinada requisição
     *
     * @param int $requestId
     */
    public function aproveRequest($requestId = null, $confirmItemNumber = null, $futureStatus = null, $checkUpdateStatus = true)
    {
        $requestId = is_null($requestId) ? $this->requestChangeExemplaryStatusId : $requestId;
        $futureStatus = is_null($futureStatus) ? $this->getFutureStatusId() : $futureStatus;

        if (!strlen($requestId))
        {
            $this->addError(108); //não existe a requisição
            return false;
        }

        if ($this->checkEqualStatus())
        {
            $this->addInformation(_M('A requisição já esta aprovada.', $this->module));
            return false;
        }

        $this->setRequestChangeExemplaryStatusStatusId(REQUEST_CHANGE_EXEMPLARY_STATUS_APROVED);
        $this->busReqChanExeStatus->requestChangeExemplaryStatusId = $requestId;

        if ($checkUpdateStatus) //permissão de estado
        {
             if(!$this->checkUpdateStatus(REQUEST_CHANGE_EXEMPLARY_STATUS_APROVED, $this->busReqChanExeStatus->getCurrentStatus($requestId)))
            {
                $this->addError(114);
                return false;
            }
        }

        //pega as composições confirmadas
        $composition = $this->busReqChanExeStsComposition->getRequestChangeExemplaryStatusComposition($requestId, DB_TRUE);

        if (!$composition)  //nenhum item confirmado
        {
            $this->addError(116);
            return false;
        }

        foreach ($composition as $itemNumber)
        {
            $confirmItemNumber[] = $itemNumber->itemNumber;
        }

        $ok = array();
        $aproved = 0;
        $apenasUm = $this->busReqChanExeStatus->checkAproveJustOne($requestId); //vai retornar true ou false se é apenas um ou não

        $this->appliedChangeStatusForExemplaries($confirmItemNumber, $requestId, $futureStatus, $apenasUm);

        // tenta trocar o estado da requisão para aprovado
        if (!$this->busReqChanExeStatus->changeStatusRequestChangeExemplaryStatus($this->getOperatorId()))
        {
            $this->addError(109);
            return false;
        }

        $this->addInformation('Requisição aprovada com sucesso.');

        return true;
    }

    /**
     * Aplica as alterações para os itens numbers que foram confirmados.
     *
     * @param array $exemplaries
     * @param int $requestId
     * @param int $futureStatus
     */
    private function appliedChangeStatusForExemplaries($exemplaries, $requestId, $futureStatus, $aproveJustOne = false)
    {
        //aprovar somente 1
        if ($aproveJustOne)
        {
            foreach ($exemplaries as $itemNumber)
            {
                //pega o código do estado do itemNumber
                $exemplaryStatus = $this->busExemplaryControl->getExemplaryStatus($itemNumber);

                // se esta disponivel
                if ($this->busExemplaryStatus->getStatusLevel($exemplaryStatus) == ID_EXEMPLARYSTATUS_INITIAL)
                {
                    $tmpExemplaries[0] = $itemNumber;
                }
            }
        }

        //se achou o disponivel, define a lista todo como o disponivel
        if ($tmpExemplaries)
        {
            $exemplaries = $tmpExemplaries;
        }

        foreach ($exemplaries as $itemNumber)
        {
            $this->busReqChanExeStsComposition->aproveItemNumberForRequest($requestId, $itemNumber);
            $exemplaryStatus = $this->busExemplaryControl->getExemplaryStatus($itemNumber);

            // VERIFICA SE O EXEPLAR JA ENCONTRA-SE NO ESTADO DESEJADO.
            if ($futureStatus == $exemplaryStatus)
            {
                // CASO ESTEJA, APLICA O MATERIAL E APOS CONFIRMA A REQUISIÇÃO
                if ($this->busReqChanExeStsComposition->appliedItemNumberForRequest($requestId, $itemNumber))
                {
                    $this->setRequestChangeExemplaryStatusStatusId(REQUEST_CHANGE_EXEMPLARY_STATUS_CONFIRMED);
                    $this->busReqChanExeStatus->requestChangeExemplaryStatusId = $requestId;
                    $this->busReqChanExeStatus->changeStatusRequestChangeExemplaryStatus($this->getOperatorId());

                    $extraData = new StdClass();
                    $extraData->itemNumber = $itemNumber;
                    $this->addInformation("O Exemplar <b>$itemNumber</b> já encontrava-se no estado desejado.", $extraData);
                    continue;
                }
            }

            $exemplaryStatusLevel = $this->busExemplaryStatus->getStatusLevel($exemplaryStatus);
            $agendar = false;

            // VERIFICA SE DEVE AGENDAR UMA TROCA DE ESTADO
            if ($exemplaryStatusLevel != ID_EXEMPLARYSTATUS_INITIAL)
            {
                $agendar = 2;
            }
            // VERIFICA SE PODE ALTERAR O ESTADO NA HORA
            elseif ($exemplaryStatusLevel == ID_EXEMPLARYSTATUS_INITIAL) //se for estado inicial
            {
                $agendar = 1;
            }

            if (!$agendar)
            {
                //não foi possível encontrar o método
                $this->addError(117);
                continue;
            }

            $this->busOperationChangeStatus->clearItems();
            $this->busOperationChangeStatus->setChangeType($agendar); // 2 = agendar; 1 = alterar na hora

            $this->busOperationChangeStatus->setExemplaryFutureStatus($futureStatus);
            $this->busOperationChangeStatus->setLibraryUnit($this->getLibraryUnit());

            // NA HORA
            if ($agendar == 1)
            {
                //troca o estado do exemplar na hora
                $this->changeNow($requestId, $itemNumber);
            }
            else
            if ($agendar == 2)
            {
                $this->scheduleChange($requestId, $itemNumber);
            }
        }
    }

    /**
     * Este metodo insere o agendamento.
     * Tambem é verificado se ja nao existe um agendamento para o itemNumber que esta pendente.
     *
     * @param int $requestId
     * @param int $itemNumber
     * @return boolean
     */
    private function scheduleChange($requestId, $itemNumber)
    {
        if ($this->busReqChanExeStsComposition->getFutureStatusForItemNumber($requestId, $itemNumber))
        {
            return true;
        }

        //BUSCA UMA REQUISIÇÃO SEMELHANTE
        $objSimilarTest = new StdClass();
        $objSimilarTest->requestChangeExemplaryStatusId = $requestId;
        $objSimilarTest->libraryUnitId = $this->getLibraryUnit();
        $objSimilarTest->date = $this->getDate();
        $objSimilarTest->finalDate = $this->getFinalDate();
        $objSimilarTest->itemNumber = array($itemNumber);

        // BUSCA UM AGENDAMENTO SEMELHANTE
        $similar = $this->busReqChanExeStatus->getSimilarRequest($objSimilarTest);
        $idAgendamento = $this->busReqChanExeStsComposition->getFutureStatusForItemNumber($similar[0], $itemNumber);
        $ok = false;

        if (!$idAgendamento)
        {
            if ($this->busOperationChangeStatus->addItemNumber($itemNumber))
            {
                $ok = $this->busOperationChangeStatus->finalize();
            }
            elseif ($this->busOperationChangeStatus->getErrorCode() == 25)
            {
                $ok = true;
            }
        }
        else
        {
            $ok = true;
        }

        if ($ok)
        {
            $idAgendamento = $idAgendamento ? $idAgendamento : $this->busOperationChangeStatus->getExemaplryFutureStatusDefined();
            $this->busReqChanExeStsComposition->setFutureStatusForItemNumber($requestId, $itemNumber, $idAgendamento);
            $extraData = new StdClass();
            $extraData->itemNumber = $itemNumber;
            $this->addInformation("Agendado troca de estado para o exemplar <b>$itemNumber</b>.", $extraData);
        }
        else
        {
            $this->addInformation("Não foi possível agendar a troca de estado para o exemplar <b>$itemNumber</b>.", $extraData);
        }

        return $ok;
    }

    /**
     * Altera o estado no momento
     *
     * @param int $requestId
     * @param int $itemNumber
     * @return boolean
     */
    private function changeNow($requestId, $itemNumber)
    {
        $ok = false;

        if ($this->busOperationChangeStatus->addItemNumber($itemNumber))
        {
            $ok = $this->busOperationChangeStatus->finalize();
        }
        elseif ($this->busOperationChangeStatus->getErrorCode() == 25)
        {
            $ok = true;
        }

        if ($ok)
        {
            // ALTERA ESTADO PARA CONFIRMADA
            $this->setRequestChangeExemplaryStatusStatusId(REQUEST_CHANGE_EXEMPLARY_STATUS_CONFIRMED);
            $this->busReqChanExeStatus->requestChangeExemplaryStatusId = $requestId;
            $this->busReqChanExeStatus->changeStatusRequestChangeExemplaryStatus($this->getOperatorId());

            $this->busReqChanExeStsComposition->appliedItemNumberForRequest($requestId, $itemNumber);

            $extraData = new StdClass();
            $extraData->itemNumber = $itemNumber;
            $this->addInformation("Trocado estado do exemplar <b>$itemNumber</b>", $extraData);
        }
        else
        {
            $this->addInformation("Não foi possível trocar o estado do exemplar <b>$itemNumber</b>", $extraData);
        }

        return $ok;
    }

    /**
     * Reprova uma determinada requisição
     *
     * @param int $requestId
     */
    public function reproveRequest($requestId = null, $checkUpdateStatus = true)
    {
        $requestId = is_null($requestId) ? $this->requestChangeExemplaryStatusId : $requestId;

        if (!strlen($requestId))
        {
            $this->addError(108);
            return false;
        }

        if ($this->checkEqualStatus(null, REQUEST_CHANGE_EXEMPLARY_STATUS_REPROVED))
        {
            $this->addInformation(_M('A requisição já esta reprovada.', $this->module));
            return false;
        }

        $this->setRequestChangeExemplaryStatusStatusId(REQUEST_CHANGE_EXEMPLARY_STATUS_REPROVED);
        $this->busReqChanExeStatus->requestChangeExemplaryStatusId = $requestId;

        if ($checkUpdateStatus)
        {
            if(!$this->checkUpdateStatus(REQUEST_CHANGE_EXEMPLARY_STATUS_REPROVED, $this->busReqChanExeStatus->getCurrentStatus($requestId)))
            {
                $this->addError(114);
                return false;
            }
        }

        if (!$this->busReqChanExeStatus->changeStatusRequestChangeExemplaryStatus($this->getOperatorId()))
        {
            $this->addError(110);
            return false;
        }

        $this->disapproveCompositionForRequest($requestId);

        $this->addInformation('Requisição reprovada com sucesso.');

        return true;
    }

    /**
     * Conclui uma determinada requisição
     *
     * @param int $requestId
     */
    public function concludeRequest($requestId = null, $checkUpdateStatus = true)
    {
        $requestId = is_null($requestId) ? $this->requestChangeExemplaryStatusId : $requestId;

        if (!strlen($requestId))
        {
            $this->addError(108);
            return false;
        }

        if ($this->checkEqualStatus(null, REQUEST_CHANGE_EXEMPLARY_STATUS_CONCLUDE))
        {
            parent::addError(_M('A requisição já esta concluída.', $this->module));
            return false;
        }

        $this->setRequestChangeExemplaryStatusStatusId(REQUEST_CHANGE_EXEMPLARY_STATUS_CONCLUDE);
        $this->busReqChanExeStatus->requestChangeExemplaryStatusId = $requestId;

        if ($checkUpdateStatus)
        {
            if(!$this->checkUpdateStatus(REQUEST_CHANGE_EXEMPLARY_STATUS_CONCLUDE, $this->busReqChanExeStatus->getCurrentStatus($requestId)))
            {
                $this->addError(114);
                return false;
            }
        }

        if(!$this->busReqChanExeStatus->changeStatusRequestChangeExemplaryStatus($this->getOperatorId()))
        {
            $this->addError(111);
            return false;
        }

        $this->returnExemplaryStatusToLastStatus($requestId);

        $this->addInformation('Requisição concluída com sucesso.');

        return true;
    }

    /**
     * Cacela uma determinada requisição
     *
     * @param int $requestId
     */
    public function cancelRequest($requestId = null, $checkUpdateStatus = true)
    {
        $requestId = is_null($requestId) ? $this->requestChangeExemplaryStatusId : $requestId;

        if ($this->checkEqualStatus(null, REQUEST_CHANGE_EXEMPLARY_STATUS_CANCEL) == true)
        {
            $this->addInformation(_M('A requisição já esta cancelada.', $this->module));
            return false;
        }

        //inválido
        if (!strlen($requestId))
        {
            $this->addError(108);
            return false;
        }

        $this->setRequestChangeExemplaryStatusStatusId(REQUEST_CHANGE_EXEMPLARY_STATUS_CANCEL); //seta o estado como cancelamento
        $this->busReqChanExeStatus->requestChangeExemplaryStatusId = $requestId; //seta id da requisição
        //Checha permissão de estados
        if ($checkUpdateStatus)
        {
            if(!$this->checkUpdateStatus(REQUEST_CHANGE_EXEMPLARY_STATUS_CANCEL, $this->busReqChanExeStatus->getCurrentStatus($requestId)))
            {
                $this->addError(114);
                return false;
            }
        }

        //tenta trocar o estado dos exemplares
        if (!$this->busReqChanExeStatus->changeStatusRequestChangeExemplaryStatus($this->getOperatorId()))
        {
        	//impossível cancelar
            $this->addError(112);
            return false;
        }

        $this->returnExemplaryStatusToLastStatus($requestId);

        $this->disapproveCompositionForRequest($requestId);

        $this->addInformation('Operação cancelada com sucessso.');

        return true;
    }

    /**
     * Renova a requisição para o semestre
     */
    public function renewRequest($requestId)
    {
        //verifica se o estado é confirmado
        if (!$this->checkEqualStatus(null, REQUEST_CHANGE_EXEMPLARY_STATUS_CONFIRMED, $requestId))
        {
            parent::addError(_M('Somente requisições <b>confirmadas</b> podem ser <b>renovadas</b>.', $this->module));
            return false;
        }

        //renova pela classe
        $ok = $this->busReqChanExeStatus->renewRequest($requestId);

        if ($ok)
        {
            $this->addInformation(_M('Requisição renovada com sucesso.', $this->module));
        }
        else
        {
            parent::addError(_M('Erro ao renovar requisição.', $this->module));
        }
    }

    /**
     * Disaprova uma composição ou um item da composição
     *
     * @param int $requestId
     * @param  int | null $itemNumber
     * @return  boolean
     */
    public function disapproveCompositionForRequest($requestId, $itemNumber = null)
    {
        if (is_null($itemNumber))
        {
            $composition = $this->busReqChanExeStsComposition->getRequestChangeExemplaryStatusComposition($requestId);
            if (!$composition)
            {
                return;
            }
            $ok = array();
            foreach ($composition as $value)
            {
                $ok[] = $this->disapproveCompositionForRequest($requestId, $value->itemNumber);
            }
            return (array_search(false, $ok) === false);
        }

        //BUSCA UMA REQUISIÇÃO SEMELHANTE
        $objSimilarTest = new StdClass();
        $objSimilarTest->requestChangeExemplaryStatusId = $requestId;
        $objSimilarTest->libraryUnitId = $this->getLibraryUnit();
        $objSimilarTest->date = $this->getDate();
        $objSimilarTest->finalDate = $this->getFinalDate();
        $objSimilarTest->itemNumber = array($itemNumber);

        // BUSCA UM AGENDAMENTO SEMELHANTE
        $similar = $this->busReqChanExeStatus->getSimilarRequest($objSimilarTest);

        if (!$similar)
        {
            $fId = $this->busReqChanExeStsComposition->getFutureStatusForItemNumber($requestId, $itemNumber);
            $this->busExemplaryFutureStatusDefined->setApplied($itemNumber, $fId);
        }

        return $this->busReqChanExeStsComposition->disapproveCompositionForItemNumber($requestId, $itemNumber);
    }

    /**
     * Retorna um determinado exemplar para o seu estado inicial anterior
     *
     * @param int $requestId
     * @param int $itemNumber
     * @return boolean
     */
    public function returnExemplaryStatusToLastStatus($requestId, $itemNumber = null, $applied = null, $exemplaryFutureStatusDefinedId = null)
    {
        //caso não tenha itemNumber // faz uma recursão para cada item number
        if (is_null($itemNumber))
        {
            $composition = $this->busReqChanExeStsComposition->getRequestChangeExemplaryStatusComposition($requestId);
            if (!$composition)
            {
                return; //não tinha que da um erro aqui???
            }

            $ok = array();

            $this->busOperationChangeStatus->clean();
            //chama recursivamente para cada exemplar a troca de estado
            foreach ($composition as $value)
            {
                $ok[] = $this->returnExemplaryStatusToLastStatus($requestId, $value->itemNumber, $value->applied, $value->exemplaryFutureStatusDefinedId);
            }

            return ( array_search(false, $ok) === false );
        }


        $applied = !is_null($applied) ? $applied : $this->busReqChanExeStsComposition->checkApplied($requestId, $itemNumber);
        $exemplaryFutureStatusDefinedId = !is_null($exemplaryFutureStatusDefinedId) ? $exemplaryFutureStatusDefinedId : $this->busReqChanExeStsComposition->getFutureStatusForItemNumber($requestId, $itemNumber);
        $similar = $this->getSimilarRequest($requestId, $itemNumber);

        if (!$applied && !$similar && $exemplaryFutureStatusDefinedId)
        {
            $this->busExemplaryFutureStatusDefined->setApplied($itemNumber, $exemplaryFutureStatusDefinedId);
            return; //se não estiver aplicaco não faz nada
        }

        if (!$applied && !$exemplaryFutureStatusDefinedId)
        {
            return;
        }

        if ($similar)
        {
            $extraData = new StdClass();
            $extraData->itemNumber = $itemNumber;
            $this->addInformation('Existe requisições semelhantes a esta : ' . implode(',', $similar) . ". Sendo assim, o exemplare vai permanecer com o mesmo estado.", $extraData);
            return true;
        }

        // CHANGE TO LAST INITIAL STATUS
        $this->busOperationChangeStatus->clean(); //limpa operação
        $this->busOperationChangeStatus->setLibraryUnit($this->getLibraryUnit());
        $this->busOperationChangeStatus->setLevel(ID_EXEMPLARYSTATUS_INITIAL);
        $this->busOperationChangeStatus->setChangeType(ID_EXEMPLARYSTATUS_INITIAL);

        $lastStatus = $this->busExemplaryStatusHistory->getLastStatus($itemNumber, ID_EXEMPLARYSTATUS_INITIAL, $this->getLibraryUnit());

        if (!$lastStatus)
        {
            $extraData = new StdClass();
            $extraData->itemNumber = $itemNumber;
            $this->addInformation('Not found last status initial to item number.', $extraData);
            return false;
        }

        $exemplaryCurrentStatus = $this->busExemplaryControl->getExemplaryStatus($itemNumber);
        $requestStatus = $this->busReqChanExeStatus->getFutureStatus($requestId);
        if ($requestStatus != $exemplaryCurrentStatus)
        {
            $this->busOperationChangeStatus->setChangeType(ID_EXEMPLARYSTATUS_PREVIOUS);

            $extraData = new StdClass();
            $extraData->itemNumber = $itemNumber;
            $this->addInformation('O exemplar (' . $itemNumber . ') não encontra-se mais no estado da requisição. Será criado um agendamento para o estado: ' . $this->busExemplaryStatus->getDescription($lastStatus), $extraData);
        }


        $this->busOperationChangeStatus->addItemNumber($itemNumber);
        $this->busOperationChangeStatus->setExemplaryFutureStatus($lastStatus);
        $ok = $this->busOperationChangeStatus->finalize();

        $messages = $this->busOperationChangeStatus->getMessages(); //mensagens da operação

        foreach ($messages as $line => $msg)
        {
            $this->addMessage(null, $msg);
        }

        return $ok;
    }

    public function getSimilarRequest($requestId, $itemNumber)
    {
        $this->getRequest($requestId);
        $finalDate = GDate::now();

        //BUSCA UMA REQUISIÇÃO SEMELHANTE
        $objSimilarTest = new StdClass();
        $objSimilarTest->requestChangeExemplaryStatusId = $requestId;
        $objSimilarTest->libraryUnitId = $this->getLibraryUnit();
        $objSimilarTest->date = $this->getDate();
        $objSimilarTest->finalDate = $finalDate->getDate(GDate::MASK_DATE_DB);
        $objSimilarTest->itemNumber = array($itemNumber);

        // BUSCA UM AGENDAMENTO SEMELHANTE
        return $this->busReqChanExeStatus->getSimilarRequest($objSimilarTest, false, REQUEST_CHANGE_EXEMPLARY_STATUS_CONFIRMED);
    }

    /**
     * Este methodo busca na base uma requisição e seta os attributos com os valores retornados
     *
     * @param int $requestId
     * @return boolean
     */
    public function getRequest($requestId = null, $return = false)
    {
        $requestId = is_null($requestId) ? $this->requestChangeExemplaryStatusId : $requestId;
        $request = $this->busReqChanExeStatus->getRequestChangeExemplaryStatus($requestId);

        if (!$request)
        {
            return false;
        }

        if ($return)
        {
            return $request;
        }

        $this->setRequestChangeExemplaryStatusStatusId($request->requestChangeExemplaryStatusStatusId);
        $this->setFutureStatusId($request->futureStatusId);
        $this->setLibraryUnit($request->libraryUnitId);
        $this->setPersonId($request->personId);
        $this->setDate($request->date);
        $this->setFinalDate($request->finalDate);
        $this->setRequestComposition($request->composition);
        $this->setAproveJustOne($request->aproveJustOne);
        $this->setDiscipline($request->discipline);
        $this->requestChangeExemplaryStatusId = $requestId;

        return true;
    }

    /**
     * Verifica se os attributos foram todos setados, seta o codigo de erro caso seja necessario.
     *
     * @return boolean
     */
    private function checkAttributes()
    {
        $this->msgCode = null;

        // VERIFICA COMPOSIÇÃO
        $this->checkComposition();

        if (!$this->getLibraryUnit())
        {
            $this->addError(104);
        }
        elseif (!$this->getPersonId())
        {
            $this->addError(102);
        }
        elseif (!$this->getPersonLink())
        {
            $this->addError(103);
        }
        elseif (!$this->getFutureStatusId())
        {
            $this->addError(101);
        }
        elseif (!$this->getDate())
        {
            $this->addError(105);
        }
        elseif (!$this->getFinalDate())
        {
            $this->addError(106);
        }
        elseif (!$this->getRequestComposition())
        {
            $this->addError(100);
        }
        elseif (!$this->getDiscipline())
        {
            $this->addError(113);
        }
        if (!is_null($this->msgCode))
        {
            return false;
        }

        return true;
    }

    /**
     * Este metodo checa se é possível alterar para o estado desejado.
     * Mantem a concistencia do fluxo de estados
     *
     * @param int $newStatus
     * @param int $currentStatus
     * @return boolean
     */
    public function checkUpdateStatus($newStatus, $currentStatus)
    {
        if ($newStatus == $currentStatus)
        {
            return true;
        }

        switch ($currentStatus)
        {
            // REQUISITADO = poder aprovar, reprovar e cancelar
            case 1 :
                if (array_search($newStatus, array(2, 3, 5)) !== false)
                {
                    return true;
                }
                return false;

            // APROVADO = pode cancelar e confirmar
            case 2:
                if (array_search($newStatus, array(5, 6)) !== false)
                {
                    return true;
                }
                return false;

            // REPROVADO = não pode mais trocar de estado
            case 3:
            // CONCLUIDO = não pode mais trocar de estado
            case 4:
            // CANCELADO = não pode mais trocar de estado
            case 5:
                return false;

            // CONFIRMADO = Pode concluir ou cancelar
            case 6:
                if (array_search($newStatus, array(4, 5)) !== false)
                {
                    return true;
                }
                return false;
        }
    }

    /**
     * limpa os attributos
     */
    public function clean()
    {
        $this->busReqChanExeStatus->clean();
        $this->busReqChanExeStsAccess->clean();
        $this->busReqChanExeStsComposition->clean();
        $this->busReqChanExeStsHistory->clean();
        $this->busReqChanExeStsStatus->clean();
    }

    public function addError($errorCode)
    {
        $this->msgCode = $errorCode;
        parent::addError($this->getMsg(), null, $this->getMsgCode());
    }

    /**
     * Retorna o resultado da operação, pode ser um erro ou uma mensagem de sucesso.
     *
     * @return string
     */
    public function getMsg()
    {
        switch ($this->getMsgCode())
        {
            case 100 : return _M("É necessário selecionar exemplares válidos para efetuar a requisição!", $this->module); break;
            case 101 : return _M("Este código do estado futuro do exemplar é inválido!", $this->module); break;
            case 102 : return _M("Este código de pessoa é inválido!", $this->module); break;
            case 103 : return _M("Este vínculo de pessoa é inválido!", $this->module); break;
            case 104 : return _M("Esta unidade de biblioteca é inválida!", $this->module); break;
            case 105 : return _M("A data é inválida!", $this->module); break;
            case 106 : return _M("Esta data final é inválida!", $this->module); break;
            case 107 : return _M("Erro na inserção do pedido", $this->module); break;
            case 108 : return _M("Esta requisição de alteração de estado é inválido!", $this->module); break;
            case 109 : return _M("Não é possível aprovar a requisição!", $this->module); break;
            case 110 : return _M("Não é possível reprovar a requisição!", $this->module); break;
            case 111 : return _M("Não é possível concluír a requisição!", $this->module); break;
            case 112 : return _M("Não é possível cancelar a requisição!", $this->module); break;
            case 113 : return _M("É necessário preencher a disciplina", $this->module); break;
            case 114 : return _M("Não é permitido mudar uma requisição com estado @1 para o estado @2", $this->module, $this->getLabelOfStatus($this->busReqChanExeStatus->getCurrentStatus($this->requestChangeExemplaryStatusId)), $this->getLabelOfStatus($this->getStatus()) . '.'); break;
            case 115 : return _M("Não é permitido agendar o atual estado do exemplar.", $this->module); break;
            case 116 : return _M("Nenhum item foi confirmado. A operação não pode ser aprovada.", $this->module); 
            case 117 : return _M("Não foi possível definir um método para alterar o estado. Verificar se o estado do material é um estado inicial ou se este permite agendamento de troca de estado.", $this->module); break;
            case 118 : return _M('Estado futuro do exemplar não permite solicitação!', $this->module); break;
            default: return $this->getMsgCode(); break;
        }
    }

    /**
     * Troca o código da do status da requisição para a descrição do mesmo
     *
     * @param $requestChangeexEmplaryStatusStatusId
     * @return string
     */
    public function getLabelOfStatus( $requestChangeexEmplaryStatusStatusId )
    {
    	$status = $this->busReqChanExeStsStatus->listRequestChangeExemplaryStatusStatus();

    	$description = $status[$requestChangeexEmplaryStatusStatusId];

    	if ( $description )
    	{
    		$requestChangeexEmplaryStatusStatusId =  $requestChangeexEmplaryStatusStatusId . ' - ' .$description; //concatena a Descrição encontrada
    	}

    	return '<b>'.$requestChangeexEmplaryStatusStatusId.'</b>';
    }

    /**
     * retorna o codigo da mensagem
     *
     * @return integer
     */
    public function getMsgCode()
    {
        return $this->msgCode;
    }

}

?>