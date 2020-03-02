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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandro@solis.coop.br]
 *
 * @since
 * Class created on 12/01/2009
 *
 **/
class FrmMaterialCirculationUserHistory extends FrmMaterialCirculationReserve
{
	public $busLibraryUnit;

	public function __construct()
	{
		$this->MIOLO = MIOLO::getInstance();
		$this->module = MIOLO::getCurrentModule();
		$this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
		parent::__construct();
	}

	public function searchFunctionCirculation($args)
	{
		$this->setResponse( $this->_getGrid($args), GForm::DIV_SEARCH );
	}

    public function searchFunction($args)
    {
    	if (MIOLO::_REQUEST('isVerifyMaterial')) //Quando estiver no F9
    	{
    		parent::searchFunction($args);
    	}
    	else
    	{
	        if (is_numeric($args))
	        {
	            $_REQUEST['isSearchFunctionCirculation'] = true;
	        }
            
	        $args = GForm::corrigeEventosGrid($args);
	        $this->searchFunctionCirculation($args);
    	}
    }

	/**
	 * Mount the user History form.
	 * Called when user press F11
	 **/
	public function onkeydown122($args) //F11
	{
		$module = MIOLO::getCurrentModule ();

		$this->setMMType( '122' );
		$this->changeTab( 'btnAction122' );
        $this->jsSetFocus( 'personIdS' );
        //$this->jsChangeButtonColor( 'btnLoan' );

		//Só tem acesso se tiver permissão gtcMaterialMovementUserHistory
		if (! GPerms::checkAccess ( 'gtcMaterialMovementUserHistory', NULL, false ))
		{
			$this->setResponse ( '', 'limbo' ); //resposta vazia para nada
			return;
		}

		$functionButtons [] = new MButton( 'btnLoan', _M( '[F2] Empréstimo', $module ), ':onkeydown113' );
		$functionButtons [] = new MButton( 'btnReserve', _M( '[F3] Reserva', $module ), ':onkeydown114' );
		$functionButtons [] = new MButton( 'btnFine', _M( '[F4] Multa', $module ), ':onkeydown115' );
		$functionButtons [] = new MButton( 'btnPenalty', _M( '[F5] Penalidade', $module ), ':onkeydown116' );
		$functionButtons [] = new MButton( 'btnCleanData', _M( '[ESC] Limpar', $module ), ':onkeydown27' );

		$fields [] = $this->getButtonContainer ( $functionButtons );

        $lookup[] = new MTextField ( 'personName', null, null, 35 , null, null, true);
        $fields[] = $lookup = new GLookupField('personIdS', null, _M('Pessoa', $module ), 'Person', $lookup );
        $lookup->lookupTextField->addAttribute( 'onPressEnter', GUtil::getAjax('definePerson') );
		$fields[] = new MHiddenField( 'notShowAction', 'true' );
		$fields [] = new MSeparator( );
		$fields [] = new MDiv( 'divGetForm' );
		$divVerifyUser = new MDiv( 'divVerifyUser', $fields );

		return $this->addResponse ( $divVerifyUser, $args );
	}

    /**
     * Limpa o formulário
     *
     * @param stdClass $args
     */
	public function cleanData122($args)
	{
		$this->jsSetValue( 'personIdS', '' );
		$this->jsSetValue( 'personName', '' );
		$this->jsSetFocus( 'personIdS' );
		$this->jsSetInner( 'divGetForm', '' );
	}

	public function onkeydown113_122($args)
	{
		$this->jsChangeButtonColor ( 'btnLoan' );
		$this->getHistoryFields( 'loan', $args );
	}

	public function onkeydown114_122($args)
	{
		$this->jsChangeButtonColor ( 'btnReserve' );
		$this->getHistoryFields('reserve', $args );
	}

	public function onkeydown115_122($args) {
		$this->jsChangeButtonColor ( 'btnFine' );
		$this->getHistoryFields('fine', $args );
	}

	public function onkeydown116_122($args)
	{
		$this->jsChangeButtonColor ( 'btnPenalty' );
		$this->getHistoryFields('penalty', $args );
	}

