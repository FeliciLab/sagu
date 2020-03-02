<?php

class PrtPreferenciaAluno extends bTipo
{

    public $personid;
    public $username;
    
    public function __construct($personid, $username)
    {
        parent::__construct('PrtPreferenciaAluno');
        
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
        $dados->notifregistrofrequencia = $args->notifregistrofrequencia=='on'?DB_TRUE:DB_FALSE;
        $dados->notifregistronota = $args->notifregistronota=='on'?DB_TRUE:DB_FALSE;
        $dados->notiffinalizacaodisciplina = $args->notiffinalizacaodisciplina=='on'?DB_TRUE:DB_FALSE;
        $dados->numeropostagensmural = $args->numeropostagensmural;
        
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
    
    public function obterNumeroDePostagens()
    {
        $sql = new MSQL();
        $sql->setTables($this->tabela);
        $sql->setColumns('numeropostagensmural');
        $sql->setWhere('personid = ?');
        $sql->addParameter($this->personid);
        
        $result = bBaseDeDados::consultar($sql);
        
        return $result[0][0];
    }

}
    
?>