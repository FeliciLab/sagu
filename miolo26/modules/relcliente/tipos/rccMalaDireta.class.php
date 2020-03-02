<?php
/**
 * @author Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @since
 * Class created on 23/12/2012
 *
 **/

class rccMalaDireta extends bTipo
{
    public function __construct() 
    {
        parent::__construct('basperson');
    }
    
    public function obterConsulta($filtros)
    {
        $msql = new MSQL();
        $msql->setTables($this->tabela);
        $msql->setColumns('personid');
        $msql->setColumns('nome');


        if ( strlen($filtros->todos) > 0 )
        {
              $msql->setWhere('personid > 0');
              $argumentos[] = $filtros->todos;
        }
        
        return $msql->select($argumentos);
    
    }
    
    
    //Retorna array de cursos
    public function selectcurso($args)
    {
            $msql = new MSQL('shortname', 'acdcourse');
            $interesses = bBaseDeDados::consultar($msql);
            foreach ($interesses as $retorno)
            {
                $cursos[] = $retorno[0];
            }

            return $cursos;
    }
    
    public function getInfo($args)
    {
        $columns = 'name, email';
        $where = 'personid > 0';
        $params = array();
        
        if ( $args->selectTipo == 0 ) 
        {
            $tables = 'basperson';
            $key = 0;
            $personid = array();
            foreach ($args->personid as $id)
            {
                   if ( strlen($id) > 0 )
                   {
                       $personid[] = $id;
                   }
            }
            
            $personid = implode(',', $personid);
            $where .= ' AND personid IN (' . $personid . ')';
        }
        else if ( $args->selectTipo == 1 )  
        {
            if ($args->selectVinculo == '')
            {  
            }
            else if ($args->selectVinculo == 0)
            {
                $tables = 'basphysicalpersonprofessor';
            }
            else if ($args->selectVinculo == 1)
            {
                $tables = 'basphysicalpersonstudent';
            }
            else if ($args->selectVinculo == 2)
            {
                $tables = 'basphysicalpersonemployee';
            }
            else
            {
                $tables = 'basphysicalperson';
            }

        }
        //TODO descobrir porque o MSQL não gosta de comparar varchar
        else
        {
            if ($args->selectInteresse == '')
            {  
            }
            else if ($args->selectInteresse >= 0)
            {
                unset($columns);
                $columns = 'nome, email';
                $tables = 'rccinteresse';
                $where = "cursoid = ?";
                $params[] = $args->selectInteresse;
            }

        }
        
        if ( $args->selectTipo == '' ) 
        {
        } 
        else
        {
            $msql = new MSQL();
            $msql->setColumns($columns, TRUE);
            $msql->setTables($tables);
            $msql->setWhere($where);
            $msql->setParameters($params);
            
            $infos = bBaseDeDados::consultar($msql, $params);

            return $infos; 
        }

            
    }
    
}
?>
