<?php

class GWorkflow
{
    /**
     * Dá início a um novo workflow.
     *
     * Para isso:
     * Deve encontrar o estado inicial e garantir que não haja inconsistência.
     * Gravar a instancia no gtcWorkflowInstance
     * Gravar registro na gtcWorkflowHistory
     *
     * @param integer $workflowId código do workflow
     * @param string $tableName nome da tabela onde o workflow se encontra
     * @param string $tableId código do registro na tabela relacionada
     *
     * @return boolean
     */
    public static function instance( $workflowId, $tableName, $tableId )
    {
        $MIOLO = MIOLO::getInstance();

        if ( !$workflowId || !$tableName || ! $tableId )
        {
            throw new Exception( _M('É necessário código do workflow, nome e código da tabela para que o workflow funcione corretamente.','gnuteca3') );
        }

        //obtem estado inicial
        $busWorkflowStatus = $MIOLO->getBusiness( 'gnuteca3', 'BusWorkflowStatus');
        $busWorkflowStatus = new BusinessGnuteca3BusWorkflowStatus(); //para autocomplete
        $initialStatus = $busWorkflowStatus->getInitialStatus( $workflowId );

        //veriica se existe uma transição inicial para inicial
        $busWorkflowTransition = $MIOLO->getBusiness( 'gnuteca3', 'BusWorkflowTransition');
        $busWorkflowTransition = new BusinessGnuteca3BusWorkflowTransition(); //para autocomplete
        $transition = $busWorkflowTransition->getWorkflowTransition( $initialStatus->workflowStatusId, $initialStatus->workflowStatusId );

        //executa a função
        GWorkflow::executeFunction( $transition->function, $workflowId, $tableName, $tableId );

        //cria a instancia caso não tenha erros
        $busWorkflowInstance = $MIOLO->getBusiness( 'gnuteca3', 'BusWorkflowInstance');
        $busWorkflowInstance = new BusinessGnuteca3BusWorkflowInstance();
        $busWorkflowInstance->tableName = $tableName;
        $busWorkflowInstance->tableId = $tableId;
        $busWorkflowInstance->workflowId = $workflowId;
        $busWorkflowInstance->workflowStatusId = $initialStatus->workflowStatusId;
        $busWorkflowInstance->date = GDate::now();
        $ok = $busWorkflowInstance->insertWorkflowInstance( null, $comment );

        return $ok;
    }

    /**
     * Retorna o estado atual da solicitação
     *
     * @param integer $workflowId código do workflow
     * @param string $tableName nome da tabela onde o workflow se encontra
     * @param string $tableId código do registro na tabela relacionada
     *
     * @return stdClass
     */
    public static function getCurrentStatus( $workflowId, $tableName, $tableId )
    {
        $MIOLO = MIOLO::getInstance();

        if ( !$workflowId || !$tableName || ! $tableId )
        {
            throw new Exception( _M('É necessário código do workflow, nome e código da tabela para que o workflow funcione corretamente.','gnuteca3') );
        }

        $busWorkflowInstance = $MIOLO->getBusiness( 'gnuteca3', 'BusWorkflowInstance');
        $busWorkflowInstance = new BusinessGnuteca3BusWorkflowInstance();
        $instance = $busWorkflowInstance->getCurrentWorkflowInstance($workflowId, $tableName, $tableId);

        return $instance;
    }

    /**
     * Retorna um array com o código e descrição dos possíveis estados.
     * Deve buscar o estado atual para verificar na tabela gtcWorkflowTransation quais são as próximas transições possíveis.
     *
     * @param integer $workflowId código do workflow
     * @param string $tableName nome da tabela onde o workflow se encontra
     * @param string $tableId código do registro na tabela relacionada
     *
     * @return stdClass
     */
    public static function getFutureStatus( $workflowId, $tableName, $tableId )
    {
        $MIOLO = MIOLO::getInstance();

        if ( !$workflowId || !$tableName || ! $tableId )
        {
            throw new Exception( _M('É necessário código do workflow, nome e código da tabela para que o workflow funcione corretamente.','gnuteca3') );
        }
        
        $currentStatus = GWorkflow::getCurrentStatus($workflowId, $tableName, $tableId);
        $busWorkflowTransition = $MIOLO->getBusiness( 'gnuteca3', 'BusWorkflowTransition');
        $busWorkflowTransition = new BusinessGnuteca3BusWorkflowTransition();
        $futureStatus = $busWorkflowTransition->getFutureStatus( $currentStatus->workflowStatusId );

        return $futureStatus;
    }

