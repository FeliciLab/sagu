<?php

/**
 * @author moises
 *
 * @since
 * Class created on 24/07/2014
 *
 */
class capplanodecontas extends bTipo
{
    public function __construct($chave)
    {
        parent::__construct('accaccountscheme');
    }   
    
    public function obterConsulta($filtros = NULL, $colunas = NULL, $limit = NULL)
    {
        if ( strlen($filtros->description) == 0 )
        {
            $colunas = 'accountschemeid, accountschemeid || \' - \' || description';
        }
        
        return parent::obterConsulta($filtros, $colunas, $limit);
    }
    
    public function obterObjetoConsulta($filtros = NULL, $colunas = array('accountschemeid', 'description'))
    {
	$msql = new MSQL();
        $msql->setTables('ONLY ' . $this->tabela);
        
        if ( strlen($filtros->description) > 0 )
        {
            $colunas = 'accountschemeid || \' - \' || description, accountschemeid';
        }
        
	if ( is_array($colunas) )
	{
            $msql->setColumns(implode(',', $colunas));
	}
	else
	{
	    $msql->setColumns($colunas);
	}

	if ( strlen($filtros->accountschemeid) > 0 )
	{
	    $msql->addEqualCondition('public.accaccountscheme.accountschemeid', $filtros->accountschemeid);
	}

	if ( strlen($filtros->description) > 0 )
	{
	    $msql->setWhere("( unaccent(lower(description)) LIKE unaccent(lower(?))
                            OR unaccent(lower(accountschemeid)) LIKE unaccent(lower(?)) )");
            
	    $parametros[] = '%' . $filtros->description . '%';
            $parametros[] = '%' . $filtros->description . '%';
	}

	if ( strlen($this->ordenacaoPadrao) > 0 )
        {
            $msql->setOrderBy($this->ordenacaoPadrao);
        }
        
        $msql->setParameters($parametros);

        return $msql;
    }
}
?>