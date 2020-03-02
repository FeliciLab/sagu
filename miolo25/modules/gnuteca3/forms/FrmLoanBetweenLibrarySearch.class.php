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
 * LoanBetweenLibrarySearch form
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
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 23/04/2008
 *
 **/
class FrmLoanBetweenLibrarySearch extends GForm
{
    public $busExemplaryControl;
    public $busLibraryUnit;
    public $busLoanBetweenLibrary;
    public $busLoanBetweenLibraryComposition;
    public $busLoanBetweenLibraryStatus;
    public $busOperationLoanBetweenLibrary;
    public $busExemplaryFutureStatusDefined;
    public $busReserve;
    public $busExemplaryStatus;
    public $busReserveStatus;

    public function __construct()
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busExemplaryControl              = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busLibraryUnit                   = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busLoanBetweenLibrary            = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibrary');
        $this->busLoanBetweenLibraryComposition = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibraryComposition');
        $this->busLoanBetweenLibraryStatus      = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibraryStatus');
        $this->busOperationLoanBetweenLibrary   = $this->MIOLO->getBusiness($this->module, 'BusOperationLoanBetweenLibrary');
        $this->busReserve                       = $this->MIOLO->getBusiness($this->module, 'BusReserve');
        $this->busExemplaryStatus               = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        $this->busReserveStatus                 = $this->MIOLO->getBusiness($this->module, 'BusReserveStatus');
        $this->busExemplaryFutureStatusDefined  = $this->MIOLO->getBusiness($this->module, 'BusExemplaryFutureStatusDefined');

        $this->setAllFunctions( 'LoanBetweenLibrary', array('libraryUnitIdS'), array('libraryUnitId'));
        
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new MTextField('loanBetweenLibraryIdS', null, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[] = new MHiddenField('libraryUnitIdS',GOperator::getLibraryUnitLogged() );

        $lblDate            = new MLabel(_M('Data do empréstimo', $this->module) . ':');
        $loanDateHidden     = new MHiddenField('loanDateS');
        $beginLoanDateS     = new MCalendarField('beginLoanDateS', $this->beginLoanDateS->value);
        $endLoanDateS       = new MCalendarField('endLoanDateS', $this->endLoanDateS->value);
        $fields[]           = new GContainer('hctDates', array($lblDate, $beginLoanDateS, $endLoanDateS,$loanDateHidden));

        $lblDate            = new MLabel(_M('Data prevista da devolução', $this->module) . ':');
        $beginReturnForecastDateS     = new MCalendarField('beginReturnForecastDateS', $this->beginLoanDateS->value);
        $endReturnForecastDateS       = new MCalendarField('endReturnForecastDateS', $this->endReturnForecastDateS->value);
        $fields[]                     = new GContainer('hctDates', array($lblDate, $beginReturnForecastDateS, $endReturnForecastDateS));

        $lblDate            = new MLabel(_M('Data limite', $this->module) . ':');
        $beginLimitDateS    = new MCalendarField('beginLimitDateS', $this->beginLimitDateS->value);
        $endLimitDateS      = new MCalendarField('endLimitDateS', $this->endLimitDateS->value);
        $fields[]           = new MContainer('hctDates', array( $lblDate, $beginLimitDateS, $endLimitDateS),'horizontal',MFormControl::FORM_MODE_SHOW_NBSP);

        $lblDate            = new MLabel(_M('Data de devolução', $this->module) . ':');
        $beginReturnDateS   = new MCalendarField('beginReturnDateS', $this->beginReturnDateS->value);
        $endReturnDateS     = new MCalendarField('endReturnDateS', $this->endReturnDateS->value);
        $fields[]           = new GContainer('hctDates', array($lblDate, $beginReturnDateS, $endReturnDateS));

        $fields[] = new GPersonLookup('personIdS', _M('Pessoa', $this->modules), 'person');
        $fields[] = new GSelection('loanBetweenLibraryStatusIdS', null, _M('Estado', $this->module), $this->busLoanBetweenLibraryStatus->listLoanBetweenLibraryStatus());
        $fields[] = new MTextField('itemNumberS', null, _M('Exemplar', $this->module), FIELD_DESCRIPTION_SIZE);

        $this->setFields($fields);
        $this->setValidators($validators);
    }


