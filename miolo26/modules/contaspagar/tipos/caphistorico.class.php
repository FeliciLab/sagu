<?php

/**
 * @author moises
 *
 * @since
 * Class created on 08/04/2013
 *
 */
class caphistorico extends bTipo
{
    public $historicoid;
    public $solicitacaoid;
    public $solicitacaoestadoid;
    public $personid;
    public $data;
    public $justificativa;
    
    public $ordenacaoPadrao = 'public.caphistorico.data';
}

?>