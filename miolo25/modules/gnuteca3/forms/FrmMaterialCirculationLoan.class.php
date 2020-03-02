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
 * Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 12/01/2009
 *
 * */

$MIOLO->getClass('gnuteca3', 'GSipCirculation');
$MIOLO->getClass('gnuteca3', 'RFID');
$MIOLO->getClass('gnuteca3', 'BiometrySession');

class FrmMaterialCirculationLoan extends FrmSimpleSearch
{
    public $busFine;
    public $busPenalty;
    public $frmMaterialMovement;
    public $busOperationReturn;
    public $busOperationChangeStatus;
    public $busMaterial;
    public $busOperationReserve;
    public $sendLoanMailReceipt;
    public $receiptText;
    public $returnTypeDescription;

    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $this->busFine = $MIOLO->getBusiness($module, 'BusFine');
        $this->busPenalty = $MIOLO->getBusiness($module, 'BusPenalty');
        $this->busOperationReturn = $MIOLO->getBusiness($module, 'BusOperationReturn');
        $this->busOperationChangeStatus = $MIOLO->getBusiness($module, 'BusOperationChangeStatus');
        $this->busOperationReserve = $MIOLO->getBusiness($module, 'BusOperationReserve');

        $options = new StdClass();
        $options->noDefineFields = true;
        
        $materialMovementReturn = FALSE;
        
        // Quando operador não tiver permissão de empréstimo, carregar automáticamente Devolução.
        if ( (!$this->checkAcces('gtcMaterialMovementLoan')) && ( $_SESSION[MaterialMovementType] == 118 || $this->checkAcces('gtcMaterialMovementReturn') ) )
        {
            $this->setMMOperation('RETURN');
            $materialMovementReturn = TRUE;
        }
        
        parent::__construct($options);
        
        // Evita repetição de finalização quando uma finalização de operação está em aberto.
        $this->page->onload("
            dojo.disconnect(gnuteca.evento_keydown); dojo.connect( document, 'onkeydown', function(event) { 

            // pega a tecla e bota na variavel, trata por navegador
            if ( window.event ) // IE
            {
                    keycode = event.keyCode;
            }
            else if ( event.which ) // Netscape/Firefox/Opera
            {
                    keycode = event.which;
            }
            
            if ( keycode == 115 )
            {
                if ( gnuteca.bloqueia_tecla == false  )
                {
                    if ( dojo.byId('codItensFinalize') != null )
                    {
                        if ( dojo.byId('codItensFinalize').value == '' )
                        {
                            gnuteca.bloqueia_tecla = true;
                            gnuteca.keydown(event);
                        }
                    }
                    else
                    {
                        gnuteca.bloqueia_tecla = true;
                        gnuteca.keydown(event);
                    }
                }
            }
            else
            {
                gnuteca.keydown(event);
            }
        });");
        

        // Altera demais componentes no formulário quando for devolução.
        if (  $materialMovementReturn )
        {
            $this->jsChangeButtonColor('btnReturn');
            $this->jsSetFocus('itemNumber');
        }
    }

    /**
     * Verifica permissão especificamente para circulação de material
     * //FIXME como o miolo foi modificado (permissões na sessão) possivelmente isso não é mais necessário
     *
     * @param $transaction
     * @param $function
     * @return boolean
     */
    public function checkAcces($transaction, $function = NULL )
    {
        //executa sql de permissões caso não encontre no objeto
        if ( !$this->perms )
        {
            $this->getPermissions();
        }

        return in_array($transaction, $this->perms);
    }

    /**
     * Verifica todas as permissões de uma só vez define permissoes no objeto do form
     * //FIXME como o miolo foi modificado (permissões na sessão) possivelmente isso não é mais necessário
     *
     * @return array de permissões
     */
    public function getPermissions()
    {
        $permsToVerify[] = 'gtcMaterialMovementLoan';
        $permsToVerify[] = 'gtcMaterialMovementReturn';
        $permsToVerify[] = 'gtcMaterialMovementRequestReserve';
        $permsToVerify[] = 'gtcMaterialMovementAnswerReserve';
        $permsToVerify[] = 'gtcMaterialMovementVerifyMaterial';
        $permsToVerify[] = 'gtcMaterialMovementVerifyUser';
        $permsToVerify[] = 'gtcMaterialMovementUserHistory';
        $permsToVerify[] = 'gtcMaterialMovementChangeStatus';
        $permsToVerify[] = 'gtcMaterialMovementVerifyProof';
        $permsToVerify[] = 'gtcMaterialMovementChangePassword';
        $permsToVerify[] = 'gtcMaterialMovementCheckPoint';
        $permsToVerify[] = 'gtcMaterialMovementCancelOperationProcess';
        $permsToVerify[] = 'gtcMaterialMovementChangeFine';
        $permsToVerify[] = 'gtcMaterialMovementChangeStatusNow';
        $permsToVerify[] = 'gtcMaterialMovementExemplaryFutureStatusDefined';

        //verifica todas as permissões de uma só vez
        //define permissoes no objeto do form
        $this->perms = GPerms::verifyAccess($permsToVerify);

        return $this->perms;
    }

    /**
     * Define o tipo da operação
     * 
     * @param String $type 
     */
    public function setMMType($type)
    {
        $_SESSION['MaterialMovementType'] = $type;
    }

    /**
     * Retorno o tipo da operação
     * 
     * @return String tipo da operação
     */
    public function getMMType()
    {
        $result = $_SESSION['MaterialMovementType'];

        if ( !$result )
        {
            $result = 118;
        }

        return strtoupper($result);
    }

    /**
     * Define a operação atual
     * 
     * @param String $op 
     */
    public function setMMOperation( $op )
    {
        $_SESSION['MaterialMovementOperation'] = $op;
    }

    /**
     * Retorna a operação da circulação de material
     * 
     * @return String
     */
    public function getMMOperation()
    {
        return strtoupper($_SESSION['MaterialMovementOperation']);
    }

    /**
     * Troca a aba selecionada
     * 
     * @param string $selectedTab 
     */
    public function changeTab($selectedTab)
    {
        $array = array(
            'btnAction118',
            'btnAction119',
            'btnAction120',
            'btnAction121',
            'btnAction122',
            'btnAction123',
            'verifyProof',
            'changePassword'
                );

        if ( is_array($array) )
        {
            foreach ( $array as $line => $info )
            {
                if ( $info != $selectedTab )
                {
                    $this->jsSetClass($info, '');
                }
            }
        }
        
        $this->jsSetClass($selectedTab, 'mToolbarButtonSelect');
    }

