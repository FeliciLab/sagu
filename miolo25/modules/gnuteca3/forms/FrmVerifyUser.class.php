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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 06/08/2008
 *
 **/
$MIOLO->getClass('gnuteca3', 'controls/GMaterialDetail');
$MIOLO->getClass('gnuteca3', 'GSipCirculation');
class FrmVerifyUser extends GForm
{
    public $busSearchFormat;
    
	function __construct($title = NULL)
	{
        //evento personalizado
        $myEvent = MIOLO::_REQUEST('myEvent');
		$this->MIOLO  = MIOLO::getInstance();
		$this->module = MIOLO::getCurrentModule();

        $this->busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        
        $this->setTransaction('gtcMaterialMovementVerifyUser');
        
		parent::__construct ( _M('Verificar usuário', $this->module) );
		$this->setIcon( GUtil::getImageTheme('person-16x16.png') );
		
        //teclas de atalho e foco
		$this->keyDownHandler( 113, 114,115,116,117,118,119 );
    	$this->jsSetFocus('personIdW',false);

        //ativa evento personalizado via ajax
        if ( $myEvent && $this->primeiroAcessoAoForm() )
        {
            $this->page->onload( GUtil::getAjax($myEvent) );
        }
    }

    /**
     * Troca as cores dos botões
     * @param string $focus
     */
    public function changeButtonFocus($focus)
    {
        $buttons = array('btnLoan2', 'btnReserve2','btnFine2', 'btnPenalty2', 'btnProfile2','btnPolicy2','btnNadaConsta');

        foreach ( $buttons as $line => $buttonId)
        {
            $color = $buttonId == $focus ? 'red' : 'black';
            $this->page->onload($js = "dojo.byId('$buttonId').style.color = '$color';" );
        }
    }

	function mainFields()
	{
		$postInterno = MIOLO::_REQUEST('__FORMSUBMIT') == 'frmwinVerifyUser' ? true : false  ;

		if ( !$postInterno )
		{
			$args = ( Object ) $_REQUEST;
			$args->return = true;
			$args->personIdW = MIOLO::_REQUEST('personId');
            $event = $this->getEvent();

            if ( $event ) // só chama caso tenha evento
            {
                $initialContent =  $this->$event( $args );
            }
		}

		$module = MIOLO::getCurrentModule();

		if ( in_array($this->getEvent(), array('openMaterialDetail', 'showHistory')))
		{
			return;
		}

        $functionButtons    = array();
		$functionButtons[]  = new MButton('btnLoan2', _M('[F2] Empréstimo', $module), ':onkeydown113');
		$functionButtons[]  = new MButton('btnReserve2', _M('[F3] Reserva', $module), ':onkeydown114');
        $functionButtons[]  = new MButton('btnFine2', _M('[F4] Multa', $module), ':onkeydown115');
        $functionButtons[]  = new MButton('btnPenalty2',  _M('[F5] Penalidade', $module), ':onkeydown116');
        $functionButtons[]  = new MButton('btnProfile2', _M('[F6] Perfil', $module),':onkeydown117');
        $functionButtons[]  = new MButton('btnPolicy2', _M('[F7] Política', $module), ':onkeydown118');
        $functionButtons[]  = new MButton('btnNadaConsta', _M('[F8] Nada consta', $module), ':onkeydown119');
        $functionButtons['esc']  = new MButton('btnEsc', _M('[ESC] Sair', $module), "javascript:" . GUtil::getAjax('verifyUserOnClose','','__mainForm') . "; miolo.getWindow('winVerifyUser').close();");

        //define classe css para botões
		foreach ($functionButtons as $line => $info)
        {
            $info->setClass('mButtonMaterialCirculationUpper');
        }

        if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE)  || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
        {
            $busPerson = $this->MIOLO->getBusiness($this->module, 'BusPerson');
            $person = $busPerson->getPerson(MIOLO::_REQUEST('personId'));
            $personId = $person->login;
        }
        else
        {
            $personId = MIOLO::_REQUEST('personId');
        }
        
