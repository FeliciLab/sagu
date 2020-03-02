<?php

class PrtPreferenciasCoordenador extends bTipo
{

    public $personid;
    public $username;
    
    public function __construct($personid, $username)
    {
        parent::__construct('prtpreferenciascoordenador');
        
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
        $dados->notificacaonovamensagem = $args->notificacaonovamensagem;
        $dados->notificacaoconviteatividade = $args->notificacaoconviteatividade;
        $dados->notificacaosolicitacaoreposicao = $args->notificacaosolicitacaoreposicao;
        $dados->personid = $this->personid;
        
        $this->definir($dados);
        
        $filtro = new stdClass();
        $filtro->personid = $dados->personid;
        
        if( count($this->buscar($filtro)) )
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