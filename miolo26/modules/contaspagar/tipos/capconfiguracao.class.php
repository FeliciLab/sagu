<?php
/**
 * @author moises
 *
 * @since
 * Class created on 28/03/2013
 *
 */
class capconfiguracao
{
    const NECESSITA_DEFERIMENTO = 'ND';
    const NAO_NECESSITA_DEFERIMENTO = 'SD';
    
    /**
     *
     * @return string
     */
    public function obterTipoSolicitacaoPagto()
    {
        $busConf = new BusinessBasicBusConfig();
        $conf = $busConf->getConfig('CONTASPAGAR', 'TIPO_DE_SOLICITACAO_DE_PAGAMENTO');

        return $conf->value;
    }
    
    public function salvaTipoSolicitacaoPagto($tipo)
    {
        $data->value = $tipo;
        $data->moduleConfig = 'CONTASPAGAR';
        $data->parameter = 'TIPO_DE_SOLICITACAO_DE_PAGAMENTO';
        
        $busConf = new BusinessBasicBusConfig();
        $busConf->updateConfigValue($data);
    }
}
?>