    public function cancel()
    {
        $gotoYes = GUtil::getAjax($function, array( 'event' => 'cancel_confirmed', 'function'=>'detail', 'loanBetweenLibraryId'  => MIOLO::_REQUEST('loanBetweenLibraryId') ) );
        $this->question(_M('Tem certeza de que realmente deseja cancelar?', $this->module), $gotoYes);
    }

    /**
     * Cancela um determinado emprestimo entre bibliotecas
     *
     */
    public function cancel_confirmed()
    {
        $ok = $this->busOperationLoanBetweenLibrary->cancelRequest( MIOLO::_REQUEST('loanBetweenLibraryId') );
        $this->informationEnd( _M('A requisição de empréstimo foi cancelada!', $this->module) );
    }

    /**
     * Este method cria um janela com a lista de exemplares da composi??o do emprestimo.
     * Permite que o usuario selecione os exemplares que deseja aprovar
     *
     */
    public function approve()
    {
        $data = array();
        $composition = $this->busLoanBetweenLibraryComposition->getComposition( MIOLO::_REQUEST('loanBetweenLibraryId'), MIOLO::_REQUEST('libraryUnitId') );
        $status[]           = ID_RESERVESTATUS_REQUESTED;
        $status[]           = ID_RESERVESTATUS_ANSWERED;
        $status[]           = ID_RESERVESTATUS_REPORTED;
        $check = true;

        foreach ($composition as $val)
        {
            $exemplaryStatusId  = $this->busExemplaryControl->getExemplaryControl($val->itemNumber)->exemplaryStatusId;
            $reserveStatusId    = $this->busReserve->getReservesOfExemplary($val->itemNumber, $status);
            unset($estatusId);
            foreach ($reserveStatusId as $reserves)
            {
                $estatusId[] = $reserves->reserveStatusId;
            }

            $data[] = array
            (
                new MRadioButton('cb_', $val->itemNumber, null, $check),
                $val->itemNumber,
                $exemplaryStatus = $this->busExemplaryStatus->getExemplaryStatus($exemplaryStatusId)->description,
                $reserveStatus   = $this->busReserveStatus->getReserveStatus($estatusId[0])->description
            );
            $check = false;
        }

        $cols = array
        (
           _M('Ação', $this->module),
           _M('Número do exemplar', $this->module),
           _M('Estado do exemplar', $this->module),
           _M('Estado da reserva', $this->module)
        );

        $tb = new MTableRaw(null, $data, $cols);
        $tb->setAlternate(true);

        $fields[] = $tb;
        $buttons[] = GForm::getCloseButton();
        $buttons[] = new MButton('btnAct', _M('Aprovar', $this->module), GUtil::getAjax('approve_confirmed', array('function'=>'detail', 'loanBetweenLibraryId' => MIOLO::_REQUEST('loanBetweenLibraryId') )), GUtil::getImageTheme('accept-16x16.png') );
        $fields[] = new MDiv('buttonsX',$buttons);
        $fields[] = new MDiv('limboX');

        $this->injectContent($fields, false, _M('Aprovação', $this->module) );
    }

    /**
     * Aprova um determinado emprestimo entre bibliotecas
     *
     */
    public function approve_confirmed()
    {
        $args = (object) $_REQUEST;

        $status[] = ID_RESERVESTATUS_ANSWERED;
        $status[] = ID_RESERVESTATUS_REPORTED;

        $items = $this->getSelectedItems();

        $hasReserve = $this->busReserve->hasReserve($args->cb_, $status, null, true);

        if ($hasReserve)
        {
            $this->error(_M('O item @1 tem reserva atendida ou comunicada', $this->module, $args->cb_));
            return true;
        }

        //pega o estado futuro definido para o Exemplar, ou seja o agendamento
        $schedule = $this->busExemplaryFutureStatusDefined->getStatusDefined($args->cb_);
        if ($schedule)
        {
            $this->error(_M('Existe uma troca de estado agendada para o exemplar @1', $this->module, $args->cb_) );
            return true;
        }

        $this->busOperationLoanBetweenLibrary->approveLoanBetweenLibrary($args->loanBetweenLibraryId, $args->cb_);

        $this->InformationEnd( _M('O item @1 foi aprovado!', $this->module, $args->cb_) );
    }

    public function confirmReceipt()
    {
        $opts['loanBetweenLibraryId']   = MIOLO::_REQUEST('loanBetweenLibraryId');
        $opts['function']               = 'detail';
        $gotoYes = GUtil::getAjax('confirmReceipt_confirmed',$opts);
        $this->question(_M('Tem certeza de que você realmente quer confirmar o recebimento?', $this->module), $gotoYes);
    }

