<?php

$MIOLO->uses('types/PrtMensagemDestinatario.class.php', 'portal');
$MIOLO->uses('types/PrtAnexo.class.php', 'portal');
$MIOLO->uses('classes/prtDisciplinas.class.php', 'portal');

class PrtMensagem extends bTipo
{
    public $mensagemid;
    public $personid;
    public $username;
    public $unitid;
    public $remetenteid;
    public $conteudo;
    public $removida;
    
    public function __construct($mensagemid=null)
    {
        parent::__construct('PrtMensagem');
        
        if($mensagemid)
        {
            $this->mensagemid = $mensagemid;
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
    
    /**
     * Obtém todas as mensagens de uma pessoa -enviadas ou recebidas
     * 
     * @param type $personId
     */
    public function obterMensagens($personId)
    {
        $sql = " SELECT A.mensagemid, 
                        A.remetenteid as remetente, 
                        A.conteudo, 
                        MAX(B.personid) as destinatario, 
                        A.date::date as data, 
                        to_char(date, 'HH24:MI') as hora, 
                        B.groupid,
                        't' as enviada,
                        COUNT(B.groupid) > 1 as grupoAlunos
                   FROM prtmensagem A
             INNER JOIN prtmensagemdestinatario B
                     ON B.mensagemid = A.mensagemid
                  WHERE A.remetenteid = {$personId}
                    AND A.removida IS FALSE
               GROUP BY B.groupId, A.mensagemid
                 UNION 
                 SELECT A.mensagemid, 
                        A.remetenteid as remetente, 
                        A.conteudo, 
                        B.personid as destinatario, 
                        A.date::date as data, 
                        to_char(date, 'HH24:MI') as hora, 
                        B.groupid,
                        'f' as enviada,
                        false as grupoAlunos
                   FROM prtmensagem A
             INNER JOIN prtmensagemdestinatario B
                     ON B.mensagemid = A.mensagemid
                  WHERE B.personid = {$personId} 
                    AND A.removida IS FALSE ";
           
        $msql = new MSQL();
        $msql->createFrom($sql);
        $msql->setOrderBy('A.date DESC');
        
        $resultado = bBaseDeDados::consultar($msql);
        
        $conversas = array();
        foreach( $resultado as $key => $conversa )
        {
            $conversas[$key] = new stdClass();
            $conversas[$key]->mensagemId = $conversa[0];
            $conversas[$key]->remetente = $conversa[1];
            $conversas[$key]->conteudo = $conversa[2];
            $conversas[$key]->destinatario = $conversa[3];
            $conversas[$key]->data = $conversa[4];
            $conversas[$key]->hora = $conversa[5];
            $conversas[$key]->disciplina = $conversa[6];
            $conversas[$key]->enviada = $conversa[7];
            $conversas[$key]->grupoAlunos = $conversa[8];
        }
        
        return $conversas;
    }
    
    /**
     * Obtém os destinatários de uma mensagem
     * 
     * @param type $mensagemId
     */
    public function obterDestinatarios($mensagemId)
    {
        $sql = new MSQL();
        $sql->setColumns('getPersonName(D.personid)');
        $sql->setTables('prtmensagem M INNER JOIN prtmensagemdestinatario D ON (M.mensagemid = D.mensagemid)');
        $sql->setWhere("M.mensagemId = {$mensagemId}");
        $sql->setOrderBy('getPersonName(D.personid)');
        
        $resultado = bBaseDeDados::consultar($sql);
        
        foreach ( $resultado as $destinatario )
        {
            $destinatarios[] = $destinatario[0];
        }
        
        return implode(', ', $destinatarios);
    }
    
    public function obterRemetentes($personId)
    {
        $prtDisciplinas = new PrtDisciplinas();
        $disciplinas = implode(',', $prtDisciplinas->obterDisciplinasMatriculadas($personId));
        
        $sql = new MSQL('M.remetenteid');
        $sql->setTables('prtmensagem M LEFT JOIN prtmensagemdestinatario D ON (M.mensagemid = D.mensagemid)');
        $sql->setWhere("D.personid = {$personId} AND M.remetenteid <> {$personId}");
        $sql->setOrderBy('M.date DESC');
        
        $resultado = bBaseDeDados::consultar($sql);
        
        $remetentes = array();
        foreach ( $resultado as $remetente )
        {
            if ( !in_array($remetente[0], $remetentes) )
            {
                $remetentes[] = $remetente[0];
            }
        }
        
        return $remetentes;
    }
    
    /**
     * Extraído lógica da função buscarConversas para utilizar no formulário
     * 
     * @param type $personId
     * @param type $remetenteId
     * @return boolean
     */
    public function verificaCoordenadorMensagens($personId, $remetenteId)
    {
        $busProfessor = new BusinessBasicBusPhysicalPersonProfessor();
        $busCourseCoordinator = new BusinessAcademicBusCourseCoordinator();
        
        $cursosDoCoordenador = $busCourseCoordinator->obterCursosDoCoordenador($personId);
        $isCoordenador = count($cursosDoCoordenador) > 0;

        if ( !$busProfessor->isProfessor($remetenteId) && !$isCoordenador )
        {
            return false;
        }
        
        return true;
    }
    
    public function buscarConversas($personId, $remetenteId, $groupId = NULL, $limit = NULL)
    {
        $busProfessor = new BusinessBasicBusPhysicalPersonProfessor();
        $busCourseCoordinator = new BusinessAcademicBusCourseCoordinator();
        
        $sql = new MSQL('M.mensagemid, M.remetenteid as remetente, M.conteudo, D.personid as destinatario, M.date::date as data, to_char(date, \'HH24:MI\') as hora, D.groupid');
        $sql->setTables('prtmensagem M LEFT JOIN prtmensagemdestinatario D ON (M.mensagemid = D.mensagemid)');
        $sql->setWhere("((M.remetenteid = {$personId} AND D.personid = {$remetenteId})");        
        $sql->setWhereOr("(M.remetenteid = {$remetenteId} AND D.personid = {$personId}))");
        $sql->setWhere("(M.removida IS false AND (CASE WHEN d.personId = {$personId} 
                                                       THEN d.removida IS FALSE
                                                       ELSE TRUE END))");        
        
        if ( !$this->verificaCoordenadorMensagens($personId, $remetenteId) )
        {
            $sql->setWhere('D.groupid IS NOT NULL');
        }
        
        if ( $groupId )
        {
            $sql->setWhere('D.groupid = ?');
            $sql->addParameter($groupId);
        }
        
        $sql->setOrderBy('M.date DESC');
        
        if ( $limit )
        {
            $sql->setLimit($limit);
        }
        
        $resultado = bBaseDeDados::consultar($sql);
        
        $conversas = array();
        foreach( $resultado as $key => $conversa )
        {
            $conversas[$key] = new stdClass();
            $conversas[$key]->mensagemId = $conversa[0];
            $conversas[$key]->remetente = $conversa[1];
            $conversas[$key]->conteudo = $conversa[2];
            $conversas[$key]->destinatario = $conversa[3];
            $conversas[$key]->data = $conversa[4];
            $conversas[$key]->hora = $conversa[5];
            $conversas[$key]->disciplina = $conversa[6];
        }

        return $conversas;
    }
    
    /*public function buscarMensagensDaPessoa($personid)
    {   
        $sql = "SELECT * FROM prtMensagem A
                    INNER JOIN prtmensagemdestinatario B ON (A.mensagemid=B.mensagemid)
                    LEFT JOIN acdcontract C ON (B.mensagemdestinatarioid = C.personid)
                    LEFT JOIN acdenroll D ON (D.contractid=C.contractid)
                    LEFT JOIN acdgroup E ON (E.groupid=D.groupid)
                    WHERE B.personid = {$personid} OR (B.personid=NULL AND B.groupid=E.groupid) OR A.remetenteid = {$personid}
                    ORDER BY A.date DESC";
        
        $resultado = bBaseDeDados::obterInstancia()->_db->query($sql, NULL, NULL, PostgresQuery::FETCH_OBJ);
        
        return $resultado;
    }*/
    
    public function popular()
    {
        $filtro = new stdClass();
        $filtro->mensagemid = $this->mensagemid;
        $data = $this->buscar($filtro);
        
        if($data)
        {
            $this->definir($data[0]);
        }
    }
    
    public function setMensagemExcluidaSeRecebeu($mensagemid, $destinatarioId)
    {
        if ( $mensagemid)
        {
            $sql = "UPDATE prtMensagemDestinatario
                       SET removida = true 
                     WHERE mensagemid = ?
                       AND personId = ?";
            $mensagemid = SDAtabase::execute($sql, array($mensagemid, $destinatarioId));
        }
    }
    
        public function setMensagemExcluidaSeEnviou($mensagemid)
    {
            if ( $mensagemid)
        {
            $sql = "UPDATE prtMensagem SET removida = true WHERE mensagemid = ?";
            $ok = SDAtabase::execute($sql, array($mensagemid));
            if ( $ok)
            {
                $sql = "UPDATE prtMensagemDestinatario SET removida = true WHERE mensagemid = ?";
                $ok = SDAtabase::execute($sql, array($mensagemid));
            }
        }
    }

    public function inserir($mensagemDestinatario, $anexo)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $ok = true;
        $sql = " INSERT INTO prtmensagem (unitid, remetenteid, conteudo)
                 VALUES ({$this->unitid}, {$this->remetenteid}, $$$this->conteudo$$) 
                 RETURNING mensagemid ";
        
        bBaseDeDados::iniciarTransacao();
        
        $mensagemId = bBaseDeDados::obterInstancia()->_db->query($sql);
        $mensagemId = $mensagemId[0][0];
        if ( $mensagemId )
        {
            $ok = $mensagemId;            
            if ( $mensagemDestinatario->personid )
            {
                $mensagemDestinatario->mensagemid = $mensagemId;
                $objMensagemDestinatario = new PrtMensagemDestinatario();
                if ( !$objMensagemDestinatario->salvar($mensagemDestinatario) )
                {
                    $ok = false;
                }
            }
            
            if ( $ok )
            {
                if ( strlen($anexo) > 0 )
                {
                    $busFile = $MIOLO->getBusiness('basic', 'BusFile');
                    
                    $files = explode(',', $anexo);
                    foreach ( $files as $file )
                    {
                        $fileInfo = explode(';', $file);
                        $filePath = $MIOLO->getConf('home.html') . "/files/tmp/" . $fileInfo[0];

                        if( file_exists($filePath) )
                        {
                            $fdata = new stdClass();            
                            $fdata->uploadFileName = $filePath;
                            $fdata->contentType = mime_content_type($filePath);
                            //$fdata->filePath = pathinfo($filePath, PATHINFO_DIRNAME);
                            $fileId = $busFile->insertFile($fdata, $filePath);

                            $objAnexo = new PrtAnexo();
                            $anexoData = new stdClass();
                            $anexoData->fileid = $fileId;
                            $anexoData->mensagemid = $mensagemId;
                            $objAnexo->salvar($anexoData);
                        }
                    }
                }
            }
        }
        else
        {
            $ok = false;
        }
        
        if ( !$ok )
        {
            bBaseDeDados::reverterTransacao();
        }
        else
        {
            bBaseDeDados::finalizarTransacao();
        }
        
        return $ok;
    }

    public function salvar($args)
    {        
        $this->definir($args);
        
        $ok = $this->inserir();
        
        $busca = $this->buscar($args);
        
        $this->mensagemid = $busca[0]->mensagemid;

        return $ok;
    }

}
    
?>