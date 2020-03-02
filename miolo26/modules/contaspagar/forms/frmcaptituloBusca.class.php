<?php
/**
 *
 * @author moises
 *
 * @since
 * Class created on 02/04/2013
 */
$MIOLO->uses('classes/capformdinamicobusca.class.php', 'contaspagar');

class frmcaptituloBusca extends capformdinamicobusca
{
    protected $botaoEditar = false;
    protected $botaoRemover = false;
    protected $botaoExplorar = false;
    protected $botaoNovo = false;
    
    protected function criarMenuDeContexto()
    {
        $menu = parent::criarMenuDeContexto();
        $menu->addCustomItem(_M('Registrar pagamento'), $this->manager->getUI()->getAjax('bfEditar:click'), MContextMenu::ICON_PASTE);
        
        return $menu;
    }
}

?>