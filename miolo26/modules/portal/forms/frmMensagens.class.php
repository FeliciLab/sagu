<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/09/11
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */
$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/prtCommonForm.class.php', $module);
$MIOLO->uses('classes/prtUsuario.class.php', $module);

class frmMensagens extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Mensagens', MIOLO::getCurrentModule()));
    }

    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $fields[] = new MDiv('divCentral', $this->principal());
        $fields[] = new MDiv('divResponse');
        
        $this->autoSave = false;
        
	parent::addFields($fields);
    }
    
    public function principal()
    {
        $fields[] = $this->button('btNovaMensagem', _M('Nova mensagem'), null, MUtil::getAjaxAction('novaMensagem'));
        $fields[] = $this->mensagens();
        
        return $fields;
    }
    
    public function conversa()
    {
        $fields[] = $this->button('btvoltarMensagen', _M('Voltar para as mensagens'), null, MUtil::getAjaxAction('voltarMensagem'));
        $fields[] = $this->mensagem();
        $fields[] = $this->mensagens();
        
        $this->setResponse($fields, 'divCentral');
    }
    
    public function mensagens()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        
        $MIOLO->uses('types/PrtMensagem.class.php', $module);
        $MIOLO->uses('types/PrtAnexo.class.php', $module);
        $busFile = $MIOLO->getBusiness('basic', 'BusFile');
        $busPerson = $MIOLO->getBusiness('basic', 'BusPerson');
        
        $mensagem = new PrtMensagem();
        
        // Obter conversas
        $remetentes = $mensagem->obterMensagens($this->personid);
        
        foreach ( $remetentes as $remente )
        {
            if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR && strlen(MIOLO::_REQUEST('groupid')) > 0 )
            {
                if ( $remente->disciplina != MIOLO::_REQUEST('groupid') )
                {
                    continue;
                }
            }
            else if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
            {
                if ( !$mensagem->verificaCoordenadorMensagens($this->personid, $remente->remetente) )
                {
                    if ( !$remente->disciplina )
                    {
                        continue;
                    }
                }
            }
            
            $destinatarios = $mensagem->obterDestinatarios($remente->mensagemId);
            
            if ( $remente->enviada == DB_TRUE )
            {
                $mensagensEnviadas[] = $this->divMensagem($remente->remetente, $remente, $destinatarios);
            }
            else if ( $remente->enviada == DB_FALSE )
            {
                $mensagensRecebidas[] = $this->divMensagem($remente->remetente, $remente, $destinatarios);
            }
        }
        
        $fields[] = new jCollapsibleSection(_M('MENSAGENS RECEBIDAS'), $mensagensRecebidas, false, 'mensagensRecebidas');
        $fields[] = new jCollapsibleSection(_M('MENSAGENS ENVIADAS'), $mensagensEnviadas, false, 'mensagensEnviadas');
         
        return $fields;
    }
    
    private function divMensagem($remetente, $mensagem, $destinatario)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        
        $MIOLO->uses('types/PrtMensagem.class.php', $module);
        $MIOLO->uses('types/PrtAnexo.class.php', $module);
        $busFile = $MIOLO->getBusiness('basic', 'BusFile');
        $busPerson = $MIOLO->getBusiness('basic', 'BusPerson');
        $prtAnexo = new PrtAnexo();
                
        $remetente = $mensagem->remetente;
        $person = $busPerson->getPerson($remetente);
        
        $divFoto = prtCommonForm::obterFoto($person->photoId, '64px', '64px');
        //$divFoto = new MDiv('', '<img width=\'64px\' height=\'64px\' src="'.$ui->getImage($module, 'sem_foto.png').'" />');
        //$divFoto->addStyle('width', '64px');
        //$divFoto->addStyle('height', '64px');
        $divFoto->addStyle('float', 'left');

        $data = $mensagem->data;
        $hora = $mensagem->hora;
        
        $tipoMensagem = ($mensagem->enviada == DB_TRUE) ? '[ENVIADA] ' : ($mensagem->enviada == DB_FALSE ? '[RECEBIDA] ' : '');

        $pessoa = new MDiv('', $tipoMensagem . $person->name);
        $pessoa->addStyle('font-size', '18px');
        $pessoa->addStyle('font-weight', 'bold');
        $pessoa->addStyle('margin-bottom', '6px');
        $pessoa->addStyle('margin-left', '80px');
        $pessoa->addStyle('color', 'navy');
        
        $destinatarios = new MDiv('', $destinatario);
        $destinatarios->addStyle('color', 'silver');

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

        $action = MUtil::getAjaxAction('confirmaExclusao', array('mensagem' => $mensagem->mensagemId, 'remetente' =>$mensagem->remetente, 'destinatario' =>$mensagem->destinatario, 'disciplina' => $mensagem->disciplina));
        $btnExcluir = new MButton('btnExcluir', _M('Excluir', $module), $action);
        $divExcluir = new MDiv('divExcluir', $btnExcluir);        
        
        $div = new MDiv('div_' . $remetente, array($divFoto, $pessoa, $destinatarios, $conteudo, $anexoDiv, $ultimaMsg, $divExcluir));
        
        $divFoto->addAttribute('onclick', MUtil::getAjaxAction('abrirChat', "$remetente|{$mensagem->disciplina}"));
        $pessoa->addAttribute('onclick', MUtil::getAjaxAction('abrirChat', "$remetente|{$mensagem->disciplina}"));
        $conteudo->addAttribute('onclick', MUtil::getAjaxAction('abrirChat', "$remetente|{$mensagem->disciplina}"));        
        if ( is_array($anexoLinks) )
        {
        $anexoDiv->addAttribute('onclick', MUtil::getAjaxAction('abrirChat', "$remetente|{$mensagem->disciplina}"));
        }
        $ultimaMsg->addAttribute('onclick', MUtil::getAjaxAction('abrirChat', "$remetente|{$mensagem->disciplina}"));
        
        $div->setWidth('100%');
        $div->setHeight('100%');
        $div->addStyle('cursor', 'pointer');        

        return new MBaseGroup('base_' . $remetente, '', array($div));
    }
    
    public function confirmaExclusao($args)
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
        $action = MUtil::getAjaxAction('btnExcluir_click', $args);        
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
    
    public function abrirChat($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $args = explode('|', $args);
        $remetente = $args[0];
        $disciplina = $args[1];
        
        $this->page->redirect($MIOLO->getActionURL($module, 'main:mensagens', NULL, array('chatWith' => $remetente, 'groupId' => $disciplina)));
    }

    public function novaMensagem()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->page->redirect($MIOLO->getActionURL($module, 'main:mensagens', NULL, array('newMessage' => 1, 'groupid' => MIOLO::_REQUEST('groupid'))));
    }
    
    public function btnExcluir_click($args)
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
            $this->page->redirect($MIOLO->getActionURL($module, 'main:mensagens', NULL, null));
            $fields[] = new MMessageSuccess('Mensagem removida com sucesso!');  
        }        
    }    
}
//array('mensagens' => $remetenteId, 'groupId' => $disciplina)
?>