        $fields[] = new MDiv('buttonContainerVU', $functionButtons);
        $lookup[] = new MTextField('personNameW', null , null, 30, null, null, true);
        $lookup[] = new MTextField('email', null ,null, 20,null, null, true);
        $fields[] = $personId = new GLookupField('personIdW', $personId, _M('Pessoa', $module), 'Person', $lookup);
        $personId->lookupTextField->addAttribute('onPressEnter', GUtil::getAjax('onkeydown113'));
        $fields[] = new MSeparator('<br/>');
        
        $divsVerifyUser[] = new MDiv('divLoanVerifyUser', $initialContent);
        $divsVerifyUser[] = new MSeparator('');
        $divsVerifyUser[] = new MDiv('divVerifyUser');
        $fields[] = new MDiv('divVerifyUserContent', $divsVerifyUser);

        $this->setFields($fields, false);
	}


	/**
	 * Emprestimo (loan)
	 *
	 * @param stdclass $args
	 */
	public function onkeydown113($args)
	{
        $module = MIOLO::getCurrentModule();
        $this->changeButtonFocus('btnLoan2');
        $busReserve = $this->MIOLO->getBusiness($this->module, 'BusReserve');
        
        if(GSipCirculation::usingSmartReader() && $args->personIdW)
        {
            //Instancia objeto pessoa
            $busPerson = $this->MIOLO->getBusiness($module, 'BusPerson');

            //Obtem a informação passada, como ID ou código do cartão
            $codUser = $args->personIdW;
            
            /* Para obter a identificação da pessoa, primeiramente procura pelo personId
             * Caso não encontre o personId, irá validar pelo login */
            $personInf = $busPerson->getPerson($codUser, TRUE);

            //Caso não tiver usuário, tentará validar pelo login
            if(!$personInf)
            {
                
                /* Alteração para funcionalidade de uso de cartão - Univates 06/2014
                 * 
                 * O personId aqui, é o código do cartão.
                 * O código do cartão na basPerson é o campo login.
                 * Deve-se procurar pela pessoa cujo login seja personId (código do cartão).
                 * Após obter a pessoa, obter o personId da mesma, e dar continuidade no algoritmo
                 */

                $idPessoa = $busPerson->getPersonIdByLogin($codUser);
                $codUserReal = $idPessoa[0][0];
                $args->personIdW = $codUserReal;
                
                //setar o valor
                $this->jsSetValue('personIdW', $args->personIdW);
                $this->page->onload("setTimeout('lookup_miolo_Dialog_0_personIdW.start(true);',100);");
            
                $this->page->onload(GUtil::getAjax('onkeydown113'));
                $this->setResponse(NULL, 'limbo');
                return false;
            }
        }
        
        //Caso não tiver o smartReader, segue o baile normalmente ;)
        if(! GSipCirculation::usingSmartReader())
        {
            //caso ainda não tenha carregado o nome faz uma resposta nula
            //e faz uma rechamada para dar tempo do lookup trabalhar
            if ( !$args->personNameW && $args->personIdW )
            {
                $this->setResponse('', 'limbo');
                $this->page->onload( GUtil::getAjax('onkeydown113') );
                return false;
            }
        }

        if ($args->personIdW)
        {
            $busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');
            $busLoan->personId = $args->personIdW;
            $loans   = $busLoan->getLoansOpen(null, null, true);
        }
        
        $busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');

	    if ($loans && is_array($loans))
	    {
            $busLoanType = $this->MIOLO->getBusiness($this->module, 'BusLoanType');
            $busPolicy = $this->MIOLO->getBusiness($this->module, 'BusPolicy');
            
	        foreach ( $loans as $line => $info)
	        {
                $materialGenderId = $busExemplaryControl->getMaterialGender($info->itemNumber);
                $policy           = $busPolicy->getPolicy($info->privilegeGroupId, $info->linkId, $materialGenderId);

                $actions       = array();
                $controlNumber = $busExemplaryControl->getControlNumber($info->itemNumber);
                $actWorkDetail = new MImageLink('lnkWorkDetail', NULL, "javascript:" . GUtil::getAjax('openMaterialDetail', $controlNumber), GUtil::getImageTheme('config-16x16.png'));
                $actWorkDetail->setAttribute('title', _M('Detalhe', $this->module));
                $actions[]     = $actWorkDetail;

                $temp   = NULL;
                $temp[] = new GContainer('hctActions', $actions);
                $temp[] = $info->itemNumber;
                $temp[] = $this->busSearchFormat->getFormatedString($info->controlNumber, MATERIAL_MOVIMENT_SEARCH_FORMAT_ID, 'search');
                $dt     = new GDate($info->loanDate);
                $temp[] = $dt->getDate(GDate::MASK_TIMESTAMP_USER);

                $dateReturn = new GDate($info->returnForecastDate);
                $dateNow    = GDate::now();
                $dt = new GDate($info->returnForecastDate);
                $returnForecastDate = $dt->getDate(GDate::MASK_DATE_USER);

                if ( $dateNow->diffDates($dateReturn)->days > 1 ) //Se emprestimo estiver em atraso, destaca o campo
                {
                    $returnForecastDate = new MLabel($returnForecastDate, 'red', TRUE);
                    $exemplaryStatus = _M('Atrasado', $this->module);
                }
                else
                {
                    $exemplaryStatus = _M('Regular', $this->module);
                }
                
	            $temp[] = $returnForecastDate;
	            $temp[] = $exemplaryStatus;
	            $temp[] = $info->libraryUnit->libraryName;
	            $temp[] = $busReserve->getTotalQueueByItemNumber($info->itemNumber);
	            $temp[] = $busLoanType->getLoanType($info->loanTypeId)->description;
	            $temp[] = $info->renewalAmount;
	            $temp[] = $info->renewalWebAmount;

	            $renewString    = '';
	            $renewTreeData  = null;

	            if (is_array ($info->renew))
	            {
	                $renewString = 'RenewId  - renewType  - Date  -  returnForecastDate  -  operator <br>';
	                foreach ($info->renew as $r)
	                {
	                    $renewString .= $r->renewId .' - '. $r->renewType .' - '.  $r->renewDate .' - '.  $r->returnForecastDate .' - '.  $r->operator . '<br>';
	                }
	            }

	            if ($renewString)
	            {
	                $renewTreeData[0]->title = _M('Renovação', $this->module);
	                $renewTreeData[0]->content = $renewString;

                    $this->MIOLO->getClass($this->module, 'controls/GTree');
	                $tree = new GTree('treeReserve'.$info->itemNumber, $renewTreeData);

	                $renewString = $tree->generate();
	            }

	            $temp[] = $this->getRenewTable($info->loanId);
	            $loanArray[] = $temp;
	        }
	    }

	    $loanTitles = array(
	        _M('Ações', $module),
	        _M('Número do exemplar',       $module),
	        _M('Dados',  $module),
	        _M('Data do empréstimo',  $module),
	        _M('Data prevista',  $module),
	        _M('Estado', $module),
	        _M('Unidade de biblioteca',  $module),
            _M('Fila de reserva', $module),
	        _M('Tipo de empréstimo',  $module),
	        _M('Quantidade de renovações permitidas',$module),
	        _M('Quantidade de renovações web permitidas',  $module),
	        _M('Renovações',  $module),
	        );

	    $table = new MTableRaw( NULL, $loanArray , $loanTitles, 'currentLoan',true);
	    $table->addStyle('width','100%');

	    if ($args->return)
	    {
	    	return $table;
	    }

        $this->setTableContent($table);
	}


	public function getRenewTable($loanId)
	{
		$tbCols = array(
            _M('Tipo', $this->module),
             _M('Data prevista da devolução', $this->module),
            _M('Data de renovação', $this->module),
            _M('Nova data prevista da devolução', $this->module),
            _M('Operador', $this->module),
		);

        $busRenew  = $this->MIOLO->getBusiness($this->module, 'BusRenew');
        
        $table = new MTableRaw(NULL, $busRenew->getHistoryOfLoan($loanId) , $tbCols,true);
        $table->addStyle('width','100%');
        
        return $table;
	}

	/**
	 * Reservas F3
	 *
	 * @param stdclass $args
	 */
	public function onkeydown114($args)
	{
		$module = MIOLO::getCurrentModule();
        $this->changeButtonFocus('btnReserve2');

		if ( !$args->personIdW)
        {
            return $this->setTableContent($table);
        }
        
        $status[] = ID_RESERVESTATUS_REQUESTED;
        $status[] = ID_RESERVESTATUS_ANSWERED;
        $status[] = ID_RESERVESTATUS_REPORTED;

        $busReserve = $this->MIOLO->getBusiness($this->module, 'BusReserve');
        $busReserve ->personIdS        = $args->personIdW;
        $busReserve ->reserveStatusIdS = $status;
        $busReserve->isFormSearch = true;
        $reserves   = $busReserve->searchReserve('R.requestedDate DESC', true, true);
        
        //Obtém a lista de reservas de determinado material
        $controlNumber = $reserves[0]->composition[0]->controlNumber;
        
        if( $controlNumber )
        {
            $listReservesOfMaterial = $busReserve->getReservesOfMaterial( $controlNumber, $status, null, null, 'A.reserveStatusId DESC, A.reserveId, B.itemNumber ASC' );
        }
        
        $reserveArray = array();

        if ($reserves && is_array($reserves) )
        {
            foreach ($reserves as $line => $info)
            {
                $tbData = array();

                //monta tabela de composição
                if (is_array($info->composition))
                {
                    $data = null;

                    foreach ($info->composition as $l => $i )
                    {
                        $beforeIsConfirmed = MUtil::getBooleanValue( $info->composition[0]->isConfirmed );

                        if ( $beforeIsConfirmed == false || $l == 0 )
                        {
                            $tbData[] = array( $i->itemNumber, $i->isConfirmedLabel );
                            $itemNumber     = $i->itemNumber;
                            $controlNumber  = $i->controlNumber;
                        }
                    }
                }

                $tbCols = array(
                   _M('Número do exemplar', $this->module),
                   _M('Está confirmado', $this->module),
                );

                $table = new MTableRaw(NULL, $tbData, $tbCols,true);
                $table->addAttribute('width', '100%');

                //cria as ações

                //mostra detalhes do material
                $action = array();
                $actWorkDetail = new MImageLink('lnkWorkDetail2', NULL, "javascript:" . GUtil::getAjax('openMaterialDetail', $controlNumber), GUtil::getImageTheme('config-16x16.png'));
                $actWorkDetail->setAttribute('title', _M('Detalhe', $this->module));
                $action[] = $actWorkDetail;

                //historico
                $url = 'javascript:'.GUtil::getAjax('showHistory', $info->reserveId);
                $actHistory = new MImageLink('lnkShowReserveStatusHistory', NULL, $url, GUtil::getImageTheme('reserve-16x16.png'));
                $actHistory->setAttribute('title', _M('Histórico de estados da reserva', $this->module));
                $action[] = $actHistory;

                //botão de cancelar reserva
                if ( GPerms::checkAccess('gtcMaterialMovementCancelReserve', NULL, FALSE ) )
                {
                    $url = 'javascript:'.GUtil::getAjax('cancelReserve', $info->reserveId);
                    $actCancel = new MImageLink('cancelReserve'.$info->reserveId, NULL, $url, GUtil::getImageTheme('cancel-16x16.png'));
                    $actCancel->setAttribute('title', _M('Cancelar reserva', $this->module));
                    $action[] = $actCancel;
                }
                
                //Posição do usuário na lista de reservas    
                $position = $busReserve->getReservePosition($info->reserveId);
                    
                if ($position[0][0] == 0)
                {
                    $queuePosition = 'Aguardando retirada'; //Quando reserva atendida
                }
                else
                {
                    $queuePosition = $position[0][0] . 'º'; //Quando reserva solicitada, retorna a posição
                }

                $requestDate = new GDate($info->requestedDate);
                $limitDate = new GDate($info->limitDate);
                $reserveArray[] = array(
                                            new GContainer('hctActions', $action),
                                            $table,
                                            $this->busSearchFormat->getFormatedString($controlNumber, MATERIAL_MOVIMENT_SEARCH_FORMAT_ID, 'search'),
                                            $queuePosition,
                                            $info->reserveStatus,
                                            $requestDate->getDate(GDate::MASK_TIMESTAMP_USER),
                                            $limitDate->getDate(GDate::MASK_DATE_USER),
                                            $info->reserveId,
                                            $info->reserveType,
                                            $info->libraryUnit,
                                       );
            }
        }

        $reserveTitles = array
        (
            _M('Ação', $module),
            _M('Composição', $module),
            _M('Dados', $module),
            _M('Posição na fila', $module),
            _M('Estado', $module),
            _M('Data da requisição', $module),
            _M('Data limite', $module),
            _M('Código da reserva', $module),
            _M('Tipo', $module),
            _M('Unidade de biblioteca', $module),
        );

        $table = new MTableRaw(NULL, $reserveArray , $reserveTitles, 'currentReserve',true);
        $table->addStyle('width', '100%');

		if ($args->return)
		{
			return $table;
		}

		$this->setTableContent($table);
	}

	public function cancelReserve($reserveId)
	{
		$args = (object) $_REQUEST;
        $busOperationReserve = $this->MIOLO->getBusiness($this->module, 'BusOperationReserve');
        $ok = $busOperationReserve->cancelReserve( $reserveId );

        if ( !$ok )
        {
            $this->injectContent( $busOperationReserve->getMessagesTableRaw()->generate(),true, _M('Cancelamento de reserva', 'gnuteca3') );
        }
        else
        {
            //Criado o information manualmente para executar o ajax quando for pressionado ESC
            $button= new MButton('btnYes', _M( 'Confirmar','gnuteca3' ), GPrompt::parseGoto(GUtil::getCloseAction(true) . GUtil::getAjax('onkeydown114')) , GUtil::getImageTheme( 'accept-16x16.png') );
            $button->addAttribute('onblur', "gnuteca.setFocus('popupTitle');"); //faz com que o foco volte ao título da janela

            $prompt = new GPrompt( _M('Informação', 'gnuteca3'),  _M('Reserva cancelada com sucesso.', $this->module));
            $prompt->setType( GPrompt::MSG_TYPE_INFORMATION);
            $prompt->addButton($button);
            $prompt->addButton($close = GForm::getCloseButton(GUtil::getAjax('onkeydown114')));
            $close->addStyle('display', 'none');
            self::injectContent( $prompt ,false, false);
        }
	}


	public function showHistory($reserveId)
	{
        $reserveId = GUtil::getAjaxEventArgs();
        $busReserveStatusHistory  = $this->MIOLO->getBusiness($this->module, 'BusReserveStatusHistory');
		$busReserveStatusHistory->reserveId = $reserveId;
		$tbData = $busReserveStatusHistory->searchReserveStatusHistory();
        
		$tbColumns = array(
		  _M('Estado da reserva', $this->module),
		  _M('Data', $this->module),
		  _M('Operador', $this->module),
		);
        
		$fields[] = $table = new MTableRaw(NULL, $tbData, $tbColumns,'',true);
		$table->addAttribute('style', 'width:100%;');

		$this->injectContent($fields,true, _M('Histórico de Reservas','gnuteca3'));
	}

	/**
	 * Fine
	 *
	 * @param object $args
	 */
	public function onkeydown115($args)
	{
        $module = MIOLO::getCurrentModule();
        $this->changeButtonFocus('btnFine2');

        if ( !$args->personIdW)
        {
            return $this->setTableContent($table);
        }
        
        $busFine = $this->MIOLO->getBusiness($this->module, 'BusFine');
        $busFine->personId      = $args->personIdW;
        $busFine->fineStatusIdS = ID_FINESTATUS_OPEN;
        $fines = $busFine->searchFine(true, null, true);

        $fineArray = null;

        if ( $fines && is_array($fines) )
        {
            $loanDate           = new GDate($info->loan->loanDate);
            $returnForecastDate = new GDate($info->loan->returnForecastDate);
            $returnDate         = new GDate($info->loan->returnDate);
            $beginDate          = new GDate($info->beginDate);
            $endDate            = new GDate($info->endDate);

            foreach ($fines as $line => $info)
            {
                $loanDate           = new GDate($info->loan->loanDate);
                $returnForecastDate = new GDate($info->loan->returnForecastDate);
                $returnDate         = new GDate($info->loan->returnDate);
                $beginDate          = new GDate($info->beginDate);
                $endDate            = new GDate($info->endDate);
                
                if ($info->loan->controlNumber)
                {
                   $data = $this->busSearchFormat->getFormatedString($info->loan->controlNumber, MATERIAL_MOVIMENT_SEARCH_FORMAT_ID, 'search');
                }
                
                $fineArray[] = array(
                   $info->loanId,
                   $info->loan->itemNumber,
                   $data,
                   $loanDate->getDate(GDate::MASK_TIMESTAMP_USER),
                   $returnForecastDate->getDate(GDate::MASK_DATE_USER),
                   $returnDate->getDate(GDate::MASK_TIMESTAMP_USER),
                   $beginDate->getDate(GDate::MASK_DATE_USER),
                   GUtil::moneyFormat($info->value),
                   $info->description,
                   $endDate->getDate(GDate::MASK_DATE_USER)

                                    );
            }
        }

        $fineTitles = array(
                        _M('Código do empréstimo',       $module),
                        _M('Número do exemplar',       $module),
                        _M('Dados', $module),
                        _M('Data do empréstimo',       $module),
                        _M('Data prevista da devolução',       $module),
                        _M('Data de devolução',       $module),
                        _M('Data inicial',  $module),
                        _M('Valor',  $module),
                        _M('Estado',  $module),
                        _M('Data final',  $module)
                           );

        $table = new MTableRaw(NULL, $fineArray , $fineTitles, 'currentFine',true);
        
        $this->setTableContent($table);
	}

	/**
	 * Penalidade
	 *
	 * @param unknown_type $args
	 */
	public function onkeydown116($args)
	{
        $module = MIOLO::getCurrentModule();
        $this->changeButtonFocus('btnPenalty2');

        if ( !$args->personIdW)
        {
            return $this->setTableContent($table);
        }
        
        $busPenalty = $this->MIOLO->getBusiness($this->module, 'BusPenalty');
        $busPenalty ->personIdS = $args->personIdW;
        $busPenalty->onlyActive = true;
        $penaltys = $busPenalty->searchPenalty();
        $penaltyArray = null;

        if ( $penaltys && is_array( $penaltys ) )
        {
            foreach ($penaltys as $line => $info)
            {
                //Só mostrar penalidades com data final igual ou maior que hoje ou quando estiver em branco
                $penaltyArray[] = array( 
                    $info[0] , 
                    $info[3], 
                    GDate::construct($info[5]),
                    GDate::construct($info[6]),
                    $info[7], 
                    $info[8]
                    );
            }
        }

        $penaltyTitles = array(
            _M('Código da penalidade',  $module),
            _M('Observação',  $module),
            _M('Data',  $module),
            _M('Data final',  $module),
            _M('Operador',  $module),
            _M('Biblioteca', $module),
            );

        $this->setTableContent( new MTableRaw(NULL, $penaltyArray , $penaltyTitles, 'currentPenalty',true) );
	}

	/**
	 * Profile
	 *
	 * @param object $args
	 */
	public function onkeydown117($args)
	{
        $this->changeButtonFocus('btnProfile2');
        
        if ( !$args->personIdW)
        {
            return $this->setTableContent($table);
        }
        
        $busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $busPersonConfig = $this->MIOLO->getBusiness($this->module, 'BusPersonConfig');

        $data = $busPersonConfig->getCompleteInfoForPersonConfig($args->personIdW);
        $table = new MTableRaw( NULL, $data , array( _M('Parâmetro', $this->module) , _M('Valor', $this->module) ), 'currentProfile',true);
        $table->addStyle('width','100%');

        $person = $busBond->getAllPersonLink($args->personIdW);
        $data = array();

        foreach ( (array)$person as $v )
        {
            $data[] = array( $v->description, GDate::construct( $v->dateValidate ) );
        }

        $cols = array( _M('Vínculo', $this->module), _M('Data de validade', $this->module) );

        $tableBond = new MTableRaw( NULL, $data, $cols, 'currentBond',true);
        $tableBond->addStyle('width','100%');

        $table = new MDiv('divTables', array($table, new MLabel( _M('Vínculo', 'gnuteca3') ), $tableBond));

        $this->setTableContent($table);
	}


	/**
	 * [F7] - Policy
	 */
	public function onkeydown118($args)
	{
        $this->changeButtonFocus('btnPolicy2');
        
	    if ( !$args->personIdW)
        {
            return $this->setTableContent($table);
        }
        
        $busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $busPolicy = $this->MIOLO->getBusiness($this->module, 'BusPolicy');
        $linkId = $busBond->getPersonLink($args->personIdW)->linkId;
        $policy = $busPolicy->getUserPolicy(NULL, $args->personIdW, $linkId);
        $data = array();

        if ( !empty($policy[0]) && strlen($linkId) > 0 )
        {
            foreach ($policy as $v)
            {
                $data[] = array(
                    $v->privilegeGroup,
                    $v->link,
                    $v->materialGender,
                    GDate::construct( $v->loanDate ),
                    $v->loanDays,
                    $v->loanLimit,
                    $v->renewalLimit,
                    $v->renewalWebLimit,
                    GUtil::moneyFormat($v->fineValue),
                    GUtil::moneyFormat($v->momentaryFineValue),
                    $v->reserveLimit,
                    $v->daysOfWaitForReserve,
                    $v->reserveLimitInInitialLevel,
                    $v->daysOfWaitForReserveInInitialLevel,
                    GUtil::getYesNo($v->renewalWebBonus),
                    $v->additionalDaysForHolidays
                );
            }
        }
        
        $cols = array(
            _M('Grupo de privilégio', $this->module),
            _M('Vínculo', $this->module),
            _M('Gênero do material', $this->module),
            _M('Data do empréstimo', $this->module),
            _M('Dias de empréstimo', $this->module),
            _M('Limite de empréstimo', $this->module),
            _M('Limite de renovações', $this->module),
            _M('Limite de renovações web', $this->module),
            _M('Valor da multa', $this->module),
            _M('Valor da multa momentânea por @1.', $this->module,(LOAN_MOMENTARY_PERIOD == 'H' )? 'hora':'dia'),
            _M('Limite de reserva', $this->module),
            _M('Dias de espera por reserva', $this->module),
            _M('Limite de reserva de nível inicial', $this->module),
            _M('Dias de espera por reserva no nível inicial', $this->module),
            _M('Bônus de renovações web', $this->module),
            _M('Adicional de dias para feriado', $this->module),
            );

        $table = new MTableRaw(NULL, $data, $cols, 'currentPolicy',true);

        //Políticas gerais
        $busGeneralPolicy = $this->MIOLO->getBusiness($this->module, 'BusGeneralPolicy');
        $policy = $busGeneralPolicy->getGeneralPolicy($v->privilegeGroupId, $linkId);

        //Só mostrar tabela se existir políticas gerais para o grupo
        if ($policy->linkId)
        {
                $data = array();
                $data[] = array(
                    $policy->privilegeGroupDescription,
                    $policy->linkDescription,
                    $policy->loanGeneralLimit,
                    $policy->reserveGeneralLimit,
                    $policy->reserveGeneralLimitIninitialLevel
                );

                $cols = array(
                    _M('Grupo de privilégio', $this->module),
                    _M('Vínculo', $this->module),
                    _M('Limite de empréstimo', $this->module),
                    _M('Limite de reserva', $this->module),
                    _M('Limite de reserva de nível inicial', $this->module),
                );

                $tableGeneral = new MTableRaw(NULL, $data, $cols, 'currentPolicy',true);
                $tableGeneral->addStyle('width', '100%');

                $table = new MDiv('divTables', array($table, new MLabeL( _M('Geral','gnuteca3') ) , $tableGeneral));
        }
        
        $this->setTableContent($table);
	}
    
    /**
     * Define o conteúdo da tabela, usando ajax
     * 
     * @param object $table 
     */
    public function setTableContent( $table )
    {
        if ( ! MIOLO::_REQUEST('personIdW') )
        {
            $table = _M('Por favor, selecione uma pessoa válida.', $this->module);
        }
        
        if ( $table instanceof MTableRaw )
        {
            $table->addStyle('width','100%');
        }
        
        $this->setResponse( $table ,'divVerifyUserContent');
    }
    
    /**
     * F8 Nada consta
     * 
     * @param stdClass $args 
     */
    public function onkeydown119($args)
	{
        $this->changeButtonFocus('btnNadaConsta');
        
        $busPerson = $this->MIOLO->getBusiness($this->module, 'BusPerson');
        
        $nothingInclude = $busPerson->nothingInclude($args->personIdW);
        
        if ( $nothingInclude )
        {
            $controls[] = new MLabel( _M( 'Nada consta.' ,'gnuteca3') );
            $controls[] = new GSelection('tipo',null, _M('Tipo', 'gnuteca3'), BusinessGnuteca3BusDomain::listForSelect('TIPO_RECIBO_NADA_CONSTA') ,null,null,null, true);
            $controls[] = new MButton('generateReceipt', _M('Gerar recibo','gnuteca3'), GUtil::getAjax('generateReceipt'), GUtil::getImageTheme('print-16x16.png'));
            $table = new MFormContainer('', $controls );
        }
        else
        {
            $restrictions = $busPerson->getRestrictions($args->personIdW);
            $controls[] = _M('Pessoa possui restrições','gnuteca3'). ':';
            $controls[] = $table = new MTableRaw( _M('Restrições','gnuteca3'), $restrictions, array( _M('Tipo','gnuteca3'),_M('Quantidade','gnuteca3') ), 'tableRestriction', true) ;
            $table->addStyle('width','100%');
            
            $table = new MDiv('', $controls );
        }
        
        $this->setTableContent( $table );
    }
    
    /**
     * Gera recibo de nada consta
     * 
     * @param stdClass $args 
     */
    public function generateReceipt($args)
    {
        $this->MIOLO->uses('/classes/receipt/GnutecaReceipt.class.php', 'gnuteca3');
        $this->MIOLO->uses('/classes/receipt/NothingIncludeReceipt.class.php', 'gnuteca3');

        $busDomain = $this->MIOLO->getBusiness($this->module, 'BusDomain');
        $busLoan   = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        
        $busDomain->keyS = $args->tipo;
        
        $args->tipo = $busDomain->searchDomain(); 
        $args->tipo = $args->tipo[0][4]; //Pega o label do dominio.
        
        // Conta os empréstimos em aberto
        $loanOpen = $busLoan->getLoanOpenByPerson($args->personIdW);
        $countLoanOpen = $loanOpen ? count($loanOpen) : 0;

        $data->personId = $args->personIdW;
        $data->librayUnitId = GOperator::getLibraryUnitLogged();
        $data->libraryName = GOperator::getLibraryNameLogged();
        $data->tipo = $args->tipo;
        $data->emaberto = $countLoanOpen;

        $receipt = new NothingIncludeReceipt($data);
        $receipt->setModel(NOTHING_RECEIPT);
        $content = $receipt->generate();

        $table = new MDiv('receiptBox','<pre>'.$content.'</pre>');
            
        $receipt = new GnutecaReceipt();
        $receipt->sendPrintServer( $content );
        
        $this->setTableContent($table);
    }

    /*Não funciona*/
    public function onkeydown27()
    {
        $this->setResponse('', 'divResponse');
    }

    /**
     * para evitar erros indesejados compatibilidade com php 5.2
     * @return <type>
     */
    public function __toString()
    {
        return '';
    }
}
?>
