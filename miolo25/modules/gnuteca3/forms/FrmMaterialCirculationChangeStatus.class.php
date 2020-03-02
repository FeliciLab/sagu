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
class FrmMaterialCirculationChangeStatus extends FrmMaterialCirculationUserHistory
{
	public $MIOLO;
	public $module;
	public $busExemplaryStatus;


    public function __construct()
    {
    	$this->MIOLO  = MIOLO::getInstance();
    	$this->module = MIOLO::getCurrentModule();
    	$this->busExemplaryStatus = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        parent::__construct();
    }


    /**
    * Mount the ChangeStatus form , called when user press F12
    *
    **/
    public function onkeydown123( $args ) //F12
    {
        // Só mostra função de agendamento se operador tiver permissão.
        if (GPerms::checkAccess('gtcMaterialMovementChangeStatus', NULL, false))
        {
            $module = MIOLO::getCurrentModule();
            $this->setMMType('123');
            $this->changeTab('btnAction123');

            //Só tem acesso se tiver permissão gtcMaterialMovementChangeStatus
            if (GPerms::checkAccess('gtcMaterialMovementChangeStatus', NULL, false))
            {
                $functionButtons[]  = new MButton('btnChange',      _M('[F2] Alterar',   $module),   ':onkeydown113');
            }

            $functionButtons[]  = new MButton('btnSchedule',    _M('[F3] Agendar', $module),   ':onkeydown114');        
            $functionButtons[]  = new MButton('btnFinalize',    _M('[F4] Finalizar', $module),   ':onkeydown115');
            $functionButtons[]  = new MButton('btnCleanData',   _M('[ESC] Limpar', $module),':onkeydown27');

            $fields[]           = $this->getButtonContainer($functionButtons);
            $line               = null;
            $line[0][]          = $this->getLibrarySelection();

            //Monta lista de status válidos
            $busExemplaryStatus = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
            $statusList         = $busExemplaryStatus->listExemplaryStatus(null, true);

            if (GPerms::checkAccess('gtcMaterialMovementChangeStatusInitial', null, false))
            {
                $levelStatus['level0'] = _M('Estado Anterior', $this->module);
                $statusList = array_merge($levelStatus, $statusList);
            }

            $line[1][0] = new MLabel( _M('Estado futuro', $this->module) );
            $line[1][1] = $exemplaryStatusId = new GSelection('exemplaryStatusId', '', null,$statusList, false, false, false,false) ;
            $exemplaryStatusId->addAttribute('onPressEnter', GUtil::getAjax('exemplaryStatusIdOnkeyDown'));
            $exemplaryStatusId->addEvent('change', GUtil::getAjax('exemplaryStatusIdOnkeyDown') );


            $downFields[] = new MDiv('divLowDate',array(new MLabel('Data de baixa:', $this->module),new MCalendarField('lowDate', GDate::now(), null)));
            //TODO esta com alinhamento errado.
            $downFields[] = new MDiv('divObservation',array(new MLabel(_M('Observação',$this->module).":", $this->module),new MMultiLineField('observation','',null, null, 4, 40)));
            $downFields[] = new MDiv('divMailObservation',array(new MLabel('Enviar e-mail de cancelamento de reserva:', $this->module),new MCheckBox('observationSendMail', true, null, true)));
            $downFields[] = new MDiv('divSendMailObservation', $this->getMailObservationField());
            $downFields = new MFormContainer('abc', $downFields );

            $line[3][1] = $divDown = new MBaseGroup('divDown',_M('Baixa',$this->module), array($downFields), 'vertical');
            $divDown->addStyle('display','none');

            $lblItemNumber = new MLabel( _M('Número do exemplar', $this->module) . ':' );
            
            //Verifica se utiliza smartReader
            if(GSipCirculation::usingSmartReader())
            {
                $itemNumber[] = $iN =  new MTextField('itemNumber');
                $iN->addAttribute('onPressEnter', 'checkItemNumberChangeStatus();');
                $iN->setReadOnly(true);
                
                $fields[] = $codItens =  new MTextField('codItens', '', '', 100, null, null, TRUE);
                $codItens->addStyle('display', 'none');
            
                $fields[] = $codItensFinalize =  new MTextField('codItensFinalize', '', '', 100, null, null, TRUE);
                $codItensFinalize->addStyle('display', 'none');
               
               // $line[1][2] = new MDiv('', array($codItens, $codItensFinalize));
                $btnAdd = new MImageButton('btnAdd', NULL, "javascript:checkItemNumberChangeStatus();dojo.byId('itemNumber').focus();", GUtil::getImageTheme('add-16x16.png'));
                $btnAdd->addAttribute('title', _M('Adicionar', $this->mMVContainerodule));
                
                $lblItems = '<div id="contador">Itens [0]</div>';
            }
            else
            {
                $itemNumber = new MTextField('itemNumber');
                $itemNumber->addAttribute('onPressEnter', GUtil::getAjax('itemNumberOnKeyDownChangeStatus'));
                $itemNumber->setReadOnly(true);
                
                $btnAdd = new MImageButton('btnAdd', NULL, "javascript:" . GUtil::getAjax('itemNumberOnKeyDownChangeStatus'), GUtil::getImageTheme('add-16x16.png'));
                $btnAdd->addAttribute('title', _M('Adicionar', $this->mMVContainerodule));
                
                $lblItems = 'Itens';
            }

            $line[2][0] = new MDiv('hctAdd', array($lblItemNumber, $itemNumber, $btnAdd));

            $tableChangeStatus  = new GRepetitiveField('tableChangeStatus', _M($lblItems, $module), null, null, array('noButtons'));
            $tableChangeStatus->setShowButtons(false);

            $tableChangeStatusColumns  = array
            (
                new MGridColumn( _M('Número do exemplar', $module)         , 'left', true, "20%", true, 'itemNumber' ),
                new MGridColumn( _M('Dados',  $module)               , 'left', true, "64%", true, 'exemplaryData' ),
                new MGridColumn( _M('Estado do exemplar',  $module)   , 'left', true, "64%", true, 'exemplaryStatusDescription' ),
                new MGridColumn( _M('Data da baixa',  $module)           , 'left', true, "64%", false, 'lowDate' ),
                new MGridColumn( _M('Descrição da baixa',  $module)    , 'left', true, "64%", false, 'observation' ),
            );

            $tableChangeStatus->setColumns($tableChangeStatusColumns);
            $tableChangeStatus->addAction('removeItemNumberChangeStatus', 'table-delete.png', $module);

            $line[4][0]         = $tableChangeStatus;
            $fields             = array_merge($fields, $this->mountContainers($line) );
            $divChangeStatus    = new MDiv('divChangeStatus', $fields);

            $this->jsSetFocus('exemplaryStatusId', false);

            return $this->addResponse( $fields, $args );
        }
        else
        {
            $this->setResponse(NULL, 'divResponse');
        }
    }


