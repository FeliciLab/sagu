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
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 30/05/2011
 *
 **/
class FrmPurchaseRequestSearch extends GForm
{
    public $business, $module;
    /** @var BusinessGnuteca3BusLibraryUnit */
    private $busLibraryUnit;
    private $busWorkflowStatus;

    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busLibraryUnit = $this->MIOLO->getBusiness('gnuteca3', 'BusLibraryUnit');
        $this->busWorkflowStatus = $this->MIOLO->getBusiness('gnuteca3', 'BusWorkflowStatus');
        $this->setAllFunctions('PurchaseRequest', array('libraryUnitId', 'personId', 'costCenterId', 'amount'), array('purchaseRequestId'), array('libraryUnitId', 'personId', 'costCenterId', 'amount'));
        $this->setWorkflow( 'PURCHASE_REQUEST' );
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new MIntegerField('purchaseRequestIdS', null, _M('Código',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('externalIdS', null, _M('Código externo', $this->module), FIELD_DESCRIPTION_LOOKUP_SIZE, '');
        $this->busLibraryUnit->labelAllLibrary = true;
        $this->busLibraryUnit->filterOperator = true;
        $fields[] =  new GSelection('libraryUnitIdS', null, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), null, null, null, true);
         
        $fields[] = new GPersonLookup('personIdS', _M('Pessoa', $this->modules), 'person');
        
        $lookup = array();
        $lookup[] = new MTextField('costCenterIdDescription', '', '', FIELD_DESCRIPTION_LOOKUP_SIZE, '', null, true);
        $fields[] = new GLookupField('costCenterIdS', '', _M('Centro de custo', $this->module), 'costCenter', $lookup );
        
        $purchaseFields = $this->business->parseFieldsPurchaseRequest();
        
        if ( is_array($purchaseFields) )
        {
            foreach ( $purchaseFields as $i=> $value )
            {
                if ( $value->searchable == DB_TRUE )
                {
                    $fields[] = new MTextField('dinamic' . $value->id, null, $value->label, FIELD_DESCRIPTION_SIZE);
                }
            }
        }
        
        $fields[] = new MIntegerField('amountS', null, _M('Quantidade', $this->module), FIELD_ID_SIZE);
        $fields[] = new GSelection('workflowStatusS', '', _M('Estado', $this->module), $this->busWorkflowStatus->listWorkflowStatus('PURCHASE_REQUEST'));
        $this->setFields( $fields );
    }
    
    /**
     * Método reescrito para tratar os dinamicFields
     * @param type $args 
     */
    public function searchFunction($args)
	{
        $args = GForm::corrigeEventosGrid($args);
        $data = $this->getData();
        
        //separa os campos dinâmicos
        $dinamicData = new stdClass();

        foreach ( $data as $i=>$nData )
        {
            if ( strpos($i, 'dinamic') === 0 )
            {
                $key = str_replace( array('_','dinamic'), array('.',  ''), $i );
                $dinamicData->$key = $nData;
            }
        }
        
        $_REQUEST['dinamicFields'] = $dinamicData;
        
        $this->setResponse( $this->getGrid( $args ), GForm::DIV_SEARCH );
	}
        
    public function btnDuplicatePurchase($args)
    {
        $args = GUtil::decodeJsArgs($args);
        GForm::question(_M("Você deseja duplicar a solicitação de compras @1 ?",$this->module, $args->purchaseRequestId), GUtil::getAjax('duplicatePurchase', $args));
        
    }
    
    public function duplicatePurchase($args)
    {
        $args = GUtil::decodeJsArgs($args);
        $busPurchaseRequest = $this->MIOLO->getBusiness('gnuteca3', 'BusPurchaseRequest');
        $duplicatedPurchase = $busPurchaseRequest->duplicatePurchaseRequest($args->purchaseRequestId, null);

        $content = ' A solicitação de compras número ' . $args->purchaseRequestId ." foi reaberta. \n";

        $content .= "\n";
        $content .= " Foi criada a solicitação de número " . $duplicatedPurchase->purchaseRequestId . ".";

        $mail = new GMail();

        $mail->addAddress( EMAIL_ADMIN_PURCHASE_REQUEST );
        $mail->setSubject( 'Reabertura da solicitação de compras '.$args->purchaseRequestId.'.' );
        $mail->setContent( $content );
        $mail->setIsHtml( true );
        $mail->send();
        
        $goto = Gutil::getCloseAction(true) . " dojo.byId('purchaseRequestIdS').value={$duplicatedPurchase->purchaseRequestId} ; " . GUtil::getAjax('searchFunction');
        GForm::information(_M('Solicitação de compra @1 criada.', $this->module, $duplicatedPurchase->purchaseRequestId),  $goto);
    }       
    

    
  
}
?>