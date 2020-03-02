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
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 30/05/2011
 *
 **/
class BusinessGnuteca3BusPurchaseRequest extends GBusiness
{
    public $MIOLO;
    public $module;
    public $colsNoId;

    public $purchaseRequestIdS,
           $libraryUnitIdS,
           $personIdS,
           $costCenterIdS,
           $amountS,
           $courseS,
           $observationS,
           $needDeliveryS,
           $forecastDeliveryS,
           $deliveryDateS,
           $voucherS,
           $controlNumberS,
           $preControlNumberS,
           $externalIdS,
           $workflowStatusS;
        
    public $purchaseRequestId,
           $libraryUnitId,
           $personId,
           $costCenterId,
           $amount,
           $course,
           $observation,
           $needDelivery,
           $forecastDelivery,
           $deliveryDate,
           $voucher,
           $controlNumber,
           $preControlNumber,
           $externalId,
           $dinamicFields,
           $quotation;
    
    function __construct()
    {
        parent::__construct();
        $this->MIOLO  = MIOLO::getInstance();
        $this->defineTableColumns();
        $this->columns  = $this->id . ',' . $this->colsNoId;
    }
    
    /**
     * Obptém e trata valores definidos na preferência de campos da solicitação de compras;
     * 
     * @param (int) unidade de biblioteca
     * @return (object) contento dados (id, label e hint de campos) 
     */
    public function parseFieldsPurchaseRequest($libraryUnitId = null)
    {
        $busLibraryUnitConfig = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitConfig');
        $fields = $busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'FIELDS_PURCHASE_REQUEST');
        $lines = explode("\n", $fields);
        
        $newFields = array();
        
        if ( is_array($lines) )
        {
            foreach ( $lines as $i=>$line )
            {
                $value = explode('|', $line);
                $data  = new stdClass();
                $data->id         = str_replace('.', '_', $value[0]); 
                $data->label      = $value[1];
                $data->hint       = $value[2];
                $data->required   = $value[3];
                $data->searchable = $value[4];
                $newFields[]       = $data;
            }
        }
    