    /**
     * Function called when user press F2 change
     *
     * @param array $args
     */
    public function onkeydown113_123( $args )
    {
    	//Só mostra função de alterar se operador tiver permissão
        if (GPerms::checkAccess('gtcMaterialMovementChangeStatus', NULL, false))
        {
	        $this->setMMOperation('CHANGE');
	        $this->cleanData123( $args );
	    	$this->busOperationChangeStatus->setChangeType(1);
	        $this->jsChangeButtonColor('btnChange');
	        $this->jsEnabled('exemplaryStatusId');
	        $this->jsEnabled('libraryUnitId_');
	        $this->jsShow('lowDateDiv');
                
	        if ( !$args->return )
	        {
	            $this->setResponse('','limbo');
	        }
        }
        else
        {
            $this->setResponse('','limbo');
            $this->onkeydown114_123();
        }
    }


    /**
     * Function called when user press F3 schedule
     *
     * @param array $args
     */
    public function onkeydown114_123( $args )
    {
        //Só mostra função de agendamento se operador tiver permissão
        if (GPerms::checkAccess('gtcMaterialMovementExemplaryFutureStatusDefined', NULL, false))
        {
            $this->setMMOperation('SCHEDULE');
            $this->cleanData123( $args );
            $this->busOperationChangeStatus->setChangeType(2);
            $this->jsChangeButtonColor('btnSchedule');
            $this->jsEnabled('exemplaryStatusId');
            $this->jsEnabled('libraryUnitId_');
            $this->jsHide('lowDateDiv');

            if ( !$args->return )
            {
                $this->setResponse('','limbo');
            }
        }
        else
        {
            $this->setResponse('','limbo');
        }
    }


