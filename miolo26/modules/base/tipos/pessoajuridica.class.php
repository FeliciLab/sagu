<?php

class pessoajuridica extends bTipo
{
    protected $ordenacaoPadrao = 'baslegalperson.name';
    
    public function __construct()
    {
        parent::__construct('baslegalperson');
    }    
}

?>
