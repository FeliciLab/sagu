<?php
/**
 *
 * @author moises
 *
 * @since
 * Class created on 02/04/2013
 */
$MIOLO->uses('classes/capformdinamicobusca.class.php', 'contaspagar');

class frmcapsolicitacaocompradorBusca extends capformdinamicobusca
{
    public $botaoRemover = false;
    
    public function criarMenuDeContexto()
    {
        $menu = parent::criarMenuDeContexto();
        $menu->addCustomItem(_M('Cancelar'), $this->manager->getUI()->getAjax(capformdinamicobusca::ACAO_CANCELAR_SOLICITACAO), MContextMenu::ICON_DELETE);
    }
}

?>