    /**
     * Function called when user press F4 (finalize)
     *
     * @param array $args
     */
    public function onkeydown115_123( $args )
    {
        $this->setResponse(array($this->getMailObservationField()), 'divSendMailObservation') ;
    	$ok  = $this->busOperationChangeStatus->finalize($args);
        
        if ( $ok )
        {
            $extraColumns['itemNumber']    = _M('Número do exemplar', $this->module );
            $extraColumns['currentStatus'] = _M('Estado atual', $this->module );
            $extraColumns['futureStatus']  = _M('Estado futuro', $this->module );
            $table  = $this->busOperationChangeStatus->getMessagesTableRaw(null, null, $extraColumns);
            $this->page->onLoad("dojo.byId('observation').value = '';");
            $this->page->onLoad("dojo.byId('observationSendMail').checked = true;");

            //é necessário dar o foco para o botão fechar para o esc funcionar
            $this->injectContent( $table, "miolo.doAjax('onkeydown27','','__mainForm');", true );
        }
        else
        {
            $this->page->onload("gnuteca.bloqueia_tecla = false");
            $this->setResponse('', 'limbo');
        }
        
    }


    /**
     * Clean change status Data
     *
     * @param object $args
     */
    public function cleanData123( $args )
    {
    	$this->jsChangeButtonColor('btnCleanData');
        
    	$this->jsSetValue('itemNumber', '' );
    	$this->jsSetReadOnly('itemNumber', true );

    	$this->jsDisabled('libraryUnitId_');

    	$this->jsDisabled('exemplaryStatusId');
    	$this->jsSetValue('exemplaryStatusId','' );
    	$this->jsHide('divDown');

        $this->busOperationChangeStatus->clearItems();
        
        $_SESSION['mudarEstadoExemplar'] = NULL;

        if ( !$args->cleanData )
        {
            $args->cleanData = true;
            $op = $this->getMMOperation();
            //ifs de ultima ação e dados salvos
            if ($op == 'SCHEDULE')
            {
                $this->onkeydown114_123( $args );
            }
            else
            {
                $this->onkeydown113_123( $args );
            }
        }

        if ( !$args->return )
        {
        	$items = null ; //FIXME colocado pois o $items esta indefinido
            GRepetitiveField::update( $items , 'tableChangeStatus');
        }
        $dateNow = GDate::now();
        $this->page->onLoad("dojo.byId('lowDate').value = '$dateNow';");
        $this->page->onLoad("dojo.byId('observation').value = '';");
        $this->page->onLoad("dojo.byId('observationSendMail').checked = true;");
        
        if(GSipCirculation::usingSmartReader())
        {
            $this->jsSetFocus('popupTitle');
            $this->limpaCacheSmartReader();
        }
        
        // Atualiza dados da tabela de itens.
        $this->updateTableItens();
    }


    /**
     * Event called when user press enter or tab in exemplaryStatus field
     *
     * @param object $args stdclass
     */
    public function exemplaryStatusIdOnkeyDown( $args )
    {
        $this->busOperationChangeStatus->setLibraryUnit( $args->libraryUnitId_ );

        //determina se é por level ou por estado futuro
        if ($args->exemplaryStatusId == 'level0')
        {
        	$this->busOperationChangeStatus->setLevel('0');
        }
        else
        if ($args->exemplaryStatusId == 'level1')
        {
        	$this->busOperationChangeStatus->setLevel('1');
        }
        else
        {
            $exemplaryStatus = $this->busOperationChangeStatus->setExemplaryFutureStatus( $args->exemplaryStatusId );
        }

        if ( MUtil::getBooleanValue( $exemplaryStatus->isLowStatus ) )
        {
        	$this->jsShow('divDown');
        }
        else
        {
        	$this->jsHide('divDown');
        }

        $this->page->onload( 'dojo.parser.parse();'); //faz o parse dos campos de calendário
        $this->jsSetReadOnly('itemNumber', false);
        $this->jsSetFocus('itemNumber');
        $this->setResponse('','limbo');
    }


