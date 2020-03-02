<?php

/**
 * @author moises
 *
 * @since
 * Class created on 08/04/2013
 *
 */
class capsolicitacaoparcela extends bTipo
{
    public $solicitacaoparcelaid;
    public $solicitacaoid;
    public $parcela;
    public $valor;
    public $datavencimento;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->adicionarTipoRelacionado('captitulo');
    }
}

?>