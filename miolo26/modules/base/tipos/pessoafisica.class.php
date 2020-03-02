<?php

class pessoafisica extends bTipo
{
    protected $ordenacaoPadrao = 'basphysicalperson.name';
    
    public function __construct()
    {
        parent::__construct('basphysicalperson');
    }
    
    public function buscarParaEscolha($filtro=NULL, $colunas=NULL, $limit=NULL)
    {
        self::setBuscarLegalPerson(true);
        
        return parent::buscarParaEscolha($filtro, $colunas, $limit);
    }
    
    public function obterConsulta($filtros = NULL, $colunas=NULL, $limit=NULL)
    {
        $sql = $this->obterObjetoConsulta($filtros, $colunas, $limit);
        
        $sqlGerado = $sql->select();

        // Faz UNION com BasLegalPerson para componente de Escolha
        if ( self::getBuscarLegalPerson() )
        {
            $sql->clearOrderBy();
            $sql->clearLimit();
            
            $sql2 = new MSQL();
            $sql2->setTables('ONLY baslegalperson');
            $sql2->setColumns($sql->getColumns());
            $sql2->setLimit(50);
            $sql2->setOrderBy('name');
            
            $sqlGerado = $sql->select() . ' UNION ALL ' . $sql2->select();
        }
        
        return $sqlGerado;
    }
    
    public static function setBuscarLegalPerson($status)
    {
        return MIOLO::getInstance()->setConf('pessoafisica.legalperson', $status);
    }
    
    /**
     * @return boolean
     */
    public static function getBuscarLegalPerson()
    {
        return MIOLO::getInstance()->getConf('pessoafisica.legalperson');
    }
}

?>
