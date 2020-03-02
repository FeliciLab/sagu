<?php
$MIOLO->getClass( 'gnuteca3', 'workflow/wfPurchaseRequest' );
class wfPurchaseRequestUnivates extends wfPurchaseRequest
{
    private $alfaSoapClient;

    public function __construct( $data )
    {
        parent::__construct( $data );

        //cliente de acesso alfa
        ini_set('default_socket_timeout', '3600');
        $this->alfaSoapClient = new AlfaSoapClient();

    }

    public function initialize()
    {
        return parent::initialize();
    }

    /**
     * De aguardando aprovação para aprovada
     *
     * @return boolean
     */
    public function aprove()
    {
        return parent::aprove();
    }

    public function cancel()
    {
        return parent::cancel();
    }

    /**
     * Função de cancelamento pelo compras
     *
     * @return boolean
     */
    public function cancelBy()
    {
        $MIOLO = MIOLO::getInstance();
        //define variáveis extras
        $this->gFunction->setVariable('$controlNumberLink','');
        $content = $this->gFunction->interpret( EMAIL_PURCHASE_REQUEST_CANCEL_CONTENT ) ;

        $mail = new GMail();
        $mail->addAddress( $this->adminMail );
        $mail->setSubject( 'Cancelado pelo compras' );
        $mail->setContent( $content );
        $mail->setIsHtml( true );
        $mail->send();

        return true;
    }

    /*
     * Documentação da univates:
     *
     * insereSolicitacaoBibliografia( $obj )
     * param $obj:
     * -> professor,
     * -> titulo,
     * -> autor,
     * -> editora,
     * -> edicao,
     * -> volume,
     * -> ref_solicitante,
     * -> ref_ccusto,
     * -> dt_necessidade,
     * -> qtde,
     * -> obs_solicitante
     *
     * return $id : o código da solicitação que foi cria
     */
    public function purchaseRequest()
    {
        $result = parent::purchaseRequest();
        $data = $this->getData();


        if ( ! $data->needDelivery || ! $data->purchaseRequest->needDelivery )
        {
            throw new Exception ( _M( 'É necessário informar a data de necessidade.','gnuteca3' ) );
        }

        //verifica centro de custos
        if ( ! $data->purchaseRequest->costCenterId )
        {
            throw new Exception ( _M( 'É necessário informar o centro de custo.','gnuteca3' ) );
        }

        $comment  = $data->comment;
        $comment .= ' - Sugestão de livro - canal de sugestões da biblioteca';
        $comment .= " Aluno=".$data->personId . '/'. $data->purchaseRequest->name;
        $comment .= " Curso=".$data->course;
        $comment .= " Observação=".$data->purchaseRequest->observation;

        $alfaObj = new stdClass();

        //vem do post para ficar atualizado, caso não, vem do banco
        $alfaObj->titulo = $data->dinamic245_a ? $data->dinamic245_a : $fields['245.a']; 
        $alfaObj->autor = $data->dinamic100_a ? $data->dinamic100_a  : $fields['100.a'];
        $alfaObj->editora = $data->dinamic260_b ? $data->dinamic260_b : $fields['260.c'];
        $alfaObj->edicao = $data->dinamic250_a ? $data->dinamic250_a : $fields['250.a'];
        $alfaObj->volume = $data->dinamic949_v ? $data->dinamic949_v : $fields['949.v'];

        //solicitado pelo William que caso vazio passe ' ' (espaço) para o alfa
        $alfaObj->titulo = $alfaObj->titulo ? $alfaObj->titulo : ' ';
        $alfaObj->autor = $alfaObj->autor ? $alfaObj->autor : ' ';
        $alfaObj->editora = $alfaObj->editora ? $alfaObj->editora : ' ';
        $alfaObj->edicao = $alfaObj->edicao ? $alfaObj->edicao : ' ';
        $alfaObj->volume = $alfaObj->volume ? $alfaObj->volume : ' ';

        //recomenandado pelo William a utilizacao do '-' que significa 'não definido'
        $alfaObj->professor = '-'; 
        //condição para testes na web2devel
        $alfaObj->ref_solicitante = GOperator::getOperatorId() == '101781' ?  '532814': GOperator::getOperatorId();
        $alfaObj->ref_ccusto = $data->purchaseRequest->costCenterId;
        $alfaObj->qtde = $data->purchaseRequest->amount;
        $alfaObj->obs_solicitante = $comment; //vem do POST
        $needDelivery = $data->purchaseRequest->needDelivery ? $data->purchaseRequest->needDelivery : $data->needDelivery;
        $alfaObj->dt_necessidade = GDate::construct($needDelivery)->getDate(GDate::MASK_DATE_DB);

        try
        {
            $externalId = $this->alfaSoapClient->executaMetodoModel( 'Solicitacoes', 'insereSolicitacaoBibliografia', array( $alfaObj ) ) ;
        }
        catch ( Exception $e )
        {
            throw new Exception( _M('Problema informado pelo Alfa: @1' ,'gnuteca3', utf8_encode( $e->getMessage() ) ) );
        }

        if ( !$externalId )
        {
            throw new Exception( _M('Impossível executar tarefa Alfa não retornou código externo!' ,'gnuteca3'));
        }
        else
        {
            $MIOLO = MIOLO::getInstance();
            $data->purchaseRequest->externalId = $externalId;
            $busPurchaseRequest = $MIOLO->getBusiness( 'gnuteca3', 'BusPurchaseRequest');
            $busPurchaseRequest = new BusinessGnuteca3BusPurchaseRequest();

            $busPurchaseRequest->setData( $data->purchaseRequest );
            $busPurchaseRequest->updatePurchaseRequest( true ); //only update main table
            return true;
        }

        return false;
    }

