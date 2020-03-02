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
$MIOLO->uses('types/PrtMensagemMural.class.php', $module);
$MIOLO->uses('types/PrtUsuarioSagu.class.php', $module);
$MIOLO->uses('classes/prtUsuario.class.php', $module);
class frmPostagensProfessor extends frmMobile
{
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Postagens', MIOLO::getCurrentModule()));

        $this->setShowPostButton(FALSE);
    }

    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $busLearningPeriod = $MIOLO->getBusiness('academic', 'BusLearningPeriod');
        $busSchedule = $MIOLO->getBusiness('academic', 'BusSchedule');
        $busPhysicalPerson = $MIOLO->getBusiness('basic', 'BusPhysicalPerson');
        
        $groupId = MIOLO::_REQUEST('groupid');
        $groupData = $busGroup->getGroup($groupId);
        
        $user = $MIOLO->getLogin();
        $filters->mioloUserName = $user->id;
        $professor = $busPhysicalPerson->searchPhysicalPerson($filters);
        
        //Obtém todos os professores da oferecida
        $professores = $busSchedule->getGroupProfessors($groupId);
        foreach( $professores as $personId => $prof )
        {
            $professoresDaOferecida[] = $personId;
        }

        //Verifica se o professor logado é professor na disciplina oferecida
        if( !in_array($professor[0][0], $professoresDaOferecida) && !(prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR) )
        {
            //Bloqueia o acesso, pois o professor não é professor da disciplina oferecida
            $MIOLO->error(_M('Apenas professores da disciplina podem ter acesso a esta tela.'));
        }
        
        $fields[] = $this->mensagem();
        
        $fields[] = new MDiv();
        
        $fields[] = $this->fileField('anexo', false);
        
        $btnEnviar = new MButton('btnEnviar', _M('Publicar no mural dos alunos'));
                
        $fields[] = new MDiv();
        $fields[] = MUtil::centralizedDiv($btnEnviar, 'divButton');
        
        $fields[] = new MDiv();
        $fields[] = $this->historico();
        $fields[] = new MDiv('divResponse');
        
        $this->autoSave = false;
        
	parent::addFields($fields);
    }
    
    public function mensagem()
    {
        $label = new MLabel(_M('Mensagem:'));
        $label->addStyle('font-weight', 'bold');
        $label->addStyle('color', 'navy');
        $label->addStyle('margin-top', '8px');
        $label->addStyle('margin-left', '10px');
        
        $msg = new MMultiLineField('mensagem', $value, null, SAGU::getParameter('BASIC', 'FIELD_DESCRIPTION_SIZE'), 4, SAGU::getParameter('BASIC', 'FIELD_DESCRIPTION_SIZE'));
        $msg->setWidth('98%');
        $msg->addStyle('margin-left', '10px');
        $msg->addStyle('margin-right', '10px');
        
        $fields[] = new MDiv();
        $fields[] = $label;
        $fields[] = $msg;
        
        return new MDiv('divMensagem', $fields);
    }
    
    public function historico()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busFile = $MIOLO->getBusiness('basic', 'BusFile');
        
        $mensagemMural = new PrtMensagemMural();
        $mensagemMural->unitid = $this->unitid;
        $mensagemMural->personid = $this->personid;
        $mensagemMural->groupid = MIOLO::_REQUEST('groupid');
        $mensagemMural->mensagemmuralid = $this->mensagemmuralid;

        $mensagensHistorico = $mensagemMural->obterMensagens();

        foreach( $mensagensHistorico as $mensagem )
        {
            $prtPessoa = new PrtUsuarioSagu($mensagem[2]);
            $pessoa = $prtPessoa->obterPessoa($mensagem[2]);

            $label = new MLabel($mensagem[0] . ' por ' . $pessoa->name);
            $label->addStyle('font-size', '12px');
            $label->addStyle('font-weight', 'bold');
            $label->setWidth('100%');
            
            $text = new MText(rand(), $mensagem[1]);
            $text->addStyle('font-size', '16px');
            $text->addStyle('margin-left', '18px');
            $text->setWidth('100%');
            
            $action = MUtil::getAjaxAction('confirmarExclusao', array('msgmuralid'=>$mensagem[4]));
            $btnExcluir = new MButton('btnExcluir', _M('Ecluir', $module), $action);
            $divExcluir = new MDiv('divExcluir', $btnExcluir);   

            if ( $mensagem[3] )
            {
                $file = $busFile->getFile($mensagem[3]);
            
                $name = basename($file->uploadFileName);
                $link = $MIOLO->getConf('home.url') . "/download.php?filename={$file->absolutePath}&contenttype={$file->contentType}&name={$name}";
                $anexoLink = new MText('lnk_' . $mensagem[3], '<a href="' . $link . '" target="_blank">' . $name . '</a>');
                $anexoDiv = new MDiv('', array($anexoLink));
                $anexoDiv->addStyle('font-size', '12px');
                $anexoDiv->addStyle('margin-top', '10px');
                $anexoDiv->addStyle('margin-left', '18px');
            }

            $fields[] = $cont = new MVContainer('dscsdc', array($label, $text, $anexoDiv, $divExcluir));
            $cont->addStyle('padding', '10px');
            $cont->addStyle('border-style', 'solid');
            $cont->addStyle('border-width', '1px');
            $cont->addStyle('border-color', '#CCC');
        }
        
        $div = new MDiv('divHistorico', array(new MBaseGroup('baseHistorico', _M('Histórico'), $fields, 'vertical')));
        
        return $div;
    }
    
    public function confirmarExclusao($args)
    {
        $action = MUtil::getAjaxAction('btnExcluir_click', $args);
        $fields[] = new MPopupConfirm('Deseja remover esta mensagem deste e de todos os murais onde ela foi publicada?', 'Confirmação!', $action);

        $this->setResponse($fields,'divResponse');
    }

    public function btnExcluir_click($args)
    { 
        $MIOLO = MIOLO::getInstance();        
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();     
        $groupid = MIOLO::_REQUEST('groupid');

        parse_str($args, $info);        
        $msgmuralid = $info['msgmuralid'];                
        $prtMensagem = new PrtMensagemMural;
        if ( $msgmuralid )
        {            
            $prtMensagem->setMensagemExcluidaMural($msgmuralid);
        }
        $this->page->redirect($MIOLO->getActionURL($module, 'main:postagensProfessor', NULL, array('groupid' => $groupid)));
    }
    public function btnEnviar_click($args)
    {
        if ( strlen($args->mensagem) > 0 )
        {
            $MIOLO = MIOLO::getInstance();
            $module = MIOLO::getCurrentModule();

            $mensagemMural = new PrtMensagemMural();
            $mensagemMural->unitid = $this->unitid;
            $mensagemMural->personid = $this->personid;
            $mensagemMural->conteudo = $args->mensagem;
            $mensagemMural->groupid = $args->groupid;

            if ( $args->anexo )
            {
                $uploaded = MFileField::uploadFiles($MIOLO->getConf('home.html') . "/files/tmp/");

                $busFile = $MIOLO->getBusiness('basic', 'BusFile');

                $file = $_REQUEST['uploadInfo'];
                $fileInfo = explode(';', $file);
                $filePath = $MIOLO->getConf('home.html') . "/files/tmp/" . $fileInfo[0];

                if( file_exists($filePath) )
                {
                    $fdata = new stdClass();            
                    $fdata->uploadFileName = $filePath;
                    $fdata->contentType = mime_content_type($filePath);
                    $fileId = $busFile->insertFile($fdata, $filePath);

                    $mensagemMural->fileid = $fileId;
                }
            }

            if ( $mensagemMural->inserir() )
            {
                new MMessageSuccess(_M('Mensagem enviada com sucesso para o mural dos alunos.'));
            }
            else
            {
                new MMessageError(_M('Erro ao salvar mensagem no mural dos alunos'));
            }

            $this->setResponse(null, 'divMensagem');
            $this->setResponse(null, 'divUpload');

            $btns[] = new MButton('btnVoltar', _M('Voltar'));
            $btns[] = new MButton('btnNovaPostagem', _M('Nova postagem'));
            $this->setResponse(MUtil::centralizedDiv($btns, 'divButton'), 'divButton');

            $this->setResponse($this->historico(), 'divHistorico');
        }
        else
        {
            new MMessageWarning(_M('Digite uma mensagem para publicar no mural dos alunos.'));
        }
    }
    
    public function btnVoltar_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->page->redirect($MIOLO->getActionURL($module, 'main:disciplinasProfessor', null, array('mostrar' => MIOLO::_REQUEST('groupid'))));
    }
    
    public function btnNovaPostagem_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->page->redirect($MIOLO->getActionURL($module, 'main:postagensProfessor', null, array('groupid' => MIOLO::_REQUEST('groupid'))));
    }

}

?>
