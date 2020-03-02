<?php
/**
 *
 * @author moises
 *
 * @since
 * Class created on 02/04/2013
 */
$MIOLO->uses('tipos/captitulo.class.php', 'contaspagar');
$MIOLO->uses('classes/capformdinamicobusca.class.php', 'contaspagar');

class frmcaplancamentoBusca extends capformdinamicobusca
{
    protected $botaoExplorar = false;
    protected $botaoNovo = false;
    
    public function __construct($parametros, $titulo = NULL)
    {
        parent::__construct($parametros, _M('Alteração de títulos'));
    }
}

?>