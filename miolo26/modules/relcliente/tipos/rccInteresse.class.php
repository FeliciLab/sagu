<?php

class rccInteresse extends bTipo
{
    public function __construct() 
    {
        parent::__construct('rccinteresse');
    }
    
    public function obterConsulta($filtros, $colunas="interesseid, rcccontatocontatoid, datahora::date, nome, telefone, email, cpf, observacao, curso, contrato", $limit)
    {
        
        $sql = $this->obterObjetoConsulta($filtros, $colunas, $limit);
        
        return $sql->select();
    }
   
}
