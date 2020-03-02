<?php


class rccContato extends bTipo
{
    public function __construct() 
    {
        parent::__construct('rccContato');
    }
    
    public function buscarNaReferencia($colunas, $valoresFiltrados = array())
    {
        $sql = parent::buscarNaReferencia($colunas, $valoresFiltrados);
        
        if ( strlen($valoresFiltrados->tipodecontatoid) > 0 )
        {
            $sql->setWhere('rcccontato.tipodecontatoid = ?');
            $sql->addParameter($valoresFiltrados->tipodecontatoid);
        }
        
        return $sql;
    }
}

?>
