<?php

/**
 * Tipo responsável pelo gerenciamento da tabela caprateio
 * 
 * @author Luís Augusto Weber Mercado [luis_augusto@solis.com.br]
 * 
 * \b Maintainers: \n
 * Luís Augusto Weber Mercado [luis_augusto@solis.com.br]
 *
 * @since
 * Classe criada em 07/05/2015
 */
class caprateio extends bTipo
{
    
    /**
     * Construtor da classe
     * 
     */
    public function __construct() 
    {
        parent::__construct();
        
        $this->adicionarTipoRelacionado("caprateiocentrodecusto");
    }
    
    /**
     * Dado um plano de contas, verifica se este possui um rateio vigente
     * 
     * @param String $accountschemeid Identificador do plano de contas
     * 
     * @return Boolean TRUE se o plano de contas informado tiver um rateio
     * vigente, FALSE caso contrário
     */
    public function planoDeContasTemRateioVigente($accountschemeid)
    {
        $filtros = new stdClass();
        $filtros->accountschemeid = $accountschemeid;
        
        $busca = $this->buscar($filtros);
        
        foreach ( $busca as $resultado )
        {
            // Em alguns casos, os planos de contas tem 'sub-planos' de contas
            // Assim pega-se apenas o plano de contas em questão
            if( $resultado->accountschemeid !== $accountschemeid )
            {
                continue;
            }
            
            if ( $this->verificaDataAtualNoRateio($resultado->datainicial, $resultado->datafinal) )
            {
                return true;
            }
        }
        
        return false;   
    }
    
    /**
     * Veririfica se o dia de hoje está presente num intervalo de Rateio
     * 
     * @param String $datainicial Data inicial
     * @param String $datafinal Data final
     * 
     * @return Boolean TRUE se a data atual está no intervalo informado, FALSE
     * caso contrário
     */
    private function verificaDataAtualNoRateio($datainicial, $datafinal)
    {
        $tinicial = strtotime($this->getDataFormatoCorreto($datainicial));
        $tfinal = strtotime($this->getDataFormatoCorreto($datafinal));
        $thoje = strtotime("today");
        
        // Se a data final foi definida
        if ( $tfinal )
        {   
            // Se a data de hoje está no intervalo
            return ( ($thoje >= $tinicial) && ($thoje <= $tfinal) );

        }
        else
        {
            // Se a data de hoje é maior
            return $thoje >= $tinicial;

        }
        
    }
    
    /**
     * Corrige o formato da data (dd/mm/yyyy) para o formato do banco de dados (yyyy-mm-dd)
     * 
     * @param String $data Data a ser formatada
     * 
     * @return String|NULL Data formatada, NULL caso a data informada seja inválida
     */
    private function getDataFormatoCorreto($data)
    {
        if ( $data )
        {
            return date('Y-m-d', strtotime(str_replace('/', '-', $data)));
        }
        
        return NULL;
    }
    
    public static function obtemInformacoesPopupAutorizacao($solicitacaoId)
    {
        $sql = " SELECT costcenterid || ' - ' || (SELECT description FROM acccostcenter WHERE costcenterid = A.costcenterid),
                        getPersonName(A.personidowner),
                        (CASE A.autorizado 
                            WHEN true
                            THEN 'Sim'
                            ELSE 'Não'
                         END) as autorizado,
                         datetouser(A.dataautorizacao::DATE)
                   FROM caprateioautorizacao A ";
        
        $msql = new MSQL();
        $msql->createFrom($sql);
        $msql->setWhere("A.solicitacaoId = {$solicitacaoId}");
        $msql->select();

        return bBaseDeDados::consultar($msql);
    }
    
    /*
     * Lógica que atualiza a autorização do chefe logado
     * 
     */
    public static function atualizaAutorizacao($data)
    {
       $MIOLO = MIOLO::getInstance();

       $autorizacaoid = caprateio::obterRateioAutorizacaoId($data->solicitacaoid, $data->personidowner);
       
       foreach($autorizacaoid as $id)
       {
            $sql = 'UPDATE caprateioautorizacao
                   SET autorizado = \''.DB_TRUE.'\',
                       dataautorizacao = now()
                 WHERE rateioautoautorizacaoid = \''.$id[0].'\'';

            $result = SDAtabase::execute($sql);
       }
        return $result;
    }
    
    public static function obterRateioAutorizacaoId($solicitacaoId, $personIdOwner = null)
    {
        $MIOLO = MIOLO::getInstance();

        $sql = 'SELECT rateioautoautorizacaoid,
                                    autorizado as aut
                      FROM caprateioautorizacao
                      WHERE solicitacaoid = \''.$solicitacaoId.'\'';
        
        if ( strlen($personIdOwner) > 0 )
        {
            $sql .= 'AND personidowner = \''.$personIdOwner.'\'';
        }

        $result = SDatabase::query( $sql );
        
        return $result;
    }    
}

?>