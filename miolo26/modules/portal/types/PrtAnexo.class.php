<?php

class PrtAnexo extends bTipo
{
    
    public $fileid;
    public $mensagemid;
    public $mensagemmuralid;
    public $descricao;
    
    public function __construct($fileid=null)
    {
        parent::__construct('prtanexo');
        
        if($mensagemid)
        {
            $his->fileid = $fileid;
            $this->popular();
        }
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
        $filtro->fileid = $this->fileid;
        $data = $this->buscar($filtro);
        
        if($data)
        {
            $this->definir($data[0]);
        }
    }


    public function salvar($args)
    {        
        $this->definir($args);

        $ok = $this->inserir();

        return $ok;
    }
    
    public function obterAnexos($mensagemId)
    {
        $sql = new MSQL();
        $sql->setColumns('F.fileid, F.uploadfilename, F.filepath, F.contenttype, A.descricao');
        $sql->setTables('prtanexo A LEFT JOIN basfile F ON (A.fileid = F.fileid)');
        $sql->setWhere('mensagemid = ?');
        $sql->addParameter($mensagemId);

        $resultados = bBaseDeDados::consultar($sql);
        
        $anexos = array();
        foreach( $resultados as $key => $resultado )
        {
            $anexos[$key] = new stdClass();
            $anexos[$key]->fileId = $resultado[0];
            $anexos[$key]->fileName = $resultado[4] ? $resultado[4] : basename($resultado[1]);
            $anexos[$key]->path = $resultado[2];
            $anexos[$key]->type = $resultado[3];
        }
        
        return $anexos;
    }
    
}
    
?>