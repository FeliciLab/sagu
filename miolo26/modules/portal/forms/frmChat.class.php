<?php

$MIOLO->uses('classes/prtCommonForm.class.php', $module);
$MIOLO->uses('classes/prtUsuario.class.php', $module);

class frmChat extends frmMobile
{
    
    private $pessoa;
    private $disciplina;
    
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busPerson = $MIOLO->getBusiness('basic', 'BusPerson');
        
        $remetente = MIOLO::_REQUEST('chatWith');
        $this->pessoa = $busPerson->getPerson($remetente);
        
        $this->disciplina = MIOLO::_REQUEST('groupId');
        
        self::$fazerEventHandler = FALSE;
        
        parent::__construct($this->pessoa->name);
    }
    
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
                
        $fields[] = new MDiv('divResponse');
        
        $this->autoSave = false;
        
        $MIOLO->uses('types/PrtMensagem.class.php', $module);
        $MIOLO->uses('types/PrtAnexo.class.php', $module);
        
        $msgField[] = new MLabel(_M('Mensagem:'));
        $msgField[] = $msg = new MMultiLineField('mensagem', '', '', 40, 2);
        $msg->setWidth('98%');
        $divMsg = new MDiv('', $msgField);
        $divMsg->addStyle('margin', '10px');
        $field[] = $divMsg;
        
        $field[] = $this->fileField('anexo');
        
        $btnEnviar = new MButton('btnEnviar', _M('Enviar'));
        $field[] = MUtil::centralizedDiv(array($btnEnviar));
        
        $fields[] = new MFormContainer('divCamposMensagem', $field);
        
        $mensagem = new PrtMensagem();
        
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
        {
            $conversas = $mensagem->buscarConversas($this->personid, $this->pessoa->personId);
        }
        elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR )
        {
            $conversas = $mensagem->buscarConversas($this->personid, $this->pessoa->personId, $this->disciplina);
        }
        elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
        {
            $conversas = $mensagem->buscarConversas($this->personid, $this->pessoa->personId);
        }
        
        foreach( $conversas as $conversa )
        {
            $fields[] = $this->divMensagem($conversa->remetente, $conversa);
        }
        
        parent::addFields($fields);
    }
    
    public function btnEnviar_click($args)
    {
        if ( strlen($args->mensagem) > 0 )
        {
            $MIOLO = MIOLO::getInstance();
            $module = MIOLO::getCurrentModule();

            $MIOLO->uses('types/PrtMensagem.class.php', $module);
            $MIOLO->uses('types/PrtAnexo.class.php', $module);

            if ( $args->anexo )
            {
                $uploaded = NULL;
                $uploaded = MFileField::uploadFiles($MIOLO->getConf('home.html') . "/files/tmp/");
            }

            $mensagem = new PrtMensagem();
            $mensagem->remetenteid = $this->personid;
            $mensagem->conteudo = $args->mensagem;
            $mensagem->unitid = $this->unitid;

            $mensagemDestinatario = new stdClass();
            $mensagemDestinatario->personid = $this->pessoa->personId;
            $mensagemDestinatario->groupid = $this->disciplina;

            if ( $mensagem->inserir($mensagemDestinatario, $_REQUEST['uploadInfo']) )
            {
                new MMessageSuccess(_M('Mensagem enviada com sucesso!'));
                $this->setResponse(MUtil::centralizedDiv(new MButton('btnVoltar', 'Voltar para mensagens')), 'divCamposMensagem');
            }
            else
            {
                new MMessageError(_M('Erro ao enviar a mensagem.'));
            }
        }
        else
        {
            new MMessageWarning(_M("Preencha o campo 'Mensagem'."));
            $this->setNullResponseDiv();
        }
    }
    
    public function btnVoltar_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->page->redirect($MIOLO->getActionURL($module, 'main:mensagens'));
    }
    
    private function divMensagem($remetente, $mensagem)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        
        $MIOLO->uses('types/PrtMensagem.class.php', $module);
        $MIOLO->uses('types/PrtAnexo.class.php', $module);
        $busFile = $MIOLO->getBusiness('basic', 'BusFile');
        $busPerson = $MIOLO->getBusiness('basic', 'BusPerson');
        
        $prtAnexo = new PrtAnexo();
        
        $person = $busPerson->getPerson($remetente);
        
        $divFoto = prtCommonForm::obterFoto($person->photoId, '64px', '64px');
        //$divFoto = new MDiv('', '<img width=\'64px\' height=\'64px\' src="'.$ui->getImage($module, 'sem_foto.png').'" />');
        //$divFoto->addStyle('width', '64px');
        //$divFoto->addStyle('height', '64px');
        $divFoto->addStyle('float', 'left');

        $data = $mensagem->data;
        $hora = $mensagem->hora;

        $pessoa = new MDiv('',$person->name);
        $pessoa->addStyle('font-size', '18px');
        $pessoa->addStyle('font-weight', 'bold');
        $pessoa->addStyle('margin-bottom', '6px');
        $pessoa->addStyle('margin-left', '80px');
        $pessoa->addStyle('color', 'navy');

        $conteudo = new MDiv('',$mensagem->conteudo);
        $conteudo->addStyle('font-size', '14px');
        $conteudo->addStyle('color', 'gray');
        $conteudo->addStyle('margin-bottom', '20px');
        $conteudo->addStyle('margin-left', '80px');
        
        // Verificar se mensagem tem anexo.
        $anexos = $prtAnexo->obterAnexos($mensagem->mensagemId);
        foreach( $anexos as $anexo )
        {
            $file = $busFile->getFile($anexo->fileId);
            
            $name = basename($file->uploadFileName);
            $link = $MIOLO->getConf('home.url') . "/download.php?filename={$file->absolutePath}&contenttype={$file->contentType}&name={$name}";
            $anexoLinks[] = new MText('lnk_' . $anexo->fileId, '<a href="' . $link . '" target="_blank">' . $anexo->fileName . '</a>');
        }
        
        if ( is_array($anexoLinks) )
        {
            $anexoDiv = new MDiv('', $anexoLinks);
            $anexoDiv->addStyle('font-size', '10px');
            $anexoDiv->addStyle('margin-top', '-16px');
            $anexoDiv->addStyle('margin-bottom', '10px');
            $anexoDiv->addStyle('margin-left', '80px');
        }

        $ultimaMsg = new MDiv('', _M('Última em ' . $data . ' às ' . $hora . '.'));
        $ultimaMsg->addStyle('color', 'red');
        $ultimaMsg->addStyle('font-weight', 'bold');
        $ultimaMsg->addStyle('margin-left', '80px');

        $action = MUtil::getAjaxAction('confirmaExclusaoChat', array('mensagem' => $mensagem->mensagemId, 'remetente' =>$mensagem->remetente, 'destinatario' =>$mensagem->destinatario, 'disciplina' => $mensagem->disciplina));
        $btnExcluir = new MButton('btnExcluirChat', _M('Excluir', $module), $action);
        $divExcluir = new MDiv('divExcluir', $btnExcluir);  

        $div = new MDiv('div_' . $remetente, array($divFoto, $pessoa, $conteudo, $anexoDiv, $ultimaMsg, $divExcluir));
        $div->setWidth('100%');
        $div->setHeight('100%');

        return new MBaseGroup('base_' . $remetente, '', array($div));
    }
    
        public function confirmaExclusaoChat($args)
    {
        $MIOLO = MIOLO::getInstance();        
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        $MIOLO->uses('types/PrtMensagem.class.php', $module);
        
        //obter usuario logado
        $mioloUserName = trim($MIOLO->getLogin()->id);
        $busPerson = new BusinessBasicBusPerson();
        $personData = $busPerson->getPersonByMioloUserName($mioloUserName);
        $personId = $personData->personId;
        
        parse_str($args, $dados);
        $remetenteId = $dados['remetente'];
        $action = MUtil::getAjaxAction('btnExcluirChat_click', $args);
        if ( $remetenteId == $personId )
        {        
            $fields[] = new MPopupConfirm('Deseja remover esta mensagem daqui e de todas as pessoas que receberam?', 'Confirmação!', $action);
        }
            else
            {
                $fields[] = new MPopupConfirm('Deseja remover esta mensagem?', 'Confirmação!', $action);
            }
        $this->setResponse($fields, 'divResponse');
    }
    
     public function btnExcluirChat_click($args)
    { 
        parse_str($args, $dados);
        
        $MIOLO = MIOLO::getInstance();        
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        $MIOLO->uses('types/PrtMensagem.class.php', $module);        
        
        //obter usuario logado
        $mioloUserName = trim($MIOLO->getLogin()->id);
        $busPerson = new BusinessBasicBusPerson();
        $personData = $busPerson->getPersonByMioloUserName($mioloUserName);
        $personId = $personData->personId;        

        $mensagemid = $dados['mensagem'];
        $remetenteId = $dados['remetente'];
        $destinatarioId = $dados['destinatario'];
        $disciplina = $dados['disciplina'];        
        $prtMensagem = new PrtMensagem();       
        
        $enviada = FALSE;
        
        if ( $remetenteId == $personId )
            {
                $prtMensagem->setMensagemExcluidaSeEnviou($mensagemid);
                $enviada = TRUE;
            }
            else
                {
                    $prtMensagem->setMensagemExcluidaSeRecebeu($mensagemid, $destinatarioId);
                    $enviada = TRUE;
                }
            if ( $enviada)
            {
                $this->setResponse($fields, 'divResponse');
                $this->page->redirect($MIOLO->getActionURL($module, 'main:mensagens', NULL, array('chatWith' => $remetenteId, 'groupId' => $disciplina)));
                $fields[] = new MMessageSuccess('Mensagem removida com sucesso!');
            }
        
          
    }
    
}

?>
