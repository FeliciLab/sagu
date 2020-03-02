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
//adiciona suporte a adião do código da unidade ao código do exemplar
if ( defined('EXEMPLAR_PLUS_UNIT') && EXEMPLAR_PLUS_UNIT == DB_TRUE && $_REQUEST['action'] == 'main:materialMovement' )
{
    //campo de inserção padrão
    if ( $_REQUEST['itemNumber'] && $_REQUEST['option'] !=  '[F5] Obra') //caso especial da reserva na catalogação
    {
        $_REQUEST['itemNumber'] = GOperator::getLibraryUnitLogged() . $_REQUEST['itemNumber'];
    }

    //checkpoint
    if ( $_REQUEST['itemNumberCheckPoint'] )
    {
        $_REQUEST['itemNumberCheckPoint'] = GOperator::getLibraryUnitLogged() . $_REQUEST['itemNumberCheckPoint'];
    }
}

class FrmMaterialMovement extends FrmMaterialCirculationChangePassword
{
    public $perms;

    public function __construct()
    {
        parent::__construct( _M('Circulação de material','gnuteca3'));
    }

    /**
     * Make the base form fields
     *
     */
    public function mainFields( $args , $setFields = true )
    {
        if ( GSipCirculation::usingSmartReader() )
        {
            session_start();
            $_SESSION['loanErrors'] = NULL;
            $_SESSION['returnErrors'] = NULL;
        }
        
    	//se tiver evento que dizer que nao é a primeira vez, então não faz nada
    	if ( $this->getEvent() )
    	{
    		return null;
    	}

        $type       = $this->getMMType();
        $fields[1]  = new MDiv('divRight', $this->loadForm( $args ) );
        $fields[2]  = new MDiv('limbo');
        $mainDiv    = new MDiv('divMain', $fields);
        
        
        //Verifica se está utilizando smartReader
        if(GSipCirculation::usingSmartReader())
        {
            //Método em JavaScript para validar os itens numbers
            $js = "
                var contadorDeItens = 0;

                var listaExemplares = new Array();
                var listaExemplaresReserva = new Array();
                var listaExemplaresChange = new Array();

                var pFila = setInterval(function(){processaExemplares()}, 200);
                //var pLimpa = setInterval(function(){limpaSessao()}, 1000);
                var pFilaReserva = setInterval(function(){processaExemplaresReserva()}, 200);
                var pFilaChange = setInterval(function(){processaExemplaresChange()}, 200);
                
                var sessaoLimpa = 1;
                
                var mutexTrava = 'f'; 
                

                function processaExemplares() {
                    // Verifica se tem itens na lista
                    if ( listaExemplares.length > 0 )
                    {
                        // Se tiver, processa o exemplar no gnuteca e remove o item da lista (com shift())

                        var itemNumber = listaExemplares[0];
                        listaExemplares.shift();

                        miolo.doAjax('addItemNumber', itemNumber, '__mainForm');
                        miolo.stopShowLoading();
                    }
                }
                
                /*function limpaSessao() {                    
                    if ( listaExemplares.length == 0 && sessaoLimpa == 0 )
                    {                        
                        miolo.doAjax('limpaSessao', '', '__mainForm');
                        miolo.stopShowLoading();
                        sessaoLimpa = 1;
                    }
                }*/
                
                function processaExemplaresReserva() {
                    // Verifica se tem itens na lista
                    if ( listaExemplaresReserva.length > 0 )
                    {
                        // Se tiver, processa o exemplar no gnuteca e remove o item da lista (com shift())

                        var itemNumber = listaExemplaresReserva[0];
                        listaExemplaresReserva.shift();

                        miolo.doAjax('addReserve', itemNumber, '__mainForm');
                        miolo.stopShowLoading();
                    }
                }
                
                function processaExemplaresChange() {
                    // Verifica se tem itens na lista
                    if ( listaExemplaresChange.length > 0 )
                    {
                        // Se tiver, processa o exemplar no gnuteca e remove o item da lista (com shift())

                        var itemNumber = listaExemplaresChange[0];
                        listaExemplaresChange.shift();

                        miolo.doAjax('itemNumberOnKeyDownChangeStatus', itemNumber, '__mainForm');
                        miolo.stopShowLoading();
                    }
                }

                function verificaItemNumber() 
                { 
                    // Obtem codigo do item corrente
                    var codItem = dojo.byId('itemNumber').value;
                    
                    // Define variável com os ids
                    var str = dojo.byId('codItens').value;

                    // Variável que seta se está liberado ou não para a ação
                    var liberado = true;


                    /*          -   -   -   -   -   -   -   -   -   -   -   -   -
                     * Verifica se o item à ser adiciona, já não está na lista de itens adicionados.
                       Caso não esteja, realiza a operação, e pôe o item na lista. 
                       Os itens adicionados ficam no campo 'codItens' do formulário   *
                                -   -   -   -   -   -   -   -   -   -   -   -   -     */
                    
                    var resultArray = str.split('-');
                    var sizeOfArray = resultArray.length;
                    
                    for(i=0; i<resultArray.length; i++)
                    {
                        if(resultArray[i] == codItem)
                        {
                            liberado = false;
                        }
                    }

                    if(liberado)
                    {
                        //Adiciona item ao array
                        dojo.byId('codItens').value = dojo.byId('codItens').value + '-' + codItem;

                        while( mutexTrava == 't'); // Trava até que outra operação tenha acabado.
                        mutexTrava = 't';
                        dojo.byId('codItensFinalize').value = dojo.byId('codItensFinalize').value + '-' + codItem;
                        mutexTrava = 'f';
                        
                        //Realiza a chamada para o algoritmo itemNumber
                        listaExemplares.push(codItem);
                        dojo.byId('itemNumber').value = '';
                        //sessaoLimpa = 0;
                        //miolo.doAjax('addItemNumber', '', '__mainForm');
                    }
                    else
                    {
                        //Não faz nenhuma ação, apenas limpa o itemNumber para receber o próximo valor
                        dojo.byId('itemNumber').value = '';
                    }
                }
                

                function verificaItemNumberReserva() 
                { 
                    // Obtem codigo do item corrente
                    var codItem = dojo.byId('itemNumber').value;
                    
                    // Define variável com os ids
                    var str = dojo.byId('codItens').value;

                    // Variável que seta se está liberado ou não para a ação
                    var liberado = true;


                    /*          -   -   -   -   -   -   -   -   -   -   -   -   -
                     * Verifica se o item à ser adiciona, já não está na lista de itens adicionados.
                       Caso não esteja, realiza a operação, e pôe o item na lista. 
                       Os itens adicionados ficam no campo 'codItens' do formulário   *
                                -   -   -   -   -   -   -   -   -   -   -   -   -     */
                    
                    var resultArray = str.split('-');
                    var sizeOfArray = resultArray.length;
                    
                    for(i=0; i<resultArray.length; i++)
                    {
                        if(resultArray[i] == codItem)
                        {
                            liberado = false;
                        }
                    }

                    if(liberado)
                    {
                        //Adiciona item ao array
                        dojo.byId('codItens').value = dojo.byId('codItens').value + '-' + codItem;

                        while( mutexTrava == 't'); // Trava até que outra operação tenha acabado.
                        mutexTrava = 't';
                        dojo.byId('codItensFinalize').value = dojo.byId('codItensFinalize').value + '-' + codItem;
                        mutexTrava = 'f';    

                        //Realiza a chamada para o algoritmo itemNumber
                        listaExemplaresReserva.push(codItem);
                        dojo.byId('itemNumber').value = '';
                    }
                    else
                    {
                        //Não faz nenhuma ação, apenas limpa o itemNumber para receber o próximo valor
                        dojo.byId('itemNumber').value = '';
                    }
                }


                function checkItemNumberChangeStatus() 
                { 
                    // Obtem codigo do item corrente
                    var codItem = dojo.byId('itemNumber').value;
                    
                    // Define variável com os ids
                    var str = dojo.byId('codItens').value;

                    // Variável que seta se está liberado ou não para a ação
                    var liberado = true;


                    /*          -   -   -   -   -   -   -   -   -   -   -   -   -
                     * Verifica se o item à ser adiciona, já não está na lista de itens adicionados.
                       Caso não esteja, realiza a operação, e pôe o item na lista. 
                       Os itens adicionados ficam no campo 'codItens' do formulário   *
                                -   -   -   -   -   -   -   -   -   -   -   -   -     */
                    
                    var resultArray = str.split('-');
                    var sizeOfArray = resultArray.length;
                    
                    for(i=0; i<resultArray.length; i++)
                    {
                        if(resultArray[i] == codItem)
                        {
                            liberado = false;
                        }
                    }

                    if(liberado)
                    {
                        //Adiciona item ao array
                        dojo.byId('codItens').value = dojo.byId('codItens').value + '-' + codItem;
                        
                        while( mutexTrava == 't'); // Trava até que outra operação tenha acabado.
                        mutexTrava = 't';
                        dojo.byId('codItensFinalize').value = dojo.byId('codItensFinalize').value + '-' + codItem;
                        mutexTrava = 'f';

                        //Realiza a chamada para o algoritmo itemNumber
                        listaExemplaresChange.push(codItem);
                        dojo.byId('itemNumber').value = '';
                        //miolo.doAjax('itemNumberOnKeyDownChangeStatus', '', '__mainForm');
                    }
                    else
                    {
                        //Não faz nenhuma ação, apenas limpa o itemNumber para receber o próximo valor
                        dojo.byId('itemNumber').value = '';
                    }
                } 
                
                function removeItemFromSmartReaderCache(itemNumber)
                {
                    var index;
                    var itens = document.getElementById('codItens').value;
                    var newValue = '';
                    var arrayItens = itens.split('-');
                                 
                    for(index = 1; index < arrayItens.length; index++)
                    {  
                        if( arrayItens[index] != itemNumber)
                        {
                            newValue += '-' +arrayItens[index];
                        }
                    }

                    document.getElementById('codItens').value = newValue;
                }
                    
                ";

            $this->page->addJsCode($js);
        }

        if ( $setFields )
        {
            $this->forceFormContent = true;
            $this->setFields( $fields );
        }

        $this->keyDownHandler(27, 113, 114, 115, 116, 117, 118,119,120,121,122,123);
    }