	/**
	 * Ao apertar Enter no código da Pessoa
	 *
	 * @param $args
	 * @return unknown_type
	 */
	public function definePerson($args)
	{
            if(GSipCirculation::usingSmartReader() && $args->personIdS)
            {
                //Instancia objeto pessoa
                $busPerson = $this->MIOLO->getBusiness($this->module, 'BusPerson');

                //Obtem a informação passada, como ID ou código do cartão
                $codUser = $args->personIdS;

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

                    $idPessoa = $this->busPerson->getPersonIdByLogin($codUser);
                    $codUserReal = $idPessoa[0][0];
                    $args->personIdS = $codUserReal;
                    
                    $this->jsSetValue('personIdS', $args->personIdS);
                    $this->page->onload("setTimeout('lookup___mainForm_personIdS.start(true);',100);");
                    $this->page->onload("miolo.doAjax('definePerson', '', '__mainForm');");
                    $this->setResponse(NULL, 'limbo');
                    return;
                }
            }
            
            if (GPerms::checkAccess ( 'gtcMaterialMovementUserHistory', NULL, false ))
            {
		$this->jsChangeButtonColor ( 'btnLoan' );
		$this->getHistoryFields( 'loan', $args );

                $_REQUEST['pn_page'] = 1; //Define a paginacao inicial
		$_POST['function'] = 'search'; //existe um caso onde a função é mantida de outro form, e neste caso precisamos que ela seja search sempre
            }
            else
            {
		$this->setResponse ( '', 'limbo' );
            }
	}

	public function getHistoryFields($name, $args)
	{
		$_SESSION['getForm'] = $name;
		$_POST['function']   = 'search';

		$MIOLO = MIOLO::getInstance ();
		$module = MIOLO::getCurrentModule ();

		if (! $args->personIdS)
		{
            $this->jsSetValue( 'personIdS', '' );
			$this->jsSetValue( 'personName', '' );
			$this->jsSetFocus( 'personIdS' );
            $this->error(_M('Por favor selecione uma pessoa válida.', $module ));
            return;
		}
		else
		{
			$_SESSION ['personId'] = $args->personIdS;
			switch($name)
			{
				case 'loan';                                
                                $args->libraryUnitIdS = '';
				$busName  = 'BusLoan';
				$gridName = 'GrdMyLoan';
				$searchMethod = 'searchLoan';
				$fields = $this->getFieldsLoan($args);
				break;
				case 'reserve';
                                $args->libraryUnitIdS = '';
                                $args->itemNumberS = '';
                        	$busName  = 'BusReserve';
				$gridName = 'GrdReservesHistory';
				$searchMethod = 'searchReserve';
				$fields = $this->getFieldsReserve($args);
				break;
				case 'fine';                                                 
                                $args->libraryUnitIdS = '';
				$busName  = 'BusFine';
				$gridName = 'GrdMyFine';
				$searchMethod = 'searchFine';
				$fields = $this->getFieldsFine($args);
				break;
				case 'penalty';               
                                $args->libraryUnitIdS = '';                                
                                $args->itemNumberS = '';
				$busName  = 'BusMyPenalty';
				$gridName = 'GrdMyPenalty';
				$searchMethod = 'searchPenalty';
				$fields = $this->getFieldsPenalty($args);
				break;
			}
		}

		$_SESSION['circulationUserHistory'] = array( 'busName' => $busName, 'gridName' => $gridName,'searchMethod' => $searchMethod );

		if ($fields)
		{
			$fields[] = $this->getSearchButton();
			$fields[] = new MDiv( GForm::DIV_SEARCH, $this->_getGrid($args) );
			$fields = array( new MContainer('hct28228', $fields, null, MControl::FORM_MODE_SHOW_SIDE));
		}
        
        $this->page->onload('dojo.parser.parse();');
		$this->setResponse ( $fields, 'divGetForm' );
	}

	public function getFieldsLoan($args = null)
	{
		$this->busLibraryUnit->onlyWithAccess  = true;
		$fields[] = new GSelection('libraryUnitIdS',   $this->libraryUnitIdS->value, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnitForSearch(false, true) , null,null, null, true);
		$fields[] = new MTextField('itemNumberS', $args->itemNumberS, _M('Número do exemplar',$this->module), FIELD_DESCRIPTION_SIZE);

		$lblDate             = new MLabel(_M('Data do empréstimo', $this->module) . ':');
		$lblDate->setWidth(FIELD_LABEL_SIZE);
		$beginLoanDateS     = new MCalendarField('beginLoanDateS', $this->beginLoanDateS->value, null, FIELD_DATE_SIZE);
		$endLoanDateS       = new MCalendarField('endLoanDateS', $this->endLoanDateS->value, null, FIELD_DATE_SIZE);
		$fields[] = new GContainer('hctDates', array($lblDate, $beginLoanDateS, $endLoanDateS));

		$lblDate                = new MLabel(_M('Data prevista da devolução', $this->module) . ':');
		$lblDate->setWidth(FIELD_LABEL_SIZE);
		$beginReturnForecastDateS     = new MCalendarField('beginReturnForecastDateS', $this->beginReturnForecastDateS->value, null, FIELD_DATE_SIZE);
		$endReturnForecastDateS       = new MCalendarField('endReturnForecastDateS', $this->endReturnForecastDateS->value, null, FIELD_DATE_SIZE);
		$fields[] = new GContainer('hctDates', array($lblDate, $beginReturnForecastDateS, $endReturnForecastDateS));

		$lblDate              = new MLabel(_M('Data de devolução', $this->module) . ':');
		$lblDate->setWidth(FIELD_LABEL_SIZE);
		$beginReturnDateS     = new MCalendarField('beginReturnDateS', $this->beginReturnDateS->value, null, FIELD_DATE_SIZE);
		$endReturnDateS       = new MCalendarField('endReturnDateS', $this->endReturnDateS->value, null, FIELD_DATE_SIZE);
		$fields[] = new GContainer('hctDates', array($lblDate, $beginReturnDateS, $endReturnDateS));

		return $fields;
	}

	public function getFieldsReserve($args = null)
	{
		$this->busLibraryUnit->onlyWithAccess  = true;
		$fields[] = new GSelection('libraryUnitIdS',   $this->libraryUnitIdS->value, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnitForSearch(false, true) , null,null, null, true);

		$lblDate             = new MLabel(_M('Data da requisição', $this->module) . ':');
		$lblDate->setWidth(FIELD_LABEL_SIZE);
		$beginRequestedDateS_DATE     = new MCalendarField('beginRequestedDateSDate', $this->beginBeginDateS->value, null, FIELD_DATE_SIZE);
		$endRequestedDateS_DATE       = new MCalendarField('endRequestedDateSDate', $this->endRequestedDateS_DATE->value, null, FIELD_DATE_SIZE);
		$fields[] = new GContainer('hctDates', array($lblDate, $beginRequestedDateS_DATE, $endRequestedDateS_DATE));
		$validators[] = new MDateDMYValidator('beginRequestedDateSDate');

		$lblDate             = new MLabel(_M('Data limite', $this->module) . ':');
		$lblDate->setWidth(FIELD_LABEL_SIZE);
		$beginLimitDateS     = new MCalendarField('beginLimitDateS', $this->beginLimitDateS->value, null, FIELD_DATE_SIZE);
		$endLimitDateS       = new MCalendarField('endLimitDateS', $this->endLimitDateS->value, null, FIELD_DATE_SIZE);
		$fields[] = new GContainer('hctDates', array($lblDate, $beginLimitDateS, $endLimitDateS));
		$validators[] = new MDateDMYValidator('beginLimitDateS');
		$fields[] = new GSelection('reserveStatusIdS', null, _M('Estado da reserva', $this->module), $this->busReserveStatus->listReserveStatus());

		return $fields;
	}

	public function getFieldsFine($args = null)
	{
                $busFineStatus = $this->MIOLO->getBusiness('gnuteca3', 'BusFineStatus');
		$this->busLibraryUnit->onlyWithAccess  = true;
		$fields[] = new GSelection('libraryUnitIdS',   $this->libraryUnitIdS->value, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnitForSearch(false, true) , null,null, null, true);
		$fields[] = new MTextField('itemNumberS', $args->itemNumberS, _M('Número do exemplar',$this->module), FIELD_DESCRIPTION_SIZE);
		$fields[] = new MTextField('valueS', $this->observationS->value, _M('Valor',$this->module), FIELD_DESCRIPTION_SIZE);
		$fields[] = new GSelection('fineStatusIdS',   $this->libraryUnitIdS->value, _M('Estado da multa', $this->module), $busFineStatus->listFineStatus());

		$lblDate             = new MLabel(_M('Data inicial', $this->module) . ':');
		$lblDate->setWidth(FIELD_LABEL_SIZE);
		$beginBeginDateS     = new MCalendarField('beginBeginDateS', $this->beginBeginDateS->value, null, FIELD_DATE_SIZE);
		$endBeginDateS       = new MCalendarField('endBeginDateS', $this->endBeginDateS->value, null, FIELD_DATE_SIZE);
		$fields[] = new GContainer('hctDates', array($lblDate, $beginBeginDateS, $endBeginDateS));
		$validators[] = new MDateDMYValidator('beginBeginDateS');

		$lblDate             = new MLabel(_M('Data final', $this->module) . ':');
		$lblDate->setWidth(FIELD_LABEL_SIZE);
		$beginEndDateS     = new MCalendarField('beginEndDateS', $this->beginEndDateS->value, null, FIELD_DATE_SIZE);
		$endEndDateS       = new MCalendarField('endEndDateS', $this->endEndDateS->value, null, FIELD_DATE_SIZE);
		$fields[] = new GContainer('hctDates', array($lblDate, $beginEndDateS, $endEndDateS));
		$validators[] = new MDateDMYValidator('beginEndDateS');

		return $fields;
	}

	public function getFieldsPenalty($args = null)
	{
		$this->busLibraryUnit->onlyWithAccess  = true;
		$fields[] = new GSelection('libraryUnitIdS',   $this->libraryUnitIdS->value, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnitForSearch(false, true) , null,null, null, true);
		$fields[] = new MTextField('observationS', $this->observationS->value, _M('Observação',$this->module), FIELD_DESCRIPTION_SIZE);
		$lblDate             = new MLabel(_M('Data da penalidade', $this->module) . ':');
		$lblDate->setWidth(FIELD_LABEL_SIZE);
		$beginBeginPenaltyDateS     = new MCalendarField('beginBeginPenaltyDateS', $this->beginBeginPenaltyDateS->value, null, FIELD_DATE_SIZE);
		$endBeginPenaltyDateS       = new MCalendarField('endBeginPenaltyDateS', $this->endBeginPenaltyDateS->value, null, FIELD_DATE_SIZE);
		$fields[] = new GContainer('hctDates', array($lblDate, $beginBeginPenaltyDateS, $endBeginPenaltyDateS));
		$validators[] = new MDateDMYValidator('beginBeginPenaltyDateS');

		$lblDate             = new MLabel(_M('Data final da penalidade', $this->module) . ':');
		$lblDate->setWidth(FIELD_LABEL_SIZE);
		$beginEndPenaltyDateS     = new MCalendarField('beginEndPenaltyDateS', $this->beginEndPenaltyDateS->value, null, FIELD_DATE_SIZE);
		$endEndPenaltyDateS       = new MCalendarField('endEndPenaltyDateS', $this->endEndPenaltyDateS->value, null, FIELD_DATE_SIZE);
		$fields[] = new GContainer('hctDates', array($lblDate, $beginEndPenaltyDateS, $endEndPenaltyDateS));
		$validators[] = new MDateDMYValidator('beginEndPenaltyDateS');

		return $fields;
	}

	public function getSearchButton()
	{
		$btnSearch = new MButton('btnSearch', _M('BUSCAR', $this->module), ':searchFunctionCirculation', GUtil::getImageTheme('search-16x16.png'));
		$btnSearch = new MDiv('btnSearchEx', $btnSearch );
		$fields[]  = $btnSearch;
		$fields[]  = new MSeparator();
		return new MVContainer('vct828282', $fields);
	}

	public function _getGrid($args)
	{
		$info = $_SESSION['circulationUserHistory'];

		if ($info['busName'])
		{
			$business = $this->MIOLO->getBusiness( $this->module, $info['busName'] );
			$business->setData($args);
		}

		if ($info['gridName'])
		{
			$grid = $this->MIOLO->getUI()->getGrid($this->module, $info['gridName']);
            
			if ($business && $info['searchMethod'])
			{
				$grid->setData( call_user_method($info['searchMethod'], $business ) );
			}
            
			return $grid;
		}
	}
}

?>