    /**
     * Retorna todo o histórico da solicitação.
     * Buscar o código da transição na gtcWorkflowInstance.
     * Buscar o histórico da instancia em gtcWorkflowHistory.
     *
     * @param integer $workflowId código do workflow
     * @param string $tableName nome da tabela onde o workflow se encontra
     * @param string $tableId código do registro na tabela relacionada
     *
     * @return array de stdClass
     */
    public static function getHistory( $workflowId, $tableName, $tableId )
    {
        $MIOLO = MIOLO::getInstance();
        
        if ( !$workflowId || !$tableName || ! $tableId )
        {
            throw new Exception( _M('É necessário código do workflow, nome e código da tabela para que o workflow funcione corretamente.','gnuteca3') );
        }

        $instance = GWorkflow::getCurrentStatus( $workflowId, $tableName, $tableId );
        $busWorkflowHistory = $MIOLO->getBusiness( 'gnuteca3', 'BusWorkflowHistory');
        $busWorkflowHistory = new BusinessGnuteca3BusWorkflowHistory();

       
        return $busWorkflowHistory->getHistory( $instance->workflowInstanceId );
    }

    /**
     * Altera o estado da solicitação.
     * 
     * Para isso:
     * Buscar o estado atual.
     * Verificar se existe a transição do estado atual para o estado futuro.
     * Verificar se existe uma função externa para esta transição. //não concordo, tem caso que não tem função
     * Se não existir a função, retornar um erro
     * Se existir executar a função.
     * Se a função retornar true executar o update do estado na gtcWorkflowInstance e inserir um novo registro em gtcWorkflowHistory.
     *
     * @param integer $workflowId código do workflow
     * @param string $tableName nome da tabela onde o workflow se encontra
     * @param string $tableId código do registro na tabela relacionada
     * @param integer $futureStatus estado futuro para qual a instancia irá
     * @param string $comment comentário justicando a troca de estado
     *
     * @return boolean
     *
     */
    public static function changeStatus( $workflowId, $tableName, $tableId, $futureStatus, $comment )
    {
        $MIOLO = MIOLO::getInstance();

        if ( !$workflowId || !$tableName || ! $tableId )
        {
            throw new Exception( _M('É necessário código do workflow, nome e código da tabela para que o workflow funcione corretamente.','gnuteca3') );
        }

        if ( !$futureStatus )
        {
            throw new Exception( _M('É necessário informar um estado futuro para efetuar a troca de estado.', 'gnuteca3') );
        }

        $currentStatus = GWorkflow::getCurrentStatus($workflowId, $tableName, $tableId);
        
        if ( !$currentStatus )
        {
            throw new Exception( _M('Imposível encontrar estado atual para solicitacao: @1 - @2 - @3', 'gnuteca3', $workflowId, $tableName, $tableId ) );
        }

        $args = GUtil::decodeJsArgs( $args );
        $MIOLO->getClass('gnuteca3', 'GWorkflow');
        $possibleFutureStatus = GWorkFlow::getFutureStatus( $workflowId, $tableName, $tableId );

        //segurança contra hackeio de parâmetros
        if ( is_array( $possibleFutureStatus  ) )
        {
            foreach ( $possibleFutureStatus as $key => $status )
            {
                //garante que a transição possa realmente existir
                if ( $status->nextWorkflowStatusId == $futureStatus && $status->previousWorkflowStatusId)
                {
                    //transforma future status em um objeto
                    $futureStatusObj = $status;
                }
            }
        }
        else
        {
            //caso não encontre o estado futuro
            throw new Exception( _M('Impossível encontrar estado futuro @1 !', 'gnuteca3', $futureStatus ) );
        }

        //caso não tenha encontrado um a transição possível mostra mensagem informativa
        if ( !$futureStatusObj )
        {
            $busWorkflowStatus = $MIOLO->getBusiness( 'gnuteca3', 'BusWorkflowStatus');
            $busWorkflowStatus = new BusinessGnuteca3BusWorkflowStatus();
            $futureStatusObj = $busWorkflowStatus->getWorkflowStatus( $futureStatus );

            throw new Exception( _M(' Impossível alterar o estado de "@1" para "@2"! Não existe transição para isto.' ,'gnuteca3', $currentStatus->statusName, $futureStatusObj->name ? $futureStatusObj->name : $futureStatus ) );
        }

        //caso tenha permissão verifica-a
        if ( $futureStatusObj->nextTransaction )
        {
            if ( !GPerms::checkAccess( $futureStatusObj->nextTransaction , 'UPDATE' ) )
            {
                throw new Exception ( _M('Sem permissão para trocar para o estado "@1".','gnuteca3' ,$futureStatusObj->name) );
            }
        }

        //caso tenha função executa-a
        if ( $futureStatusObj->function)
        {
            $functionResult = GWorkflow::executeFunction( $futureStatusObj->function, $workflowId, $tableName, $tableId, $futureStatusObj, $currentStatus , $comment );
        }

        $busWorkflowInstance = $MIOLO->getBusiness( 'gnuteca3', 'BusWorkflowInstance');
        $busWorkflowInstance = new BusinessGnuteca3BusWorkflowInstance();
        $busWorkflowInstance->tableName = $tableName;
        $busWorkflowInstance->tableId = $tableId;
        $busWorkflowInstance->workflowId = $workflowId;
        $busWorkflowInstance->workflowStatusId = $futureStatusObj->nextWorkflowStatusId;
        $busWorkflowInstance->workflowInstanceId = $currentStatus->workflowInstanceId;
        $busWorkflowInstance->date = GDate::now();
      
        return $busWorkflowInstance->updateWorkflowInstance( null, $comment );
    }

