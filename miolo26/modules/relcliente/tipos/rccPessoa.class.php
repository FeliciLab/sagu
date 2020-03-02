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

//TODO select Criar uma classe de popup para visualização de informações básicas da pessoa.
//A)dados cadastrais
//B)serviços contratados (visão que Academico do Sagu e CL).
//C)pagamentos realizados e a realizar. (título do financeiro)

class rccPessoa extends bTipo
{
    public function __construct() 
    {
        parent::__construct('rccpopuppessoa');
    }
    
    public function buscarPessoa($filtros) 
    {
        
        $msql = new MSQL();
        $msql->setTables('rccpopuppessoa');
        $msql->setColumns('name');
        $msql->setColumns('courseid');
        $msql->setColumns('value');
        $msql->setColumns('operationtypeid');


        if ( strlen($filtros->todos) > 0 )
        {
              $msql->setWhere('personid > ?');
              $argumentos[] = $filtros->personid;
        }
        
        return $msql->select($argumentos);
    
    }
   
}

?>