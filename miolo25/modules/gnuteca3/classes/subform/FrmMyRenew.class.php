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
 * MyRenew form
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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 08/12/2008
 *
 **/
class FrmMyRenew extends GSubForm
{
	public $MIOLO;
	public $module;
    public $busAuthenticate;
    public $busExemplaryControl;
    public $busLibraryUnit;
    public $busLoan;
    public $busMaterial;
    /**  @var BusinessGnuteca3BusOperationRenew */
    public $busOperationRenew;
    public $busSearchFormat;
    public $busReserve;

    public function __construct()
    {
    	$this->MIOLO   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();
        $this->busAuthenticate     = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busLibraryUnit      = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
    	$this->busLoan             = $this->MIOLO->getBusiness($this->module, 'BusLoan');
    	$this->busMaterial         = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
    	$this->busOperationRenew   = $this->MIOLO->getBusiness($this->module, 'BusOperationRenew');
        $this->busSearchFormat     = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $this->busReserve          = $this->MIOLO->getBusiness($this->module, 'BusReserve');
    	$this->busOperationRenew->clearData();
    	$this->busOperationRenew->setRenewType( ID_RENEWTYPE_WEB );
        parent::__construct(_M('Renovação', $this->module));

        $this->addjscode("
        checkAllBox = function()
        {
            checked = dojo.byId('checkAll').checked;
            elements = document.getElementsByTagName('input');

            for ( i = 0 ; i<elements.length ; i++ )
            {
                element = elements[i];

                if ( element.type == 'checkbox' )
                {
                    element.checked = checked;
                }
            }

        }
        ");
    }

    public function createFields()
    {
    	//Mensagem a ser mostrada no topo da tela
    	$fields[] = new MDIv('', LABEL_RENEW);

        $loans = $this->getLoansOpen();
        //Se nao testar o evento, vai entrar novamente aqui e vai mostrar mensagens na renovação de materiais que não podem ser renovados.
        if ( (count($loans) > 0) && ($this->getEvent() != "btnFinalize") )
        {
	        $fields[] = $this->getGrid();
	        $finalize = new MButton('btnFinalize', _M('Renovar', $this->module), ':btnFinalize', GUtil::getImageTheme('renew-16x16.png'));
	        $finalize->addStyle('height','30px');

            $fields[] = new MHContainer('hctCheck', array( $finalize ) );
        }
        else
        {
        	$fields[] = new MDiv('',_M('Não há empréstimos aberto', $this->module), 'red');
        }
        
        $this->setFields($fields);
    }


    public function getGrid()
    {
        $loans = $this->getLoansOpen();

        if (!$loans)
        {
            return;
        }

       	$gridData = array();

        foreach ($loans as $i=>$loan)
        {
            $this->busOperationRenew->clearMessages();
            $this->busOperationRenew->checkLoan($loan->loanId, $loan->personId);
            $msgs = $this->busOperationRenew->messagesToString();

            $reason = '-';
            $cb = '';
            
            //Se tiver uma mensagem
            if (strlen($msgs) > 0)
            {
                //Mostra mensagem no lugar do checkbox.
                $cb = $msgs;
            }
            else
            {
                //Se nao tiver mensagem permite renovar deixando checkbox a mostra.
                $cb = new MCheckBox('cb_' . $loan->loanId);
                $cb = $cb->generate();
            }
            $controlNumber = $this->busExemplaryControl->getExemplaryControl($loan->itemNumber)->controlNumber;

            //verifica quantas reservas tem o itemNumber e mostra na coluna correspondente
            $reserves = $this->busReserve->getReservesOfExemplary($loan->itemNumber, ID_RESERVESTATUS_REQUESTED );
            $reserveCount = 0;
            if (is_array($reserves))
            {
                $reserveCount = count($reserves);
            }

            //lista as colunas
            $gridData[$i][] = GForm::isGenerateDocumentEvent() ? '-' : $cb;
            $gridData[$i][] = $loan->itemNumber;
            $gridData[$i][] = $controlNumber ? $this->busSearchFormat->getFormatedString($controlNumber, FAVORITES_SEARCH_FORMAT_ID) : '';
            $gridData[$i][] = $loan->returnForecastDate;
            $gridData[$i][] = $loan->renewalAmount;
            $gridData[$i][] = $loan->renewalWebAmount;
            $gridData[$i][] = $reserveCount;
            $gridData[$i][] = $this->busLibraryUnit->getLibraryUnit($loan->libraryUnitId)->libraryName;
        }

        $grid = $this->MIOLO->getUI()->getGrid($this->module, 'GrdMyRenew');
        $grid->setData($gridData);

        return $grid;
    }

    /**
     * Finalize processo de renovação
     *
     */
    public function btnFinalize()
    {
        $MIOLO = MIOLO::getInstance();
        $args = (object)$_REQUEST;

    	$loans = $this->getLoansOpen();
        
        $loansNotRenewed = array();
    	foreach ($loans as $loan)
    	{
            //Verify if item has been checked
    		$check = 'cb_' . $loan->loanId;
    		if (isset($args->$check))
    		{
    			$objLoan = $this->busLoan->getLoan($loan->loanId, true);
    			$this->busOperationRenew->addLoan($objLoan);
    		}
            else
            {
                $loansNotRenewed[] = $loan;
            }
    	}

        $personId = BusinessGnuteca3BusAuthenticate::getUserCode();

        $this->manager->uses('db/BusPersonConfig.class.php',MIOLO::getCurrentModule() );
        $busPersonConfig = new BusinessGnuteca3BusPersonConfig();
        //pega configuração para renovação via web
        $sendReceipt = MUtil::getBooleanValue( $busPersonConfig->getValuePersonConfig($personId, 'USER_SEND_RECEIPT_RENEW_WEB') );
        
    	$this->busOperationRenew->finalize(null, $sendReceipt, true );
        $this->busOperationRenew->receipt->generate();

        $itenReceipt = $this->busOperationRenew->receipt->getItens();

        foreach ($itenReceipt as $types => $person)
        {
            foreach ( $person as $line => $receipt )
            {
                if ( !$receipt->pdf )
                {
                    $receipt->createPDF();
                }
                
                $lastReceiptFileName = $receipt->getPdfFileName();
            }
        }
        
        //adiciona os itens que não puderam ser atualiazados
        if (  is_array($loansNotRenewed) )
        {
            $objectBusOperationRenew = new BusinessGnuteca3BusOperationRenew();
            foreach( $loansNotRenewed as $i=>$loan )
            {
                $objectBusOperationRenew->clearMessages();
                $objectBusOperationRenew->checkLoan($loan->loanId, $loan->personId);
                $msgs = $objectBusOperationRenew->messagesToString();
                
                if ( strlen($msgs) == 0 )
                {
                    $msgs .= _M('Material não selecionado.', $this->module);
                }

                $extraColumns['itemNumber'] = $loan->itemNumber;
                $this->busOperationRenew->addAlert(_M('Material não renovado - ' . $msgs, $this->module), $extraColumns);
            }
        }

    	$extraColumns['itemNumber'] = _M('Número do exemplar', $this->module);
        $table = $this->busOperationRenew->getMessagesTableRaw(null, null, $extraColumns);
        
        $tabControl = new GTabControl('tabDetail', _M('Resultado da renovação', $this->module));
        $tabControl->addTab( 'tabResponse', _M('Resultado',  $this->module), array($table, $table2));

        $filename = baseName($lastReceiptFileName);
        $busFile = $MIOLO->getBusiness('gnuteca3','BusFile');
        $file = $busFile->getFile('receipt'.'/'.$filename);

        $receiptText = $this->busOperationRenew->receipt->getReceiptsText();

        if ( $receiptText )
        {
	        $divReport = new MDiv('receiptBox', '<pre>'. str_replace("\r", '', $receiptText).'</pre>' );
	        $tabControl->addTab('tabReceipt',  _M('Recibo', $this->module), array($divReport));
            $botaoOpen = new MDiv('',new MButton('btnOpen', _M('Obter PDF', $this->module), "javascript:window.open('{$file->mioloLink}');", GUtil::getImageTheme('accept-16x16.png')) );
        }
        
        if ( $botaoOpen )
        {
        	$botoes[] = $botaoOpen;
        }

        $botoes[] = new MDiv( '', new MButton('btnClose', _M('Fechar', $this->module), $this->getCloseAndReloadAction(), GUtil::getImageTheme('exit-16x16.png')) );

        $botao      = new MHContainer('hctBotn', $botoes);

        $fields[] = $tabControl->generate();
        $fields[] = $botao;

        GForm::injectContent( $fields , false, true);
    }

    public function getLoansOpen()
    {
        $this->busLoan->personId = $this->busAuthenticate->getUserCode();
        $this->busLoan->orderByLibraryUnit = TRUE;
        return $this->busLoan->getLoansOpen();
    }
}
?>
