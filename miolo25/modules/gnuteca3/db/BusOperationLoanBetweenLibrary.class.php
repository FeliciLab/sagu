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
 * LoanBetweenLibrary operation
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 16/12/2008
 *
 **/
class BusinessGnuteca3BusOperationLoanBetweenLibrary extends GOperation
{
	public $MIOLO;
	public $module;

	public $busExemplaryControl;
	public $busExemplaryStatusHistory;
	public $busLibraryUnit;
	public $busLibraryUnitConfig;
    public $busLoanBetweenLibrary;
    public $busLoanBetweenLibraryStatusHistory;
    public $busLoanBetweenLibraryComposition;
    public $busMaterial;
    public $busRulesForMaterialMovement;

    //Fields of gtcLoanBetweenLibrary
    public $loanBetweenLibraryId;
    public $loanDate;
    public $returnForecastDate;
    public $returnDate;
    public $limitDate;
    public $libraryUnitId;
    public $personId;
    public $loanBetweenLibraryStatusId;
    public $observation;
    public $libraryComposition;

    public $mail;

    public function __construct()
    {
        parent::__construct();
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();

        $this->MIOLO->getClass($this->module, 'GSendMail');
        $this->mail = new GSendMail();

        $this->busExemplaryControl                  = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busExemplaryStatusHistory            = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatusHistory');
        $this->busLibraryUnit                       = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busLibraryUnitConfig                 = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitConfig');
        $this->busLoanBetweenLibrary                = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibrary');
        $this->busLoanBetweenLibraryStatusHistory   = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibraryStatusHistory');
        $this->busLoanBetweenLibraryComposition     = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibraryComposition');
        $this->busMaterial                          = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->busRulesForMaterialMovement          = $this->MIOLO->getBusiness($this->module, 'BusRulesForMaterialMovement');

        $this->setLocation(1);
    }



    /**
     * Efetua uma requisi??o de emprestimos entre bibliotecas.
     *
     * @return boolean
     */
    public function insertRequest()
    {
    	//$data = $this->getData();
    	$this->busLoanBetweenLibrary->setData($this);
    	$loanBetweenLibraryId = $this->busLoanBetweenLibrary->insertLoanBetweenLibrary();

    	//Change statsu to REQUESTED
        $ok = $this->busLoanBetweenLibrary->changeStatus($loanBetweenLibraryId, ID_LOANBETWEENLIBRARYSTATUS_REQUESTED);

        // ENVIA EMAIL INFORMANDO NOVA REQUISI??O DE EMPRESTIMOS ENTRE BIBLIOTECAS
        $this->mail->sendMailLoanBetweenLibraryRequest($loanBetweenLibraryId);

        return $ok;
    }



    /**
     * Atualiza uma requisição;
     *
     * @return boolean
     */
    public function updateRequest()
    {
        $this->busLoanBetweenLibrary->setData($this);
        $ok = $this->busLoanBetweenLibrary->updateLoanBetweenLibrary();
        
        if(!$ok)
        {
            $this->addError(_M('Erro ao atualizar dados!', $this->module));
            return false;
        }

        foreach ($this->libraryComposition as $composition)
        {
            $this->busLoanBetweenLibraryComposition->setData($composition);
            $this->busLoanBetweenLibraryComposition->updateLoanBetweenLibraryComposition($composition);
        }

        // ATUALIZA STATUS DA REQUISIção
        switch ($this->getStatus())
        {
            case ID_LOANBETWEENLIBRARYSTATUS_APPROVED    :
                return $this->approveLoanBetweenLibrary ($composition->loanBetweenLibraryId, $composition->itemNumber, false);

            case ID_LOANBETWEENLIBRARYSTATUS_DISAPPROVED   :
                return $this->disapproveMaterial($composition->loanBetweenLibraryId, false);

            case ID_LOANBETWEENLIBRARYSTATUS_DEVOLUTION   :
                return $this->returnMaterial($composition->loanBetweenLibraryId, false);

            case ID_LOANBETWEENLIBRARYSTATUS_CONFIRMED   :
                return $this->confirmReceipt($composition->loanBetweenLibraryId, false);

            case ID_LOANBETWEENLIBRARYSTATUS_FINALIZED   :
                return $this->confirmReturn($composition->loanBetweenLibraryId, false);

            case ID_LOANBETWEENLIBRARYSTATUS_CANCELED     :
                return $this->cancelRequest($composition->loanBetweenLibraryId);
        }

        return true;
    }