    protected static function executeFunction( $function , $workflowId, $tableName, $tableId, $transition = null , $instance = null )
    {
        $MIOLO = MIOLO::getInstance();
        $function = explode('::', $function );
        $class = trim($function[0]);
        $function = trim($function[1]);

        $filename = BusinessGnuteca3BusFile::getAbsoluteServerPath(true) . "/workflow/$class.class.php";
        if ( file_exists($filename) )
        {
            require_once($filename);
        }
        else
        {
            require_once(BusinessGnuteca3BusFile::getAbsoluteServerPath(true) . "/workflow/wfPurchaseRequestDefault.class.php");
            $class = 'wfPurchaseRequestDefault';
        }

        $data = (object) $_REQUEST;
        $data->workflowId = $workflowId;
        $data->tableName = $tableName;
        $data->tableId = $tableId;
        $data->nextWorkflowStatusId;
        //$data->comment = $comment;
        $data->transition = $transition;
        $data->instance = $instance;

        //para a inicilização poder obter a requisição
        if ( !$data->instance )
        {
            $data->instance->tableId = $tableId;
        }

        $classInstance = new $class( $data );

        if ( !method_exists( $classInstance, $function ) )
        {
            throw new Exception( _M('Método "@1" não existe na classe "@2"! Impossível trocar o estado!', 'gnuteca3', $function, $class ) );
        }

        $result = $classInstance->$function( );

        if ( ! $result )
        {
            throw new Exception( _M('Função @2::@1 inválidou o resultado!', 'gnuteca3', $function, $class ) );
        }

        return $result;
    }

     /**
     * Remove uma instancia e seu histórico
     *
     * @param integer $workflowId código do workflow
     * @param string $tableName nome da tabela onde o workflow se encontra
     * @param string $tableId código do registro na tabela relacionada
     *
     * @return boolean
     */
    public static function deleteInstance( $workflowId, $tableName, $tableId )
    {
        $MIOLO = MIOLO::getInstance();
        
        if ( !$workflowId || !$tableName || ! $tableId )
        {
            throw new Exception( _M('É necessário código do workflow, nome e código da tabela para que o workflow funcione corretamente.','gnuteca3') );
        }

        $instance = GWorkflow::getCurrentStatus( $workflowId, $tableName, $tableId );
        $busWorkflowInstance = $MIOLO->getBusiness( 'gnuteca3', 'BusWorkflowInstance');
        $busWorkflowInstance = new BusinessGnuteca3BusWorkflowInstance();

        if ( $instance->workflowInstanceId )
        {
            return $busWorkflowInstance->deleteWorkflowInstance( $instance->workflowInstanceId );
        }
        
        return false;
        
    }
}
?>