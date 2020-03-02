<?php

/**
 * @author moises
 *
 * @since
 * Class created on 08/04/2013
 *
 */
$MIOLO->uses('tipos/capsolicitacao.class.php', 'contaspagar');

class capsolicitacaocomprador extends capsolicitacao
{
    public function buscarNaReferencia($colunas, $valoresFiltrados = array( ))
    {
        $msql = parent::buscarNaReferencia($colunas, $valoresFiltrados);
        
        // Filtra apenas pelas solicitacoes feitas pelo usuario logado
        $msql->addEqualCondition('public.view_capsolicitacaopagamento.cod_usuario', SAGU::getUsuarioLogado()->personId);
        
        return $msql;
    }
    
    public static function verificaFornecedorNotaFiscal($data)
    {
        $msql = new MSQL();
        $msql->setColumns(' COUNT(*) > 0 ');
        $msql->setTables('capsolicitacao');
        $msql->addEqualCondition('capsolicitacao.fornecedorid', $data->fornecedorid);
        $msql->addEqualCondition('capsolicitacao.numerodanotafiscal', $data->numerodanotafiscal);
        
        $resultado = DB_FALSE;
        if ( strlen($data->fornecedorid) > 0 && strlen($data->numerodanotafiscal) > 0 )
        {
            $resultado = bBaseDeDados::consultar($msql);
        }
        
        return is_array($resultado) ? $resultado[0][0] : $resultado;
    }
    
    public function salvarContasPagarRateio($data)
    {
        $sql = 'INSERT INTO caprateioautorizacao
                        (rateioautoautorizacaoid,
                                   solicitacaoid,
                                    costcenterid,
                                   personidowner,
                                      autorizado,
                                 dataautorizacao)
                                    VALUES (?,
                                            ?,
                                            ?,
                                            ?,
                                            ?,
                                            now())';
        $sqlPK = "SELECT nextval('caprateioautorizacao_rateioautoautorizacaoid_seq'::regclass)";
        $result = SDatabase::query($sqlPK);
        $rateioautorizaocaoid = $result[0][0];

        $args = array(
        $rateioautorizaocaoid,
        $data->solicitacaoid,
        $data->costcenterid,
        $data->personidowner,
        'f' );

        $result2 = SDatabase::execute($sql, $args);
        
        return $result2;
    }
}

?>