    public function getLoanBetweenLibraryStatus()
    {
        return $this->busLoanBetweenLibrary->loanBetweenLibraryStatusId;
    }


    public function getStatus()
    {
        return $this->getLoanBetweenLibraryStatus();
    }


    /**
     * Retorna o resultado da opera??o, pode ser um erro ou uma mensagem de sucesso.
     *
     * @return string
     */
    public function getMsg()
    {
        switch ($this->getMsgCode())
        {
            // INSERT ERRORS
            case 107 : return _M("Erro na inserção do pedido",                       $this->module);

        }
    }


    public function getMsgCode()
    {
        return $this->msgCode;
    }


    /**
     * Cancela um determinado emprestimo entre bibliotecas
     *
     * @param integer $loanBetweenLibraryId
     * @return boolean
     */
    public function cancelRequest($loanBetweenLibraryId)
    {
        $get = $this->busLoanBetweenLibrary->getLoanBetweenLibrary($loanBetweenLibraryId);
        
        //caso já esteja cancelado, não precisa cancelar
        if ( $get->loanBetweenLibraryStatusId == ID_LOANBETWEENLIBRARYSTATUS_CANCELED )
        {
            return true;
        }
        
        if ($get->loanBetweenLibraryStatusId != ID_LOANBETWEENLIBRARYSTATUS_REQUESTED)
        {
            $this->addError(_M('Estado é diferente da requisição', $this->module));
            return false;
        }

        //Change status to CANCELED
        $ok = $this->busLoanBetweenLibrary->changeStatus($loanBetweenLibraryId, ID_LOANBETWEENLIBRARYSTATUS_CANCELED);

        // ENVIA EMAIL INFORMANDO NOVA REQUISI??O DE EMPRESTIMOS ENTRE BIBLIOTECAS
        $this->mail->sendMailLoanBetweenLibraryCancel($loanBetweenLibraryId);

        return $ok;
    }



    /**
     * Confirma uma determinada requisi??o;
     *
     * @param integer $loanBetweenLibraryId
     */
    public function confirmReceipt($loanBetweenLibraryId)
    {
        $data = $this->busLoanBetweenLibrary->getLoanBetweenLibrary($loanBetweenLibraryId);

        //Altera a unidade do material para a unidade solicitante
        $composition = $this->busLoanBetweenLibraryComposition->getComposition($loanBetweenLibraryId);
        $count = 0;
        foreach ($composition as $v)
        {
        	if ( !MUtil::getBooleanValue($v->isConfirmed) )
        	{
        		continue;
        	}

        	//Get exemplary
        	$ex = $this->busExemplaryControl->getExemplaryControl($v->itemNumber);

            //Altera a unidade do material para a unidade solicitante
            $this->busExemplaryControl->changeLibraryUnit($v->itemNumber, $data->libraryUnitId, true);

            //Checar as regras dos estados
            $futureStatus = $this->busRulesForMaterialMovement->getFutureStatus($v->itemNumber, $ex->exemplaryStatusId, ID_OPERATION_LOAN_BETWEEN_UNITS_CONFIRM_RECEIPT, $this->getLocation());
            //Nao existe regras
            if (!$futureStatus)
            {
                $this->addError(_M('Sem estado futuro definido para o exemplar @1', $this->module, $v->itemNumber));
            }
            else //Existe regras
            {
            	//Atribui o estado
                $this->busExemplaryControl->changeStatus($v->itemNumber, $futureStatus);

                $count ++;
            }
        }

        if ($count > 0)
        {
	        //Altera o estado para CONFIRMADO
	        $this->busLoanBetweenLibrary->changeStatus($loanBetweenLibraryId, ID_LOANBETWEENLIBRARYSTATUS_CONFIRMED);
        }
        else
        {
        	$this->addError(_M('Não há itens para confirmar o recebimento', $this->module));
        }
        return true;
    }