    /**
     * Função que monta o formulário principal
     *
     * @param stdClass $args
     */
    public function onkeydown118($args = NULL)
    {
        $this->setMMType('118');
        $this->changeTab('btnAction118');
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();      

        if ( $this->checkAcces('gtcMaterialMovementLoan') )
        {
            if( GSipCirculation::usingSmartReader() )
            {
                $functionButtons[] = new MButton('btnLoan', _M('[F2] Empréstimo', $module), "if ( sessaoLimpa == 1 || (listaExemplares.length == 0 && listaExemplaresReserva.length == 0 && listaExemplaresChange.length == 0 ) ) { miolo.doAjax('onkeydown113','','__mainForm'); } return false;");
            }
            else
            {
                $functionButtons[] = new MButton('btnLoan', _M('[F2] Empréstimo', $module), ':onkeydown113');
            }
        }

        if ( $this->checkAcces('gtcMaterialMovementReturn') )
        {
            if( GSipCirculation::usingSmartReader() )
            {
                $functionButtons[] = new MButton('btnReturn', _M('[F3] Devolução', $module), "if ( sessaoLimpa == 1 || (listaExemplaresReserva.length == 0 ) ) { miolo.doAjax('onkeydown114','','__mainForm'); } return false;");
            }
            else
            {
                $functionButtons[] = new MButton('btnReturn', _M('[F3] Devolução', $module), ':onkeydown114');
            }
        }

        if( GSipCirculation::usingSmartReader() )
        {
            $functionButtons[] = new MButton('btnFinalize', _M('[F4] Finalizar', $module), "if ( sessaoLimpa == 1 || (listaExemplares.length == 0 && listaExemplaresReserva.length == 0 && listaExemplaresChange.length == 0 ) ) { miolo.doAjax('onkeydown115','','__mainForm'); } return false;");
        }
        else
        {
            $functionButtons[] = new MButton('btnFinalize', _M('[F4] Finalizar', $module), ':onkeydown115');
        }

        if ( $this->checkAcces('gtcMaterialMovementCheckPoint') )
        {
            if( GSipCirculation::usingSmartReader() )
            {
                $functionButtons[] = new MButton('btnCheckpoint', _M('[F5] Checkpoint', $module), "if ( sessaoLimpa == 1 || (listaExemplares.length == 0 && listaExemplaresReserva.length == 0 && listaExemplaresChange.length == 0 ) ) { miolo.doAjax('onkeydown116','','__mainForm'); } return false;");
            }
            else
            {
                $functionButtons[] = new MButton('btnCheckpoint', _M('[F5] Checkpoint', $module), ':onkeydown116');
            }
        }

        if( GSipCirculation::usingSmartReader() )
        {
            $functionButtons[] = new MButton('btnCleanData', _M('[ESC] Limpar', $module), "if ( sessaoLimpa == 1 || (listaExemplares.length == 0 && listaExemplaresReserva.length == 0 && listaExemplaresChange.length == 0 ) ) { miolo.doAjax('onkeydown27','','__mainForm'); } return false;");
        }
        else
        {
            $functionButtons[] = new MButton('btnCleanData', _M('[ESC] Limpar', $module), ':onkeydown27');
        }

        $divLoanLeft[] = $this->getButtonContainer($functionButtons);
        $divLoanLeft[] = $this->getLibrarySelection();

        $busLocationForMaterialMovement = $MIOLO->getBusiness($module, 'BusLocationForMaterialMovement');

        $selections[] = new MLabel(_M('Local/Tipo', $module));
        $locationArgs->locationId = 1;
        $selections[] = $locationId = new GSelection('locationId', $locationArgs->locationId, null, $busLocationForMaterialMovement->listLocationForMaterialMovement(), null, null, null, true);
        $locationId->addAttribute('onchange', GUtil::getAjax('selectLocation'));
        $divLoanLeft[] = new MHiddenField('locationIdValue');
        $busOperationLoan = $MIOLO->getBusiness('gnuteca3', 'BusOperationLoan');
        $busOperationLoan->setLocation(1);//define o local padrão

        $busLoanType = $MIOLO->getBusiness($module, 'BusLoanType');

        $selections[] = $loanTypeId = new GSelection('loanTypeId', 1, null, $this->busLoanType->listLoanType(NULL, TRUE), null, null, null, true);
        $loanTypeId->addAttribute('onchange', "dojo.byId('contReturnForecastDate').style.display = ( dojo.byId('loanTypeId').value == " . ID_LOANTYPE_FORCED . ") ? 'block' : 'none';");

        $busReturnType = $MIOLO->getBusiness('gnuteca3', 'BusReturnType');
        $selections[] = $returnTypeId = new GSelection('returnTypeId', null, null, $busReturnType->listReturnType());
        $returnTypeId->addAttribute('onchange', GUtil::getAjax('selectReturnType'));
        $divLoanLeft[] = new GContainer('selectionContainer', $selections);

        $bases = BusinessGnuteca3BusAuthenticate::listMultipleLdap();

        if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) && (strlen(implode('', $bases)) > 0) )
        {
            $divLoanLeft[] = new GContainer('contBaseLdap', array( new MLabel(_M('Base:', $this->module)), new GSelection('baseLdap', '', null, $bases, false, '', '', true) ));
            $related = 'personId,personName,baseLdap';
        }
        else
        {
            $related = 'personId,personName';
        }

        if ( $this->checkAcces('gtcMaterialMovementLoan') )
        {
            $lFields[] = $personName = new MTextField('personName', null, null, 20, null, null, true);
            $personName->addStyle('width', '200px');
            $lFields[] = $pass = new MPasswordField('password', null, null, 10, null, null, true);
            $pass->addAttribute('onPressEnter', GUtil::getAjax('authenticate'));
            $pass->addStyle('width', '110px');
            $lFields[] = new MDiv('divPassResult', '');
            
            //Verifica se está utilizando smartReader
            if(GSipCirculation::usingSmartReader())
            {
                $lFields[] = $codItens =  new MTextField('codItens', '', '', 100, null, null, TRUE);
                $lFields[] = $codItensFinalize =  new MTextField('codItensFinalize', '', '', 100, null, null, TRUE);
                $codItens->addStyle('display', 'none');
                $codItensFinalize->addStyle('display', 'none');
            }
            
            if(BIOMETRIC_INTEGRATION == DB_TRUE) {
                $lFields[] = $btBiometricAccess = new MButton('btnBioAccess', _M('Autenticação biométrica', $this->module), ':identifyBiometry', GUtil::getImageTheme('loan-16x16.png'));
                $lFields[] = $divIframeBioAccess = new MDiv('divIframeBioAccess'); // Div na qual será executado o script para identificação biométrica.
                $divIframeBioAccess->addStyle('display','none');
                               
            }   
            $divLoanLeft[] = $look = new GLookupField('personId', null, _M('Pessoa/Senha', $module), 'PersonMaterialCirculation', $lFields, false);
            $look->lookupTextField->related = $related; //seta o related no lookup de pessoa
            $look->lookupTextField->addAttribute('onPressEnter', GUtil::getAjax('getPerson'));
            $look->lookupTextField->addStyle('width', '95px');
            $look->setRelated($related);
            
            
            
        }

        $itemNumber[0] = new MLabel(_M('Exemplar'), $module);
        
        //Verifica se está utilizando smartReader
        if(GSipCirculation::usingSmartReader())
        {
            $itemNumber[1] = new MTextField('itemNumber', null, null, 10, null, null, $this->checkAcces('gtcMaterialMovementLoan'));
            $itemNumber[1]->addAttribute('onPressEnter', 'verificaItemNumber();'); 
            //Adiciona campo de itemnumber para Checkpoint
            $itemNumber[2] = new MTextField('itemNumberCheckpoint', null, null, 10, _M('Digite o código do exemplar e pressione enter.', $module) , null);
            
        }else
        {
            $itemNumber[1] = new MTextField('itemNumber', null, null, 10, null, null, $this->checkAcces('gtcMaterialMovementLoan'));
            $itemNumber[1]->addAttribute('onPressEnter', GUtil::getAjax('addItemNumber')); 
            //Adiciona campo de itemnumber para Checkpoint
            $itemNumber[2] = new MTextField('itemNumberCheckpoint', null, null, 10, _M('Digite o código do exemplar e pressione enter.', $module) , null);
        }

        //Testar se tem a preferencia de utilizar o RFID
        if(RFID_INTEGRATION == DB_TRUE)
        {
            $ajaxAction = 'javascript:'.GUtil::getAjax('getExemplarId', $param).';';

            //$itemNumber[3] = new MButton('searchItemNumber', _M('Obter', $this->module) , $ajaxAction, GUtil::getImageTheme('accept-16x16.png') );
            $itemNumber[3] = new MButton('searchItemNumber', _M('Ler', $this->module) , $ajaxAction, GUtil::getImageTheme('changeMaterial-16x16.png') );
            $itemNumber[3]->addStyle('display', 'none');
        }

        //Workaround para que nao desabilite o campo do checkpoint quando outros 
        //ajax sao executados na existencia da tela dizendo que o exemplar nao esta emprestado.
        $js = 
        "   if (dojo.byId('divPromptDown') == null )
            {
        "
            .GUtil::getAjax('doCheck').
        "   }
        ";

        $itemNumber[2]->addAttribute('onPressEnter', $js);
        $divLoanLeft[] = new GContainer('', $itemNumber);

        $returnForecastDate = new MCalendarField('returnForecastDate', null, null, null, _M("Se este campo não for preenchido a previsão de devolução será definida pela politica de empréstimo.", $this->module));
        $returnForecastDate->setHint($hint);
        $divLoanLeft[] = new GContainer('contReturnForecastDate', array( new MLabel(_M('Data prevista da devolução', $module)), $returnForecastDate ));

        $divLoanLeft[] = new MDiv('divPhoto', '', 'personPhoto personPhotoMaterialCirculation');
        $divLoanLeft[] = new MDiv('divLoanRight', $this->getRightFields());

        if ( GSipCirculation::usingSmartReader() )
        {
            $legend = '<div id="contador">Itens [0]</div>';
        }
        else
        {
            $legend = 'Itens';
        }
        
        $tableItems = new GRepetitiveField('tableItems', _M($legend, $this->module), null, null, array( 'noButtons' ));
        $tableItems->setShowButtons(false);

        $imgPrint = new MImage('imgPrint', null, GUtil::getImageTheme('print-16x16.png'));
        $imgEmail = new MImage('imgEmail', null, GUtil::getImageTheme('email-16x16.png'));

        $tableItemsColumns = array
            (
            new MGridColumn(_M('exemplar', $module), 'left', true, null, true, 'itemNumber'),
            new MGridColumn(_M('Dados', $module), 'left', true, null, true, 'searchData'),
            new MGridColumn(_M('Estado', $module), 'left', true, null, true, 'exemplaryStatusDescription'),
            new MGridColumn(_M('Tipo', $module), 'left', true, null, true, 'loanTypeDescription'),
            new MGridColumn($imgPrint->generate(), 'left', true, null, true, 'printReceiptLabel'),
            new MGridColumn($imgEmail->generate(), 'left', true, null, true, 'sendReceiptLabel'),
        );

        $tableItems->setColumns($tableItemsColumns);
        $tableItems->addAction('removeItemNumber', 'table-delete.png', $module);
        $this->tables['tableItems'] = $tableItems;

        $divLoanLeft[] = $tableItems;
        $tablePolicy = new GRepetitiveField('tablePolicy', _M('Política', $module), null, null, array( 'noButtons' ));
        $tablePolicy->setShowButtons(false);

        $tablePolicyColumns = array
            (
            new MGridColumn(_M('Grupo de privilégio', $module), 'left', true, "20%", false, 'privilegeGroupDescription'),
            new MGridColumn(_M('Código do vínculo', $module), 'left', true, "20%", false, 'linkId'),
            new MGridColumn(_M('Gênero do material', $module), 'left', true, "20%", true, 'materialGenderDescription'),
            new MGridColumn(_M('Data do empréstimo', $module), 'left', true, "20%", false, 'loanDate'),
            new MGridColumn(_M('Dias de empréstimo', $module), 'left', true, "20%", true, 'loanDays'),
            new MGridColumn(_M('Limite de empréstimo', $module), 'left', true, "20%", true, 'loanLimit'),
            new MGridColumn(_M('Limite de renovações', $module), 'left', true, "20%", false, 'renewalLimit'),
            new MGridColumn(_M('Dias de espera por reserva', $module), 'left', true, "20%", false, 'daysOfWaitForReserve'),
            new MGridColumn(_M('Limite de renovações web', $module), 'left', true, "20%", false, 'renewalWebLimit'),
            new MGridColumn(_M('Bônus de renovações web', $module), 'left', true, "20%", false, 'renewalWebBonus'),
            new MGridColumn(_M('Adicional de dias para feriado', $module), 'left', true, "20%", false, 'additionalDaysForHolidays'),
            new MGridColumn(_M('Limite de reserva de nível inicial', $module), 'left', true, "20%", false, 'reserveLimitInInitialLevel'),
            new MGridColumn(_M('Empréstimos em aberto', $module), 'left', true, "20%", true, 'loanOpenOfAssociation'),
            new MGridColumn(_M('Empréstimo atrasado', $module), 'left', true, "20%", true, 'delayLoan'),
            new MGridColumn(_M('Reservas', $module), 'left', true, "20%", true, 'reserves'),
            new MGridColumn(_M('Reservas comunicadas', $module), 'left', true, "20%", true, 'answeredReserves')
        );

        if ( $this->checkAcces('gtcMaterialMovementLoan') )
        {
            $tablePolicy->setColumns($tablePolicyColumns);
            $this->tables['tablePolicy'] = $tablePolicy;

            $divLoanLeft[] = $tablePolicy;
            $divLoanLeft[] = new MSeparator('<br/>');
        }

        $fields[0] = new MDiv('divLoanLeft', $divLoanLeft);

        // botoes de recibo
        $buttonsR[] = new MButton('reprint', _M('Reimprimir', $this->module), ':reprintReceipt');
        $buttonsR[] = new MButton('resend', _M('Reenviar', $this->module), ':resendReceipt');
        $buttonsR[] = new MButton('visualize', _M('Visualizar', $this->module), ':visualizeReceipt');
        $bottomButtons[0] = new MBaseGroup('receiptBoxLastReceipt', _M('Último recibo', $this->module), array( new MDiv('', $buttonsR) ));

        if ( ($this->checkAcces('gtcMaterialMovementVerifyUser')) && ( $this->checkAcces('gtcMaterialMovementLoan')) )
        {
            $buttonsP[] = new MButton('btnViewLoan', _M('Empréstimos', $this->module), ':displayLoans', GUtil::getImageTheme('loan-16x16.png'));
            $buttonsP[] = new MButton('btnViewReserve', _M('Reservas', $this->module), ':displayReserves', GUtil::getImageTheme('reserve-16x16.png'));
            $bottomButtons[1] = new MBaseGroup('hctView', _M('Pessoa', $this->module), array( new MDiv('', $buttonsP) ));
        }

        $fields[2] = new MHContainer('divActions', $bottomButtons);
        //Div responsavel por mostrar as informacoes do checkpoint
        $fields[3] = new MDiv('checkResponse' );
        $divLoanMain = new MDiv('divLoanMain', $fields);

        return $this->addResponse($divLoanMain, $args);
    }
    
    /*
     * Método que irá ler da etiqueta, e salvará o número da tag
     */
    public function getExemplarId($args)
    {
        $res = RFID::readTag();
        
        if(is_array($res))
        {
            GPrompt::error("Problema ao ler a etiqueta. <br> 
                            ". $res[0]);
        }else
        {
            $resp = (string) $res;
            //Colocar valor no campo addItem e pressionar
            $this->page->onLoad("dojo.byId('itemNumber').value = '$resp';");
            $this->page->onLoad("eval(dojo.byId('itemNumber').getAttribute('onpressenter'));");
        }
	$this->setResponse(NULL, 'limbro');
    }
    
    public function getRightFields()
    {
        //check boxes de ação para recibos
        $imgPrint = new MImage('imgPrint', null, GUtil::getImageTheme('print-16x16.png'));
        $imgEmail = new MImage('imgEmail', null, GUtil::getImageTheme('email-16x16.png'));
        $fields[] = new GContainer(null, array( $imgPrint, new GSelection('printReceipt', null, null, GnutecaReceipt::getConfigList(), null, null, null, true) ));
        $fields[] = new GContainer(null, array( $imgEmail, new GSelection('sendReceipt', null, null, GnutecaReceipt::getConfigList(), null, null, null, true) ));
        
        if ( $this->checkAcces('gtcMaterialMovementReturn'))
        {
            $fields[] = new MHContainer( null, array( new MCheckBox('checkReserve', true, null, true ), new MLabel(_M('Verificar reserva', $this->module)) ) );
        }        
        
        if ( GPerms::checkAccess('gtcMaterialMovementCommunicateReserves', null, false) )
        {
            $fields[] = new MHContainer(null, array( new MCheckBox('communicateReserves', true, null, false), new MLabel(_M('Comunicar reserva', $this->module)) ));
            $this->jsSetChecked('communicateReserves', (MARK_COMUNICATE_RESERVE == 't'));
        }
        
        
        //Testar se tem a preferencia de utilizar o Rfid
        if(RFID_INTEGRATION == DB_TRUE)
        {
                 
            $nCookie = RFID_COOKIE;
            
            if(! isset($_COOKIE[$nCookie]))
            {
                $op = RFID::getTerms();
                $texto = "<p align='center'>A preferência <b>RFID_INTEGRATION</b> esta <b>habilitada</b>.<br>".
                         "Identifique o terminal que você esta utilizando.<br>".
                         "Após salvar, será gerado um <b>cookie permanente</b> em seu computador.<br>".
                         "Caso o mesmo seja apagado, será necessário identificar novamente.";
                                
                $termIdent[] = new MDiv('texto', $texto);
                $termIdent[] = new MLabel('Identificação:');
                $termIdent[] = $cmb = new GContainer(null, array( new GSelection('termDir', null, null, $op, false, false, null, false)));
                $termIdent[] = $bt = new MButton('btnSaveCookie', _M('Salvar', $this->module), GUtil::getAjax('makeCookieDir', ''), GUtil::getImageTheme('accept-16x16.png'));
                $bt->addAttribute('title', _M('Salvar terminal de acesso no navegador.', $this->module));
                
                //Aqui irá definir o cookie que será usado para definir o diretório da comunicação com o RFID
                $this->injectContent($termIdent, false, _M("Identificação de terminal:"), '500px');
            }
            else
            {
                $valor = $_COOKIE[$nCookie];
            }
        }
        //Fim de teste de RFID
        
        return $fields;
    }
    
   /**
     * Salva cookie para identificar terminal
     */
    public function makeCookieDir()
    {
        $var = $this->getFormValue("termDir");
        if(empty($var))
        {
            if(!$var === 0)
            {
                return;
            }
        }
        $var++;
        $cookieValue = "termDir".$var;
        
        //GForm::question(_M("Você irá se identificar como terminal de acesso #$var. <br>Salvar?"), 'javascript:' . GUtil::getAjax('gravaCookie', $cookieValue));
        //Necessário fechar o prompt
        
        GPrompt::question(_M("Você irá se identificar como terminal de acesso #$var. <br>Salvar?"), 'javascript:' . GUtil::getAjax('gravaCookie', $cookieValue));
    }

    /**
     * Grava o Cookie no terminal
     */
    public function gravaCookie($termDir)
    {
        
        //Criar o cookie do diretório
        $res = setcookie(RFID_COOKIE, $termDir);
        
        if($res)
        {
            $this->page->redirect("index.php?module=gnuteca3&action=main:materialMovement");
        }else
        {
            GPrompt::error("Não foi possível gravar a identificação.");
        }
    }    
    
    
    /**
     * Reenvia os recibos de emprestimo e devolução por email.
     *
     */
    public function resendReceipt()
    {
        $gnutecaReceipt = new GnutecaReceipt();
        $gnutecaReceipt->resendStoredMails();
        $this->injectContent($gnutecaReceipt->getMessagesTableRaw(), true, true);
    }

    /**
     * retorna o box com o recibo
     *
     * @return object MDiv
     */
    public function getReceiptBox($print = false)
    {
        $receiptObject = new GnutecaReceipt();
        $receiptText = $receiptObject->getReceiptsText();

        if ( !strlen($receiptText) )
        {
            return false;
        }

        $receiptText = str_replace("\r", '', $receiptText);

        if ( $print )
        {
            $print = $receiptObject->sendPrintServer($receiptText);
        }

        $text = new GString('<pre>' . $receiptText . '</pre>');
        $divReport = new MDiv('receiptBox', $text);

        return $divReport;
    }

    /**
     * Exibe o conteudo do recibo na tela;
     *
     * @param unknown_type $args
     */
    public function visualizeReceipt()
    {
        $this->injectContent($this->getReceiptBox(false), true, _M('Visualizar recibo', $this->module));
    }

    public function reprintReceipt()
    {
        $this->injectContent($this->getReceiptBox(true), true, _M('Imprimir recibo', $this->module));
    }

    public function printReceipt($args)
    {
        $this->getReceiptBox(true);
        $this->setResponse(null, 'divResponse');
    }

    public function removeItemNumber($args)
    {
        $data = GRepetitiveField::getData('tableItems');
        $register = $data[$args->arrayItemTemp];
        $itemNumber = $register->itemNumber;
        $type = $this->getMMOperation();
        
        if(GSipCirculation::usingSmartReader())
        {
            $this->page->onload("removeItemFromSmartReaderCache('$itemNumber');");
        }

        if ( $args->GRepetitiveField == 'tableItems' )
        {
            if ( $type == 'LOAN' )
            {
                $busOperationLoan = $this->MIOLO->getBusiness('gnuteca3', 'BusOperationLoan');
                $busOperationLoan->deleteItemNumber($itemNumber);
            }
            else if ( $type == 'RETURN' )
            {
                $this->busOperationReturn->deleteItemNumber($itemNumber);
            }
        }

        $this->updateTableItems($args);
    }

    public function selectLocation($data)
    {
        //Se for uma chamada ajax decoda o argumento
        if ( $this->isAjaxCall() )
        {
            $data = GUtil::decodeJsArgs($data);
        }

        //Como campo Local fica readOnly, não passa adiante o localId. Desta forma, foi necessário gravar na sessão
        if ( !$data->locationId )
        {
            $data->locationId = $this->busOperationReturn->getLocation();
        }

        $busOperationLoan = $this->MIOLO->getBusiness('gnuteca3', 'BusOperationLoan');
        $busOperationLoan->setLocation($data->locationId);

        $busLocationType = $this->MIOLO->getBusiness('gnuteca3', 'BusLocationForMaterialMovement');
        $locationType = $busLocationType->getLocationForMaterialMovement($data->locationId,true);

        if ( $this->getMMOperation() == 'LOAN' )
        {
            if ( MUtil::getBooleanValue($locationType->sendLoanReceiptByEmail) == DB_TRUE )
            {
                //define se e para mandar ou nao
                $this->page->onLoad("dojo.byId('sendReceipt').value = '{$locationType->sendLoanReceiptByEmail}';");            
            }
        }
        else if( $this->getMMOperation() == 'RETURN' )
        {
            if ( MUtil::getBooleanValue($locationType->sendReturnReceiptByEmail) == DB_TRUE )
            {
                //define se e para mandar ou nao
                $this->page->onLoad("dojo.byId('sendReceipt').value = '{$locationType->sendReturnReceiptByEmail}';");
            }
            
            if(GSipCirculation::usingSmartReader())
            {
                $this->page->onload("var btFinalize = document.getElementById('btnClose');
                    
                                     if(btFinalize != null)
                                     {
                                        document.getElementById('btnClose').focus();
                                     }
                                     else
                                     {
                                        document.getElementById('itemNumber').focus();
                                     }");
            }
            
            $this->jsSetFocus('itemNumber');
        }    
        
        if ( $data->__ISAJAXEVENT == 'yes' ) 
        {
            $this->setResponse('', 'limbo');
        }
    }

    /**
     * Define a pessoa que será usada na circualação
     * FIXME o nome desta função deveria ser setPerson, pois define a pessoa
     * FIXME esta função deveria ser simplificada e otimizada, algumas regras deveriam estar nas classes de regras de negocio
     * 
     * @param stdClass $data post ajax do miolo
     * @return boolean 
     */
    public function getPerson($data)
    {
        $module = MIOLO::getCurrentModule();
                
        //define a unidade na sessão, pois o campo é desabilitado
        $_SESSION['libraryUnitId_'] = $data->libraryUnitId_;

        $busOperationLoan = $this->MIOLO->getBusiness($module, 'BusOperationLoan');

        //define a unidade de biblioteca
        if ( !$busOperationLoan->setLibraryUnit($data->libraryUnitId_) )
        {
            return $this->error(_M('Por favor selecione uma unidade de biblioteca válida', $module));
        }

        //define o local do empréstimo
        if ( ! $busOperationLoan->setLocation($data->locationId) )
        {
            return $this->error(_M('Por favor selecione um local válido', $module));
        }
        
        $personIdReturn = $this->busPerson->insertPersonLdapWhenNeeded($data->personId, $data->baseLdap);
        if ( strlen($personIdReturn) == 0 )
        {
            // Remove caracteres não numéricos da campo personid
            $data->personId = str_replace(array('.', ',', '-', '/', '\'', ' '), '', $data->personId);
            $personIdReturn = $this->busPerson->insertPersonLdapWhenNeeded($data->personId, $data->baseLdap);
        }
        
        /* Implementação para logar na circulação de material com numero do cartão
         * e número do usuário.
         */
        
        if(GSipCirculation::usingSmartReader() && $data->personId)
        {
            //Instancia objeto pessoa
            $busPerson = $this->MIOLO->getBusiness($module, 'BusPerson');

            //Obtem a informação passada, como ID ou código do cartão
            $codUser = $personIdReturn;
            
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
                $personIdReturn = $codUserReal;
            }
        }
        
        if ( $this->MIOLO->getConf('login.classUser') == 'gAuthMoodle' && (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) && is_numeric($personIdReturn) == false)
        {
            $this->MIOLO->getClass('gnuteca3', 'gauthmoodle');
            
            $gAuthMoodle = new gAuthMoodle();
            $personIdReturn = $gAuthMoodle->synchronizePersonFromMoodle($personIdReturn);
            
            $personIdReturn = $this->busPerson->insertPersonLdapWhenNeeded($data->personId, $data->baseLdap);
        }
        
        //A partir daqui trabalha somente com o Código da Pessoa e não mais com o Login
        $data->personId = $personIdReturn;
        
        $_REQUEST['personId'] = $data->personId;

        $ok = $busOperationLoan->setPerson( $data->personId ); //define a pessoa

        //caso tenha algum problema na definição da pessoa
        if ( !$ok )
        {
            
            $extraColumns['personId'] = _M('Pessoa', $this->module);
            $extraColumns['personName'] = _M('Nome', $this->module);

            $table[] = $busOperationLoan->getMessagesTableRaw(null, false, $extraColumns);

            //caso tenha processo de operação
            if ( ( $busOperationLoan->isOperationProcess) && ( GPerms::checkAccess('gtcMaterialMovementCancelOperationProcess', 'update', FALSE) ) )
            {
                $btn = new MButton('btnCancel', _M('Cancelar processo de operação', $this->module), GUtil::getAjax('cancelOperationProcess', $data->personId), GUtil::getImageTheme('cancel-16x16.png'));
                $btn->addAttribute('title', _M('Cancelar processo de operação', $this->module));
                $table[] = $btn;
                $table[] = $btnClose = GForm::getCloseButton();
            }

            $this->jsSetValue('personName', '');
            $this->jsSetValue('personId', '');
            $this->jsSetValue('password', '');

            //this js is the extra functions th closeButon
            return $this->injectContent($table, !$btnClose, true);
        }
        else //caso a definição da pessoa ocorra corretamente
        {
            $_SESSION['personId'] = $data->personId;

            //tenta obter possíveis multas e penalidades
            $fields = $this->getPenaltyFine();

            if ( $fields ) //caso tenha multa, joga os campos na tela
            {
                return $this->injectContent($fields, false, true);
            }
            else
            {
                $person = $busOperationLoan->getPerson();
                $changePersonPermissions = $this->busPerson->getPersonChangePermissions();                        
                
                //caso não tenha senha definida pergunta para a pessoa,
                //mas só pergunta se tiver permissão de alterar pessoa e se não tiver permissão para passar sem senha
                if ( ! $person->password && ! MUtil::getBooleanValue( MY_LIBRARY_AUTHENTICATE_LDAP ) 
                        && MUtil::getBooleanValue($changePersonPermissions->tabMain)
                        && !MUtil::getBooleanValue( $this->checkAcces('gtcMaterialMovementChangePassword') ) )
                {
                    return $this->askPassword();
                }

                $this->jsSetValue('personId', $person->personId);
                $this->jsSetValue('personName', addslashes($person->personName));
                $this->jsSetValue('password', '');
                $this->jsSetFocus('password');
                $this->jsSetInner('divPassResult', '');
                $this->jsSetReadOnly('itemNumber', true);
                $this->jsDisabled('libraryUnitId_');
                $this->jsDisabled('locationId');
                $this->jsSetReadOnly('personId', true);

                //desativa campo baseLdap caso tipo de autenticação for TYPE_AUTHENTICATE_LOGIN_BASE
                if ( MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE )
                {
                    $this->jsSetReadOnly('baseLdap', true);
                    $this->jsDisabled('baseLdap');
                }

                $this->updateTablePolicy();
                $this->jsSetInner('divPhoto', GUtil::getPersonPhoto($person->personId, array( 'height' => '120px' )));
                //ajeita a foto dentro da div
                $this->page->onload("setTimeout( 'photo = dojo.query(\'#divPhoto img\')[0]; if ( photo.clientWidth > 90 ) { photo.style.marginLeft = \'-\' + Math.floor( (photo.clientWidth - 90 ) / 2 ) + \'px\'; }', 2000);");
            }
        }

        $this->jsSetReadOnly('password', false);
        
        if ( BIOMETRIC_INTEGRATION == DB_TRUE )
        {
            $this->page->onload(GUtil::getAjax('authenticate'));
        }
        
        return $person;
    }

    /**
     * Força a pessoa a digitar a senha caso não tenha
     * 
     * @return boolean
     */
    public function askPassword()
    {
        $module = 'gnuteca3';
        $personId = $_SESSION['personId'];

        $fields[] = MMessage::getMessageContainer();
        $fields[] = new MDiv( '',_M( "O usuário <b>{$personId}</b> não possui uma senha definida.", $module) );
        $fields[] = new MDiv( '',_M( "Esta interface permite a definição da primeira senha.", $module) );

        $fields[] = new MSeparator('<br/>');
        $fields[] = $newPassword = new MPasswordField( 'newPassword', '', _M('Senha',$module), 10);
        $fields[] = $passwordConfirm = new MPasswordField( 'newPasswordConfirm', '', _M('Confirmação',$module), 10);
        
        $newPassword->addAttribute( 'onpressenter' , "gnuteca.setFocus('newPasswordConfirm', true);");
        $passwordConfirm->addAttribute("onpressenter", GUtil::getAjax("saveAskPassword") );
        
        $buttons[] = new MButton('btnConfirm', _M("Confirmar", $module), GUtil::getAjax("saveAskPassword"), GUtil::getImageTheme('accept-16x16.png') );
        $buttons[] = GForm::getCloseButton( );
        
        $fields[] = new MSeparator('<br/>');
        $fields[] = new MDiv('buttons', $buttons );
       
        $fields = new MFormContainer('', $fields );
        
        $this->jsSetFocus('newPassword', false); //define o foco na senha

        return $this->injectContent($fields, false, _M("Primeira definição de senha", $module), '500px');
    }

    /**
     * Faz a confirmação da digitação da senha
     * 
     * @param stdClass $data
     * @return boolean 
     */
    public function saveAskPassword($data)
    {
        $personId = $_SESSION['personId'];
        $module = 'gnuteca3';

        $validators[] = new MRequiredValidator('newPassword', _M("Senha", $module));
        $validators[] = new MRequiredValidator('newPasswordConfirm', _M("Confirmação", $module));

        $this->setValidators($validators);

        if ( $this->validate(null, null, false) )
        {
            //verifica se a senha e o confirmação são iguais
            if ( $data->newPassword != $data->newPasswordConfirm )
            {                
                $this->jsSetFocus('newPassword', false); //define o foco na senha
                $this->jsSetValue('newPassword', '');
                $this->jsSetValue('newPasswordConfirm', '');

                $msg = _M("Senhas não conferem. Por favor revise a digitação!", $module);
                $box = MMessage::getStaticMessage(MMessage::MSG_DIV_ID, $msg, MMessage::TYPE_WARNING);

                return $this->setResponse($box, MMessage::MSG_CONTAINER_ID);
            }
            else
            {
                $busAuthenticate = $this->MIOLO->getBusiness($module, 'BusAuthenticate');
                $ok = $busAuthenticate->changePassword($data->personId, $data->newPassword, $data->newPasswordConfirm);

                if ( $ok )
                {
                    //javascript para fazer a senha ser preenchida automaticamente
                    $js = " setTimeout( 'if ( dojo.byId(\'password\').className != \'mReadOnly\' ) { document.getElementById(\'password\').value=\'{$data->newPassword}\'; eval( document.getElementById(\'password\').getAttribute(\'onpressenter\') );} ', 4000);";
                    $goto = "javascript: gnuteca.closeAction();" . GUtil::getAjax('getPerson') . $js;

                    return GForm::information(_M('Nova senha salva com sucesso!', $module), $goto);
                }
                else
                {
                    throw new Exception(_M("Impossível atualizar a senha!", $module));
                }
            }
        }
        else
        {
            $this->jsSetFocus('newPassword', false);
            $this->setResponse('', 'limbo');
        }
    }

    public function getPenaltyFine()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $data = (Object) $_REQUEST;
        $busOperationLoan = $MIOLO->getBusiness($module, 'BusOperationLoan');
        $person = $busOperationLoan->getPerson();
        $data->libraryUnitId_ = $data->libraryUnitId_ ? $data->libraryUnitId_ : MIOLO::_REQUEST('libraryUnitId_');

        //Verifica penalidades e multa
        $penalty = $this->busPenalty->getPenaltyOfAssociation($data->libraryUnitId_, $data->personId);

        $listFine = $this->busFine->getFineOpenOfAssociation($data->libraryUnitId_, $data->personId);

        $fine = array( );

        if ( is_array($listFine) && !empty($listFine[0]) )
        {
            foreach ( $listFine as $f )
            {
                $fine[$f->fineId] = $f->loanId;
            }
        }
        
        if ( !$penalty && !$fine )
        {
            return false;
        }

        $tabControl = new GTabControl('tabDetail');

        $titles = array(
            _M('Código', $this->module),
            _M('Unidade de biblioteca', $this->module),
            _M('Data da penalidade', $this->module),
            _M('Data final de penalidade', $this->module),
            _M('Operador', $this->module),
            _M('Observação', $this->module),
            _M('Observação interna', $this->module),
        );

        $busLibraryUnit = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $listLibraryUnit = $busLibraryUnit->listLibraryUnit();

        foreach ( $penalty as $p )
        {
            $p = $this->busPenalty->getPenalty($p->penaltyId);

            $penaltyEndDate = new MCalendarField("penaltyEndDate[{$p->penaltyId}]", GDate::construct($p->penaltyEndDate)->getDate(GDate::MASK_DATE_USER));
            $libraryUnit = new GSelection("_libraryUnitId_[{$p->penaltyId}]", $p->libraryUnitId_, NULL, $listLibraryUnit, NULL, NULL, NULL, ($p->libraryUnitId_) ? TRUE : FALSE);
            $observation = new MMultiLineField("observation[{$p->penaltyId}]", $p->observation, NULL, NULL, 5, 15);
            $internalObservation = new MMultiLineField("internalObservation[{$p->penaltyId}]", $p->internalObservation, NULL, NULL, 5, 15);
            $btnSave = new MButton('btnSave1', null, ':savePenalty');

            //Se operador não tiver permissão para mudar de permissão 
            if ( !GPerms::checkAccess('gtcPenalty', 'update', false) )
            {
                $libraryUnit->setReadOnly(TRUE);
                $penaltyEndDate->setReadOnly(TRUE);
                $observation->setReadOnly(TRUE);
                $internalObservation->setReadOnly(TRUE);
                $btnSave->setVisibility(FALSE);
            }

            $tbData[] = array(
                $p->penaltyId,
                $libraryUnit,
                GDate::construct($p->penaltyDate)->getDate(GDate::MASK_DATE_USER),
                $penaltyEndDate,
                $p->operator,
                $observation,
                $internalObservation,
            );
        }

        $lbl = new MLabel(_M('A pessoa @1 tem as seguintes penalidades', $this->module, $person->personId . ' - ' . $person->personName) . ':');
        $lbl->setBold(TRUE);
        $flds[] = $lbl;
        $flds[] = new MSeparator('<br/>');

        $table = new MTableRaw(NULL, $tbData, $titles);
        $flds[] = $table;
        $flds[] = $btnSave;
        $flds[] = new MDiv('divMessagesPenalty');

        if ( $penalty )
        {
            $tabControl->addTab('tabPenalty', _M('Penalidade', $this->module), $flds);
        }

        if ( $fine )
        {
            unset($flds);

            $lbl = new MLabel(_M('A pessoa @1 tem as seguintes multas', $this->module, $person->personId . ' - ' . $person->personName) . ':');
            $lbl->setBold(TRUE);
            $flds = array_merge(array( $lbl ), $this->getBoxFine($fine, TRUE));
            $tabControl->addTab('tabFine', _M('Multa', $this->module), $flds);
        }

        //cria uma tab de recibo de multa esconde ela
        $tabControl->addTab('tabFineReceipt', _M('Recibo de multa', $this->module), array( new MLabel('') ));
        $this->page->onLoad("dojo.byId('tabFineReceiptButton').style.display = 'none'; dojo.byId('tabFineReceipt').style.display = 'none';");

        $flds = null;
        $flds[] = new MLabel(_M('Sem recibo neste momento', $this->module) . ':');
        $tabControl->addTab('tabReceipt', _M('Recibo', $this->module), $flds);
        $this->page->onload("document.getElementById('tabReceiptButton').style.display = 'none'");

        $fields[] = $tabControl;
        $fields[] = new MSeparator();
        $fields[] = $btnClose = GForm::getCloseButton();

        $this->MIOLO->page->onload('dojo.parser.parse();');

        return $fields;
    }

    public function savePenalty($args)
    {
        $m = new GMessages();
        $penalty = $this->busPenalty->getPenaltyOfAssociation($args->libraryUnitId_, $args->personId);

        if ( $penalty )
        {
            foreach ( $penalty as $p )
            {
                if ( $m->getErrors() )
                {
                    continue;
                }

                $observation = $args->observation[$p->penaltyId];
                $penaltyEndDate = $args->penaltyEndDate[$p->penaltyId];
                $internalObservation = $args->internalObservation[$p->penaltyId];
                $p = $this->busPenalty->getPenalty($p->penaltyId);
                $date = new GDate($p->penaltyEndDate);
                $p->penaltyEndDate = $date->getDate(GDate::MASK_DATE_DB);
                $p->libraryUnitId_ = $args->_libraryUnitId_[$p->penaltyId];
                $p->observation = $observation;
                $p->internalObservation = $internalObservation;
                $p->penaltyEndDate = $penaltyEndDate;
                $this->busPenalty->setData($p);
                $this->busPenalty->updatePenalty();
            }
        }

        if ( !$m->getErrors() )
        {
            $m->addInformation(MSG_RECORD_UPDATED);
        }

        $this->setFocus('divMessagesPenalty');
        $this->setResponse($m->getMessagesTableRaw(), 'divMessagesPenalty');
        $this->_cancelOperationProcess();
    }

    public function saveFine($args)
    {
        $receipt = new GnutecaReceipt();
        GnutecaReceipt::clearReceitpsText();
        ReceiptMail::clearStoredEmails();
        $fineChanged = FALSE;
        $m = new GMessages();
        $saveFines = array( );

        //obtem a relação de multas
        foreach ( $args->fines as $fineId )
        {
            $fines[] = $this->busFine->getFine($fineId, false);
        }

        if ( $fines )
        {
            foreach ( $fines as $f )
            {
                $fineId = $f->fineId;
                $fine = $this->busFine->getFineAndHistory($fineId);
                $fineStatusId = $args->fineStatusId[$fineId];
                $fineValue = GUtil::moneyToFloat($args->fineValue[$fineId]);
                $fineObservation = $args->fineObservation[$fineId];

                //Para multas Abonadas e Pagas, deve ser mostrado no recibo o valor como 0,
                //exibir qual foi o valor anterior, 
                //porem deve manter o valor original na base de dados
                //Verifica se usuario alterou valor da multa ou estado e nã mudou a observacão
                $casoEspecial = (in_array($fineStatusId, array( ID_FINESTATUS_EXCUSED )) && ($fineStatusId != $fine->fineStatusId));
                
                if ( ( ($fine->value != $fineValue) ||
                        ($fineStatusId == ID_FINESTATUS_EXCUSED) && ($fine->fineStatusId != $fineStatusId)) &&
                        (($fine->observation == $fineObservation) ||
                        (!$args->fineObservation[$fineId])) )
                {
                    $m->addError(_M('Você precisa alterar a observação', $this->module));
                    continue;
                }

                $operation = '';

                //se mudou o estado
                if ( $fineStatusId != $fine->fineStatusId )
                {
                    $fineChanged = TRUE;
                    $busFineStatus = $this->MIOLO->getBusiness('gnuteca3', 'BusFineStatus');
                    $fineStatus = $busFineStatus->getFineStatus($fineStatusId);
                    $operation .= $fineStatus->description . ' ';
                }

                //se mudou o valor
                if ( ($fine->value != $fineValue) || ($casoEspecial) )
                {
                    $fineChanged = TRUE;
                    $value = GUtil::moneyFormat($fine->value);
                    
                    if ( $casoEspecial )		
                        {
                            $operation .= _M('(Anterior R$ @1)', $this->module, $value);

                        }
                        else
                        {
                            $operation .= _M('Valor alterado (Anterior R$ @1)', $this->module, $value);
                        }           
                }

                //Armazena dados para salvar depois, caso nao haja NENHUM erro
                $f->value = $fineValue;
                $f->fineStatusId = $fineStatusId;
                $f->observation = $fineObservation;
                $saveFines[] = $f;

                $loan = $this->busLoan->getLoan($fine->loanId);
                //Só gera recibo para as multas alteradas
                if ( $operation &&
                        ($fineStatusId != ID_FINESTATUS_OPEN || $fineValue != $fine->value) ) //Nao deve gerar recibo para estado Em aberto, a nao ser que o valor da multa tenha sido alterado
                {
                    if ( $casoEspecial ) //Zera o valor que sera exibido no recibo
                    {
                        $fineValue = GUtil::moneyToFloat('0');
                    }
                    
                    //objetos de recibos
                    $fineReceiptWork = new FineReceiptWork();
                    $fineReceiptWork->setFineId($fineId);
                    $fineReceiptWork->setValue($fineValue);
                    $fineReceiptWork->setItemNumber($loan->itemNumber);
                    $fineReceiptWork->setOperation($operation);

                    //verifica se já existe um recibo de multa pra esta pessoa
                    $fineReceipt = $receipt->getItem(get_class(new FineReceipt), $loan->personId);

                    //caso não exista cria um
                    if ( !$fineReceipt )
                    {
                        $fineReceipt = new FineReceipt();
                        $fineReceipt->personId = $loan->personId;
                        $fineReceipt->libraryUnitId_ = $loan->libraryUnitId_;
                        $fineReceipt->setIsPostable(false);
                        $fineReceipt->setIsPrintable(true);
                        $receipt->addItem($fineReceipt);
                    }

                    //adicia item
                    $fineReceipt->addItem($fineReceiptWork);
                }
            }
        }

        $receipt->generate(); //gera o recibo

        $itens = $receipt->getItens();

        if ( is_array($itens) )
        {
            foreach ( $itens as $line => $type )
            {
                foreach ( $type as $l => $receipt )
                {
                    //gera para poder criar o Pdf, isto é necessário para conseguir enviar o email posteriormente
                    $receipt->generate();
                    //envio falso, usado para que crie o objeto na sessão para poder ser enviado posteriormente
                    $receipt->send(true);
                }
            }
        }


        $errors = $m->getErrors();
        if ( !$errors ) //Nao ocorreu nenhum erro
        {
            //Salva todos os dados novos da multa
            if ( $saveFines )
            {
                foreach ( $saveFines as $_fine )
                {
                    $this->busFine->setData($_fine);
                    $this->busFine->updateFine();

                    switch ( $_fine->fineStatusId )
                    {
                        case DEFAULT_VALUE_FINE_INITIAL_STATUS:
                            break;
                        case ID_FINESTATUS_PAYED:
                            $this->busFine->setFinePay($_fine->fineId, FALSE);
                            break;
                        case ID_FINESTATUS_BILLET:
                            $this->busFine->setFinePayRoll($_fine->fineId, FALSE);
                            break;
                        case ID_FINESTATUS_EXCUSED:
                            $this->busFine->setFineBonus($_fine->fineId, $_fine->observation, FALSE);
                            break;
                        case ID_FINESTATUS_OPEN:
                            $ok = $this->busFine->setFineOpen($_fine->fineId);
                            break;
                    }
                }
            }

            $m->addInformation(_M('Multa atualizada com sucesso', $this->module));
        }

        $this->setResponse($m->getMessagesTableRaw(), 'divMessagesFine');

        $this->_cancelOperationProcess();

        //se precisa de recibo, atualiza tab
        if ( $fineChanged && //se foi alterada a multa
                (count($itens) > 0) && //se nao houver nenhuma obra para o recibo
                !$errors ) //caso ocorra algum erro, nao deve gerar recibo
        {
            $receiptFields[] = $this->getReceiptBox();

            $receiptButtons[0] = new MButton('printFineReceipt', _M('Imprimir', $this->module), ':printFineReceipt', GUtil::getImageTheme('document-16x16.png'));
            $receiptButtons[1] = new MButton('sendFineReceipt', _M('Enviar', $this->module), ':sendFineReceipt;' . $pdfPath, GUtil::getImageTheme('email-16x16.png'));

            $receiptFields[] = new MDiv(null, $receiptButtons);
            $receiptFields[] = new MDiv('fineReceiptResponse', null);

            $MIOLO = MIOLO::getInstance();
            $MIOLO->page->addJsCode("dojo.byId('tabFineReceiptButton').style.display = 'block';");
            $MIOLO->page->onLoad("gnuteca.changeTab('tabFineReceipt', 'tabDetail');");

            GTabControl::ajaxUpdateTab($receiptFields, 'tabFineReceipt');
        }
    }

    public function printFineReceipt()
    {
        $receiptObject = new GnutecaReceipt();
        $receiptText = str_replace("\r", '', trim(GnutecaReceipt::getReceiptsText()));
        $print = $receiptObject->sendPrintServer($receiptText);
        //resposta vazia, a intenção é executar a função
        $this->setResponse(null, 'fineReceiptResponse');
    }

    public function sendFineReceipt()
    {
        $gnutecaReceipt = new GnutecaReceipt();
        $gnutecaReceipt->resendStoredMails();
        $this->setResponse($gnutecaReceipt->getMessagesTableRaw(), 'fineReceiptResponse');
    }

    /**
     * Valida o usuario e senha
     *
     * @param object form $data
     * @return boolean
     */
    public function authenticate($data)
    {
        $busOperationLoan = $this->MIOLO->getBusiness('gnuteca3', 'BusOperationLoan');
        $ok = $busOperationLoan->personAuthenticate($data->password);

        if ( !$ok )
        {
            $image = new MImage('error', 'Error', GUtil::getImageTheme('error-16x16.png'));
            $this->jsSetValue('password', '');
            $this->jsSetFocus('password');
        }
        else
        {
            $image = new MImage('accept', 'Accept', GUtil::getImageTheme('accept-16x16.png'));
            $this->jsSetReadOnly('itemNumber', false);
            $this->jsSetFocus('itemNumber');
            
            if(RFID_INTEGRATION == DB_TRUE)
            {
                $this->jsShow('searchItemNumber');
            }
        }

        $this->setResponse($image, 'divPassResult');

        return $ok;
    }

    /**
     * Adiciona um item nao lista de materiais
     *
     * @param object $args
     */
    public function addItemNumber($args)
    {
        if(GSipCirculation::usingSmartReader())
        {
            $itemNumber = '-' . $args;

            $js = "
            while( mutexTrava == 't');
             mutexTrava = 't';
            codItensFinalize = dojo.byId('codItensFinalize').value;
            codItensFinalize = codItensFinalize.replace('{$itemNumber}', '');
            dojo.byId('codItensFinalize').value = codItensFinalize;
            mutexTrava = 'f';";

            $this->page->onload($js);
        }
        
        $type = $this->getMMOperation();

        if ( $type == 'LOAN' )
        {
            $this->addLoan($args);
        }
        else if ( $type == 'RETURN' )
        {
            $this->addReturn($args);
        }
    }

    /**
     * Adiciona uma devoluçao a relação
     * 
     * @return void
     */
    public function addReturn($args=NULL)
    {
        if ( $args == NULL )
        {
            $args = (object) $_REQUEST;
        }
        else
        {
            if ( GSipCirculation::usingSmartReader() )
            {
                $itemNumber = $args;
                $args = (object) $_REQUEST;
                $args->itemNumber = $itemNumber;
            }
        }
        
        if ( GUtil::getAjaxEventArgs() == 'clicked' )
        {
            $args = (object) $_REQUEST;
        }

        //Como campo Local fica readOnly, não passa adiante o localId. Desta forma, foi necessário gravar na sessão
        if ( !$args->locationId )
        {
            $args->locationId = $this->busOperationReturn->getLocation();
        }

        //relação de colunas extras a serem mostradas na tabela de retorno
        $extraColumns['itemNumber'] = _M('Número do exemplar', $this->module);

        $items = $this->busOperationReturn->getItemsReturn(); //pega itens adicionados
        //só define a unidade e demais dados, caso seja a primeira adição
        if ( count($items) == 0 )
        {
            $this->busOperationReturn->setLibraryUnit($args->libraryUnitId_);
            $this->busOperationReturn->setLocation($args->locationId);
            $this->busOperationReturn->setReturnType($args->returnTypeId);
            $busReturnType = $this->MIOLO->getBusiness('gnuteca3', 'BusReturnType');
            $this->returnTypeDescription = $_SESSION['loanTypeDescription'] = $busReturnType->getReturnTypeDescription($args->returnTypeId);
        }

        //verifica se o item pode ser adicionado
        $ok = $this->busOperationReturn->checkItemNumber($args->itemNumber, $args->checkReserve);

        //se não passou checkagem
        if ( !$ok )
        {
            //remove itemNumber do cache do smartReader
            if ( GSipCirculation::usingSmartReader() )
            {
                $this->page->onload("removeItemFromSmartReaderCache('{$args->itemNumber}');");
                
                $_SESSION['returnErrors'][$args->itemNumber] = array($args->itemNumber, $args->checkReserve);
                $errors = $_SESSION['returnErrors'];

                $tables = array();
                $busOperationReturnError = $this->MIOLO->getBusiness($this->module, 'BusOperationReturn');
                
                foreach ( $errors as $error )
                {
                    $busOperationReturnError->checkItemNumber($error[0], $error[1]);
                    $tables[] = $busOperationReturnError->getMessagesTableRaw(null, null, $extraColumns);                
                }
                
                $tableData = array();
                foreach ( $tables as $table )
                {
                    $titles = $table->colTitle;
                    $tableData = array_merge($tableData, $table->array);
                }
                
                $table = new MTableRaw('', null);
                $table->setAttributes('cellspacing=1 width=null cellpadding=3 align=center width=100%');
                $table->colTitle = $titles;
                $table->array = $tableData;
                
                $tabControl = new GTabControl('tabDetail', _M("Adicionar resposta ao item", $this->module));
                $tabControl->addTab('tabResponse', _M('Resultado', $this->module), array($table));
                
                $this->injectContent($tabControl, "dojo.byId('itemNumber').value = ''; dojo.byId('itemNumber').focus(); return false;", _M('Problemas ao inserir uma devolução', $this->module));
            }
            else
            {
                //retorna relação de erros
                $table = $this->busOperationReturn->getMessagesTableRaw(null, null, $extraColumns);
                $this->injectContent($table, "dojo.byId('itemNumber').value = '' ; dojo.byId('itemNumber').focus(); return false;", _M('Problemas ao inserir uma devolução', $this->module));
            }
        }
        else
        {
            $item = $this->busOperationReturn->getTemporaryExemplar($args->itemNumber);

            if ( GSipCirculation::usingSmartReader() )
            {
                //1. caso tenha reserva
                //2. se não confirmou a checagem
                //3. é para verificar as reservas                
                if ( GUtil::getAjaxEventArgs() == 'clicked' )
                {
                    $this->busOperationReturn->addItemNumber($args->itemNumber, $args->printReceipt, $args->sendReceipt);
                }
                elseif ( $item->hasReserve && $args->checkReserve && $item->exemplaryStatusId != DEFAULT_EXEMPLARY_STATUS_RESERVADO )
                {
                    //Isto foi feito para gerar um Questionamento sem que o enter acione a confirmação.
                    $btnYes = new MButton('btnYes2', _M('Sim', 'gnuteca3'), 'javascript:' . "dojo.byId('itemNumber').value = '{$args->itemNumber}'; " . GUtil::getAjax('addItemNumber', 'clicked') . GUtil::getCloseAction(), GUtil::getImageTheme('accept-16x16.png'));
                    $msg = new MSpan('popupTitle', _M('O exemplar @1 tem reserva. Você quer devolvê-lo?', $this->module, $args->itemNumber), 'popupTitleInner');

                    $prompt = new GPrompt(_M('Exemplar com reserva', 'gnuteca3'), $msg);
                    $prompt->setType(GPrompt::MSG_TYPE_QUESTION);
                    $prompt->addButton($btnYes);
                    $prompt->addNegationButton(Gutil::getCloseAction(true) . "dojo.byId('itemNumber').value = ''; dojo.byId('itemNumber').focus(); removeItemFromSmartReaderCache('{$args->itemNumber}');");

                    $this->injectContent($prompt, false, false);

                    return;
                }
                else
                {
                    $this->busOperationReturn->addItemNumber($args->itemNumber, $args->printReceipt, $args->sendReceipt);
                }
            }
            else
            {
                //1. caso tenha reserva
                //2. se não confirmou a checagem
                //3. é para verificar as reservas
                if ( $item->hasReserve && !GUtil::getAjaxEventArgs() == 'clicked' && $args->checkReserve && $item->exemplaryStatusId != DEFAULT_EXEMPLARY_STATUS_RESERVADO )
                {
                    //Isto foi feito para gerar um Questionamento sem que o enter acione a confirmação.
                    $btnYes = new MButton('btnYes2', _M('Sim', 'gnuteca3'), 'javascript:' . GUtil::getAjax('addItemNumber', 'clicked') . GUtil::getCloseAction(), GUtil::getImageTheme('accept-16x16.png'));
                    $msg = new MSpan('popupTitle', _M('O exemplar @1 tem reserva. Você quer devolvê-lo?', $this->module, $args->itemNumber), 'popupTitleInner');

                    $prompt = new GPrompt(_M('Exemplar com reserva', 'gnuteca3'), $msg);
                    $prompt->setType(GPrompt::MSG_TYPE_QUESTION);
                    $prompt->addButton($btnYes);
                    $prompt->addNegationButton(Gutil::getCloseAction(true) . "dojo.byId('itemNumber').value = ''; dojo.byId('itemNumber').focus(); ");

                    $this->injectContent($prompt, false, false);

                    return;
                }
                else
                {
                    $this->busOperationReturn->addItemNumber($args->itemNumber, $args->printReceipt, $args->sendReceipt);
                }
            }

            //só executa a ação, caso seja true ou false
            if ( !is_null($item->sendReturnMailReceipt) )
            {
                $this->jsSetChecked('returnReceipt', $item->sendReturnMailReceipt);
                $this->jsSetChecked('printReceipt', $item->markPrintReceiptReturn);
            }

            //disabilita campos após primeira insersão
            if ( count($items) == 1 )
            {
                $this->jsDisabled('libraryUnitId_');
                $this->jsDisabled('locationId');
                $this->jsDisabled('returnTypeId');
            }
        }

        //limpa o número de controle e define foco nele
        $this->jsSetValue('itemNumber', '');
        $this->jsSetFocus('itemNumber');

        $args->locationId = $this->busOperationReturn->getLocation();
        $args->loanTypeDescription = $_SESSION['loanTypeDescription'];
        $this->updateTableItems($args);
    }

    public function addLoan($args)
    {
        if ( !is_object($args) )
        {
            $itemNumber = $args;
            $args = (object) $_REQUEST;
            $args->itemNumber = $itemNumber;
        }
        
        $extraColumns['itemNumber'] = _M('Número do exemplar', $this->module);
        $busOperationLoan = $this->MIOLO->getBusiness('gnuteca3', 'BusOperationLoan');

        $ok = $busOperationLoan->addItemNumber($args->itemNumber, $args->loanTypeId, $args->printReceipt, $args->sendReceipt);

        if ( !$ok )
        {
            $tabControl = new GTabControl('tabDetail', _M("Adicionar resposta ao item", $this->module));
            
            //remove itemNumber do cache do smartReader
            if ( GSipCirculation::usingSmartReader() )
            {
                $this->page->onload("removeItemFromSmartReaderCache('{$args->itemNumber}');");
                
                $_SESSION['loanErrors'][$args->itemNumber] = array($args->itemNumber, $args->loanTypeId, $args->printReceipt, $args->sendReceipt);
                $errors = $_SESSION['loanErrors'];

                $tables = array();
                $busOperationLoanError = $this->MIOLO->getBusiness('gnuteca3', 'BusOperationLoan');
                foreach ( $errors as $error )
                {
                    $busOperationLoanError->addItemNumber($error[0], $error[1], $error[2], $error[3]);
                    $tables[] = $busOperationLoanError->getMessagesTableRaw(null, null, $extraColumns);                
                }
                
                $tabControl->addTab('tabResponse', _M('Resultado', $this->module), array($tables[count($tables) - 1]));
            }
            else
            {
                $table = $busOperationLoan->getMessagesTableRaw(null, null, $extraColumns);
                $tabControl->addTab('tabResponse', _M('Resultado', $this->module), array( $table ));
            }

            $fine = $busOperationLoan->getFine();

            if ( $fine )
            {
                $tabControl->addTab('tabFine', _M('Multa', $this->module), $this->getTableRawFine($fine));
            }

            $this->injectContent($tabControl, "dojo.byId('itemNumber').value = ''; dojo.byId('itemNumber').focus(); return false;", _M('Problemas ao inserir um empréstimo', $this->module));
        }

        $this->jsSetValue('itemNumber', '');
        $this->jsSetFocus('itemNumber');
        $this->updateTableItems($args);
    }

    /**
     * Update the table items
     *
     * @param unknown_type $args
     */
    public function updateTableItems($args)
    {
        $type = $this->getMMOperation();

        if ( $type == 'LOAN' )
        {
            $busOperationLoan = $this->MIOLO->getBusiness('gnuteca3', 'BusOperationLoan');
            $items = $busOperationLoan->getItemsLoan();
        }
        else if ( $type == 'RETURN' )
        {
            $items = $this->busOperationReturn->getItemsReturn();

            if ( $args->loanTypeDescription )
            {
                foreach ( $items as $item )
                {
                    $items[$item->itemNumber]->loanTypeDescription = $args->loanTypeDescription;
                }
            }
        }

        GRepetitiveField::setData($items, 'tableItems');

        if ( !$args->return )
        {
            $this->setResponse(GRepetitiveField::generate(false, 'tableItems'), 'divtableItems');
        }
        
        if ( GSipCirculation::usingSmartReader() )
        {
            $totalItens = count($items);
            
            $this->page->onload("
                contadorDeItens = {$totalItens};
                dojo.byId('contador').innerHTML = 'Itens [' + contadorDeItens + ']';
            ");
        }
    }
    
    public function limpaSessao()
    {
        $_SESSION['loanErrors'] = NULL;
        $_SESSION['returnErrors'] = NULL;
        
        $this->setResponse(new MDiv('limbo'), 'limbo');
    }

    /**
     * Update the policy table
     *
     */
    public function updateTablePolicy($args = null)
    {
        $busOperationLoan = $this->MIOLO->getBusiness('gnuteca3', 'BusOperationLoan');
        $person = $busOperationLoan->getPerson();
        $items = $person->policy;

        GRepetitiveField::setData($items, 'tablePolicy');

        if ( !$args->return )
        {
            $this->setResponse(GRepetitiveField::generate(false, 'tablePolicy'), 'divtablePolicy');
        }
    }

    /**
     * Finalize loan/return operation
     *
     * @param stdClass $args
     */
    public function onkeydown115_118($args)
    {
        ReceiptMail::clearStoredEmails(); //limpa os emails do Resend
        GnutecaReceipt::clearReceitpsText(); //limpa texto de recibo armazenado
        $op = $this->getMMOperation();

        $busOperationLoan = $this->MIOLO->getBusiness('gnuteca3', 'BusOperationLoan');

        if ( $op == 'LOAN' )
        {
            if ( is_array($busOperationLoan->getItemsLoan()) )
            {
                $ok = $busOperationLoan->finalize($args);
                //colunas extras de informação a mostrar na tabela
                $extraColumns['itemNumber'] = _M('Número do exemplar', $this->module);
                $extraColumns['returnForecastDate'] = _M('Data prevista', $this->module);

                $table = $busOperationLoan->getMessagesTableRaw(null, false, $extraColumns);
                $fine = $this->busOperationReturn->getFine();
                //$mkReceipt  = ($ok && $args->printReceipt);
                $mkReceipt = true;

                if ( $mkReceipt || $fine )
                {
                    if ( $mkReceipt )
                    {
                        $receipt = $this->getReceiptBox(true);
                    }

                    $tabControl = new GTabControl('tabDetail', _M("Loan Response", $this->module));
                    $tabControl->addTab('tabResponse', _M('Resultado', $this->module), array( $table ));
                    $fields[] = $tabControl;

                    if ( $receipt || $fine )
                    {
                        if ( $receipt )
                        {
                            $tabControl->addTab('tabReceipt', _M('Recibo', $this->module), array( $receipt ));
                        }

                        if ( $fine )
                        {
                            $tabControl->addTab('tabFine', _M('Multa', $this->module), $this->getBoxFine($fine, FALSE));
                        }

                        $fieldsC[] = new MButton('btnPrint', 'Imprimir', ':printReceipt', $this->MIOLO->getUI()->getImage($this->module, 'document-16x16.png'));
                    }

                    $fieldsC[] = GForm::getCloseButton("miolo.doAjax('onkeydown27','','__mainForm');");
                    $fields[] = new MDiv('contButtons', $fieldsC);
                    $tabControl->addTab('tabFineReceipt', _M('Recibo de multa', $this->module), array( new MLabel('') ));
                    //esconde tab recibo de multa
                    $this->page->onLoad("dojo.byId('tabFineReceiptButton').style.display = 'none'; dojo.byId('tabFineReceipt').style.display = 'none';");
                }

                if ( GSipCirculation::usingSmartReader() )
                {
                    if ( $ok )
                    {
                        $this->injectContent($fields, false, _M('Finalização do empréstimo', $this->module));
                    }
                }
                else
                {
                    $this->injectContent($fields, false, _M('Finalização do empréstimo', $this->module));
                }
            }
            else
            {
                 $this->page->onload("gnuteca.bloqueia_tecla = false;");
                 $this->setResponse('', 'limbo');
            }
        }
        else if ( $op == 'RETURN' )
        {
            $items = $this->busOperationReturn->getItemsReturn();

            if ( is_array($items) )
            {
                $loan = null;

                foreach ( $items as $line => $info )
                {
                    $itemNumber = $info->itemNumber;

                    //só defini $loanOpen, caso tenha realmante um empréstimo, isso serve para verificar a necessidade de recibos
                    if ( $info->loan )
                    {
                        $loanOpen = $info->loan;
                    }
                }

                $extraColumns['itemNumber'] = _M('Número do exemplar', $this->module);

                $ok = $this->busOperationReturn->finalize($args);
                $table = $this->busOperationReturn->getMessagesTableRaw(null, null, $extraColumns);

                $tabControl = new GTabControl('tabDetail', _M("Resposta do empréstimo", $this->module));
                $tabControl->addTab('tabResponse', _M('Resultado', $this->module), array( $table ));

                // ADICIONA TAB DE RECIBO
                if ( $ok && $loanOpen )
                {
                    $receipt = $this->getReceiptBox(true);

                    if ( $receipt )
                    {
                        $tabControl->addTab('tabReceipt', _M('Recibo', $this->module), array( $receipt ));
                        $buttons[] = new MButton('btnPrint', _M('Imprimir', $this->module), ':printReceipt', GUtil::getImageTheme('document-16x16.png'));
                    }
                }

                // ADICIONA TAB DE MULTAS
                $fine = $this->busOperationReturn->getFine();

                if ( $ok && $fine )
                {
                    $tabControl->addTab('tabFine', _M('Multa', $this->module), $this->getBoxFine($fine, TRUE));
                }

                $tabControl->addTab('tabFineReceipt', _M('Recibo de multa', $this->module), array( new MLabel('') ));
                //esconde tab receibo de multa
                $this->page->onLoad("dojo.byId('tabFineReceiptButton').style.display = 'none'; dojo.byId('tabFineReceipt').style.display = 'none';");

                //Define focus no campo itemNumber apos terminar o processo de devolucao
                $buttons[] = GForm::getCloseButton("miolo.doAjax('onkeydown27','','__mainForm');");
                $fields[] = $tabControl;
                $fields[] = new MDiv('butt', $buttons);

                if ( GSipCirculation::usingSmartReader() )
                {
                    if ( $ok )
                    {
                        //o foco precisa estar no btnClose para o esc funcionar
                        $this->injectContent($fields, false, true);
                    }
                    else
                    {
                        $this->setResponse('', 'limbo');
                    }
                }
                else
                {
                    //o foco precisa estar no btnClose para o esc funcionar
                    $this->injectContent($fields, false, true);
                }
            }
            else
            {
                $this->page->onload("gnuteca.bloqueia_tecla = false;");
                $this->setResponse('', 'limbo');  
            }
        }
        else
        {
            $this->page->onload("gnuteca.bloqueia_tecla = false;");
            $this->setResponse('', 'limbo');    
        }
        
       
    }

    /**
     * VerifyUser - Clear divActionDetail
     *
     */
    public function hideDetail()
    {
        $this->setResponse('', 'divActionDetail');
    }

    /**
     * Clean the data of loan/return form.
     *
     */
    public function cleanData118($args)
    {
        $value = true;

        if ( !$this->checkAcces('gtcMaterialMovementReturn') )
        {
            $value = false;
        }

        $this->jsSetValue('personId', '');
        $this->jsSetReadOnly('personId', $value);
        $this->jsSetValue('password', '');
        $this->jsSetReadOnly('password', $value);
        $this->jsSetValue('personName', '');
        $this->jsSetReadOnly('personName', true);
        $this->jsSetValue('itemNumber', '');
        $this->jsSetInner('divPhoto', ''); //tira foto
        $this->jsSetValue('itemNumberCheckpoint', '');
        $this->jsSetInner('checkResponse', '');
        
        if ( $this->checkAcces('gtcMaterialMovementLoan') )
        {
            $this->jsSetReadOnly('itemNumber', true);
        }

        $this->jsSetInner('divPassResult', '');
        $this->jsEnabled('libraryUnitId_');
        $this->jsEnabled('locationId');
        $this->jsEnabled('returnTypeId');
        $this->jsSetValue('returnTypeId', '');
        $busOperationLoan = $this->MIOLO->getBusiness('gnuteca3', 'BusOperationLoan');
        $busOperationLoan->clearItemsLoan();
        $busOperationLoan->unsetPerson();
        $this->busOperationReturn->clearItemsReturn();
        $this->updateTableItems($args);
        $this->updateTablePolicy($args);

        $this->page->onload("dojo.byId('loanTypeId').value = '" . ID_LOANTYPE_DEFAULT . "'");
        $this->page->onload("dojo.byId('loanTypeId').onchange();");

        if ( !$args->cleanData )
        {
            if ( !$this->primeiroAcessoAoForm() )
            {
                $this->selectLocation($args);
            }

            $args->cleanData = true;
            $op = $this->getMMOperation();

            //ifs de ultima ação e dados salvos
            if ( $op == 'LOAN' )
            {
                $this->onkeydown113_118($args);
            }
            else if ($op == 'RETURN')
            {
                $this->onkeydown114_118($args);
            }
            else if ($op == 'CHECKPOINT')
            {
                $this->onkeydown116_118($args);
            }
        }
    }

    /**
     * Press F2 loan - empréstimo
     *
     * @param object $args
     */
    public function onkeydown113_118($args)
    {
        $this->page->onload("gnuteca.bloqueia_tecla = false;");
        $this->jsSetFocus('personId');

        if ( $this->checkAcces('gtcMaterialMovementLoan') )
        {
            $personId = MIOLO::_REQUEST('personId');

            if ( $personId )
            {
                $busOperationLoan = $this->MIOLO->getBusiness('gnuteca3', 'BusOperationLoan');
                $busOperationLoan->removeOperationProcess($personId);
            }

            $this->setMMOperation('LOAN');
            $args->cleanData = true;
            $this->jsEnableCheckpointField(false);
            $this->jsSetValue('printReceipt', 'i');
            $this->jsSetValue('sendReceipt', 'i');
            $this->jsEnabled('personId');
            $this->jsChangeButtonColor('btnLoan');
            
            //Verifica se está utilizando smartStation
            if(GSipCirculation::usingSmartReader())
            {
                $this->page->onload("var btFinalize = document.getElementById('btnClose');
                    
                                     if(btFinalize != null)
                                     {
                                        document.getElementById('btnClose').focus();
                                     }
                                     else
                                     {
                                        document.getElementById('personId').focus();
                                     }");
            }
            
            $this->jsSetFocus('personId', false);
            $this->cleanData($args);
            $this->jsSetReadOnly('personId', false);
            $this->jsSetReadOnly('password', true);

            //ativa novamente o campo base
            if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
            {
                $this->jsEnabled('baseLdap');
                $this->jsSetReadOnly('baseLdap', false);
            }

            $this->jsSetReadOnly('itemNumber');
            $this->jsShow('tablePolicy');
            $this->jsShow('loanTypeId', true);
            $this->jsHide('returnTypeId', true);
            $this->jsShow('hctView');
            $this->jsEnabled('personId');
            $this->jsHide('checkReserve', true);
            $this->jsHide('communicateReserves', true); //o método detecta se o campo realmente existe, evitando erros de js caso o campo não existir
            $this->jsShow('divPhoto'); //mostra foto
            $this->page->onload("document.getElementById('personId').parentNode.parentNode.childNodes[3].style.display = 'block';"); //Mostra a lupa do lookup 'personId'
            
            //Chama o procedimento que define se vai imprimir/enviar recibo para o local passado por parametro
            $this->selectLocation($args);
        }
        else
        {
            $this->setResponse('', 'limbo');
        }
    }

    /**
     * Press F3 return  devolução
     *
     * @param object $args
     */
    public function onkeydown114_118($args)
    {
        //limpa processo de operação
        $this->_cancelOperationProcess();

        if ( $this->checkAcces('gtcMaterialMovementReturn') )
        {
            $args->cleanData = true; //impede de chamar a função de limpeza sem ser preciso
            //Ativa checkBox de recibo
            $this->setMMOperation('RETURN');
            $this->jsChangeButtonColor('btnReturn');
            $this->cleanData($args);
            $this->jsSetValue('printReceipt', 'i');

            //desativa campo baseLdap caso o modo de autenticação for login
            if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
            {
                $this->jsSetReadOnly('baseLdap');
                $this->jsDisabled('baseLdap');
            }
            $this->jsEnableCheckpointField(false);
            $this->jsSetValue('sendReceipt', 'i');
            $this->jsSetReadOnly('personId');
            $this->jsSetReadOnly('password');
            $this->jsSetReadOnly('itemNumber', false);
            $this->jsSetFocus('itemNumber');
            $this->jsHide('tablePolicy');
            $this->jsHide('loanTypeId', true);
            $this->jsShow('returnTypeId', true);
            $this->jsHide('hctView');
            $this->jsShow('checkReserve', true);
            $this->jsShow('communicateReserves', true);
            $this->jsHide('divPhoto');
            
            //Chama o procedimento que define se vai imprimir/enviar recibo para o local passado por parametro
            $this->selectLocation($args);
            
            $this->page->onload("element = document.getElementById('personId'); if (element) {element.parentNode.parentNode.childNodes[3].style.display = 'none';}");//Deixa invisivel a lupa do lookup 'personId'
        }
        else
        {
            $this->setResponse('', 'limbo');
        }
        
    }
    
    /**
     * Press F5 Checkpoint
     *
     * @param object $args
     */
    public function onkeydown116_118($args)
    {
        //limpa processo de operação
        $this->_cancelOperationProcess();

        if ( $this->checkAcces('gtcMaterialMovementReturn') )
        {
            $args->cleanData = true; //impede de chamar a função de limpeza sem ser preciso
            //Ativa checkBox de recibo
            $this->setMMOperation('CHECKPOINT');
            $this->jsChangeButtonColor('btnCheckpoint');
            $this->cleanData($args);
            $this->jsEnableCheckpointField(true);
            $this->setFocus('itemNumberCheckpoint');
            $this->page->onload("document.getElementById('personId').parentNode.parentNode.childNodes[3].style.display = 'none';");//Deixa invisivel a lupa do lookup 'personId'
        }
        else
        {
            $this->setResponse('', 'limbo');
        }
    }
    
    /**
     * Esta funcao habilita ou desabilita o campo itemNumberCheckpoint.
     * Quando itemNumbercheckpoint estiver habilitado, todos os outros
     * campos devem ficar desabilitados, deixando somente ele visivel na tela para uso.
     * 
     * @param boolean $enabled
     */
    public function jsEnableCheckpointField($enabled)
    {

        $style = $enabled?'none':'block';

        //Desabilita|Habilita divs para deixar somente o checkpoint visivel.
        $this->page->onload("document.getElementById('divLoanRight').style.display = '$style';");
        $this->page->onload("document.getElementById('divPhoto').style.display = '$style';");
        $this->page->onload("document.getElementById('receiptBoxLastReceipt').style.display = '$style';");
        $this->page->onload("element = document.getElementById('hctView'); if (element) {element.style.display = '$style';}");
        $this->page->onload("element = document.getElementById('tablePolicy'); if (element) {element.style.display = '$style';}");
        $this->page->onload("document.getElementById('tableItems').style.display = '$style';");
        $this->page->onload("document.getElementById('selectionContainer').style.display = '$style';");
        $this->page->onload("element = document.getElementById('personIdCont'); if (element) {element.style.display = '$style';}");
        
        //Verifica se esta habilitado div com informaçoes de LDAP e aplica a desabilitacao|habilitacao
        $this->page->onload("var contBaseLdap = document.getElementById('contBaseLdap'); 
                             if (contBaseLdap != null )
                             {
                                contBaseLdap.style.display = '$style';
                             }");
        
        //Se for para habilitar campo itemNumberCheckpoint
        if ( $this->checkAcces('gtcMaterialMovementCheckPoint') && $enabled )
        {
            $this->jsShow('itemNumberCheckpoint',true);
            $this->jsHide('itemNumber',true);
        }  
        else
        {
            $this->jsHide('itemNumberCheckpoint',true);
            $this->jsShow('itemNumber',true);            
        }
    }

    public function getBoxFine($fine, $isFromPerson = null)
    {
        $flds[] = new MSeparator('<br/>');
        $flds = array_merge($flds, $this->getTableRawFine($fine, $isFromPerson));
        $flds[] = new MDiv('divMessagesFine');

        return $flds;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $fine
     * @return unknown
     */
    public function getTableRawFine($fine, $isFromPerson = FALSE)
    {
        $MIOLO = MIOLO::getInstance();
        //Não mostrar botão de enviar se operador não tiver permissão de alterar multa
        if ( $this->checkAcces('gtcMaterialMovementChangeFine') )
        {
            $button = new MButton('btnSave', null, ':saveFine');
        }

        // TRABALHA GRID COM INFORMAÇÕES
        $busFine = $this->MIOLO->getBusiness($this->module, 'BusFine');
        $busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');

        $tbData = array( );
        $totalFine = 0;
        foreach ( $fine as $fineId => $exemplar )
        {
            if ( is_object($exemplar) )
            {
                $loanId = $exemplar->loan->loanId;
                $fines = $exemplar->fine;
                $loanObject = $exemplar->loan;
                $searchData = $exemplar->searchData;
            }
            else
            {
                $loanId = $exemplar;
                $fines = $busFine->getFineAndHistory($fineId, false);
                $loanObject = $busLoan->getLoan($loanId, true, true);
                $busSearchFormat = $MIOLO->getBusiness('gnuteca3', 'BusSearchFormat');
                if ( $loanObject->controlNumber )
                {
                    $searchData = $busSearchFormat->getFormatedString($loanObject->controlNumber, MATERIAL_MOVIMENT_SEARCH_FORMAT_ID, 'detail');
                }
            }

            $hidden[] = new MHiddenField("fines[$fineId]", $fineId);

            $fDate = new GDate($loanObject->returnForecastDate);

            $itemNumber = $loanObject->itemNumber;
            $fineStatusId = $fines->fineStatusId;
            $fineValue = GUtil::moneyFormat($fines->value);
            $observation = $fines->observation;

            $busFineStatus = $MIOLO->getBusiness('gnuteca3', 'BusFineStatus');

            $fineStatusId = new GSelection("fineStatusId[{$fineId}]", $fineStatusId, NULL, $busFineStatus->listFineStatus(), NULL, NULL, NULL, TRUE);
            $fineStatusId->addAttribute('onchange', "var obs = document.getElementById('fineObservation[{$fineId}]'); if ( this.value == '" . ID_FINESTATUS_EXCUSED . "' ) { obs.style.border = '3px solid red'; obs.focus(); } else { obs.style.border = '1px solid #93BCD9'; obs.blur(); } ");
            $fineStatusId->addStyle('width', '110px');

            $fineValue = new MFloatField("fineValue[{$fineId}]", $fineValue);
            $fineValue->addStyle('width', '95%');

            $form = $MIOLO->page->getFormId();
            $fineValue->addAttribute('onchange', "dojo.byId('divMessagesFine').innerHTML = ''; obs = document.getElementById('fineObservation[{$fineId}]'); obs.style.border = '3px solid red'; f = 'miolo.doAjax(\'updateFineValue\', this.value, \'{$form}\')'; setTimeout(f,1000);");
            $observation = new MMultiLineField("fineObservation[{$fineId}]", $observation, NULL, NULL, 5, 20);
            $observation->addStyle('width', '95%');

            if ( !$this->checkAcces('gtcMaterialMovementChangeFine', 'update') )
            {
                $fineStatusId->setReadOnly(TRUE);
                $fineValue->setReadOnly(TRUE);
                $observation->setReadOnly(TRUE);
            }

            $tbData[] = array(
                $itemNumber,
                $fineStatusId,
                $fineValue,
                $fDate->getDate(GDate::MASK_DATE_USER),
                $observation,
                $searchData
            );

            $totalFine += $fines->value;
        }

        //adiciona linha com totalizaï¿½ï¿½o
        $tbData[] = array(
            '<b>' . _M("Total", $this->module) . '</b>',
            '',
            '<b>' . GUtil::moneyFormat($totalFine) . '</b>',
            '',
            '',
            '',
        );

        $titles = array(
            _M("Número do exemplar", $this->module),
            _M('Estado da multa', $this->module),
            _M("Valor da multa", $this->module),
            _M("Data prevista da devolução", $this->module),
            _M('Observação', $this->module),
            _M('Dados', $this->module),
        );
        $table = new MTableRaw(NULL, $tbData, $titles);
        $table->setCellAttribute(count($tbData), 2, 'id', 'fineValueTotal');
        $table->addAttribute('width', '100%');

        $fields[] = new MSeparator();
        $fields[] = $table;
        $fields[] = $button;
        $fields[] = new MHContainer('', $hidden);

        return $fields;
    }

    public function updateFineValue($value, $return = false)
    {
        $value = GUtil::moneySum(MIOLO::_REQUEST('fineValue'));
        $fld = new MLabel($value);
        $fld->setBold(TRUE);
        if ( $return )
        {
            return $fld;
        }
        else
        {
            $this->setResponse($fld, 'fineValueTotal');
        }
    }

    /**
     * Cancela operacao em processo
     *
     * @param unknown_type $args
     */
    public function cancelOperationProcess($personId)
    {
        $ok = $this->_cancelOperationProcess($personId);

        if ( $ok )
        {
            $this->information(_M('Processo de operação cancelado', $this->module));
        }
        else
        {
            $this->error(_M('Erro ao remover o processo de operação', $this->module));
        }
    }
    
    /**
     * Função que cancela uma operação na circulação de material que já tenha iniciado.
     * 
     * @param int $personId
     * @return boolean 
     */
    public function _cancelOperationProcess($personId = NULL)
    {
        if ( !$personId )
        {
            $personId = MIOLO::_REQUEST('personId');
        }

        if ( !$personId )
        {
            $personId = $_SESSION['personId'];
        }

        if ( $personId )
        {
            //se tipo de autenticação for por login
            if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
            {
                //Pega o id da pessoa baseado no login.
                $person = $this->busPerson->getPersonIdFormLoginAndBase($personId);
                $personId = $person->personId;
            }

            return $this->busPerson->removeOperationProcess($personId);
        }

        return FALSE;
    }

    /**
     * Display loan button action
     *
     * @param unknown_type $args
     */
    public function displayLoans($args)
    {
        $args->event = 'onkeydown113'; //key event to show loans
        $this->openVerifyUserWindow($args);
    }
    
    /**
     * [F6] - Display reserves
     *
     * @param unknown_type $args
     */
    public function displayReserves($args)
    {
        $args->event = 'onkeydown114'; //key event to show reserves
        $this->openVerifyUserWindow($args);
    }

    /**
     * Automatically load the needed form
     *
     * @param object $args
     * @return object or ajax response
     */
    public function loadForm($args)
    {
        if ( isset($_REQUEST['__EVENTTARGETVALUE']) )
        {
            $args->return = true;

            $type = $this->getMMType();

            if ( $type == 'CHANGEPASSWORD' )
            {
                return $this->changePassword($args);
            }

            if ( $type == 'CHECKPOINT' )
            {
                return $this->checkPoint($args);
            }

            if ( GForm::primeiroAcessoAoForm() )
            {
                if ( !$type )
                {
                    $this->setMMType(118);
                }
            }

            $valids = array( 118, 119, 120, 122, 123 ); //valids forms
            return $this->executeParenteFunction('onkeydown', $valids, $args);
        }
    }

    /**
     * Verify if is to make a ajax response or return the object
     *
     * @param object $content the content to post
     * @param objecy $args user $args->return = true to return as a object
     * @return ajax response or a object
     */
    public function addResponse($content, $args)
    {
        if ( !$args->return )
        {
            $this->setResponse($content, 'divRight');
            //FIXME fazer isso sem precisar de ajax
            $this->page->addJsCode(GUtil::getAjax('autoLoad'));
        }
        else
        {
            $this->autoLoad($args);
            return $content;
        }
    }

    public function autoLoad($args)
    {
        $type = $this->getMMType();
        $op = $this->getMMOperation();
        
        if ( $type == '118' )
        {
            if ( $op == 'RETURN' )
            {
                $this->onkeydown114($args);
            }
            else
            {
                $this->onkeydown113($args);
            }
        }
        else if ( $type == '119' )
        {
            if ( $op == 'ANSWER' || !$op )
            {
                $this->onkeydown114($args);
            }
            else
            {
                $this->onkeydown113($args);
            }
        }
        else if ( $type == '123' )
        {
            if ( $op == 'SCHEDULE' )
            {
                $this->onkeydown114($args);
            }
            else
            {
                $this->onkeydown113($args);
            }
        }
        else if ( $type = 'changePassword' || $type = '122' )
        {
            $this->jsSetFocus('personId');

            if ( !$args->return )
            {
                $this->setResponse('', 'limbo');
            }
        }
    }

    /**
     * Mount a container with an array of buttons
     *
     * @param unknown_type $functionButtons
     * @return unknown
     */
    public function getButtonContainer($functionButtons)
    {
        foreach ( $functionButtons as $line => $info )
        {
            $info->setClass('mButtonMaterialCirculationUpper');
        }

        return new MDiv('buttonContainer', $functionButtons);
    }

    /**
     * Return a LibraryUnit Field
     *
     * @return GSelection object
     */
    public function getLibrarySelection()
    {
        $MIOLO = MIOLO::getInstance();
        $busLibraryUnit = $MIOLO->getBusiness('gnuteca3', 'BusLibraryUnit');
        $libraryList = $busLibraryUnit->listLibraryUnit(NULL, TRUE);
        return new MHiddenField('libraryUnitId_', GOperator::getLibraryUnitLogged());
        ;
    }

    /**
     * Event generated when user press F2 function
     *
     * @param object $args the miolo ajax stdclass object
     */
    public function onkeydown113($args = NULL)
    {
        $_SESSION['pn_page'] = 1; //zera a paginacao
        $valid = array( 118, 119, 122, 123 );

        // Habilita o botão de acesso biométrico novamente...
        if( BIOMETRIC_INTEGRATION == DB_TRUE )
        {
            $this->jsEnabled('btnBioAccess');
            
        }
        
        return $this->executeParenteFunction('onkeydown113_', $valid, $args);
    }

    /**
     * Functin Called when user press F3 function
     *
     */
    public function onkeydown114($args)
    {
        $_SESSION['pn_page'] = 1; //zera a paginacao
        $valids = array( 118, 119, 122, 123 );
        return $this->executeParenteFunction('onkeydown114_', $valids, $args);
    }

    /**
     * Function called when user press F4 key (normally finalize function)
     *
     * @param object $args the miolo ajax stdclass aobject
     */
    public function onkeydown115($args) //F4
    {
        $_SESSION['pn_page'] = 1; //zera a paginacao
        $valid = array( 118, 119, 122, 123 );

        return $this->executeParenteFunction('onkeydown115_', $valid, $args);
    }
    
    public function executeParenteFunction($func, $valid, $args)
    {
        $type = $this->getMMType();

        if ( in_array($type, $valid) )
        {
            $function = $func . $type;
            return $this->$function($args);
        }

        if ( !$args->return )
        {
            $this->setResponse('', 'limbo');
        }
    }
    /**
     * Function called when user press F5 key
     *
     * @param object $args the miolo ajax stdclass aobject
     */
    public function onkeydown116($args) //F5
    {
        $_SESSION['pn_page'] = 1; //zera a paginacao
        $type = $this->getMMType();
        $function = 'onkeydown116_' . $type;
        
        //Só pode abrir a janela VerifyUser se tiver permissão
        if ( ($type == '118') && ( $this->checkAcces('gtcMaterialMovementVerifyUser')) ) //F7
        {
            $this->$function($args);
        }
        else if ( $type == '120' || $type == '119' || $type == '122' )
        {
            $this->$function($args);
        }
        else
        {
            $this->setResponse('', 'limbo');
        }
    }

    /**
     * Function called when user press F6 key
     *
     * @param object $args the miolo ajax stdclass aobject
     */
    public function onkeydown117($args) //F6
    {
        $type = $this->getMMType();
        $function = 'onkeydown117_' . $type;

        if ( $type == '119' || $type == '120' )
        {
            $this->$function($args);
        }
        else
        {
            $this->setResponse('', 'limbo');
        }
    }

    /**
     * verifyMaterial press F9
     * */
    public function onkeydown120($args) //f9
    {
        if ( $this->checkAcces('gtcMaterialMovementVerifyMaterial') )
        {
            $this->setMMType('120');
            $this->changeTab('btnAction120');
            $options = new StdClass();
            $options->noDefineFields = true;
            $formSearch = new FrmSimpleSearch($options);
            $fields = $formSearch->defineFields();
            $fields[] = new MHiddenField('isVerifyMaterial', 1);
            $this->jsSetFocus('termText[]', false);

            return $this->addResponse($fields, $args);
        }
        else
        {
            $this->setResponse('', 'limbo');
        }
    }

    /**
     * Mount the verifyUser form.
     * Called when user press  F10 function
     * */
    public function onkeydown121()
    {
        if ( $this->checkAcces('gtcMaterialMovementVerifyUser') )
        {
            $MIOLO = MIOLO::getInstance();
            $module = MIOLO::getCurrentModule();
            $urlWindow = $this->manager->getActionURL($module, 'main:verifyUser', '');
            $urlWindow = str_replace('&amp;', '&', $urlWindow);
            $windowName = 'winVerifyUser';
            $win = new MWindow($windowName, array( 'url' => $urlWindow, 'title' => 'Verify user' ));

            $this->page->onload("miolo.getWindow('{$windowName}').open();");
        }

        $this->setResponse('', 'limbo');
    }

    /**
     * Define a pessoa no formulário
     *
     * @param unknown_type $args
     */
    public function getPersonSimple($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        //$person     = $this->busPerson->getPerson($args->personId, true);
        $type = $this->getMMType();
        $op = $this->getMMOperation();

        //verifica o tipo de autenticação
        if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
        {
            $person = $this->busPerson->getPersonIdFormLoginAndBase($args->personId, $args->baseLdap);
            
            //Se a pessoa não existe no gnuteca, pega o usuário do moodle e cria no gnuteca
            if (!$person)
            {
                $persons = $this->busPerson->insertPersonLdapWhenNeeded($args->personId);
                $person->personId = $persons;
                
                $this->MIOLO->getClass('gnuteca3', 'gauthmoodle');
            
                $gAuthMoodle = new gAuthMoodle();
                $person->personId = $gAuthMoodle->synchronizePersonFromMoodle($person->personId );
            }
            
            $args->personId = $person->personId;
            $_REQUEST['personId'] = $person->personId; //FIXME esta sendo desta maneira, foi necessário setar no $_REQUEST
        }
        
        //Verifica se está utilizando smartReader
        if(GSipCirculation::usingSmartReader() && $args->personId )
        {
            //Instancia objeto pessoa
            $busPerson = $this->MIOLO->getBusiness($module, 'BusPerson');

            //Obtem a informação passada, como ID ou código do cartão
            $codUser = $args->personId;
            
            /* Para obter a identificação da pessoa, primeiramente procura pelo personId
             * Caso não encontre o personId, irá validar pelo login */
            $personInf = $busPerson->getPerson($codUser, TRUE);

            //Caso não tiver usuário, tentará validar pelo login (cartão)
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
                
                //Atualizando valor do personId
                $args->personId = $codUserReal;
                
                //Seta o valor do campo como o id do usuário
                $this->jsSetValue('personId', $codUserReal);
                
                //Entra novamente no algoritmo com o código do usuário
                $this->page->onload("miolo.doAjax('getPersonSimple', '', '__mainForm');");
                
                $this->setResponse(NULL, 'limbo');
                return;
            }
        }

        $_SESSION['personId'] = $args->personId;
        $_SESSION['libraryUnitId_'] = $args->libraryUnitId_;

        $busOperationReserve = $MIOLO->getBusiness($module, 'BusOperationReserve');

        if ( $type == '119' )
        {
            if ( (!isset($args->libraryUnitId_)) && $busOperationReserve->getLibraryUnit() )
            {
                $ok = TRUE;
            }
            else
            {
                $ok = $busOperationReserve->setLibraryUnit($args->libraryUnitId_);
            }

            if ( $ok )
            {
                $policy = $busOperationReserve->setPerson($args->personId);
            }

            if ( $ok && $policy )
            {
                $fields = $this->getPenaltyFine();
                        
                //pega multas caso existam
                if ( $fields )
                {
                    $this->injectContent($fields, false, true);
                }
                else
                {
                    $this->jsSetReadOnly('itemNumber', false);
                    $this->jsSetValue('itemNumber', '');
                    $this->jsSetFocus('itemNumber');
                    $this->jsDisabled('libraryUnitId_', true);
                    $this->jsDisabled('personId', true);

                    //desativa o campo base
                    if ( MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE )
                    {
                        $this->jsSetReadOnly('baseLdap', false);
                        $this->jsDisabled('baseLdap', true);
                    }

                    GRepetitiveField::update($policy, 'tablePolicyReserve');
                }
            }
            else
            {
                $this->cleanData119();
                $table = $busOperationReserve->getMessagesTableRaw();
                $this->injectContent($table, true, true);
            }
            
            $fields = '';
            $this->setResponse($fields, 'limbo');
        }
    }

    /**
     * CleanData [ESC]
     */
    public function onkeydown27($args) //esc
    {
        $type = $this->getMMType();
        if (
                $type == '118' ||
                $type == '119' ||
                $type == '123'
        )
        {
            $this->jsChangeButtonColor('btnCleanData');
        }
        $this->cleanData($args);
        $this->setResponse('', 'limbo');

        //Clear simple search
        if ( $type == 120 )
        {
            $this->page->onload("clearForm()");
        }

        //se for login do tipo login/base, tira campo baseLdap do readonly
        if ( MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE )
        {
            $this->jsSetReadOnly('baseLdap', false);
            $this->jsDisabled('baseLdap', false);
        }

        //Remove operation process
        if ( ($type == 118) && ($personId = MIOLO::_REQUEST('personId')) ) //[F7] Loan / devolution
        {
            $busPerson = $this->MIOLO->getBusiness('gnuteca3', 'BusPerson');
            $this->busPerson->removeOperationProcess($personId);
        }
        
        //Remove botão de obter item
        if(RFID_INTEGRATION == DB_TRUE)
        {
            $this->jsHide('searchItemNumber');
        }
        
        // Habilita o botão de acesso biométrico novamente...
        if( BIOMETRIC_INTEGRATION == DB_TRUE )
        {
            $this->jsEnabled('btnBioAccess');
            
        }
        
    }

   /**
     * Limpa os dados do formulário
     *
     * @param stdClass $args
     */
    public function cleanData($args=null)
    {
        $this->page->onload("gnuteca.bloqueia_tecla = false;");
        //Se utilizar smartReader
        if(GSipCirculation::usingSmartReader())
        {
            $this->limpaCacheSmartReader();
            $_SESSION['loanErrors'] = NULL;
            $_SESSION['returnErrors'] = NULL;
        }
        
        $type = $this->getMMType();
        $valids = array( 118, 119, 122, 123 );

        if ( in_array($type, $valids) )
        {
            $function = 'cleanData' . $type;
            $this->$function($args);
        }
    }

    /**
     * Troca as cores dos botões por javascript
     * 
     * @param string $selectButtonId botão selecionado
     */
    public function jsChangeButtonColor($selectButtonId)
    {
        $type = $this->getMMType();

        if ( $type == '118' )
        {
            if ( (!$this->checkAcces('gtcMaterialMovementLoan')) && ($this->checkAcces('gtcMaterialMovementReturn')) )
            {
                $buttons = array( 'btnReturn', 'btnFinalize', 'btnCleanData' );
            }
            elseif ( ($this->checkAcces('gtcMaterialMovementLoan')) && (!$this->checkAcces('gtcMaterialMovementReturn')) )
            {
                $buttons = array( 'btnLoan', 'btnFinalize', 'btnCleanData' );
            }
            else
            {
                $buttons = array( 'btnLoan', 'btnReturn', 'btnFinalize', 'btnCleanData' );
            }
            
            ($this->checkAcces('gtcMaterialMovementLoan')) ? ($buttons[] = 'btnCheckpoint') : null;
        }

        if ( $type == '119' )
        {
            if ( ($this->checkAcces('gtcMaterialMovementAnswerReserve')) && (!$this->checkAcces('gtcMaterialMovementRequestReserve')) )
            {
                $buttons = array( 'btnAnswer', 'btnFinalize', 'btnCleanData' );
            }
            elseif ( (!$this->checkAcces('gtcMaterialMovementAnswerReserve')) && ($this->checkAcces('gtcMaterialMovementRequestReserve')) )
            {
                $buttons = array( 'btnRequest', 'btnFinalize', 'btnCleanData' );
            }
            else
            {
                $buttons = array( 'btnRequest', 'btnAnswer', 'btnFinalize', 'btnCleanData' );
            }
        }

        if ( $type == '122' )
        {
            $buttons = array( 'btnLoan', 'btnReserve', 'btnFine', 'btnPenalty', 'btnCleanData' );
        }

        if ( $type == '123' )
        {
            if ( ($this->checkAcces('gtcMaterialMovementChangeStatus')) && (!$this->checkAcces('gtcMaterialMovementExemplaryFutureStatusDefined')) )
            {
                $buttons = array( 'btnChange', 'btnFinalize', 'btnCleanData' );
            }
            elseif ( (!$this->checkAcces('gtcMaterialMovementChangeStatus')) && ($this->checkAcces('gtcMaterialMovementExemplaryFutureStatusDefined')) )
            {
                $buttons = array( 'btnSchedule', 'btnFinalize', 'btnCleanData' );
            }
            else
            {
                $buttons = array( 'btnChange', 'btnSchedule', 'btnFinalize', 'btnCleanData' );
            }
        }

        foreach ( $buttons as $line => $info )
        {
            if ( $info == $selectButtonId )
            {
                $this->page->onload('element = document.getElementById(\'' . $info . '\'); if (element) {element.style.color =\'red\';}');
            }
            else
            {
                $this->page->onload('element = document.getElementById(\'' . $info . '\'); if (element) {element.style.color =\'black\';}');
            }
        }
    }

    /**
     * Efetua logout no sistema e redireciona para o login do administrativo
     */
    public function logout()
    {
        $return_to = $this->MIOLO->getActionURL($this->module, 'main:administration');
        $this->MIOLO->getAuth()->logout();
        $this->page->redirect($this->MIOLO->getActionURL('admin', 'login', null, array( 'return_to' => $return_to )));
    }
    
    public function doCheck( $args )
    {
        
        if ($args->itemNumberCheckpoint)
        {
            $hasLoan = $this->busLoan->getLoanOpen($args->itemNumberCheckpoint);
            $hasLoan = $hasLoan ? true : false; //converta pra boolean

            if ( $hasLoan )
            {
                $fields[] = new MLabel( $args->itemNumberCheckpoint . ' - '.  _M('Material emprestado.'), 'green', true);
                $this->setResponse( $fields, 'checkResponse');
                $this->jsSetValue('itemNumberCheckpoint', '');
            }
            else
            {
                GForm::jsSetFocus('itemNumberCheckpoint'); 
                $this->jsSetValue('itemNumberCheckpoint', '');
                $this->error( _M('O exemplar @1 não está emprestado.','gnuteca3', $args->itemNumberCheckpoint) , GUtil::getCloseAction(true) . "dojo.byId('itemNumberCheckpoint').value = '';" ); //no clique do fechar deve habilitar novamente o campo de checagem.
                $this->setResponse('', 'checkResponse'); //limpa a div de resposta
            }
        }
        else
        {
            $this->setResponse('','limbo');
        }
    }   
    
    /**
     * Metodo criado para contemplar ticekt #18872
     * Quando um tipo de retorno tiver definido que tem que mandar e-mail
     * desabilita impressao e ativa e-mail.
     * 
     * @param argumentos $args
     */
    public function selectReturnType($args)
    {
        
        $busReturnType = $this->MIOLO->getBusiness('gnuteca3', 'BusReturnType');
        $returnTypeInfo = $busReturnType->getReturnType($args->returnTypeId);

        if ( !is_null($returnTypeInfo->sendMailReturnReceipt) && MUtil::getBooleanValue($returnTypeInfo->sendMailReturnReceipt) )
        {
            $this->page->onLoad("element = dojo.byId('sendReceipt'); if (element) {element.value = 't';}");
            $this->page->onLoad("element = dojo.byId('printReceipt'); if (element) {element.value = 'f';}");
            }
        else
        {
            $this->page->onLoad("element = dojo.byId('sendReceipt'); if (element) {element.value = 'i';}");
            $this->page->onLoad("element = dojo.byId('printReceipt'); if (element) {element.value = 'i';}");
        }
        
        // Define o foco no campo de exemplar.
        $this->jsSetFocus('itemNumber');
        
        $this->setResponse('','limbo');
    }
    
    // Abrir seção da integração biométrica.
    public function identifyBiometry()
    {
        // Abre uma sessão.
        $sessionId = BiometrySession::openSession();
        
        // Executa o script que se conecta ao webservice e envia para ele o resultado da verificação. 
        $this->setResponse('<iframe src="'. BIOMETRIC_URL .'ident.php?idsession=' . $sessionId . '"></iframe>', 'divIframeBioAccess');
        
        // Executa o método que irá autenticar.
        $this->page->onload(GUtil::getAjax('authenticateWithBiometry', $sessionId));
        
    }
    
    // Autentica o usuário retornado pelo script.
    public function authenticateWithBiometry($sessionId)
    {
        // Obtém o personId relativo ao id da sessão correspondente.
        $personId = BiometrySession::getReturn($sessionId);
        
        if( ($personId != 0) && ($personId != NULL) ) 
        {
            $this->jsDisabled('btnBioAccess');
            
        }
        
        // Define usuário e autentica.
        $this->jsSetValue('personId', $personId); // Atribui o valor recebido ao text field da pessoa.
        $this->page->onload(GUtil::getAjax('getPerson'));
        $this->setResponse(null, 'limbo');
    }
    
    public function limpaCacheSmartReader()
    {
        $this->page->onload("
                            if(document.getElementById('codItens') != null)
                             {
                                 var sizeCodItens = document.getElementById('codItens').value;

                                 if(sizeCodItens.length > 0)
                                 {
                                    document.getElementById('codItens').value = '';
                                 }
                             }");
    }
}

?>
