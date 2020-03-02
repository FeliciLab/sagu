<?php

/**
 * @author Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * \b Maintainers: \n
 * Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @since
 * Class created on 07/11/2012
 *
 */
class rccContatoAgendamento extends bTipo
{
    public function __construct() 
    {
        parent::__construct('rccContato');

    }
    
    public function obterConsulta($filtros)
    {            
        $msql = new MSQL();
        $msql->setTables($this->tabela . ' a  INNER JOIN rccTipoDeContato b ON a.tipodecontatoid = b.tipodecontatoid INNER JOIN ONLY basperson C ON c.personid = a.pessoa');
        $msql->setColumns('a.contatoid');
        $msql->setColumns('c.personid');
        $msql->setColumns('c.name');
        $msql->setColumns('b.descricao');
        $msql->setColumns('a.datahoraprevista');

        $argumentos = array();
        
        if ($filtros->checkTodos == 't')
        {
            
        }
        else if ($filtros->checkRecebidas == 't' && $filtros->checkEnviados == null)
        {
            $msql->setWhere('a.rcccontatocontatoid is null');
        }
        else if ($filtros->checkEnviados == 't' && $filtros->checkRecebidas == null )
        {
            $msql->setWhere('a.rcccontatocontatoid is not null');
        }
        else if ($filtros->checkRespondidos == 't')
        {
            $msql->setWhere('EXISTS(SELECT D.rcccontatocontatoid from rcccontato D WHERE a.contatoid = D.rcccontatocontatoid)');
        }
        
        
        if ( strlen($filtros->datahoraprevista) > 0 && strlen($filtros->hoje) > 0 )
        { 
            $msql->setWhere('to_char(a.datahoraprevista,\'YYYY-MM-DD\') = ?');
            $argumentos[] = $filtros->datahoraprevista;
        }
        
        if ( strlen($filtros->datahoraprevista) > 0 && strlen($filtros->atrasados) > 0 )
        {
            $msql->setWhere('a.datahoraprevista < ?');
            $argumentos[] = $filtros->datahoraprevista;
        }
        
        if ( strlen($filtros->datahoraprevista) > 0 && strlen($filtros->semana) > 0 )
        {
            $msql->setWhere('a.datahoraprevista >= ?');
            $argumentos[] = $filtros->datahoraprevista;
            
            $msql->setWhere('a.datahoraprevista <= ?');
            $argumentos[] = $filtros->domingo;
        }
        
        $msql->setOrderBy('a.datahoraprevista');

        return $msql->select($argumentos);
    }
    
    public function buscarPessoa($filtros) 
    {
        $msql = new MSQL();
        $msql->setTables('basperson A 
                          LEFT JOIN bascity C
                          ON a.cityid = c.cityid');
        $msql->setColumns('a.name');
        $msql->setColumns('a.email');
        $msql->setColumns('a.emailalternative');
        $msql->setColumns('c.name as cidade');

        if ( strlen($filtros->personid) > 0 )
        {
              $msql->setWhere('personid = ?');
              $argumentos[] = $filtros->personid;
        }

        $sql = $msql->select($argumentos);
        $resultado = bBaseDeDados::obterInstancia()->_db->query($sql, NULL, NULL, PostgresQuery::FETCH_OBJ);

        return $resultado->result;
    
    }
}

?>
