<?php

/**
 * @author moises
 *
 * @since
 * Class created on 03/04/2013
 *
 */

class capsolicitacaoestado extends bTipo
{
    public $solicitacaoestadoid;
    public $nome;
    
    const AGUARDANDO_DEFERIMENTO = 1;
    const AGUARDANDO_PAGAMENTO = 2;
    const CANCELADO = 4;
    const FECHADO = 6;
    
    public function __construct() 
    {
        parent::__construct('capsolicitacaoestado');
    }
}

?>