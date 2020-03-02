<?php
$MIOLO = MIOLO::getInstance();
$MIOLO->uses('forms/frmDinamicoBusca.class.php', 'base');
$MIOLO->uses('tipos/capsolicitacao.class.php', 'contaspagar');

class capformdinamicobusca extends frmDinamicoBusca
{
    const ACAO_CANCELAR_SOLICITACAO = 'bfCancelarSolicitacao:click';
    
    public function bfCancelarSolicitacao_click()
    {
        $solicitacaoId = $this->obterIdSelecionado();
        
        $solicitacao = new capsolicitacao();
        $solicitacao->solicitacaoid = $solicitacaoId;
        $solicitacao->popular();
        $solicitacao->verificaSePodeCancelar();
        
        MPopup::confirm(_M('Confirma cancelamento de solicitação?'), _M('Confirmação de cancelamento'), ':cancelarDefinitivamente');
    }
    
    public function cancelarDefinitivamente()
    {
        $solicitacaoId = $this->obterIdSelecionado();
        
        $solicitacao = new capsolicitacao();
        $solicitacao->solicitacaoid = $solicitacaoId;
        $solicitacao->popular();
        $solicitacao->solicitacaoestadoid = capsolicitacaoestado::CANCELADO;
        $solicitacao->salvar();
        
        new MMessageSuccess(_M('Solicitação cancelada com sucesso.'));
        MPopup::remove();
    }
}
?>