    /**
     * Enter description here...
     *
     * @param unknown_type $loanBetweenLibraryId
     */
    public function returnMaterial($loanBetweenLibraryId)
    {
    	$data = $this->busLoanBetweenLibrary->getLoanBetweenLibrary($loanBetweenLibraryId);

    	//Altera o estado para DEVOLUCAO
    	$this->busLoanBetweenLibrary->changeStatus($loanBetweenLibraryId, ID_LOANBETWEENLIBRARYSTATUS_DEVOLUTION);

        $composition = $this->busLoanBetweenLibraryComposition->getComposition($loanBetweenLibraryId);
        $exemplaries = false;

        foreach ($composition as $v)
        {
            if ( !MUtil::getBooleanValue($v->isConfirmed) )
            {
                continue;
            }

            //Get exemplary
            $ex = $this->busExemplaryControl->getExemplaryControl($v->itemNumber, TRUE);

            //Checar as regras dos estados
            $futureStatus = $this->busRulesForMaterialMovement->getFutureStatus($v->itemNumber, $ex->exemplaryStatusId, ID_OPERATION_LOAN_BETWEEN_UNITS, $this->getLocation());

            //Nao existe regras
            if (!$futureStatus)
            {
                $this->addError(_M('Sem estado futuro definido para o exemplar @1', $this->module, $v->itemNumber));
                continue;
            }

            //Atribui o estado
            if($this->busExemplaryControl->changeStatus($v->itemNumber, $futureStatus))
            {
                $exemplaries[$v->itemNumber] = $v->itemNumber;
            }
        }


        if (!$exemplaries)
        {
        	$this->addError(_M('Não há itens válidos para devolver', $this->module));
        	return false;
        }

        $this->mail->sendMailLoanBetweenLibraryReturnMaterial($loanBetweenLibraryId, $exemplaries);

        return true;
    }




    /**
     * Aprova um emprestimo entre bibliotecas
     *
     * @param integer $loanBetweenLibraryId
     * @param array $itemNumbers
     * @return boolean
     */
    public function approveLoanBetweenLibrary($loanBetweenLibraryId, $itemNumber)
    {
        //if(!is_array($itemNumber) || !count($itemNumber))
        if (!$itemNumber)
        {
            return false;
        }

        $approvedItens = array();
        $ex = $this->busExemplaryControl->getExemplaryControl($itemNumber, TRUE);

        //Checar regras dos estados
        $futureStatus = $this->busRulesForMaterialMovement->getFutureStatus($itemNumber, $ex->exemplaryStatusId, ID_OPERATION_LOAN_BETWEEN_UNITS, $this->getLocation());

        //Nao existe regras
        if (!$futureStatus)
        {
            $this->addError(_M('Sem estado futuro definido para o exemplar @1', $this->module, $itemNumber));
            return false;
        }

        if($this->busExemplaryControl->changeStatus($itemNumber, $futureStatus))
        {
            $this->busLoanBetweenLibraryComposition->loanBetweenLibraryId  = $loanBetweenLibraryId;
            $this->busLoanBetweenLibraryComposition->itemNumber            = $itemNumber;
            $this->busLoanBetweenLibraryComposition->isConfirmed           = DB_TRUE;
            $this->busLoanBetweenLibraryComposition->updateLoanBetweenLibraryComposition();

            $approvedItens[$itemNumber] = $itemNumber;
        }

        if (!count($approvedItens))
        {
            $this->addError(_M('Não há itens válidos para aprovar', $this->module));
            return false;
        }

        if($this->busLoanBetweenLibrary->changeStatus($loanBetweenLibraryId, ID_LOANBETWEENLIBRARYSTATUS_APPROVED))
        {
            $this->mail->sendMailLoanBetweenLibraryApproveMaterial($loanBetweenLibraryId, $approvedItens);
            return true;
        }

        return false;
    }