    public function getToolBar()
    {
        $MIOLO  = MIOLO::getInstance();
		$module = MIOLO::getCurrentModule();
		$action = MIOLO::getCurrentAction();

        $this->_toolBar = new GToolBar('toolBar', $MIOLO->getActionURL($module, $action));
        $this->_toolBar->removeButtons(array( MToolBar::BUTTON_NEW, MToolBar::BUTTON_SAVE, MToolBar::BUTTON_DELETE, MToolBar::BUTTON_EXIT, MToolBar::BUTTON_RESET, MToolBar::BUTTON_SEARCH, MToolBar::BUTTON_PRINT) );

        if ( $this->checkAcces('gtcMaterialMovementLoan') || $this->checkAcces('gtcMaterialMovementReturn') )
        {
            $this->_toolBar->addButton('btnAction118', null,  ':onkeydown118', '[F7] '. _M('Emprestar / Devolver', $module), true, GUtil::getImageTheme('materialMovement-32x32.png'), GUtil::getImageTheme('materialMovement-32x32.png'));
        }

        if ( $this->checkAcces('gtcMaterialMovementRequestReserve') || $this->checkAcces('gtcMaterialMovementAnswerReserve'))
        {
            $this->_toolBar->addButton('btnAction119', null ,':onkeydown119', '[F8] '. _M('Reservar material',  $module), true, GUtil::getImageTheme('toolbar-reserve.png') );
        }

        if ( $this->checkAcces('gtcMaterialMovementVerifyMaterial'))
        {
            $this->_toolBar->addButton('btnAction120',null ,':onkeydown120',  '[F9] ' ._M('Verificar material',   $module) , true, GUtil::getImageTheme('search-32x32.png') );
        }

        if ( $this->checkAcces('gtcMaterialMovementVerifyUser'))
        {
            $this->_toolBar->addButton('btnAction121', null  ,':onkeydown121', '[F10]'._M('Verificar usuário',      $module), true, GUtil::getImageTheme('toolbar-person.png') );
        }

        if ( $this->checkAcces('gtcMaterialMovementUserHistory') )
        {
            $this->_toolBar->addButton('btnAction122', null  ,':onkeydown122', '[F11] '._M('Histórico do usuário',      $module), true, GUtil::getImageTheme('toolbar-bond.png') );
        }

        if ( ($this->checkAcces('gtcMaterialMovementChangeStatus')) || ( $this->checkAcces('gtcMaterialMovementExemplaryFutureStatusDefined')) )
        {
            $this->_toolBar->addButton('btnAction123',null ,':onkeydown123', '[F12] '._M('Alterar estado',      $module) , true, GUtil::getImageTheme('toolbar-changeStatus.png') );
        }

        if ( $this->checkAcces('gtcMaterialMovementVerifyProof'))
        {
            //FIXME Implementar
            //$this->_toolBar->addButton('verifyProof',null  ,':verifyProof',  _M('Verify proof',      $module), true, GUtil::getImageTheme('report-32x32.png'));
        }

        if ( $this->checkAcces('gtcMaterialMovementChangePassword'))
        {
            $this->_toolBar->addButton('changePassword', null ,':changePassword', _M('Alterar senha',      $module) , true, GUtil::getImageTheme('toolbar-changePassword.png') );
        }

        return $this->_toolBar;
    }

    /**
     * Funcão chamada ao fechar a mWindow de verifyUser, chama a função da aba anterior
     * 
     * @param type $args 
     */
    function verifyUserOnClose( $args )
    {
        $this->setFocus('itemNumber');
        $this->mainFields( null, false);
        $this->setResponse('','limbo');
    }

    /**
     * Open VerifyUser window (MaterialMovement related)
     *
     * @param unknown_type $args
     */
    public function openVerifyUserWindow($args)
    {
        $personId = ($args->personId) ? $args->personId : $_SESSION['personId'];
        $urlWindow  = $this->manager->getActionURL($this->module, 'main:verifyUser', '', array('myEvent' => $args->event, 'personId' => $personId));
        $urlWindow  = str_replace('&amp;', '&', $urlWindow);

        $win = new MWindow( 'winVerifyUser' , array('url'=>$urlWindow,'title'=> _M( 'Verificação de usuário' , 'gnuteca3' ) ) );
        $this->page->onload("miolo.getWindow('winVerifyUser').open();");
        $this->setResponse('', 'limbo');
    }
    
    /**
     * Retorna modo de busca para evitar mensagem de campos modificados
     * 
     * @return string 'search'
     */
    public function getFormMode()
    {
        return 'search';
    }
}
?>
