<?php

class PrtMensagemDestinatario extends bTipo
{
    public $mensagemdestinatarioid;
    public $groupid;
    public $mensagemid;
    public $personid;
    public $courseid;
    
    public function __construct($mensagemid=null)
    {
        parent::__construct('PrtMensagemDestinatario');
        
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
    
    public function inserir()
    {
        $sql = new MSQL();
        $sql->setTables($this->tabela);
        $sql->setColumns('mensagemid, personid, groupid, courseid');
        
        $parametros[] = $this->mensagemid;
        $parametros[] = $this->personid;
        $parametros[] = $this->groupid ? $this->groupid : '';
        $parametros[] = $this->courseid ? $this->courseid : '';
        
        return bBaseDeDados::inserir($sql, $parametros);
    }
    
    public function popular()
    {
        $filtro = new stdClass();
        $filtro->mensagemdestinatarioid = $this->mensagemdestinatarioid;
        $data = $this->buscar($filtro);
        
        if($data)
        {
            $this->definir($data[0]);
        }
    }


    public function salvar($args)
    {
        $busPerson = new BusinessBasicBusPhysicalPerson();
        $busFile = new BusinessBasicBusFile();
        $person = $busPerson->getPhysicalPerson($args->personid);
     
        $this->definir($args);
        $ok = $this->inserir();

        if ( $ok )
        {
            $busCompany = new BusinessBasicBusCompany();
            $dataCompany = $busCompany->getCompany(SAGU::getParameter('BASIC', 'DEFAULT_COMPANY_CONF'));
            
            $mensagem = new PrtMensagem($args->mensagemid);
            $remetente = $busPerson->getPhysicalPerson($mensagem->remetenteid);

            // Parameters
            $fromName = $dataCompany->acronym;
            $recipient[$person->name] = strtolower($person->email);
            
            $subject = _M('Você possui uma nova mensagem em seu ambiente web');
            $body = 'Remetente: ' . $remetente->name . '<br>' . 'Mensagem: ' . $mensagem->conteudo;
            
            $mail = new sendEmail($from, $fromName, $recipient, $subject, $body, array());
            $mail->setCharSet('UTF-8');

            //Adicionar os anexos
            $anexos = PrtAnexo::obterAnexos($args->mensagemid);
            
            foreach ( $anexos as $anexo )
            {
                $filePath = $busFile->getFilePath($anexo->fileId);
                $mail->AddAttachment($filePath, $anexo->fileName);
            }
            
            $okEnvio = $mail->sendEmail();
        }

        if ( $okEnvio && $ok )
        {
            $return = true;
        }
        elseif ( $ok )
        {
            $return = $mail->getErrors();
        }
        else
        {
            $return = false;
        }
        
        return $return;
    }

}
    
?>