    public function finalize()
    {
        $finalizeStatus = parent::finalize();
        
        //Se a finalizacao ocorreu com sucesso.        
        if ( $finalizeStatus )
        {
            $MIOLO = MIOLO::getInstance();
            //Efetua reserva para pessoa.
            $busExemplaryControl = $MIOLO->getBusiness( 'gnuteca3', 'BusExemplaryControl');
            $exemplarys = $busExemplaryControl->getExemplaryByTomeTypeVolume( $this->data->purchaseRequest->controlNumber, $this->data->purchaseRequest->libraryUnitId );

            foreach ($exemplarys as $materialTypeId => $materialPhysicalTypeId)
            {
                foreach ($materialPhysicalTypeId as $key => $materialPhysical)
                {
                    foreach ($materialPhysical as $tomo => $volumeArray)
                    {
                        foreach ($volumeArray as $volume => $objExemplary)
                        {
                            foreach ($objExemplary as $it)
                            {
                                //Adiciona exemplares respeitando as categorias deles.
                                $itens[$it->materialType."_".$it->materialPhysicalTypeId."_".$it->tomo."_".$it->volume][] = $it->itemNumber;
                            }
                        }
                    }
                }
            }

            //Percorre exemplares fazendo reservas.
            foreach ($itens as $exemplar)
            {
                $busOperationReserve = $MIOLO->getBusiness('gnuteca3', 'BusOperationReserve');
                $busOperationReserve->clear();
                $busOperationReserve->setLibraryUnit($this->data->purchaseRequest->libraryUnitId); //define unidade
                $busOperationReserve->setReserveType(ID_RESERVETYPE_WEB);
                $busOperationReserve->setPerson($this->data->purchaseRequest->personId);            

                foreach($exemplar as $itemNumber)
                {
                    $busOperationReserve->addItemNumber($itemNumber, true);                
                }

                $busOperationReserve->finalize(); 
            }

        }
        
        return $finalizeStatus;
    }
}

class Solicitacoes {}
class tpgsql{}
class tsqlselect{}

/**
 * Classe de conexão via soap com o alpha, codigo herdado do fermilk
 */
class AlfaSoapClient extends SoapClient
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        
        $location = $MIOLO->getConf('webservice.alfa.location');
        $uri      = $MIOLO->getConf('webservice.alfa.uri');

        if ( empty($location) || empty($uri) )
        {
            throw new Exception("Localização/URI do Alfa nao está definida no miolo.conf");
        }
        
        parent::__construct(null, array('location'=>"$location",'uri'=>"$uri",'encoding'=>"UTF-8"));
    }

    public function __call($name, $arguments)
    {
        $MIOLO = MIOLO::getInstance();

        $key = $MIOLO->getConf('webservice.alfa.key');

        $arguments[]= $key; //adiciona a chave

        $result = parent::__soapCall($name, $arguments);   
        $result = base64_decode($result);
        $result = unserialize( $result );
        
        if ( $result instanceof Exception )
        {
            throw $result;
        }
        else
        {
            return $result;
        }
    }
}
?>
