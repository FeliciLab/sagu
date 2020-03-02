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

class rccInadimplentes extends bTipo
{
    public function __construct() 
    {
        parent::__construct('relclienteinadimplentes');

    }
    
    public function obterSaldoDevedor($personId)
    {       
        $MIOLO = MIOLO::getInstance();
        $saldo = 0;
        
        try
        {
            $busReceivableInvoice = $MIOLO->getBusiness('finance', 'BusReceivableInvoice');
            $totais = $busReceivableInvoice->totalizationInvoicesForPerson($personId, true);
            $saldo = $totais[0][3];
        }
        catch( Exception $e ){}

        return $saldo;
    }
    
    public function obterConsulta($filtros)
    {        
        $filtro = '';
        
        $argumentos = array();
                
        if ( strlen($filtros->atraso) )
        {
            $argumentos[] = " now()::date - a.maturitydate <= '{$filtros->atraso}' ";
        }
    
        if ( strlen($filtros->comunicado) )
        {
            $argumentos[] = " now()::date - c.datahoradocontato::date >= '{$filtros->comunicado}' ";
        }

        if ( strlen($filtros->personid) )
        {
            $argumentos[] = " a.personid =  '{$filtros->personid}' ";
        }

        if ( strlen($filtros->foiComunicado) )
        {
            
            
            if ( $filtros->foiComunicado == DB_TRUE )
            {
                $argumentos[] = ' a.personid IN (SELECT pessoa FROM rcccontato) ';
            }
            else
            {
                $argumentos[] = ' a.personid NOT IN (SELECT pessoa FROM rcccontato) ';
            }
        }
        
        if ( $filtros->ultimoContato == DB_TRUE || !(strlen($filtros->ultimoContato) > 0) )
        {
            $sql = " SELECT DISTINCT ON (a.name) a.personid, ";
        }
        else
        {
            $sql = " SELECT a.personid, ";
        }
        
        if ( count($argumentos) > 0 )
        {
            $filtro = ' WHERE ' . implode('AND', $argumentos);
        }
        
        $sql .= "        a.name,
                        NULL,
                        now()::date - c.datahoradocontato::date AS contato,                         
                        now()::date - a.maturitydate AS dias
                   FROM ( SELECT aa.personid, getpersonname(aa.personid) as name, min(aa.maturitydate) AS maturitydate
                            FROM finreceivableinvoice aa
                           WHERE aa.balance > 0::numeric AND aa.maturitydate < now()::date AND aa.iscanceled = false
                           GROUP BY 1,2 ORDER BY 2) a
               LEFT JOIN ( SELECT max(c.contatoid) AS contatoid, c.pessoa, c.datahoradocontato
                    FROM rcccontato c
                GROUP BY c.pessoa, c.datahoradocontato) c ON c.pessoa = a.personid
                {$filtro}
                ORDER BY a.name, contato  ";
                        
        return $sql;
    }
    
    public function getPersonInfo($filtros)
    {
        $msql = new MSQL();
        $msql->setTables('basperson');
        $msql->setColumns('name');
        $msql->setColumns('email');
        $argumentos = array();

        $msql->setWhere('personid = ?');
        $argumentos[] = $filtros->personid;
        
        
        $consulta = bBaseDeDados::consultar($msql, $argumentos);
        
        foreach ($consulta as $key)
        {
            $infos = $key;
        }
        
        return $infos; 

    }
    
    public function confirmarContato($argumentos)
    {
               

        $sql = "insert into rcccontato (mensagem, origemdecontatoid) 
                values ('".$argumentos->resposta."', ".$argumentos->origem.");";

        $retorno = bBaseDeDados::executar($sql);
 
    }
    
    //TODO verificar mudanças de canceled e POLYCE
    public function buscarPessoa($filtros) 
    {

        $msql = new MSQL();
        $msql->setTables(' finreceivableinvoice A');

        //$msql->setColumns('DISTINCT y.name');
        $msql->setColumns('a.invoiceid');
        $msql->setColumns('to_char(a.maturitydate, \'DD/MM/YYYY\') as dia');
        $msql->setColumns('to_char(balancewithpoliciesdated(a.invoiceid, now()::date),\'999G999G990D99\') as valor');
        
        $msql->setWhere(' balance(A.invoiceid) > 0 AND A.maturitydate < now()::date AND A.iscanceled = FALSE');

        if ( strlen($filtros->personid) > 0 )
        {
              $msql->setWhere('a.personid = ?');
              $argumentos[] = $filtros->personid;
        }

        $sql = $msql->select($argumentos);

        $resultado = bBaseDeDados::obterInstancia()->_db->query($sql);

        return $resultado;
    
    }


}

?>