    /**
     * Desaprova um determinado emprestimo entre bibliotecas.
     *
     * @param integer $loanBetweenLibraryId
     * @param string $observation
     * @return boolean
     */
    public function disapproveMaterial($loanBetweenLibraryId, $observation = NULL)
    {
        $data = $this->busLoanBetweenLibrary->getLoanBetweenLibrary($loanBetweenLibraryId);

		//Change status to DISAPPROVED
        if(!$this->busLoanBetweenLibrary->changeStatus($loanBetweenLibraryId, ID_LOANBETWEENLIBRARYSTATUS_DISAPPROVED))
        {
            $this->addError(_M('Não é possível alterar o estado do exemplar.', $this->module));
            return false;
        }

        //Inclui observacao
        $this->busLoanBetweenLibrary->getLoanBetweenLibrary($loanBetweenLibraryId);
        $this->busLoanBetweenLibrary->observation = $observation;
        $this->busLoanBetweenLibrary->updateLoanBetweenLibrary();

        $composition = $this->busLoanBetweenLibraryComposition->getComposition($loanBetweenLibraryId);

        //Se estivesse estado como APROVADO anteriormente
        if ($data->loanBetweenLibraryStatusId == ID_LOANBETWEENLIBRARYSTATUS_APPROVED)
        {
            $operator = GOperator::getOperatorId();

        	//Altera o estado do material para o estado anterior
	       	foreach ($composition as $v)
	       	{
	       		$lastStatus = $this->busExemplaryStatusHistory->getLastStatus($v->itemNumber);
	       		if ($lastStatus)
	       		{
	       		    $this->busExemplaryControl->changeStatus($v->itemNumber, $lastStatus, $operator);
	       		}
	       	}
        }

        $this->mail->sendMailLoanBetweenLibraryDisapproveMaterial($loanBetweenLibraryId);
    }



    /**
     * Confirma devolu??o de um determinado material
     *
     * @param integer $loanBetweenLibraryId
     * @return boolean
     */
    public function confirmReturn($loanBetweenLibraryId)
    {
        //Altera o estado para FINALIZADO
        $this->busLoanBetweenLibrary->changeStatus($loanBetweenLibraryId, ID_LOANBETWEENLIBRARYSTATUS_FINALIZED);

        $operator    = GOperator::getOperatorId();
        $composition = $this->busLoanBetweenLibraryComposition->getComposition($loanBetweenLibraryId);
        foreach ($composition as $v)
        {
            if ( !MUtil::getBooleanValue($v->isConfirmed) )
            {
                continue;
            }
            $ex = $this->busExemplaryControl->getExemplaryControl($v->itemNumber, TRUE);
            //Checar regras dos estados
            $futureStatus = $this->busRulesForMaterialMovement->getFutureStatus($v->itemNumber, $ex->exemplaryStatusId, ID_OPERATION_RETURN_BETWEEN_UNITS, $this->getLocation());

            //Nao existe regras
            if (!$futureStatus)
            {
                $this->addError(_M('Sem estado futuro definido para o exemplar @1', $this->module, $itemNumber));
                continue;
            }

            $ex = $this->busExemplaryControl->getExemplaryControl($v->itemNumber);
            $this->busExemplaryControl->changeLibraryUnit($v->itemNumber, $ex->originalLibraryUnitId, true);
            $this->busExemplaryControl->changeStatus($v->itemNumber, DEFAULT_EXEMPLARY_STATUS_DISPONIVEL, $operator);
/*            $lastStatus = $this->busExemplaryStatusHistory->getLastStatus($v->itemNumber, NULL, $ex->originalLibraryUnitId);
            if ($lastStatus)
            {
                $this->busExemplaryControl->changeStatus($v->itemNumber, DEFAULT_EXEMPLARY_STATUS_DISPONIVEL, $operator);
            }*/
        }
        return TRUE;
    }
}
?>