    public function confirmReceipt_confirmed()
    {
        $ok = $this->busOperationLoanBetweenLibrary->confirmReceipt( MIOLO::_REQUEST('loanBetweenLibraryId') );
        $this->informationEnd( _M('A requisição foi confirmada com sucesso!', $this->module));
    }

    public function disapprove()
    {
        $fields[] = new MDiv('',_M("Informe o motivo do cancelamento", $this->module).":");
    	$fields[] = new MHiddenField('loanBetweenLibraryId', MIOLO::_REQUEST('loanBetweenLibraryId'));
        $fields[] = new MMultiLineField('observation', NULL, _M('Observação', $this->module), NULL, 5, 65);

        $buttons[] = new MButton('btnGo', _M('Reprovar', $this->module), ':disapprove_confirmed', GUtil::getImageTheme('error-16x16.png'));
        $buttons[] = GForm::getCloseButton();
        $fields[] = new MDiv('abc',$buttons );

        $this->injectContent($fields, false , _M('Reprovar pedido de empréstimo', $this->module), '580px !important');
    }

    /**
     * Disaprova um determinado emprestimo entre bibliotecas
     *
     */
    public function disapprove_confirmed()
    {
        $this->busOperationLoanBetweenLibrary->disapproveMaterial(MIOLO::_REQUEST('loanBetweenLibraryId'), MIOLO::_REQUEST('observation'));
        $this->informationEnd( _M('O empréstimo foi reprovado', $this->module));
    }

    public function returnMaterial()
    {
        $opts['function'] = 'detail';
        $opts['loanBetweenLibraryId'] = MIOLO::_REQUEST('loanBetweenLibraryId');
        $opts['libraryUnitId'] = MIOLO::_REQUEST('libraryUnitId');
        $goto   = GUtil::getAjax('returnMaterial_confirmed',$opts);
        $this->question(_M('Confirmar retorno do material?', $this->module), $goto);
    }

    public function returnMaterial_confirmed()
    {
        $this->busOperationLoanBetweenLibrary->returnMaterial( MIOLO::_REQUEST('loanBetweenLibraryId') );
        $this->informationEnd();
    }

    public function confirmReturn()
    {
        $opts['function'] = 'detail';
        $opts['loanBetweenLibraryId'] = MIOLO::_REQUEST('loanBetweenLibraryId');
        $opts['libraryUnitId'] = MIOLO::_REQUEST('libraryUnitId');
        $goto = $this->MIOLO->getActionURL($this->module, $this->_action, null, $opts);
        $goto   = GUtil::getAjax('confirmReturn_confirmed',$opts);
        $this->question(_M('Confirmar retorno?', $this->module), $goto);
    }

    public function confirmReturn_confirmed()
    {
        $this->busOperationLoanBetweenLibrary->confirmReturn( MIOLO::_REQUEST('loanBetweenLibraryId') );
        $this->informationEnd( );
    }

    /**
     * Mostra caixa de informação de finalização, especial para esta interface.
     * Caso tenha erros mostra tabela do bussines
     *
     * @param string $msg mensagem no caso de sucesso
     */
    public function informationEnd( $msg = 'Operação executada com sucesso!' )
    {
        if ( count( $this->busOperationLoanBetweenLibrary->getMessages() ) > 0 )
        {
            $this->injectContent( $this->busOperationLoanBetweenLibrary->getMessagesTableRaw(), true, _M('Finalização:', $this->module) );
        }
        else
        {
            $loanBetweenLibraryId = MIOLO::_REQUEST('loanBetweenLibraryId');
            $goto = Gutil::getCloseAction(true) . " dojo.byId('loanBetweenLibraryIdS').value={$loanBetweenLibraryId} ; " . GUtil::getAjax('searchFunction');
            $this->information($msg, $goto);
        }
    }

    public function getSelectedItems()
    {
        $list = array();
        $composition = $this->busLoanBetweenLibraryComposition->getComposition( MIOLO::_REQUEST('loanBetweenLibraryId') );
        foreach ($composition as $val)
        {
            $cb = MIOLO::_REQUEST('cb_' . $val->itemNumber);
            if (isset($cb))
            {
                $list[] = $val->itemNumber;
            }
        }
        return $list;
    }
}
?>