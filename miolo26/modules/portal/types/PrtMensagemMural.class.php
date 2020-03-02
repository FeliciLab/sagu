<?php

$MIOLO->uses('types/PrtPreferenciaAluno.class.php', $module);

class PrtMensagemMural extends bTipo
{
    public $mensagemmuralid;
    public $personid;
    public $unitid;
    public $groupid;
    public $conteudo;
    
    public $fileid;
    
    public function __construct($mensagemmuralid=null)
    {
        parent::__construct('prtmensagemmural');
        
        if( $mensagemmuralid )
        {
            $this->mensagemmuralid = $mensagemmuralid;
            $this->popular();
        }
    }
    
    public function inserir()
    {
        $sql = new MSQL();
        $sql->setTables($this->tabela);
        $sql->setColumns('personid, unitid, groupid, conteudo, fileid');
        
        $parametros[] = $this->personid;
        $parametros[] = $this->unitid;
        $parametros[] = $this->groupid;
        $parametros[] = $this->conteudo;
        $parametros[] = $this->fileid;
        
        return bBaseDeDados::inserir($sql, $parametros);
    }
    
    public function obterMensagens()
    {
        $sql = new MSQL();
        $sql->setTables($this->tabela);
        $sql->setColumns('datetime::date, conteudo, personid, fileid, mensagemmuralid');
        $sql->setWhere('unitid = ?');
        $sql->setWhere('personid = ?');
        $sql->setWhere('groupid = ?');
        $sql->setWhere('removida IS FALSE');
        $sql->setOrderBy('datetime desc');
        
        $parametros[] = $this->unitid;
        $parametros[] = $this->personid;
        $parametros[] = $this->groupid;

        return bBaseDeDados::consultar($sql, $parametros);
    }
    
        public function setMensagemExcluidaMural($msgmuralid)
    {
        if ( $msgmuralid)
        {
            $sql = "UPDATE prtMensagemMural 
                       SET removida = true 
                     WHERE mensagemMuralId = ?";
            $ok = SDAtabase::execute($sql, array($msgmuralid));
        }
    }
    
    public function obterMuralDoAluno()
    {
        $preferenciaAluno = new PrtPreferenciaAluno($this->personid);
        $numeroPostagens = $preferenciaAluno->obterNumeroDePostagens();
        
        $sql = new MSQL();
        $sql->setTables($this->tabela);
        $sql->setColumns('datetime::date as data, conteudo, personid, fileid, groupid');
        $sql->setWhere('removida IS FALSE');
        $sql->setWhere('unitid = ?');
        $sql->setWhere('groupid IN (SELECT groupid 
            FROM acdenroll E 
            INNER JOIN acdcontract C ON (E.contractid = C.contractid) 
            WHERE C.personid = ?)');
        $sql->setOrderBy('datetime DESC');
        $sql->setLimit($numeroPostagens);

        $parametros[] = $this->unitid;
        $parametros[] = $this->personid;

        return bBaseDeDados::consultar($sql, $parametros);
    }

}
    
?>
