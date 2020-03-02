<?php

/**
 * @author moises
 *
 * @since
 * Class created on 08/04/2013
 *
 */
class capcentrodecusto extends bTipo
{
    public function __construct($chave)
    {
        parent::__construct('accCostCenter');
    }
    
    public function obterConsulta($filtros = NULL, $colunas = NULL, $limit = NULL)
    {
        if ( strlen($filtros->description) == 0 )
        {
            $colunas = 'costcenterid, costcenterid || \' - \' || description';
        }
        
        return parent::obterConsulta($filtros, $colunas, $limit);
    }
    
    public function obterObjetoConsulta($filtros = NULL, $colunas = array('costcenterid', 'description'))
    {
	$msql = new MSQL();
        $msql->setTables('ONLY ' . $this->tabela);
        
        if ( strlen($filtros->description) > 0 )
        {
            $colunas = 'costcenterid || \' - \' || description, costcenterid';
        }
        
	if ( is_array($colunas) )
	{
            $msql->setColumns(implode(',', $colunas));
	}
	else
	{
	    $msql->setColumns($colunas);
	}

	if ( strlen($filtros->costcenterid) > 0 )
	{
	    $msql->addEqualCondition('public.acccostcenter.costcenterid', $filtros->costcenterid);
	}

	if ( strlen($filtros->description) > 0 )
	{
	    $msql->setWhere("( unaccent(lower(description)) LIKE unaccent(lower(?))
                            OR unaccent(lower(costcenterid)) LIKE unaccent(lower(?)) )");
            
	    $parametros[] = '%' . $filtros->description . '%';
            $parametros[] = '%' . $filtros->description . '%';
	}
        
        // Filtra na lookup (bEscolha) para que filtre apenas por centros de custo que aceitem solicitacoes
        $msql->setWhere('public.acccostcenter.allowPaymentRequest = true');

	if ( strlen($this->ordenacaoPadrao) > 0 )
        {
            $msql->setOrderBy($this->ordenacaoPadrao);
        }
        
        $msql->setParameters($parametros);

        return $msql;
    }
}

?>
