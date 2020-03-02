<?php

class wfPurchaseRequest
{
    /**
     * Dados passados para a função
     * 
     * @var stdClass
     */
    protected $data;

    /**
     * Objeto GFunction
     *
     * @var GFunction
     */
    protected $gFunction;

    /**
     * Definição do email administrativo
     *
     * @var string
     */
    protected $adminMail;

    /**
     * Estado futuro chamado após relação com circulação de material, normalmente CATALOGADA
     *
     * @var integer
     */
    protected static $relationWithMaterialCirculationFuturesStatus = 100008;

    /**
     * Variável que identidica o código do estado finalizado.
     *
     * @var integer
     */
    protected static $finalizedStatus = 100009;

    /**
     * Passa pelo construtor toda vez e define os parametros básicos.
     *
     * @param stdClass $data
     */
    public function __construct( $data )
    {
        $MIOLO = MIOLO::getInstance();
        $busPurchaseRequest = $MIOLO->getBusiness( 'gnuteca3', 'BusPurchaseRequest');
        $busPurchaseRequest = new BusinessGnuteca3BusPurchaseRequest();
        
        if ( $data->instance->tableId )
        {
            $purchase = $busPurchaseRequest->getPurchaseRequest( $data->instance->tableId );
        }
        
        $data->purchaseRequest = $purchase;

        if ( $purchase )
        {
            //ajeita conteúdo dos campos dinâmicos
            $dinamicFields = $data->purchaseRequest->dinamicFields;

            if ( is_array( $dinamicFields ) );
            {
                foreach ( $dinamicFields as $key => $field )
                {
                    $field->subFieldId = $field->subfieldId;
                    $values[ $field->fieldId . '.' . $field->subfieldId][] = $field;
                    $fields[ $field->fieldId . '.' . $field->subfieldId][] = $field->content;
                }
            }
            
            $data->fields = $fields;
            
            //obtem conteúdo formatado
            $busSearchFormat = $MIOLO->getBusiness('gnuteca3', 'BusSearchFormat');
            $data->purchaseRequest->materialContent = $busSearchFormat->formatSearchData( ADMINISTRATION_SEARCH_FORMAT_ID, $values );

            $variables['$username'] = $data->purchaseRequest->name;
            $variables['$purchaseRequestId'] = $data->purchaseRequest->purchaseRequestId;
            $variables['$comment'] = $data->comment;
            //troca br por linha nova para poder reinterpretar após
            $variables['$content'] = str_replace('<br/>', "\n", $data->purchaseRequest->materialContent );

            $this->gFunction = new GFunction();
            $this->gFunction->setVariables( $variables );

            //escolhe email administrativo
            $this->adminMail = defined('EMAIL_ADMIN_PURCHASE_REQUEST') ? EMAIL_ADMIN_PURCHASE_REQUEST : EMAIL_ADMIN;
        }

        $this->setData( $data );
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Define os dados.
     * Pode ser extendido para modificar os dados.
     *
     * @param stdClass $data
     */
    public function setData( $data )
    {
        $this->data = $data;
    }

    /**
     * Inicializa solicitação de compras enviando email para administrador e solicitante
     *
     * @return boolean
     */
    public function initialize()
    {
        $MIOLO = MIOLO::getInstance();

        $mail = new GMail();

        if ( $this->data->purchaseRequest->email  )
        {
            $mail->addAddress( $this->data->purchaseRequest->email );
        }

        $mail->addAddress( $this->adminMail );
        $mail->setSubject( EMAIL_PURCHASE_REQUEST_INITIALIZE_SUBJECT );
        $mail->setContent( $this->gFunction->interpret( EMAIL_PURCHASE_REQUEST_INITIALIZE_CONTENT ) );
        $mail->setIsHtml( true );

        $mail->send();
        
        return true;
    }

    public function purchaseRequest()
    {
        //'integra com compras';
        if ( !$this->data->costCenterId )
        {
            throw new Exception ( _M('É necessário centro de custo para solicitar a compra!') );
        }

        return true;
    }

    /**
     * Função de cancelamento
     *
     * @return boolean
     */
    public function cancel()
    {
        $MIOLO = MIOLO::getInstance();

        if (  ! ( $this->data->purchaseRequest->comment || $this->data->comment ) )
        {
            throw new Exception ( _M('É necessário informar um motivo para o cancelamento!') );
        }

        //define variáveis extras
        $this->gFunction->setVariable('$controlNumberLink','');

        //caso exista número de controle troca por uma url para a pesquisa
        if ( $this->data->purchaseRequest->controlNumber )
        {
            $url = $MIOLO->getActionURL( 'gnuteca3', 'main:search:simpleSearch', null, array( 'controlNumber' => $this->data->purchaseRequest->controlNumber ));
            $url = "<a href='{$url}'>Clique aqui para visualizar o material.</a>";
            //feito desta forma porque o GFunction não suporta conteúdo HTML
            $this->gFunction->setVariable( '$controlNumberLink','$tmpControlNumberLink');
        }

        //interpretação, feito desta forma porque o GFunction não suporta conteúdo HTML
        $content = str_replace( '$tmpControlNumberLink', $url, $this->gFunction->interpret( EMAIL_PURCHASE_REQUEST_CANCEL_CONTENT ) );

        $mail = new GMail();
        $mail->addAddress( $this->data->purchaseRequest->email );
        $mail->addAddress( $this->adminMail );
        $mail->setSubject( EMAIL_PURCHASE_REQUEST_CANCEL_SUBJECT );
        $mail->setContent( $content );
        $mail->setIsHtml( true );

        $mail->send();
        
        //caso tenha número da pré catalogação tenta remove-lo
        if ( $this->data->purchaseRequest->preControlNumber )
        {
            $busPreCatalogue = $MIOLO->getBusiness( 'gnuteca3', 'BusPreCatalogue');
            $busPreCatalogue = new BusinessGnuteca3BusPreCatalogue();
            $busPreCatalogue->controlNumber = $this->data->purchaseRequest->preControlNumber;
            $busPreCatalogue->deleteMaterial( true );
            
            $busPurchaseRequest = $MIOLO->getBusiness( 'gnuteca3', 'BusPurchaseRequest');
            $busPurchaseRequest = new BusinessGnuteca3BusPurchaseRequest();
            $busPurchaseRequest->setData( $this->data->purchaseRequest );
            $busPurchaseRequest->preControlNumber = ''; //limpa número na solicitação
            $busPurchaseRequest->updatePurchaseRequest( true );
        }

        return true;
    }

    /**
     * Função chamada na transição de aprovação
     *
     * @return boolean
     */
    public function aprove()
    {
        $MIOLO = MIOLO::getInstance();
        
        if ( !$this->data->purchaseRequest->forecastDelivery )
        {
            throw new Exception ( _M('Para aprovar é necessário definir uma previsão de entrega!') );
        }

        $this->gFunction->setVariable( '$forecastDelivery', $this->data->forecastDelivery );

        $mail = new GMail();
        $mail->addAddress( $this->data->purchaseRequest->email );
        $mail->addAddress( $this->adminMail );
        $mail->setSubject( EMAIL_PURCHASE_REQUEST_APROVE_SUBJECT );
        $mail->setContent( $this->gFunction->interpret( EMAIL_PURCHASE_REQUEST_APROVE_CONTENT) );
        $mail->setIsHtml( true );

        $busPurchaseRequest = $MIOLO->getBusiness( 'gnuteca3', 'BusPurchaseRequest');
        $busPurchaseRequest = new BusinessGnuteca3BusPurchaseRequest();

        //gera registro da pré-catalogação
        $busPurchaseRequest->convertToPreCatalogue( $this->data->purchaseRequest->purchaseRequestId );

        $mail->send();

        return true;
    }

    public function catalogue()
    {
        if ( ! $this->data->purchaseRequest->controlNumber )
        {
            throw new Exception ( _M('É necessário informar um número de controle para catalogar!') );
        }

        return true;
    }

    public function finalize()
    {
        $MIOLO = MIOLO::getInstance();
        
        if ( ! $this->data->purchaseRequest->controlNumber )
        {
            throw new Exception ( _M('É necessário informar um número de controle para finalização!') );
        }
       
        //caso tenha número da pré catalogação tenta remove-lo
        if ( $this->data->purchaseRequest->preControlNumber )
        {
            $busPreCatalogue = $MIOLO->getBusiness( 'gnuteca3', 'BusPreCatalogue');
            $busPreCatalogue = new BusinessGnuteca3BusPreCatalogue();
            $busPreCatalogue->controlNumber = $this->data->purchaseRequest->preControlNumber;
            $busPreCatalogue->deleteMaterial( true );
            
            $busPurchaseRequest = $MIOLO->getBusiness( 'gnuteca3', 'BusPurchaseRequest');
            $busPurchaseRequest = new BusinessGnuteca3BusPurchaseRequest();
            $busPurchaseRequest->setData( $this->data->purchaseRequest );
            $busPurchaseRequest->preControlNumber = ''; //limpa número na solicitação
            $busPurchaseRequest->updatePurchaseRequest( true );
        }

        //define variáveis extras
        $this->gFunction->setVariable('$controlNumberLink','');

        //caso exista número de controle troca por uma url para a pesquisa
        if ( $this->data->purchaseRequest->controlNumber )
        {
            $url = $MIOLO->getActionURL( 'gnuteca3', 'main:search:simpleSearch', null, array( 'controlNumber' => $this->data->purchaseRequest->controlNumber ));
            $url = "<a href='{$url}'>Clique aqui para visualizar o material.</a>";
            //feito desta forma porque o GFunction não suporta conteúdo HTML
            $this->gFunction->setVariable( '$controlNumberLink','$tmpControlNumberLink');
        }

        //interpretação, feito desta forma porque o GFunction não suporta conteúdo HTML
        $content = str_replace( '$tmpControlNumberLink', $url, $this->gFunction->interpret( EMAIL_PURCHASE_REQUEST_FINALIZE_CONTENT ) );

        $mail = new GMail();
        $mail->addAddress( $this->data->purchaseRequest->email );
        $mail->addAddress( $this->adminMail );
        $mail->setSubject( EMAIL_PURCHASE_REQUEST_FINALIZE_SUBJECT );
        $mail->setContent( $content );
        $mail->setIsHtml( true );
        $mail->send();

        return true;
    }

    /**
     * Faz relação entre material e solicitação
     *
     * @param integer $purchaseRequestId código da solicitação, caso for código externo,esse é o código externa
     * @param integer $controlNumber número de controle
     * @param boolean $externalId informa que o $purchaseRequestId é o código externo
     * @return boolean 
     */
    public static function relationWithMaterialCirculation( $purchaseRequestId , $controlNumber , $externalId )
    {
        $MIOLO = MIOLO::getInstance();
        
        if ( !$controlNumber )
        {
            throw new Exception ( _M( 'É necessário salvar o material.','gnuteca3') );
        }

        if ( !$purchaseRequestId )
        {
            throw new Exception ( _M( 'É necessário informar o código da solicitação.','gnuteca3') );
        }

        $busPurchaseRequest = $MIOLO->getBusiness( 'gnuteca3', 'BusPurchaseRequest');
        $busPurchaseRequest = new BusinessGnuteca3BusPurchaseRequest();
        $busPurchaseRequest->beginTransaction();

        //caso for id externo obtem o código normal (interno);
        if ( $externalId )
        {
            $busPurchaseRequest->externalIdS = $purchaseRequestId;
            $externalPurchase = $busPurchaseRequest->searchPurchaseRequest( null, true );
            $externalPurchase = $externalPurchase[0];
            $myPurchaseRequestId = $externalPurchase ->purchaseRequestId;

            if ( !$myPurchaseRequestId )
            {
                throw new Exception( _M('Impossível encontrar código externo "@1".','gnuteca3',$purchaseRequestId ) );
            }
            else
            {
                $purchaseRequestId = $myPurchaseRequestId;
                $busPurchaseRequest->externalIdS = null;
            }
        }

        $purchase = $busPurchaseRequest->getPurchaseRequest( $purchaseRequestId );

        if ( !$purchase )
        {
            throw new Exception ( _M('Solicitação de número @1 não existe', 'gnuteca3') ) ;
        }

        //caso tenha número da pré catalogação tenta remove-lo
        if ( $purchase->preControlNumber )
        {
            $busPreCatalogue = $MIOLO->getBusiness( 'gnuteca3', 'BusPreCatalogue');
            $busPreCatalogue = new BusinessGnuteca3BusPreCatalogue();
            $busPreCatalogue->controlNumber = $purchase->preControlNumber;
            $busPreCatalogue->deleteMaterial( true );
            $busPurchaseRequest->preControlNumber = ''; //limpa número na solicitação
        }

        //atualiza número de controle
        $busPurchaseRequest->controlNumber = $controlNumber;
        $ok = $busPurchaseRequest->updatePurchaseRequest( true ); //somente atualizar tabela principal

        try
        {
            $MIOLO->getClass('gnuteca3', 'GWorkflow');
            $worflowOk = GWorkFlow::changeStatus( 'PURCHASE_REQUEST', 'gtcPurchaseRequest', $purchaseRequestId, self::$relationWithMaterialCirculationFuturesStatus );
        }
        catch (Exception $e)
        {
            $busPurchaseRequest->rollbackTransaction();
            throw new Exception ( $e->getMessage() );
        }

        $busPurchaseRequest->commitTransaction();

        return $ok;
    }
    
    public static function finalizePurchaseRequestTaks()
    {
        $MIOLO = MIOLO::getInstance();

        $busPurchaseRequest = $MIOLO->getBusiness( 'gnuteca3', 'BusPurchaseRequest');
        $busPurchaseRequest = new BusinessGnuteca3BusPurchaseRequest();
       
        $requests = $busPurchaseRequest->listFinalizablePurchaseRequest( self::$relationWithMaterialCirculationFuturesStatus , DEFAULT_EXEMPLARY_STATUS_PROCESSANDO );
       
        if ( is_array ( $requests ) )
        {
            $MIOLO->getClass('gnuteca3', 'GWorkflow');
            
            foreach ( $requests as $line => $request )
            {
                try
                {
                    $worflowOk = GWorkFlow::changeStatus( 'PURCHASE_REQUEST', 'gtcPurchaseRequest', $request[0], self::$finalizedStatus );
                }
                catch ( Exception $e )
                {
                    //guarda erro no array
                    $request[4] = $e->getMessage();
                }
                
                $request[3] = $worflowOk;
                $purchaseRequestId[] = $request[0];
            }
        }

        if ( $purchaseRequestId )
        {
            $busPurchaseRequest->purchaseRequestIdS = implode(',',$purchaseRequestId );
            $list = $busPurchaseRequest->searchPurchaseRequest( null , true );

            $now = GDate::now()->__toString();
            $content = "Listagem de solicitações finalizadas em $now:\n";
            $content = "\n";
            $content = "Código - Pessoa - Unidade - Resultado:\n";

            if ( is_array( $list ) )
            {
                foreach ( $list as $key => $r )
                {
                    $result = $request[4] ? $request[4] : 'OK';
                    $content .= $r->purchaseRequestId ." - ". $r->personId. ' - '.$r->libraryname .' - ' .$result ."\n";
		    //Este comando foi adicionado pois o conteúdo do e-mail estava ficando em apenas uma linha
                    $content = str_replace("\n", "<br>", $content);
                }
            }

            $mail = new GMail();
            $mail->addAddress( defined('EMAIL_ADMIN_PURCHASE_REQUEST') ? EMAIL_ADMIN_PURCHASE_REQUEST : EMAIL_ADMIN );
            $mail->setSubject( _M( 'Aviso de finalização de solicitação de compras' , 'gnuteca3' ) );
            $mail->setContent( $content );

            return $mail->send();
        }

        return true;
    }
}
?>
