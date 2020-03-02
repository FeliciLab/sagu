<?php

class PrtPreferenciaProfessor extends bTipo
{

    public $personid;
    public $username;
    
    public function __construct($personid, $username)
    {
        parent::__construct('PrtPreferenciaProfessor');
        
        $this->username = $username;
        $this->personid = $personid;
        $this->popular();
    }
    
    //sobreescrito para compatibilidade com o sagu
    public function buscar($filtros, $colunas = NULL)
    {
        $sql = $this->obterConsulta($filtros, $colunas);
        $resultado = bBaseDeDados::obterInstancia()->_db->query($sql, NULL, NULL, PostgresQuery::FETCH_OBJ);
        
        return $resultado;
    }
    
    public function popular()
    {
        $filtro = new stdClass();
        $filtro->personid = $this->personid;
        $data = $this->buscar($filtro);
        
        if($data)
        {
            $this->definir($data[0]);
        }
    }


    public function salvar($args)
    {
        $dados = new stdClass();
        $dados->notifmsgrecebida = $args->notifmsgrecebida=='on'?DB_TRUE:DB_FALSE;
        $dados->notifpostagemmural = $args->notifpostagemmural=='on'?DB_TRUE:DB_FALSE;
        $dados->notifconviteatividade = $args->notifconviteatividade=='on'?DB_TRUE:DB_FALSE;
        $dados->notifsolicitacaoreposicao = $args->notifsolicitacaoreposicao=='on'?DB_TRUE:DB_FALSE;
        $dados->notiffinalizacaodisciplina = $args->notiffinalizacaodisciplina=='on'?DB_TRUE:DB_FALSE;
        $dados->numeropostagensmural = $args->numeropostagensmural?$args->numeropostagensmural:'0';
        $dados->personid = $this->personid;
        
        $this->definir($dados);
        
        $filtro = new stdClass();
        $filtro->personid = $dados->personid;
        
        if(count($this->buscar($filtro)))
        {
            $ok = $this->editar();
        }
        else
        {
            $ok = $this->inserir();
        }

        return $ok;
    }

}
    
?>