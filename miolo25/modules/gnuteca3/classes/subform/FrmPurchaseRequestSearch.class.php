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
 * Class created on 03/06/2011
 *
 **/
class FrmPurchaseRequestSearch extends GSubForm
{
    public $MIOLO;
    public $module;
    public $action;
    public $business;
    public $grid;
    public $function;
    private $busLibraryUnit, $busWorkflowStatus;
    private $purchaseFields;

    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->action   = MIOLO::getCurrentAction();
        $this->business = $this->MIOLO->getBusiness( $this->module, 'BusPurchaseRequest');
        $this->busLibraryUnit = $this->MIOLO->getBusiness( $this->module, 'BusLibraryUnit');
        $this->busWorkflowStatus = $this->MIOLO->getBusiness( $this->module, 'BusWorkflowStatus');
        $this->function = MIOLO::_REQUEST('function');
        $this->purchaseFields = $this->business->parseFieldsPurchaseRequest();

        $this->gridName = 'GrdMyPurchaseRequest';
        $this->gridSearchMethod = 'searchPurchaseRequest';

        parent::__construct( _M('Sugestão de material', $this->module) );
                
        //realiza a pesquisa quando vem da inclusão de registro
        if ( $this->firstAccess() )
        {
            $session = $this->MIOLO->getSession();
            $event = $session->getValue('searchPurchaseRequest');
           
            if ( $event == DB_TRUE )
            {
                $this->MIOLO->page->onload(GUtil::getAjax('searchFunctionSub'));
                $session->setValue('searchPurchaseRequest', null);
            }
        }
    }

    public function createFields()
    {
        $fieldsSearch[] = new MDiv('divInterest', LABEL_PURCHASE_REQUEST_SEARCH );
        $fieldsSearch[] = new MSeparator('<br>');
      
        $text = new MImage('', '', GUtil::getImageTheme('add-16x16.png' ));

        $this->busLibraryUnit->labelAllLibrary = true;
        $libraries = $this->busLibraryUnit->listLibraryUnit();
        
        $fieldsSearch[] = new MIntegerField('purchaseRequestIdS', null, _M('Código',$this->module), FIELD_ID_SIZE);
        $fieldsSearch[] = $personId = new MTextField('personIdS', BusinessGnuteca3BusAuthenticate::getUserCode() );
        $personId->addStyle('display', 'none' );

        $this->busLibraryUnit->onlyWithAccess  = true;
        $fieldsSearch[] = new GSelection('libraryUnitIdS',   $this->libraryUnitIdS->value, _M('Unidade de biblioteca', $this->module), $libraries, null,null, null, true);
      
        if ( is_array($this->purchaseFields) )
        {
            foreach ( $this->purchaseFields as $i=> $value )
            {
                if ( $value->searchable == DB_TRUE )
                {
                    $fieldsSearch[] = new MTextField('dinamic' . $value->id, null, $value->label, FIELD_DESCRIPTION_SIZE);
                }
            }
        }
        
        $fieldsSearch[] = new MTextField('observationS', null, _M('Observação', $this->module), FIELD_DESCRIPTION_SIZE);
        $fieldsSearch[] = new GSelection('workflowStatusS', '', _M('Estado', $this->module), $this->busWorkflowStatus->listWorkflowStatus('PURCHASE_REQUEST'));
        $fieldsSearch[] = new MSeparator('<br>');

        $buttons[] = new MButton('btnSearch', _M('Buscar', $this->module), 'javascript:' . GUtil::getAjax('searchFunctionSub'), GUtil::getImageTheme('search-16x16.png'));
        
        if ( is_array($libraries) )
        {
            $buttons[] = new MButton('btnNewPurchase', _M('Sugerir novo material', $this->module), 'javascript:'.GUtil::getAjax('subForm','PurchaseRequest'), GUtil::getImageTheme('button_insert.png') );
        }
        
        $fieldsSearch[] = $divButtons = new Div('divButtons', $buttons);
        $divButtons->addStyle('text-align', 'center');
        
        $fieldsSearch[] = new MSeparator();
        $fieldsSearch[] = new MDiv( self::DIV_SEARCH );
        
        GForm::jsSetFocus('purchaseRequestIdS',false);
        $this->setFields( GUtil::alinhaForm($fieldsSearch));
    }

    public function getGrid()
    {
        //adiciona % na frente, para facilitar vida do usuário
        if ( $_REQUEST['observationS'] )
        {
            $_REQUEST['observationS'] = '%'.$_REQUEST['observationS'];
        }
        
        return parent::getGrid();
    }

    /**
     * Obtem dados da grid por consulta no banco
     *
     * @return (array) de dados
     */
    public function getGridData()
    {
        $data   = (object)$_REQUEST;
        
        //separa os campos dinâmicos
        $newData = new stdClass();
        $dinamicData = new stdClass();

        foreach ( $data as $i=>$nData )
        {
            if ( strpos($i, 'dinamic') === 0 )
            {
                $newData->$i = $nData;
                $key = str_replace( array('_','dinamic'), array('.',  ''), $i );
                $dinamicData->$key = $nData;
            }
            else
            {
                $newData->$i = $nData;
            }
        }
        
        $newData->dinamicFields = $dinamicData;
        
        $this->business->setData($newData);
        //Protecao anti select massivo
        if (MIOLO::_REQUEST('personIdS'))
        {
            return $this->business->searchPurchaseRequest('E.date desc');
        }
    }
}
?>