        return $newFields;
    }

    /**
     * Obtém a solicitação de compra
     *
     * @param (int) chave primária do registro
     *
     * @return (object) contém o registro obtido
     *
     **/
    public function getPurchaseRequest($purchaseRequestId)
    {
        $busPurchaseRequestQuotation = $this->MIOLO->getBusiness($this->module, 'BusPurchaseRequestQuotation');
        $busPurchaseRequestMaterial = $this->MIOLO->getBusiness($this->module, 'BusPurchaseRequestMaterial');
        $data = array($purchaseRequestId);
        
        $this->clear();
        $this->setColumns( 'purchaseRequestId,libraryUnitId,
                           PR.personId,
                           costCenterId,
                           amount,
                           course,
                           PR.observation,
                           needDelivery,
                           forecastDelivery,
                           deliveryDate,
                           voucher,
                           controlNumber,
                           preControlNumber,
                           externalId,PE.name, PE.email');
        $this->setTables('gtcPurchaseRequest PR LEFT JOIN ONLY basPerson PE ON ( PR.personId = PE.personId )');
        $this->setWhere($this->id . ' = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject=true);
        $data = new stdClass();
        
        if ($rs[0])
        {
			$data = $rs[0];
            
            //busca os campos dinamicos do formulário
            $busPurchaseRequestMaterial->purchaseRequestId = $data->purchaseRequestId;
            $data->dinamicFields = $busPurchaseRequestMaterial->searchPurchaseRequestMaterial('purchaseRequestId', true);

            //busca quotações dessa solicitação
            $busPurchaseRequestQuotation->purchaseRequestId = $data->purchaseRequestId;
            $data->quotation = $busPurchaseRequestQuotation->searchPurchaseRequestQuotation('purchaseRequestId', true);
        }

        $this->setData($data);
        
        return $data;
	}

    /**
     * Busca as solicitações de compras
     *
     * @param (string) coluna de ordenação dos registros
     *
     * @return (array) contendo os dados que vão compor a grid
     **/
    public function searchPurchaseRequest($orderBy = 'purchaseRequestId', $toObject = false )
    {
        $this->clear();

        if ( $this->purchaseRequestIdS )
        {
            $this->setWhere("A.purchaseRequestId in ( {$this->purchaseRequestIdS} )");
        }
        
        if ( $this->libraryUnitIdS )
        {
            $this->setWhere("A.libraryUnitId IN  ({$this->libraryUnitIdS})");
        }
        
        if ( $this->personIdS )
        {
            $this->setWhere('A.personId = ?');
            $data[] = $this->personIdS;
        }
        
        if ( $this->costCenterIdS )
        {
            $this->setWhere('A.costCenterId = ?');
            $data[] = $this->costCenterIdS;
        }
        
        if ( $this->amountS )
        {
            $this->setWhere('A.amount = ?');
            $data[] = $this->amountS;
        }
        
        if ( $this->courseS )
        {
            $this->setWhere('lower(A.course) like (lower(?))');
            $data[] = $this->courseS;
        }
        
        if ( $this->observationS )
        {
            $this->setWhere('lower(A.observation) like (lower(?))');
            $data[] = $this->observationS.'%';
        }
        
        if ( $this->needDeliveryS )
        {
            $this->setWhere('A.needDelivery = ?');
            $data[] = $this->needDeliveryS;
        }
        
        if ( $this->forecastDeliveryS )
        {
            $this->setWhere('A.forecastDelivery = ?');
            $data[] = $this->forecastDeliveryS;
        }
        
        if ( $this->deliveryDateS )
        {
            $this->setWhere('A.deliveryDate = ?');
            $data[] = $this->deliveryDateS;
        }
        
        if ( $this->voucherS )
        {
            $this->setWhere('A.voucher = ?');
            $data[] = $this->voucherS;
        }
        
        if ( $this->controlNumberS )
        {
            $this->setWhere('A.controlNumber = ?');
            $data[] = $this->controlNumberS;
        }
        
        if ( $this->preControlNumberS )
        {
            $this->setWhere('A.preControlNumber = ?');
            $data[] = $this->preControlNumberS;
        }
        
        if ( $this->externalIdS )
        {
            $this->setWhere('A.externalId = ?');
            $data[] = $this->externalIdS;
        }
        
        if ( $this->workflowStatusS )
        {
            $workflowStatusS = $this->workflowStatusS;

            if ( is_array( $this->workflowStatusS ) )
            {
                $workflowStatusS = implode(',', $workflowStatusS);
            }

            $this->setWhere( "E.workflowstatusid in ( {$workflowStatusS} ) ");
        }
        
        if ( $this->dinamicFields )
        {
            $dinamicFields = (array) $this->dinamicFields;
            
            //caso tiver filtro dinâmico, busca os números de controle
            if ( strlen(implode('', $dinamicFields)) > 0 )
            {
                $busPurchaseRequestMaterial = $this->MIOLO->getBusiness($this->module, 'BusPurchaseRequestMaterial');
                $purchaseRequestIds = implode(',', $busPurchaseRequestMaterial->searchPurchaseRequestIdOfPurchaseRequestMaterial($dinamicFields));
                
                if ( strlen($purchaseRequestIds) > 0 )
                {
                    $this->setWhere("A.purchaseRequestId IN ($purchaseRequestIds)");
                }
                else
                {
                    return null;
                }
            }
        }
        
        $columns = 'A.purchaseRequestId,
                    A.personId,
                    B.name,
                    A.costCenterId,
                    C.description,
                    \'\',
                    A.amount,
                    A.libraryUnitId,
                    D.libraryname,
                    A.observation,
                    E.workflowstatusid,
                    A.course,
                    A.needDelivery,
                    A.forecastDelivery,
                    A.deliveryDate,
                    A.voucher,
                    A.controlNumber,
                    A.preControlNumber,
                    A.externalId,
                    F.name';

        $this->setColumns($columns);
    
        $this->setTables("gtcPurchaseRequest A
          INNER JOIN ONLY basPerson B
                       ON ( A.personId = B.personId )
                LEFT JOIN  gtccostcenter C 
                       ON ( A.costCenterId = C.costCenterId )
                LEFT JOIN  gtcLibraryUnit D 
                       ON ( A.libraryUnitId = D.libraryUnitId )
                LEFT JOIN gtcWorkflowInstance E
                       ON ( E.tableName = '{$this->tables}' AND tableid = cast(A.purchaseRequestId as varchar) )
                LEFT JOIN gtcWorkflowStatus F
                       ON ( E.workflowStatusId = F.workflowStatusId )");
        
        $this->setOrderBy($orderBy);
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject );
        
        return $rs;
    }

    /**
     * Insere uma nova solicitação de compra
     *
     * @param boolean $object Define se e para retornar o objeto apos inserir.
     * @return True se funcionou
     *
     **/
    public function insertPurchaseRequest($object = null)
    {
        $busPurchaseRequestQuotation = $this->MIOLO->getBusiness($this->module, 'BusPurchaseRequestQuotation');
        $busPurchaseRequestMaterial = $this->MIOLO->getBusiness($this->module, 'BusPurchaseRequestMaterial');
        
        if ( $this->checkPurchaseRequest() )
        {
            $this->clear();

            $this->purchaseRequestId = $this->getNextId();

            $this->setTables($this->tables);
            $this->setColumns($this->columns);
            $sql = $this->insert( $this->associateData($this->columns) );
            $ok[0]  = $this->execute($sql);

            if ( $ok[0] )
            {
                if ( $this->dinamicFields )
                {
                    //grava campos dinamicos
                    foreach( $this->dinamicFields as $key => $dinamic )
                    {
                        //só grava se tiver valor no campo
                        if ( strlen($dinamic) > 0 )
                        {
                            $etiqueta = explode('.', $key);
                            $data = new stdClass();
                            $data->purchaseRequestId = $this->purchaseRequestId;
                            $data->fieldId = $etiqueta[0];
                            $data->subfieldId = $etiqueta[1];
                            $data->content = $dinamic;

                            $busPurchaseRequestMaterial->setData($data);
                            $ok[] = $busPurchaseRequestMaterial->insertPurchaseRequestMaterial();
                        }
                    }
                }

                //grava cotação
                if ( $this->quotation )
                {
                    foreach ( $this->quotation as $i=>$quotation )
                    {
                        $quotation->purchaseRequestId = $this->purchaseRequestId;
                        $busPurchaseRequestQuotation->setData($quotation);
                        $ok[] = $busPurchaseRequestQuotation->insertPurchaseRequestQuotation();
                    }

                }
            }

            if( !$object )
            {
                return !in_array(false, $ok);
            }
            else
            {
                return $this;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * Atualiza o registro solicitação de compra
     *
     * @param boolean $onlyUpdateMainTable para somente atualizar a tabela principal
     * @return (boolean): True se teve sucesso
     *
     **/
    public function updatePurchaseRequest( $onlyUpdateMainTable = false )
    {
        $busPurchaseRequestQuotation = $this->MIOLO->getBusiness($this->module, 'BusPurchaseRequestQuotation');
        $busPurchaseRequestMaterial = $this->MIOLO->getBusiness($this->module, 'BusPurchaseRequestMaterial');
        
        if ( $this->checkPurchaseRequest() )
        {
            $data = $this->associateData( $this->colsNoId . ', purchaseRequestId' );

            $this->clear();
            $this->setWhere('purchaseRequestId = ?');
            $this->setColumns($this->colsNoId);
            $this->setTables( 'gtcPurchaseRequest' ); //para funcionar após um get
            $sql = $this->update($data);
            $ok[0] = $this->execute($sql);

            if ( $ok[0] && !$onlyUpdateMainTable )
            {
                if ( $this->dinamicFields )
                {
                    //apaga todos campos dinamicos
                    $busPurchaseRequestMaterial->deletePurchaseRequestMaterial($this->purchaseRequestId);

                    //grava campos dinamicos
                    foreach( $this->dinamicFields as $key => $dinamic )
                    {
                        //só grava se tiver valor
                        if ( strlen($dinamic) > 0 )
                        {
                            $etiqueta = explode('.', $key);
                            $data = new stdClass();
                            $data->purchaseRequestId = $this->purchaseRequestId;
                            $data->fieldId = $etiqueta[0];
                            $data->subfieldId = $etiqueta[1];
                            $data->content = $dinamic;

                            $busPurchaseRequestMaterial->setData($data);
                            $ok[] = $busPurchaseRequestMaterial->insertPurchaseRequestMaterial();
                        }
                    }

                }


                //atualiza quotações
                if ( $this->quotation )
                {
                    foreach( $this->quotation as $i => $quotation )
                    {
                        $quotation->purchaseRequestId = $this->purchaseRequestId;
                        $busPurchaseRequestQuotation->setData($quotation);
                        $ok[] = $busPurchaseRequestQuotation->updatePurchaseRequestQuotation();
                    }
                }
            }

            return !in_array(false, $ok);
        }
        else
        {
            return false;
        }
    }

    /**
     * Apaga a solicitação de compra
     *
     * @param (int) chave primária da requisição
     *
     * @return (boolean) true se apagou
     *
     **/
    public function deletePurchaseRequest($purchaseRequestId)
    {
        $busPurchaseRequestQuotation = $this->MIOLO->getBusiness($this->module, 'BusPurchaseRequestQuotation');
        $busPurchaseRequestMaterial = $this->MIOLO->getBusiness($this->module, 'BusPurchaseRequestMaterial');
        //remove materiais e quotações
        $busPurchaseRequestMaterial->deletePurchaseRequestMaterial( $purchaseRequestId );
        $busPurchaseRequestQuotation->deletePurchaseRequestQuotation( $purchaseRequestId );

        $data = array($purchaseRequestId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere($this->id . ' = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
       
        return $rs;
    }
    
    /**
     * Obtém próximo id de solicitação de compras
     * @return type 
     */
    public function getNextId()
    {
        $query = $this->query("SELECT NEXTVAL('seq_gtcpurchaserequest')");
        return $query[0][0];
    }

    /**
     * Converte uma solicitação para pré-catalogação.
     * Registra oo número da pré na solicitação.
     *
     * @param integer $purchaseRequestId
     * @return integer código da pré-catalogação
     */
    public function convertToPreCatalogue( $purchaseRequestId )
    {
        if ( !$purchaseRequestId )
        {
            throw new Exception ( _M('Impossível migrar solicitação para pré-catalogação sem código de solicitação.','gnuteca3') );
        }

        $purchase = $this->getPurchaseRequest($purchaseRequestId );

        if ( !$purchaseRequestId )
        {
            throw new Exception ( _M('Impossível migrar solicitação para pré-catalogação. Solicitação @1 não encontrada.','gnuteca3', $purchaseRequestId) );
        }

        if ( $purchase )
        {
            $sql = "INSERT INTO gtcPreCatalogue ( controlnumber, fieldid , subfieldid, line, content, searchcontent ) ( SELECT ( SELECT coalesce(max(controlnumber),0)+1 from gtcPreCatalogue), fieldid, subfieldid, 0, content, upper( content ) FROM gtcpurchaserequestmaterial WHERE purchaseRequestId = {$purchaseRequestId} ) RETURNING controlNumber;";

            $result = $this->query( $sql );
           
            $this->preControlNumber = $result[0][0];

            $this->updatePurchaseRequest( true );
        }

        return $result[0][0];
    }

    /**
     * Esta função relacionada todos as solicitações, do estado $workflowStatusId (normalmente catalogada)
     * relecionando com cada número de controle que não tiver exemplares no estado $exemplaryStatusId (normalmente "Em processamento"
     * 
     * Esta função foi criada para uma tarefa muito especifica para Univates.
     *
     * @param integer $workflowStatusId
     * @param integer $exemplaryStatusId
     */
    public function listFinalizablePurchaseRequest( $workflowStatusId = null, $exemplaryStatusId = null )
    {
        if ( !$exemplaryStatusId )
        {
            return false;
        }

        if ( WF_PURCHASE_REQUEST_CLASS == 'wfPurchaseRequestUnivates' )
        {

            $sql = "select distinct B.purchaserequestid, C.controlnumber, D.content
                               FROM gtcworkflowinstance A
                         INNER JOIN gtcpurchaserequest B ON (A.tableid::int = B.purchaserequestid and A.tablename = 'gtcPurchaseRequest')        
                         INNER JOIN gtcmaterial C ON (C.fieldid = '949' and C.subfieldid = 's' and C.content = B.externalid)
                         INNER JOIN gtcmaterial D ON (C.controlnumber = D.controlnumber AND D.fieldid = '949' AND D.subfieldid = 'g' AND D.line = C.line)
                         
                              WHERE tablename = 'gtcPurchaseRequest'
                                AND workflowstatusid = '{$workflowStatusId}'
                                AND D.content::int IN (select exemplarystatusid FROM gtcexemplarystatus WHERE level = 1);";
        }
        else
        {

            if ( !$workflowStatusId )
            {
                return false;
            }

            $sql = "SELECT distinct B.purchaserequestid, B.controlnumber
                               FROM gtcworkflowinstance A
                         INNER JOIN gtcpurchaserequest B ON (A.tableid::int = B.purchaserequestid and A.tablename = 'gtcPurchaseRequest')
                         INNER JOIN gtcexemplarycontrol C ON (B.controlnumber = C.controlnumber)
                              WHERE tablename = 'gtcPurchaseRequest'
                                AND workflowstatusid = '$workflowStatusId'
                                AND C.controlnumber in (SELECT controlnumber FROM gtcexemplarycontrol where exemplarystatusid <> $exemplaryStatusId );";

        }

        return $this->query( $sql );
    }
    
    /**
     * Realiza uma checagem de pessoa e operador antes de inserir/editar
     * 
     * @return boolean, true caso for possível 
     */
    private function checkPurchaseRequest()
    {
        $busLibraryUnit = $this->MIOLO->getBusiness('gnuteca3', 'BusLibraryUnit');
        $libraryUnit = $busLibraryUnit->getLibraryUnit1($this->libraryUnitId);
        
        if ( $libraryUnit->acceptPurchaseRequest == DB_TRUE )
        {
            if (  GOperator::isLogged() )
            {
                return true;
            }
            else
            {
                if ( $libraryUnit->isRestricted == DB_TRUE )
                {
                    //checa se usuário possui permissão
                    $libraries = $busLibraryUnit->listListLibraryUnitAcceptingPurchaseRequest($this->personId);
                    
                    $perm = false;
                    if ( is_array($libraries) )
                    {
                        foreach ( $libraries as $i => $library )
                        {
                            if ( $this->libraryUnitId == $library[0] )
                            {
                                $perm = true;
                                break;
                            }
                        }
                    }
                    
                    if ( $perm )
                    {
                        return true;
                    }
                    else
                    {
                        throw new Exception( _M('Usuário sem permissão na unidade de biblioteca', 'gnuteca3') );
                    }
                }
                else
                {
                    return true;
                }
            }
        }
        else
        {
            throw new Exception( _M('A unidade de biblioteca não aceita sugestão de material', 'gnuteca3') );
        }
    }
    
    /**
     * Metodo que recebe o id da requisicao $purchaseRequestId, e a duplica
     * com dados do material que estao no parametro $purchaseRequestData, se $purchaseRequestData
     * nao for passado entao duplica requisicao com os dados da requisicao com 
     * id = $purchaseRequestId.
     * Ao passar o novo centro de custo $newCostCenterId, define-o como o centro de custo
     * da duplicacao caso contrario duplica sem definir o centro de custo.
     * 
     * 
     * @param type $purchaseRequestId
     * @param stdClass $purchaseRequestData
     * @param type $newCostCenterId
     * @param type $doTransaction
     * @return \BusinessGnuteca3BusPurchaseRequest
     */
    public function duplicatePurchaseRequest($purchaseRequestId, stdClass $purchaseRequestData = null, $doTransaction = true)
    {
        $this->MIOLO->getClass('gnuteca3', 'GWorkflow');
        if($doTransaction)
        {
            $this->beginTransaction();
        }
        
        //Dando o get ele ja da o setData
        $this->getPurchaseRequest($purchaseRequestId);

        //Define o novo centro de custo
        $this->externalId = '';
        $this->preControlNumber = '';
        $this->controlNumber = '';
        $this->forecastDelivery = '';
        $this->deliveryDate = ''; 
        $this->quotation = '';
        
        //Se algum dado foi passado sobre as informaçoes do material, os define no objeto.
        if ( $purchaseRequestData )
        {
            $this->dinamicFields['245.a'] = trim( new GString( $purchaseRequestData->titulo )."" );
            $this->dinamicFields['100.a'] = trim( new GString( $purchaseRequestData->autor )."" );
            $this->dinamicFields['260.b'] = trim( new GString( $purchaseRequestData->editora )."" );
            $this->dinamicFields['250.a'] = trim( new GString( $purchaseRequestData->edicao )."" );
            $this->dinamicFields['949.v'] = trim( new GString( $purchaseRequestData->volume )."" );
        }
        else
        {
            //Obtem os dados do material na solicitacao original
            $busPurchaseRequestMaterial = $this->MIOLO->getBusiness('gnuteca3', 'BusPurchaseRequestMaterial');
            $busPurchaseRequestMaterial->purchaseRequestId = $this->purchaseRequestId;
            $purchaseFields = $busPurchaseRequestMaterial->searchPurchaseRequestMaterial();

            //Itera entre campos da solicitacao original adicionando-os no dinamicfields
            foreach ($purchaseFields as $field)
            {
                //Preferi fazer assim para facilitar a leitura em algum momento do futuro
                $fieldId = $field[1];
                $subFieldId = $field[2];
                $content = $field[3];
                
                $this->dinamicFields[$fieldId.'.'.$subFieldId] = $content;
            }

        }

        $this->defineTableColumns();
        $ok = $this->insertPurchaseRequest( );
        
        if ( $ok )
        {
            //instancia novo workflow
            $ok = GWorkFlow::instance('PURCHASE_REQUEST','gtcPurchaseRequest', $this->purchaseRequestId );
        }

        if($doTransaction)
        {
            $this->commitTransaction();
        }
        
        if ( $ok )
        {
            return $this;
        }
        else
        {
            return $ok;
        }
    }
    
    public function defineTableColumns()
    {
        $this->id       = 'purchaseRequestId';
        $this->colsNoId = 'libraryUnitId,
                           personId,
                           costCenterId,
                           amount,
                           course,
                           observation,
                           needDelivery,
                           forecastDelivery,
                           deliveryDate,
                           voucher,
                           controlNumber,
                           preControlNumber,
                           externalId';
        $this->columns  = $this->id . ',' . $this->colsNoId;
        $this->tables   = 'gtcPurchaseRequest';        
    }
    
    /**
     * Método utilizado no webservice de obtenção de exemplares pelos números de solicitações de compra.
     * Recebe um array com os números das solicitações de compra e retorna um array de objetos com as informações dos exemplares.
     * 
     * @param Array $solicitacoesDeCompra Array com todos os números de solicitação de compras
     * 
     * @return Array Retorna um array de objetos com as informações dos exemplares.
     */
    public function getExemplariesFromPurchaseRequest($solicitacoesDeCompra)
    {
        $exemplares = array();

	if ( count($solicitacoesDeCompra) > 0 )
        {
            foreach ( $solicitacoesDeCompra as $numSolicitacao )
            {
                if ( strlen($numSolicitacao) > 0 )
                {
                    $sql = " SELECT M.content as codSolicitacao,
                                    E.controlnumber as numeroDeControle, 
                                    E.itemnumber as tombo,
                                    CASE WHEN E.exemplarystatusid <> 15 THEN 'CATALOGADO' ELSE 'PROCESSAMENTO' END as estado
                               FROM gtcexemplarycontrol E
                          LEFT JOIN gtcmaterial M ON (E.controlnumber = M.controlnumber AND E.line = M.line)
                              WHERE M.fieldid = '949'
                                AND M.subfieldid = 's'
                                AND M.content = '{$numSolicitacao}' ";
                            
                    $query = $this->query($sql);
                    foreach ( $query as $key => $line )
                    {
                        $exemplares[$key] = new stdClass();
                        $exemplares[$key]->codSolicitacaoCompra = $line[0];
                        $exemplares[$key]->numeroDeControle = $line[1];
                        $exemplares[$key]->tombo = $line[2];
                        $exemplares[$key]->estado = $line[3];
                    }
                }
            }
	}
	else
	{
	    $sql = " SELECT M.content as codSolicitacao,
                            E.controlnumber as numeroDeControle, 
                            E.itemnumber as tombo,
                            CASE WHEN E.exemplarystatusid <> 15 THEN 'CATALOGADO' ELSE 'PROCESSAMENTO' END as estado
                       FROM gtcexemplarycontrol E
                  LEFT JOIN gtcmaterial M ON (E.controlnumber = M.controlnumber AND E.line = M.line)
                      WHERE M.fieldid = '949'
                        AND M.subfieldid = 's' ";

            $query = $this->query($sql);
            foreach ( $query as $key => $line )
            {
                $exemplares[$key] = new stdClass();
                $exemplares[$key]->codSolicitacaoCompra = $line[0];
                $exemplares[$key]->numeroDeControle = $line[1];
                $exemplares[$key]->tombo = $line[2];
                $exemplares[$key]->estado = $line[3];
            }
	}
                    
        return $exemplares;
    }
    
    public function getPurchaseRequestByExternalId($externalId)
    {
        $solicitacao = NULL;
        
        if ( strlen($externalId) > 0 )
        {
            $sql = " SELECT purchaserequestid FROM gtcpurchaserequest WHERE externalid = '{$externalId}' ";
            
            $query = $this->query($sql);
            $solicitacao = $query[0][0];
        }
        
        return $solicitacao;
    }
}
?>
