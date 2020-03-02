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
 * Congelado form
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
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
 * Class created on 06/01/2009
 *
 **/
class FrmCongelado extends GSubForm
{
	public $MIOLO;
	public $module;
    public $business;
    public $busOperationRequestChangeExemplaryStatus, $businessLibrayUnitConfig, $busAuthenticate;
    public $action;
    public $busRequestChangeExemplaryStatusAccess;
    public $busRequestChangeExemplaryStatusStatus;
    public $busRequestChangeExemplaryStatus;
    public $busLibraryUnit;
    
    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->business                                     = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatus');
        $this->busOperationRequestChangeExemplaryStatus     = $this->MIOLO->getBusiness( $this->module, 'BusOperationRequestChangeExemplaryStatus');
        $this->busRequestChangeExemplaryStatusAccess        = $this->MIOLO->getBusiness( $this->module, 'BusRequestChangeExemplaryStatusAccess');
        $this->busRequestChangeExemplaryStatusStatus        = $this->MIOLO->getBusiness( $this->module, 'BusRequestChangeExemplaryStatusStatus');
        $this->busRequestChangeExemplaryStatus              = $this->MIOLO->getBusiness( $this->module, 'BusRequestChangeExemplaryStatus');
        $this->businessLibrayUnitConfig                     = $this->MIOLO->getBusiness( $this->module, 'BusLibraryUnitConfig');
        $this->busAuthenticate                              = $this->MIOLO->getBusiness( $this->module, 'BusAuthenticate');
        $this->busLibraryUnit                               = $this->MIOLO->getBusiness( $this->module, 'BusLibraryUnit');

        $this->MIOLO->getClass($this->module, 'GSendMail');
        $this->mail = new GSendMail();

        $this->gridName = 'GrdCongelado';
        $this->gridSearchMethod = 'searchRequestChangeExemplaryStatus';

        parent::__construct(_M('Congelado', $this->module));
    }

    public function createFields()
    {
        GForm::setFocus('personIdS',false);
        
        //Mensagem a ser mostrada no topo da tela
        $fields[] = new MDiv('', LABEL_CONGELADO);

        $fields[] = new GPersonLookup('personId', _M('Professor(a)', $this->modules), 'personCongelado');
        $lblDate        = new MLabel(_M('Data', $this->module) . ':');
        $dateS          = new MCalendarField('beginDateS');
        $finalDateS     = new MCalendarField('endDateS');
        $fields[]       = new GContainer('hctDates', array($lblDate, $dateS, $finalDateS));

        $options  = $this->busRequestChangeExemplaryStatusStatus->listRequestChangeExemplaryStatusStatus(false, true);
        $fields[] = new GSelection('requestChangeExemplaryStatusStatusIdS', $this->requestChangeExemplaryStatusStatusIdS->value, _M('Estado', $this->module), $options, null, null, null, false);
        $fields[] = new GSelection('libraryUnitIdS', $this->libraryUnitIdS->value, _M('Unidade de biblioteca',  $this->module), $this->busLibraryUnit->listLibraryUnit());
        $fields[] = new MTextField('disciplineS', $this->discipline->value, _M('Disciplina',    $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MHiddenField('userCodeLogged', $this->busAuthenticate->getUserCode());
        $fields[] = $this->getBtnSearch();
        $fields[] = new MSeparator();
        $fields[] = new MDiv( self::DIV_SEARCH );

        $this->setFields( GUtil::alinhaForm($fields) );
    }

    /**
     * Janela da data de renovação
     */
    public function WindowRenew()
    {
        $periodInterval = $this->busRequestChangeExemplaryStatus->getPeriodInterval();  
        $fields[]  = $finalDate = new MCalendarField('finalDate', $periodInterval->finalDate->getDate(GDate::MASK_DATE_USER), _M('Data final', $this->module), FIELD_DATE_SIZE);

        if ( $periodInterval->finalDate )
        {
            $finalDate->setReadOnly(true);
        }
        else
        {
            $fields[] = new MDiv('',_M('Por favor digite uma data para a renovação',$this->module));
        }

        $buttons['print'] = new MButton('btnFinalize', _M("Finaliza renovação", $this->module), ':btnFinalize', GUtil::getImageTheme('accept-16x16.png'));
        $buttons['close'] = GForm::getCloseButton();

        $fields[] = new MDiv('containerRequestButtons', $buttons);
        
        GForm::injectContent( GUtil::alinhaForm( $fields ) , false, _M('Renovar congelamento', $this->module ) );
    }

    public function btnFinalize()
    {
        $args = (object)$_REQUEST;
        $this->WindowRenew();

        if ($args->finalDate)
        {
        	if ($args->selectGrdCongelado)
        	{
	        	foreach ($args->selectGrdCongelado as $requestId)
	        	{
	        		$this->busRequestChangeExemplaryStatus->renewRequest($requestId, $args->finalDate);
	        	}
                
                GForm::information(_M('Renovação efetuada com sucesso!', $this->module), $this->getCloseAndReloadAction());
                return true;
        	}

        	GForm::error(_M('Não há item selecionados', $this->module), $this->getCloseAndReloadAction());
        	return false;
        }
    }

    /**
     * Cancela uma requisição (CONFIRMACAO)
     */
    public function cancelReserveConfirm($args)
    {
        $args      = GUtil::decodeJsArgs($args);
        $gotoYes = GUtil::getAjax('cancelReserve', (array)$args);
        GForm::question(_M('Tem certeza que deseja cancelar a requisição?', $this->module), $gotoYes);
    }

    /**
     * Cancela uma requisição
     */
    public function cancelReserve($args)
    {
        $args = GUtil::decodeJsArgs($args);
        $this->busOperationRequestChangeExemplaryStatus->clean();
        $this->busOperationRequestChangeExemplaryStatus->getRequest($args->requestChangeExemplaryStatusId);

        $ok = $this->busOperationRequestChangeExemplaryStatus->cancelRequest($args->requestChangeExemplaryStatusId);

        if($ok)
        {
            //ENVIA EMAIL PARA ADMINISTRADOR INFORMANDO CANCELAMENTO
            $this->mail->sendMailToAdminCancelRequestChangeExemplaryStatus($args->requestChangeExemplaryStatusId);

            GForm::information(MSG_RECORD_CANCELED, $this->getCloseAndReloadAction());
        }
        else
        {
            GForm::error( MSG_RECORD_ERROR, $this->getCloseAndReloadAction());
        }
    }

    public function getGrid()
    {
		//Passa o usuário logado para filtrar as solicitações
        $_REQUEST['userCodeLogged'] = BusinessGnuteca3BusAuthenticate::getUserCode(); 

        $grid = parent::getGrid();

        return $grid;
    }

    public function getGridData()
    {
        $personId = BusinessGnuteca3BusAuthenticate::getUserCode();

        if ($personId)
        {
            $data = parent::getGridData();
            $data->personIdS = $personId;
            return $data;
        }
    }
}
?>