    /**
     * Event called when user press enter or tab in itemNumber field
     *
     * @param object $args
     */
    public function itemNumberOnKeyDownChangeStatus( $args )
    {
        if ( !is_object($args) )
        {
            $itemNumber = $args;
            $args = (object) $_REQUEST;
            $args->itemNumber = $itemNumber;
        }
        
        if(GSipCirculation::usingSmartReader())
        {
             $itemNumberC = '-' . $args->itemNumber;
             
            $js = "
            while( mutexTrava == 't');
             mutexTrava = 't';
            codItensFinalize = dojo.byId('codItensFinalize').value;
            codItensFinalize = codItensFinalize.replace('{$itemNumberC }', '');
            dojo.byId('codItensFinalize').value = codItensFinalize;
            mutexTrava = 'f';";

            $this->page->onload($js);
        }
        
        $exemplaryStatus = $this->busExemplaryStatus->getExemplaryStatus( $args->exemplaryStatusId , true);

        if ( !MUtil::getBooleanValue( $exemplaryStatus->isLowStatus ) )
        {
            $args->observationSendMail = false;
        }
        
        if ( MUtil::getBooleanValue($args->observationSendMail) == false )
        {
            $args->observationMail = null;
        }
        
        $this->jsSetValue('itemNumber', '' );
        
        $ok = $this->busOperationChangeStatus->addItemNumber( $args->itemNumber , $args->lowDate, $args->observation, $args->observationMail );

        $this->jsDisabled('exemplaryStatusId');
        $this->jsDisabled('libraryUnitId_');

        if ( $ok )
        {
            $items  = $this->busOperationChangeStatus->getItems();
            GRepetitiveField::update( $items, 'tableChangeStatus');
        }
        else
        {
            if ( GSipCirculation::usingSmartReader() )
            {
                $erros = $this->busOperationChangeStatus->getErrors();

                if ( is_array($erros) )
                {
                    $_SESSION['mudarEstadoExemplar'][$args->itemNumber]  = serialize($erros[0]);
                }

                $this->busOperationChangeStatus->clearMessages();

                $erros = array();
                foreach ( $_SESSION['mudarEstadoExemplar'] as $error )
                {
                    $erros[] = unserialize($error);
                }

                $this->busOperationChangeStatus->setMessages($erros);
                $this->page->onload("removeItemFromSmartReaderCache('{$args->itemNumber}');");
            }
            
            $extraColumns['itemNumber'] = _M('Número do exemplar', $this->module);
            $table = $this->busOperationChangeStatus->getMessagesTableRaw(null, false, $extraColumns);
            
            $this->injectContent($table, "dojo.byId('itemNumber').focus(); return false;", TRUE);
        }
        
        if ( GSipCirculation::usingSmartReader() )
        {    
            $this->jsSetFocus('itemNumber');
        }
        else
        {
            $this->jsSetFocus('btnClose');
        }
        
        $this->updateTableItens();
    }
    
    
    /**
     * Atualiza tabela com itens de mudança de estado.
     */
    private function updateTableItens()
    {
        $items  = $this->busOperationChangeStatus->getItems();
        
        if ( GSipCirculation::usingSmartReader() )
        {
            $totalItens = count($items);
            
            $this->page->onload("
                contadorDeItens = {$totalItens};
                dojo.byId('contador').innerHTML = 'Itens [' + contadorDeItens + ']';
            ");
        }
        
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $args
     */
    public function removeItemNumberChangeStatus($args)
    {
        $items = $this->busOperationChangeStatus->getItems();

        if (is_array($items))
        {
            $line = 0;
            foreach ($items as $info)
            {
                if ( $args->arrayItemTemp == $line)
                {
                    //Verifica se utiliza smartReader
                    if(GSipCirculation::usingSmartReader())
                    {
                        $this->page->onload("removeItemFromSmartReaderCache('{$info->itemNumber}');");
                    }
                    
                    $this->busOperationChangeStatus->deleteItemNumber($info->itemNumber);
                }
                $line++;
            }
        }
        
        $newItems = $this->busOperationChangeStatus->getItems();
        GRepetitiveField::setData($newItems,'tableChangeStatus');
        $this->setResponse(GRepetitiveField::generate(false, 'tableChangeStatus') , 'divtableChangeStatus');
        
        if ( GSipCirculation::usingSmartReader() )
        {
            $totalItens = count($newItems);
            
            $this->page->onload("
                contadorDeItens = {$totalItens};
                dojo.byId('contador').innerHTML = 'Itens [' + contadorDeItens + ']';
            ");
        }
    }
    
    public function getMailObservationField()
    {
        return array( new MLabel(_M('Observação do e-mail de cancelamento de reserva',$this->module)).":", new MMultiLineField('observationMail',  EMAIL_CANCEL_RESERVE_COMUNICA_SOLICITANTE_CONTENT, null, null, 6, 40) );
    }    
}
?>
