<?php

class pessoa extends bTipo
{
    protected $ordenacaoPadrao = 'basperson.name';
    
    public function __construct()
    {
        parent::__construct('basperson');
    }
}

?>
