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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 22/10/2008
 *
 **/
class BusinessGnuteca3BusOperationChangeStatus extends GOperation
{
    public $MIOLO;
    public $module;

    public $busExemplaryControl;
    public $busExemplaryFutureStatusDefined;
    public $busExemplaryStatus;
    public $busExemplaryStatusHistory;
    public $busLibraryUnit;
    public $busLoan;
    public $busReserve;
    public $busOperationReserve;

    public $libraryUnitId;
    public $level;
    public $exemplaryFutureStatus;
    public $exemplaryStatusId;
    public $exemplary;
    public $operator;

    private $errorCode;

    public function __construct()
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->exemplary = array();

        $this->busExemplaryControl              = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busExemplaryStatus               = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        $this->busExemplaryStatusHistory        = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatusHistory');
        $this->busLibraryUnit                   = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busLoan                          = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $this->busReserve                       = $this->MIOLO->getBusiness($this->module, 'BusReserve');
        $this->busOperationReserve              = $this->MIOLO->getBusiness($this->module, 'BusOperationReserve');
        $this->busExemplaryFutureStatusDefined  = $this->MIOLO->getBusiness($this->module, 'BusExemplaryFutureStatusDefined');
    }


    /**
     * Define o changeType
     * 1 para alteração de estado - change
     * 2 para agendamento de alteração - schedule
     *
     * @param integer $type 1 para alteração de estado, 2 para agendamento de alteração
     * @return true
     */
    public function setChangeType( $type )
    {
    	$_SESSION['changeType'] = $type;

        return true;
    }


    /**
     * Return the type of change
     *
     * @return 1 for alteration now , 2 for future alteration
     */
    public function getChangeType()
    {
    	return $_SESSION['changeType'];
    }


    /**
     * Define the future status of all exemplarys
     *
     * @param integer $exemplaryStatusId
     *
     * @return object exemplaryFutureStatus object
     */
    public function setExemplaryFutureStatus( $exemplaryStatusId )
    {
    	if ( $exemplaryStatusId == NULL)
    	{
    		unset( $_SESSION['exemplaryFutureStatus'] );
            return false;
    	}

    	$exemplaryStatus = $this->busExemplaryStatus->getExemplaryStatus( $exemplaryStatusId , true);

    	if ( $exemplaryStatus )
    	{
           $this->setLevel( $exemplaryStatus->level );
           $_SESSION['exemplaryFutureStatus'] = $exemplaryStatusId;
           return $exemplaryStatus;
    	}
    }


    public function getExemplaryFutureStatus()
    {
        return $_SESSION['exemplaryFutureStatus'];
    }


    /**
     * Define the level
     * 0 para estado anterior
     * 1 para estado inicial
     *
     * @param integer $level
     */
    public function setLevel( $level )
    {
    	if ($level == NULL)
    	{
    		unset( $_SESSION['changeLevel']);
    		return false;
    	}
    	else
    	{
            $_SESSION['changeLevel'] = $level;
            $this->setExemplaryFutureStatus( null );
    	}
    }


    public function getLevel()
    {
        return $_SESSION['changeLevel'];
    }


    public function getExemplary($itemNumber)
    {
        return $this->exemplary[$itemNumber];
    }


    public function setLowExemplary($itemNumber, $lowDate, $observation)
    {
        $_SESSION['itemsChangeStatus'][$itemNumber]->lowDate     = $lowDate;
        $_SESSION['itemsChangeStatus'][$itemNumber]->description = $description;
    }



    /**
     * Enter description here...
     *
     * @param unknown_type $itemNumber
     * @param unknown_type $lowDate
     * @param unknown_type $observation
     * @return unknown
     */
    public function addItemNumber($itemNumber, $lowDate = null, $observation = null, $cancelReserveEmailObservation = null)
    {
        //dados que serão passados na mensagem
    	$extraData = new StdClass();
        $extraData->itemNumber = $itemNumber;
//Quando estado atual for reservado e estado futuro for disponivel
        
        if ( !$itemNumber )
    	{
    		$this->addError( _M('Número do exemplar não informado.', $this->module) );
                return false;
    	}

        $exemplary = $this->busExemplaryControl->getExemplaryControl($itemNumber);

        if (!$exemplary)
        {
            $this->addError( _M('Exemplar não encontrado.', $this->module) );
            return false;
            
        }

        $exemplaryStatus = $this->busExemplaryStatus->getExemplaryStatus($exemplary->exemplaryStatusId,true);
        
        if (!$observation)
        {
        	$observation = ' ';
        }

        if ( is_null($lowDate) )
        {
            $lowDate = GDate::now()->getDate(GDate::MASK_DATE_USER);
        }
        
        $changeStatusMSG = array();
        $changeStatusMSG['itemNumber'] = $itemNumber;

        if ( $this->getChangeType() == 2 )
        {
            //Verifica se o estado atual é estado de baixa, se for bloqueia a adição do item
	        if ( MUtil::getBooleanValue($exemplaryStatus->isLowStatus) )
	        {
	            $this->addError( _M('Impossível adicionar exemplares em estado de baixa', $this->module), $changeStatusMSG);
                    return false;
	        }

            //caso não possa o estado não permita agendar
            if ( ! MUtil::getBooleanValue( $exemplaryStatus->scheduleChangeStatusForRequest ) )
            {
                $this->addError( _M('Estado "@1" não permite agendamento.', $this->module,$exemplaryStatus->description), $changeStatusMSG);
                return false;
            }
        }

        $isLowStatus = $this->busExemplaryStatus->getExemplaryStatus($this->busExemplaryFutureStatusDefined->getStatusDefined($itemNumber))->isLowStatus;
        $isLowStatus = MUtil::getBooleanValue($isLowStatus);

        if (($this->getChangeType() == 2) && ($isLowStatus))
        {
            $this->addError(_M('O item já tem agendamento de estado de baixa.', $this->module), $changeStatusMSG);
            return false;
        }

        $isLowStatus = $this->busExemplaryStatus->getExemplaryStatus($this->getExemplaryFutureStatus() )->isLowStatus;

        if ( MUtil::getBooleanValue($isLowStatus) && (!$lowDate))
        {
            $this->addError(_M('É necessário definir a data de baixa.', $this->module), $changeStatusMSG);
            return false;
        }

        $exemplary->lowDate         = $lowDate;
        $exemplary->observation     = $observation;
        $busSearchFormat            = $this->MIOLO->getBusiness( $this->module, 'BusSearchFormat' );
        $exemplary->exemplaryData   = $busSearchFormat->getFormatedString($exemplary->controlNumber, MATERIAL_MOVIMENT_SEARCH_FORMAT_ID, 'search');

        //Se for baixa, verifica se deve adicionar
        if ( $isLowStatus )
        {
            $exemplary->cancelReserveEmailObservation = is_null($cancelReserveEmailObservation)? null : $cancelReserveEmailObservation;
        }
        
        if ($exemplary->libraryUnitId != $this->getLibraryUnit() )
        {
            $this->addError( _M('Exemplar não pertence a biblioteca.', $this->module), $changeStatusMSG);
            return false;
        }

        $futureStatus    = $this->getExemplaryFutureStatus();
        //Se código do estado for zero, deve pegar o estado inicial
        if ( !isset($futureStatus) || ($futureStatus == 0) )
        {
        	$level          = $this->getLevel();
            $futureStatus   = $this->busExemplaryStatusHistory->getLastStatus( $itemNumber, $level);

            if ( !$futureStatus )
            {
                $this->addError( _M('Estado futuro do exemplar não encontrado', $this->module), $changeStatusMSG );
                return false;
            }
        }

        if ( $exemplary->exemplaryStatusId == $futureStatus )
        {
            $this->addError( _M('O estado futuro do exemplar é igual ao estado futuro atual.', $this->module), $changeStatusMSG );
            return false;
        }

        if ( MUtil::getBooleanValue( $exemplaryStatus->isLowStatus ) )
        {
            $exemplary->low = 2;
        }
        if ( ($this->getChangeType() == 2)  && ($exemplaryStatus->level == ID_EXEMPLARYSTATUS_INITIAL))
        {
            $this->addError( _M('Impossível agendar mudança de estado de materiais no nível inicial.', $this->module), $changeStatusMSG);
            return false;
        }

        if ( ($this->getChangeType() != 2 ) )
        {
            //Verifica se tem reserva comunicada e com exemplar confirmado
            $reserveStatusId = array(ID_RESERVESTATUS_REPORTED);
            $reserve = $this->busReserve->getReservesOfExemplary($itemNumber, $reserveStatusId, true);

            if ($reserve)
            {
                $this->addError( _M('O estado está como reservado e tem reserva.', $this->module), $changeStatusMSG );
                return false;
            }
            
            $reserveStatusId = array(ID_RESERVESTATUS_ANSWERED);
            $reserve = $this->busReserve->getReservesOfExemplary($itemNumber, $reserveStatusId, true);            
            
            //Se a reserva estiver atendida e nao for estado de baixa, nao pode deixar trocar o estado.
            if ($reserve && !MUtil::getBooleanValue($isLowStatus) )
            {
                $this->addError( _M('Existe reserva atendida para o exemplar, exemplares com reservas atendidas só podem ser baixados.', $this->module), $changeStatusMSG );
                return false;
            }            
            
            $loan = $this->busLoan->getLoanOpen($itemNumber);

            if ( $loan )
            {
                $this->addError( _M('Exemplar com empréstimo em aberto', $this->module), $changeStatusMSG );
                return false;
            }
        }

        $futureStatusObj = $this->setExemplaryFutureStatus($futureStatus);
        $exemplary->futureStatus = $futureStatusObj;

        return $this->_addItemNumber( $exemplary, 'itemsChangeStatus' );
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $itemNumber
     */
    public function deleteItemNumber($itemNumber)
    {
        parent::deleteItemNumber($itemNumber, 'itemsChangeStatus');
    }


    /**
     * Return the ChangeStatus Items
     *
     * @return array of objects
     */
    public function getItems()
    {
    	return parent::getItems( 'itemsChangeStatus' );
    }


    /**
     * Clear the exemplary list
     *
     */
    public function clearItems()
    {
        return parent::clearItems('itemsChangeStatus' );
    }


    /**
     * Finalize the operation
     *
     * @return boolean
     */
    public function finalize($args = null)
    {
    	$items = $this->getItems();

    	if (!$items )
    	{
            $this->addError( _M('Lista vazia, por favor adicione algum exemplar', $this->module) );
            $this->errorCode = 10;
            return false;
    	}

        foreach ($items as $itemNumber => $exemplary)
        {
            $exemplaryStatus = $this->busExemplaryStatus->getExemplaryStatus( $this->getExemplaryFutureStatus() );
            
            $exemplary->lowDate = GDate::construct($exemplary->lowDate)->getDate(GDate::MASK_DATE_USER);
            unset( $extraData );
            $extraData['itemNumber']    = $itemNumber;
            $extraData['currentStatus'] = $exemplary->currentStatus->description;
            $extraData['futureStatus']  = '<b>'.$exemplary->futureStatus->description.'</b>';

            //monta string de baixa
            if ( $exemplary->observation )
            {
                $exemplaryDescription = "\n".'Alterado para '. $exemplary->futureStatus->description . ' em ' .$exemplary->lowDate. ' - '.$exemplary->observation;
            }

        	//2 schedule agendamento
            if ( $this->getChangeType() == 2 )
            {
                $this->busExemplaryFutureStatusDefined->exemplaryStatusId  = $exemplary->futureStatus->exemplaryStatusId;
                $this->busExemplaryFutureStatusDefined->itemNumber         = $exemplary->itemNumber;
                $this->busExemplaryFutureStatusDefined->applied            = DB_FALSE;
                $this->busExemplaryFutureStatusDefined->operator           = $this->getOperator();
                $this->busExemplaryFutureStatusDefined->date               = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
                $this->busExemplaryFutureStatusDefined->observation        = $exemplaryDescription;
                $this->busExemplaryFutureStatusDefined->cancelReserveEmailObservation        = $exemplary->cancelReserveEmailObservation;
                $this->busExemplaryFutureStatusDefined->insertExemplaryFutureStatusDefined();
                $this->addInformation( _M('Estado agendado com sucesso!', $this->module), $extraData);
                continue;
            }

            //alteração - change
            $isLowStatus = $exemplaryStatus->isLowStatus;
            $isLowStatus = MUtil::getBooleanValue($isLowStatus);

            //Se nao for estado de baixa
            if (!$isLowStatus)
            {
            	$exemplary->lowDate   = NULL;
            	$exemplaryDescription = NULL;
            }
                
            //processo para autalizar baixa do exemplar
            $this->busExemplaryControl->updateLow($exemplary->itemNumber, $exemplary->lowDate, $exemplaryDescription, true);

            // EFETUA ALTERAÇÂO DO STATUS
            if($this->busExemplaryControl->changeStatus( $itemNumber, $this->getExemplaryFutureStatus() , $this->getOperator() ))
            {
                $this->addInformation( _M('Estado alterado com sucesso.', $this->module), $extraData);
                $changedStatus = $this->busExemplaryStatus->getExemplaryStatus( $this->getExemplaryFutureStatus(), true );
               
                //testa se é estado de baixa
                if ( !MUtil::getBooleanValue($changedStatus->executeLoan ) )
                {
                    $this->addAlert( _M('O estado alterado <b>não permite empréstimos</b>, cancelando reservas para este exemplar.', $this->module), $extraData);

                    //cancela as reservas do item
                    $this->busOperationReserve->cancelReserveByItemNumber($exemplary, $args);
                }
            }
            else
            {
                $this->addInformation( _M('Não é possível alterar o estado do exemplar.', $this->module), $extraData);
            }
        }

        $this->errorCode = null;
        return true;
    }



    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getExemaplryFutureStatusDefined()
    {
        if($this->getChangeType() == 2)
        {
            return $this->busExemplaryFutureStatusDefined->getCurrentId();
        }

        return false;
    }



    /**
     * retorna o codigo do erro
     *
     * @return integer
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }


    public function clean()
    {
        $this->libraryUnitId =
        $this->level =
        $this->exemplaryFutureStatus =
        $this->exemplaryStatusId =
        $this->exemplary =
        $this->operator =
        $this->errorCode =    null;
        $this->clearItems();
        parent::clean();
    }

}
?>
