<?php

$MIOLO->uses('db/BusContract.class', 'academic');

class PrtUsuarioSagu extends bTipo
{

    public $personid;
    
    public function __construct($personid)
    {
        parent::__construct('user_sagu');
        $this->personid = $personid;
    }
    
    public function obterPessoa($personid)
    {
        $msql = new MSQL();
        $msql->setTables('user_sagu');

        $msql->setColumns('login, personid, name, mail, city, isstudent, isprofessor, isemployee, iscoordinator, cpf, isactive');

        $msql->setWhere("personid = $personid");

        $resultado = bBaseDeDados::consultar($msql);

        $pessoa = new stdClass();
        list(   $pessoa->login,
                $pessoa->personid,
                $pessoa->name,
                $pessoa->mail,
                $pessoa->city,
                $pessoa->isstudent,
                $pessoa->isprofessor,
                $pessoa->isemployee,
                $pessoa->isemployee,
                $pessoa->iscoordinator,
                $pessoa->cpf,
                $pessoa->isactive
                ) = current($resultado);
        
        return $pessoa;
    }
    
    public static function obterContratosDaPessoa($personId)
    {
        $busContract = new BusinessAcademicBusContract();

        return $busContract->listContracts($personId, true);
    }
    
    public static function obterContratosAtivosDaPessoa($personId)
    {
        $busContract = new BusinessAcademicBusContract();
        
        $filtros = new stdClass();
        $filtros->personId = $personId;
        
        $contratos = array();
        foreach ( $busContract->getActiveContract($filtros) as $key => $contrato )
        {
            if ( !in_array($contrato[0], $contratos) )
            {
                $contratos[] = $contrato[0];
            }
        }
        
        return $contratos;
    }
    
    public static function obterInscricoesAtivasDaPessoa($personId)
    {
        $type = new AcpInscricao();
        $sql = $type->msql();
        $sql->addInnerJoin('AcpInscricaoTurmaGrupo', 'AcpInscricao.inscricaoid = AcpInscricaoTurmaGrupo.inscricaoid');
        $sql->addEqualCondition('AcpInscricao.personid', $personId);
        $sql->addNotEqualCondition('AcpInscricao.situacao', AcpInscricao::SITUACAO_CANCELADO);
        $sql->setOrderBy('AcpInscricao.inscricaoid DESC');
        
        $inscricoes = array();
        foreach( $type->findMany($sql) as $cod => $inscricao )
        {
            $inscricoes[] = $inscricao->inscricaoid;
        }

        return $inscricoes;
    }
    
    public static function obterInscricoesAtivasDaPessoaTurmaGrupo($personId, $evitarDuplicados = false)
    {
        $sql = new MSQL();
        $sql->setColumns('ITG.inscricaoid,
                          OT.descricao AS ofertaturma');
        $sql->setTables('AcpInscricaoTurmaGrupo ITG');
        $sql->addInnerJoin('AcpInscricao I', 'I.inscricaoid = ITG.inscricaoid');
        $sql->addInnerJoin('AcpOfertaTurma OT', 'OT.ofertaturmaid = ITG.ofertaTurmaId');
        $sql->addEqualCondition('I.personid', $personId);
        $sql->addNotEqualCondition('I.situacao', AcpInscricao::SITUACAO_CANCELADO);
        $sql->setOrderBy('I.inscricaoid DESC');
                
        $inscricoes = array();

        foreach( SDatabase::queryAssociative($sql) as $cod => $row )
        {
            if ( $evitarDuplicados && in_array($row['ofertaturma'], $inscricoes) )
            {
                continue;
            }

            $inscricoes[ $row['inscricaoid'] ] = $row['ofertaturma'];
        }
        
        return $inscricoes;
    }
    
    public static function obterContrato($contractId)
    {
        $busContract = new BusinessAcademicBusContract();
        
        return $busContract->getContract($contractId);
    }
    
    public function verificaProfessor()
    {        
        $pessoa = $this->obterPessoa($this->personid);
        
        if($pessoa->isprofessor==DB_TRUE)
        {
            return true;
        }
        
        return false;
    }
    
    public function verificaAluno()
    {        
        $pessoa = $this->obterPessoa($this->personid);
        
        if($pessoa->isstudent==DB_TRUE)
        {
            return true;
        }
        
        return false;
    }
    
    public function verificaCoordenador()
    {
        $pessoa = $this->obterPessoa($this->personid);
        
        if($pessoa->iscoordinator==DB_TRUE)
        {
            return true;
        }
        
        return false;
    }

}
    
?>