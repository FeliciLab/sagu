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
class FrmPurchaseRequest extends GSubForm
{
    public $MIOLO;
    public $module;
    public $action;
    /** @var BusinessGnuteca3BusPurchaseRequest */
    public $business;
    public $grid;
    public $function;
    private $busLibraryUnit, $busWorkflowStatus;
    public $purchaseFields;

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

        $libraries = $this->busLibraryUnit->listListLibraryUnitAcceptingPurchaseRequest(BusinessGnuteca3BusAuthenticate::getUserCode()); //Pega unidades de biblioteca do usuário.

        if ( !is_array($libraries) ) //Se não tiver unidades de bibliotecas o usuário não tem permissão para sugerir compra de materiail.
        {
            GForm::information(_M('Você não possui permissão para sugerir novos materias', $this->module), 'javascript:' . GUtil::getCloseAction());
        }
        else
        {
            parent::__construct( _M('Sugestão de material', $this->module) );
        }
    }

    public function createFields()
    {
        $fields[] = new MDiv('divInterest', LABEL_PURCHASE_REQUEST );
        $fields[] = new MSeparator('<br>');
        GForm::jsSetFocus('libraryUnitId',false);
        
        $this->busLibraryUnit->onlyWithAccess  = true;
        $fields[] = new GSelection('libraryUnitId', '', _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listListLibraryUnitAcceptingPurchaseRequest(BusinessGnuteca3BusAuthenticate::getUserCode()), null,null, null, true);

        //campos dinâmicos
        $purchaseFields = $this->purchaseFields;

        if ( is_array($purchaseFields) )
        {
            foreach ( $purchaseFields as $i=> $value )
            {
                $dinamicField = 'dinamic' . $value->id; //Nome do campo
                $dinamicValue = base64_decode(MIOLO::_REQUEST($dinamicField)); //Valor para ele decodificando a base 64 por causa de problemas com o miolo no aninhamento de " e ' que estava conflitando.
                $fields[] = new MTextField( $dinamicField, $dinamicValue, $value->label, FIELD_DESCRIPTION_SIZE, $value->hint);
            }
        }

        $fields[] = new MIntegerField('amount', 1, _M('Quantidade', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('course', null, _M('Curso', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MMultiLineField('observation', null, _M('Observação', $this->module), NULL, FIELD_MULTILINE_ROWS_SIZE, 60);
        $fields[] = new MSeparator('<br>');
        $buttons[] = new MButton('save', _M('Salvar'), 'javascript:' . GUtil::getAjax('savePurchaseRequest'),GUtil::getImageTheme('save-16x16.png'));
        $buttons[] = new MButton('myPurchases', _M('Minhas sugestões', $this->module), 'javascipt:' . GUtil::getAjax('subForm', 'PurchaseRequestSearch'), GUtil::getImageTheme('search-16x16.png'));  
        $fields[] = $divButtons = new Div('divButtons', $buttons);
        $divButtons->addStyle('text-align', 'center');
        
        $this->setFields( GUtil::alinhaForm($fields) );
    }

    public function getData()
    {
        $data = parent::getData();
        $data->personId = BusinessGnuteca3BusAuthenticate::getUserCode();

        return $data;
    }

    /**
     * Função que retorna os validadores
     * @return MRequiredValidator
     */
    public function getValidators()
    {
        $purchaseFields = $this->purchaseFields;

        if ( is_array($purchaseFields) )
        {
            foreach ( $purchaseFields as $i=> $value )
            {
                if ( $value->required == DB_TRUE )
                {
                    $validators[]   = new MRequiredValidator('dinamic' . $value->id, $value->label );
                }
            }
        }

        $validators[] = new MIntegerValidator('amount', _M('Quantidade', 'gnuteca3'), 'required');
        $validators[] = new MRequiredValidator('libraryUnitId');
        $validators[] = new MRequiredValidator('course', _M('Curso', 'gnuteca3'));
        
        return $validators;
    }

    /**
     *  Salva a solicitação de compras
     */
    public function savePurchaseRequest()
    {
        try
        {
            //validação
            if (  ! $this->validate() )
            {
                return false;
            }

            $MIOLO = MIOLO::getInstance();
            $data = $this->getData();

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

            $tableId = $business->$key; //obtem código da tabela retornado do insert;
            $this->manager->getClass('gnuteca3', 'GWorkflow');

            $this->business->beginTransaction();
            $this->business->setData($newData);

            if ( $this->business->insertPurchaseRequest() )
            {
                $worflowOk = GWorkFlow::instance( 'PURCHASE_REQUEST', 'gtcPurchaseRequest', $this->business->purchaseRequestId );
                $_REQUEST['event'] = 'btnSearch';
                $session = $MIOLO->getSession();
                $session->setValue('searchPurchaseRequest', DB_TRUE);
                GForm::information( _M('Sugestão de livro inserida com sucesso!'), GUtil::getAjax('subForm','PurchaseRequestSearch'). GUtil::getCloseAction() );
            }
            else
            {
                GForm::error(MSG_RECORD_ERROR);
            }

            $this->business->commitTransaction();
        }
        catch ( Exception $e )
        {
            GForm::error( $e->getMessage() );
        }
    }
}
?>