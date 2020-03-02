<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/09/10
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

$MIOLO->uses('types/PrtUsuarioSagu.class.php', $module);

$file =  dirname(__FILE__) . '/../../../../miolo20/classes/model/mcustomfield.class';

if ( file_exists($file) )
{
    require_once $file;
}

$file = dirname(__FILE__) . '/../../../../miolo20/classes/model/mcustomvalue.class';

if ( file_exists($file) )
{
    require_once $file;
}

class frmMobile extends bForm
{
    const CLASS_AUTOSAVE = 'autosavefield';
    
    public $formFields;
    public $personid;
    public $titulo;
    public $unitid;
    public $person;
    
    public $btInicio = true;
    public $btVoltar = true;
    public $btAcessarComo = true;
    public $btSair = true;
    
    public $contratos;
    
    public $inscricoes;

    /**
     * Salvar campos automaticamente.
     *
     * @var boolean
     */
    public $autoSave = false;
    
    /**
     * Array com lista de campos MControl que devem ser salvos automaticamente.
     * O uso deste atributo faz sentido apenas quando $autoSave = false.
     * 
     * @var array
     */
    private $autoSaveFields = array();
    
    /**
     * Campos personalizados
     *
     * @var array
     */
    public $mioloCustomFields = array();
    
    public function __construct($titulo=null)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        //Problemas ao verificar se usuário é ADMIN
        !(strlen(MIOLO::_REQUEST('isAdmin') > 0)) ? $isAdmin = prtUsuario::isAdmin() : $isAdmin = MIOLO::_REQUEST('isAdmin');

        $this->titulo = $titulo;
        
        $this->person = prtUsuario::obtemUsuarioLogado();
        $this->personid = $this->person->personId;
        $this->unitid = sMultiUnidade::obterUnidadeLogada() ? sMultiUnidade::obterUnidadeLogada() : 1;
        
        //Senão for admin (exceto se o admin for um aluno), procura as definições
        if ( $isAdmin != DB_TRUE || prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
        {
            $inativo = !prtUsuario::obterContratoAtivo() && !prtUsuario::obterInscricaoAtiva();
            
            //Obtem inscrições e contratos
            if ( $inativo )
            {
                $this->inscricoes = PrtUsuarioSagu::obterInscricoesAtivasDaPessoa($this->personid);
                $this->contratos = PrtUsuarioSagu::obterContratosDaPessoa($this->personid);            
            }
            
            //Seta definições de contrato
            if ( $inativo )
            {
                if ( count($this->contratos) == 1 )
                {
                    prtUsuario::definirContratoAtivo($this->contratos[0][0]);
                    prtUsuario::definirMultiplosContratos(false);
                }
                else
                {
                    prtUsuario::definirContratoAtivo($this->contratos[0][0]);
                    prtUsuario::definirMultiplosContratos();
                }
            }

            //Seta definições de inscricao
            if( $inativo )
            {
                if( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' )
                {
                    prtUsuario::definirMultiplasInscricoes( count($this->inscricoes) > 1 );

                    if( count($this->inscricoes) >= 1 )
                    {
                        prtUsuario::definirInscricaoAtiva(current(array_values($this->inscricoes)));
                    }
                }
            }
            
            //Se tiver um de cada, seta múltiplos contratos
            if ( count($this->inscricoes) == 1 &&
                 count($this->contratos) == 1)
            {
                prtUsuario::definirMultiplosContratos();
            }
        }
        
        $MIOLO->page->onLoad("urlFileUpload = 'miolo26/html/fileUpload.php';");
        $MIOLO->page->addJsCode('alertarErroAjax = true;');
        
        parent::__construct(null);
        
        $this->addFields($fields);
        
        $this->setShowPostButton(FALSE);
    }
    
    public function getAutoSaveFields()
    {
        return $this->autoSaveFields;
    }

    public function setAutoSaveFields($autoSaveFields = array())
    {
        foreach ( $autoSaveFields as $field )
        {
            $field instanceof MControl;
            $this->addAutoSaveField($field);
        }
    }
    
    public function addAutoSaveField(MControl $field)
    {
        $field->setClass(self::CLASS_AUTOSAVE);
        
        $this->autoSaveFields[] = $field;
    }
    
    public function addFields($formFields)
    {              
        $browser = MUtil::getBrowser();
        if ( $browser != 'Firefox' && $browser != 'Google Chrome' && $browser != 'Android' )
        {
            $alerta = _M('Atenção!<br><br>Este navegador não é homologado para utilização do portal, podendo ocasionar erros durante a execução.<br>
            Por favor utilize o Mozilla Firefox ou o Google Chrome.');
            
            $this->formFields[] = MMessage::getStaticMessage('infoAlerta', $alerta, MMessage::TYPE_INFORMATION);
            foreach ( $formFields as $field )
            {
                $this->formFields[] = $field;
            }
        }
        else
        {
            $this->formFields = $formFields;
        }
    }
    
    public function isAjax()
    {   
        if( MUtil::isFirstAccessToForm() )
        {
            return false;
        }
        
        return true;
    }
    
    public function defineFields(){}
    
    public function miniButton($id = null, $action = null, $icon = null, $label = null)
    {
        $content = '        
        <a title="' . $label . '" href="'.$action.'" data-role="button" style="width: 90px;" onclick="javascript:miolo.doLink(this.href,\'__mainForm\'); event.preventDefault();">
            <img width="50" height="50" src="'.$icon.'"></img>
        </a>';
        
        $bt = new MDiv($id, $content);
        
        return $bt;
    }
    
    public function miniPanel()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        
        $groupid = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');  
        
        $professor = $this->retornaPersonIdPessoaLogada();
        $isProfessorResponsible = $this->verificaProfessorResponsavel($groupid, $professor);
        
        if ( $MIOLO->checkAccess('FrmProgramaProfessor', A_ACCESS, FALSE) )
        {
            $panel[] = $this->miniButton('btnProfessor', $MIOLO->getActionURL($module, 'main:programaProfessor', null, array('groupid'=>$groupid)), $ui->getImageTheme($module, 'programa.png'), _M('Programa'));
        }
        
        if ( $MIOLO->checkAccess('FrmPostagensProfessor', A_ACCESS, FALSE) )
        {
            $panel[] = $this->miniButton('btnPostagens', $MIOLO->getActionURL($module, 'main:postagensProfessor', null, array('groupid'=>$groupid)), $ui->getImageTheme($module, 'postagens.png'), _M('Postagens'));
        }
        
        if ( $MIOLO->checkAccess('FrmMensagensProfessor', A_ACCESS, FALSE) )
        {
            $panel[] = $this->miniButton('btnMensagens', $MIOLO->getActionURL($module, 'main:mensagens', null, array('groupid'=>$groupid)), $ui->getImageTheme($module, 'mail.png'), _M('Mensagens'));
        }
        
        if ( $MIOLO->checkAccess('FrmFrequenciasProfessor', A_ACCESS, FALSE) )
        {
            $panel[] = $this->miniButton('btnFrequencias', $MIOLO->getActionURL($module, 'main:frequenciasProfessor', null, array('groupid'=>$groupid)), $ui->getImageTheme($module, 'livro_presenca.png'), _M('Frequências'));
        }
        
        if ( $MIOLO->checkAccess('FrmNotasProfessor', A_ACCESS, FALSE) )
        {
            if( $isProfessorResponsible == DB_TRUE )
            {
                $panel[] = $this->miniButton('btnNotas', $MIOLO->getActionURL($module, 'main:notasProfessor', null, array('groupid'=>$groupid)), $ui->getImageTheme($module, 'notas.png'), _M('Notas'));
            }
        }
        
        if ( $MIOLO->checkAccess('FrmEstatisticasProfessor', A_ACCESS, FALSE) )
        {
            $panel[] = $this->miniButton('btnEstatisticas', $MIOLO->getActionURL($module, 'main:estatisticaDisciplina', null, array('groupid'=>$groupid)), $ui->getImageTheme($module, 'stats.png'), _M('Estatísticas'));
        }
        
        if ( $MIOLO->checkAccess('FrmResultadoFinalProfessor', A_ACCESS, FALSE) )
        {
            $panel[] = $this->miniButton('btnResultado', $MIOLO->getActionURL($module, 'main:resultadoFinal', null, array('groupid'=>$groupid)), $ui->getImageTheme($module, 'resultado.png'), _M('Resultado final'));
        }
        
        if ( $MIOLO->checkAccess('FrmDocumentosProfessor', A_ACCESS, FALSE) )
        {
            $panel[] = $this->miniButton('btnDocumentos', $MIOLO->getActionURL($module, 'main:documentosProfessor', null, array('groupid'=>$groupid)), $ui->getImageTheme($module, 'docs.png'), _M('Documentos'));
        }
        
        if ( $MIOLO->checkAccess('FrmCadastroAvaliacoes', A_ACCESS, FALSE) )
        {
            $panel[] = $this->miniButton('btnAvaliacoes', $MIOLO->getActionURL($module, 'main:avaliacoes', null, array('groupid' => $groupid)), $ui->getImageTheme($module, 'evaluation.png'), _M('Avaliações'));
        }
        
        $div = new MDiv('divMiniPainel',$panel);
        $div->addStyle('width', '136px');
        $div->addStyle('right', '5px');
        $div->addStyle('position', 'fixed');
        $div->addStyle('top', '45px');
        $div->addStyle('display', 'none');
        $div->addAttribute('data-role', 'button');
        
        $fields[] = $div;
        
        $bt = '<a href="#" onclick="displayMenuPanel();" ><img src="'.$ui->getImageTheme($module, 'setting.png').'" width="25" height="25" ></a>';
        
        $div2 = new MDiv('divMore', $bt);
        
        $div2->addStyle('width', '30px');
        $div2->addStyle('right', '0px');
        $div2->addStyle('position', 'fixed');
        $div2->addStyle('top', '10px');
        
        $fields[] = $div2;
        
        return $fields;
    }
    
    public function topNav()
    {   
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/prtUsuario.class.php', $module);
        
        $groupid = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');
        if( $groupid && prtUsuario::obterTipoDeAcesso() != prtUsuario::USUARIO_ALUNO )
        {
            $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
            $info = $busGroup->getGroup($groupid);
            $curriculumCurricularComponentName = substr($info->curriculumCurricularComponentName, 0, 35);
            $this->titulo .= ' ['.$curriculumCurricularComponentName.']';
            
            $fields[] = $this->miniPanel();
        }
        
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO && prtUsuario::obterContratoAtivo() )
        {
            $contrato = PrtUsuarioSagu::obterContrato(prtUsuario::obterContratoAtivo());
            $this->titulo .= ' [' . $contrato->courseName . ']';
        }
        elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO && prtUsuario::obterInscricaoAtiva() )
        {
            $inscricao = AcpInscricao::obterCursoInfo(prtUsuario::obterInscricaoAtiva());
            $this->titulo .= ' [' . $inscricao[0][0] . ']';
        }
        
        //Caso nao seja aluno nao precisa mostrar curso
        if ( $this->titulo == _M("Curso: ") && prtUsuario::obterTipoDeAcesso() != prtUsuario::USUARIO_ALUNO)
        {
            if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR )
            {
                $this->titulo = _M("Portal do Professor");
            }
            else if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
            {
                $this->titulo = _M("Portal do Coordenador");
            }
            else
            {
                $this->titulo = _M("Portal");
            }
        }
        
	$titulo = new MDiv('divTitulo', $this->titulo, "tituloTopo");
        $fields[] = $titulo;
        
        if(sMultiUnidade::estaHabilitada())
        {
            $unit = sMultiUnidade::obterObjetoUnidade();
            
            $unidade = new MDiv('divUnidade','Unidade: '.substr($unit->description, 0, 30), "tituloTopo");
            $fields[] = $unidade;
        }
        $isAdmin = MIOLO::_REQUEST('isAdmin');
        if( $isAdmin != DB_TRUE )
        {
            if ( strlen($this->personid) == 0 )
            {
                $href = $MIOLO->getActionURL('portal', 'main:logout');
                $goto = new MLink('lnkGo', _M('clique aqui'), $href);
                
                $MIOLO->Alert(_M('Ops! O usuário logado no sistema não é uma pessoa física, portando não pode acessar o Portal. Caso queira se deslogar para entrar com outro usuário, @1.', null, $goto));
            }
            
            $busPhysicalPerson = new BusinessBasicBusPhysicalPerson();
            $person = $busPhysicalPerson->getPhysicalPerson($this->personid);
            $acesso = prtUsuario::listTiposAcessoExtenso(prtUsuario::obterTipoDeAcesso());
            
            //Caso seja administrador, sobrescreve acesso
            if ( prtUsuario::isAdmin() && count($acesso) > 1 )
            {
                $acesso = _M("Administrador");
            }
            
            $usuario = new MDiv('divUsuario', $acesso . ': '.substr($person->name, 0, 30), "tituloTopo");
            $fields[] = $usuario;
        
        }
        $div = new MDiv('divTop', $fields);
        
        if(MUtil::getBrowser()=='Firefox')
        {
            $div->addStyle('background', '-moz-linear-gradient(#268CEB, #1F72BF) repeat scroll 0 0 #1F72BF;');
        }
        else
        {
            $div->addStyle('background', '-webkit-gradient(linear, left top, left bottom,from(#268CEB), to(#1F72BF)) repeat scroll 0 0 #1F72BF;');
        }
        
        $div->addStyle('border', '1px solid #456F9A');
        $div->addStyle('width', '100%');
        $div->addStyle('margin', '0');
        $div->addStyle('padding', '0');
        $div->addStyle('position', 'fixed');
        $div->addStyle('top', '0');
        $div->addStyle('z-index', '110');
        
        return $div;
    }
    
    public function createFields($formFields)
    {
        if( !$this->isAjax() )
        {
            $MIOLO = MIOLO::getInstance();
            $module = MIOLO::getCurrentModule();
            $ui = $MIOLO->getUI();
            $fields = array();

            $fields[] = $this->topNav();
            
            $fields[] = MDialog::getDefaultContainer();
            
            $this->defineFields();

            $fields[] = $divErro = new MDiv('divErroConexao', '','mMessage mMessage Error');
            $divErro->addAttribute('style', 'display:none');
            
            //adiciona o campo para mensagens
            $forms[] = MMessage::getMessageContainer();
            
            if ( MUtil::getBooleanValue(prtUsuario::obterMultiplosContratos()) && !prtUsuario::obterContratoAtivo() && (!prtUsuario::obterInscricaoAtiva() && MIOLO::_REQUEST('isAdmin') != DB_TRUE) )
            {
                if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR || prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR )
                {
                    $forms[] = $this->formFields;
                }
                else
                {
                    $panel = new mobilePanel('escolha');

                    foreach ( $this->contratos as $contrato )
                    {
                        $panel->addActionAJAX($contrato[1], $ui->getImageTheme($module, 'perfil.png'), 'definePerfil', $contrato[0]);
                    }

                    $fields[] = new MDiv();
                    $fields[] = new MDiv();
                    $fields[] = $label = new MLabel(_M('Escolha o perfil desejado para utilizar o portal.'), 'blue', true);
                    $label->addStyle('font-size', '18px');
                    $fields[] = $panel;

                    $this->btAcessarComo = false;
                }
            }
            else
            {
                $forms[] = $this->formFields;
            }
            
            $isAdmin = MIOLO::_REQUEST('isAdmin');
            
            if(  strlen($this->personid)>0 ||  $isAdmin == DB_TRUE )
            {
                $conteudo = new MDiv('', $forms);
                $conteudo->addStyle('padding-top', '40px');
                $conteudo->addStyle('padding-bottom', '150px');

                $fields[] = $conteudo;
            }
            else
            {
                $fields[] = MPrompt::information(_M('Não foi encontrado um cadastro de pessoa física para este login. Entre em contato com a secretaria acadêmica.'),'NONE',_M('Information'));
            }

            $bottomBar = new bottomBar();
            
            if ( $this->btInicio )
            {
                $url = $MIOLO->getActionURL($module, 'main');
                $bottomBar->addButton('Inicio', "javascript:miolo.doLink('$url', '__mainForm');", $ui->getImageTheme($module, 'home.png'));
            }
            
            if ( $this->btVoltar )
            {
                $groupId = MIOLO::_REQUEST('groupid');
                $mostrar = MIOLO::_REQUEST('mostrar');
                $periodId = MIOLO::_REQUEST('periodid');
                
                if ( $groupId )
                {
                    if ( strlen($periodId) > 0 )
                    {
                        $url = $MIOLO->getConf('home.url').'/index.php?module='.$module.'&action=main:disciplinasProfessor&mostrar='.$groupId.'&periodid='.$periodId;
                    }
                    else
                    {
                        $url = $MIOLO->getConf('home.url').'/index.php?module='.$module.'&action=main:disciplinasProfessor&mostrar='.$groupId;
                    }
                    $bottomBar->addButton('Voltar', "javascript:miolo.doLink('$url', '__mainForm');", $ui->getImageTheme($module, 'back.png'));
                }
                elseif ( $mostrar )
                {
                    $url = $MIOLO->getActionURL($module, 'main');
                    $bottomBar->addButton('Voltar', "javascript:miolo.doLink('$url', '__mainForm');", $ui->getImageTheme($module, 'back.png'));
                }
                else
                {
                    $bottomBar->addButton('Voltar','javascript:history.back()',$ui->getImageTheme($module, 'back.png'));
                }
                
            }
            
            if( $this->btAcessarComo && ( sMultiUnidade::estaHabilitada() || prtUsuario::temMaisDeUmNivel() || MUtil::getBooleanValue(prtUsuario::obterMultiplosContratos()) || MUtil::getBooleanValue(prtUsuario::obterMultiplasInscricoes()) ) )
            {
                $bottomBar->addButton('Acessar como',"javascript:miolo.doPostBack('trocarTipoDeAcesso','','__mainForm')",$ui->getImageTheme($module, 'trocar_usuario.png'));
            }
            
            if($this->btSair)
            {
                $bottomBar->addButton('Sair',"javascript:miolo.doPostBack('confirmarSair','','__mainForm')",$ui->getImageTheme($module, 'exit.png'));
            }
            
            if( $isAdmin != DB_TRUE )
            {
                $fields[] = $bottomBar;
            }
            
            $fields[] = new MDiv('responseDiv', NULL);

            if ($this->autoSave)
            {
                //incluir dojo connect e chamada ajax para salvar via ajax
                $this->page->onload("jQuery(document).change(function() { ".MUtil::getAjaxAction('salvar')." });");
            }
            
            // Verifica a resolução da tela e ajusta o título
            $this->page->onload("
                ajustaFonteElemento('divTitulo');
                ajustaFonteElemento('divUnidade');
                ajustaFonteElemento('divUsuario');

            ");

            $this->setFields($fields);
        }
    }
    
    public function definePerfil($contractId)
    {
        $MIOLO = MIOLO::getInstance();
        prtUsuario::definirContratoAtivo($contractId);
        
        $this->page->redirect($MIOLO->getCurrentURL());
    }

    /**
     * Exibe confirmação para sair do sistema. 
     */
    public function confirmarSair()
    {
        $campos = array();
        $botoes = array();

        $campos[] = new MLabel(_M('Você realmente deseja sair?', $this->modulo));
        $botoes[] = new MButton('botaoSair', _M('Sim', $this->modulo), ':sair');
        $botoes[] = new MButton('botaoCancelarSair', _M('Não', $this->modulo), "dijit.byId('dialogoConfirmarSair').hide();");
        $campos[] = MUtil::centralizedDiv($botoes);

        $dialog = new MDialog('dialogoConfirmarSair', _M('Confirmar', $this->modulo), $campos);
        $dialog->show();
    }

    /**
     * Redireciona para tela de logout. 
     */
    public function sair()
    {
        MDialog::close('dialogoConfirmarSair');
        $this->setResponse(NULL, 'divBotaoSair');
        $url = $this->manager->getActionURL($this->modulo, 'main:logout');
        prtUsuario::definirContratoAtivo(NULL);
        prtUsuario::definirMultiplosContratos(false);
        $_SESSION["loginFrom"] = null;
        $this->page->redirect($url);
    }
    
    public function trocarAcesso($data)
    {
        $module = MIOLO::getCurrentModule();
                
        if ( $data->tipoUsuario || $data->inscricaoAtiva )
        {
            if ( $data->tipoUsuario )
            {
                if ( is_numeric($data->tipoUsuario) )
                {
                    prtUsuario::definirContratoAtivo($data->tipoUsuario);
                }
                else if ( $data->tipoUsuario == 'A' )
                {
                    $contratos = PrtUsuarioSagu::obterContratosAtivosDaPessoa($this->personid);
                    prtUsuario::definirTipoDeAcesso($data->tipoUsuario);
                    prtUsuario::definirContratoAtivo($contratos[0]);
                    prtUsuario::definirMultiplosContratos(count($contratos) > 1);
                }
                else
                {
                    prtUsuario::definirTipoDeAcesso($data->tipoUsuario);
                    prtUsuario::definirContratoAtivo(NULL);
                    prtUsuario::definirMultiplosContratos(FALSE);
                }
            }

            if ( $data->inscricaoAtiva )
            {
                prtUsuario::definirInscricaoAtiva($data->inscricaoAtiva);
            }
            
            if ( $data->unitId )
            {
                if ( sMultiUnidade::estaHabilitada() )
                {
                    sMultiUnidade::definirUnidadeLogada( $data->unitId );
                }
            }

            $this->page->redirect($this->manager->getActionURL($module, 'main'));
        }
        else
        {
            MDialog::close('');
            $this->setNullResponseDiv('dialogoTrocarTipoDeAcesso');
        }
    }
    
    public function trocarTipoDeAcesso()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/prtUsuario.class.php', $module);
        
        $campos = array();
        $botoes = array();

        if ( prtUsuario::temMaisDeUmNivel() || prtUsuario::obterMultiplosContratos() ) // Caso tenha mais de um tipo de acesso exibe combo
        {
            $campos[] = new MSelection('tipoUsuario', null, '', prtUsuario::listaNiveisDeAcessos($this->personid));
        }
        
        if ( sMultiUnidade::estaHabilitada() )
        {
            $unit = new MSelection('unitId', MIOLO::_REQUEST('unitId'), '', sMultiUnidade::obterUnidadesPessoaLogada());
            $campos[] = $unit;
        }
        
        $inscricoes = PrtUsuarioSagu::obterInscricoesAtivasDaPessoaTurmaGrupo($this->personid, true);
        
        if ( count($inscricoes) > 0 )
        {   
            $campos[] = new MSelection('inscricaoAtiva', null, '', $inscricoes);
        }
        
        $botoes[] = $this->button('botaoTrocarAcesso', _M('Trocar', $this->modulo), NULL, MUtil::getAjaxAction('trocarAcesso'));
        $botoes[] = $this->button('botaoCancelarSair', _M('Cancelar', $this->modulo), NULL, "dijit.byId('dialogoTrocarTipoDeAcesso').hide();");
        $campos[] = MUtil::centralizedDiv($botoes);

        $dialog = new MDialog('dialogoTrocarTipoDeAcesso', _M('Trocar tipo de Acesso?', $this->modulo), $campos);
        $dialog->setWidth('350px');
        $dialog->show();
    }
    
    
    public function salvar($args)
    {
        $this->setResponse(null, 'responseDiv');   
    }
    
    //////////////////// PASSAR PARA COMPONENTES //////////////////////////////
    
    public function MTextFieldReadOnly($id=null, $text=null)
    {
        $field = new MDiv($id, $text);
        $field->setClass('mTextField ui-input-text ui-body-d ui-corner-all ui-shadow-inset');
        $field->addAttribute('style', 'background: #F0F0F0; font-size: 18px; height: 24px; padding: 6px; width: 97%;');
        
        return $field;
    }
    
    public function MTextAreaReadOnly($id=null, $text=null)
    {
        $field = new MDiv($id, $text);
        $field->setClass('mTextField ui-input-text ui-body-d ui-corner-all ui-shadow-inset');
        $field->addAttribute('style', 'background: #F0F0F0; font-size: 18px; height: 100px; padding: 6px; width: 97%;');
        
        return $field;
    }
    
    public function fileField($name, $multiple = true)
    {
        $file = new MFileField($name, null, null, 40, _M("Tamanho máx. de arquivo configurado pelo servidor: " . ini_get('post_max_size')));
        $file->setIsMultiple($multiple);
        $file->setClass('fileField');
        $label = new MLabel('Anexo:');
        $label->addStyle('margin-top', '8px');
        $label->addStyle('margin-left', '30px');
        $div = new MHContainer('divUpload', array($label, $file));
        
        return $div;
    }
    
    public function timeStampField($name, $value=null, $label=null)
    {
        $label = new MLabel($label);
        $timestamp = new MTimestampField($name, $value, null);
        
        $container = new MHContainer('div'.$name, array($label, $timestamp) );
        
        return $container;
    }
    
    public function checkbox($id, $txt1, $txt2, $value, $class = null)
    {
        if($value==DB_TRUE)
        {
            $check = 'checked="checked"';
        }
        
        $desabilitaBotaoDireito = "if (event.button != 0){alert('Utilize o botão esquerdo do mouse para marcar/desmarcar.');return false;}";
        $div = new MDiv('div_'.$id,'<div onmousedown="' . $desabilitaBotaoDireito . '" onselectstart="return false" oncontextmenu="return false" ondragstart="return false" data-role="fieldcontain" style="width:100%;">
                                    <fieldset data-role="controlgroup" style="width:100%;">
                                            <input type="checkbox" name="'.$id.'" id="'.$id.'" '.$check.' class="custom ' . $class . '" />
                                            <label for="'.$id.'"><div style="font-size:12px; font-weight:bold;">'.$txt1.'</div><div style="font-size:14px; font-weight:normal;">'.$txt2.'</div></label>
                                        </fieldset>
                                    </div>');
        
        $div->addStyle('width','20%');
        
        return $div;
    }
    
    public function slider($id, $txt1, $txt2, $value)
    {
        $v = $value?$value:10;
        
        $div = new MDiv('div_'.$id,'<label for="slider-fill"><div>'.$txt1.'</div><div style="font-size:14px; font-weight:normal;">'.$txt2.'</div></label>
                        <input type="range" name="'.$id.'" id="'.$id.'" value="'.$v.'" min="0" max="30" data-highlight="true" />');
        
        $div->addStyle('width','98%');    
        
        return $div;
    }
    
    public function mobileGrid($grid)
    {
        foreach ($grid->columns as $k => $col)
        {
                $titulo[] = $col->title;
        }
        
        if ($grid->data)
        {
            $i = 0;
            foreach ($grid->data as $data)
            {
                foreach ($data as $d)
                {
                    $value[$i][] = $d;
                }
                $i++;
            }
            
        }

        $list = '<div data-role="content"> 
		<div class="content-primary">	
		<ul data-role="listview">';
        
        foreach($value as $v)
        {
            $list .= '<li>';
            
            foreach($v as $k=>$v_)
            {
                if($titulo[$k])
                {
                    $list .= '<!-- <img src="images/album-bb.jpg" /> -->
                                <h4>'.$titulo[$k].' : '.$v_.'</h4> 
                                <!-- <p>Broken Bells</p> -->';
                }
            }
            
            $list .= '</li>';
        }
        
        $list .= '</ul> 
		</div>
                </div>';
        
        //return new MDiv('divSchoolHistoric', utf8_encode($grid->generate()));
        return new MDiv('divSchoolHistoric', utf8_encode($list));
    }
    
    public function listView($id, $title, $columns, $data, $options)
    {
        $html = '<div data-role="content"> 
                        <div class="content-primary">	
                        <ul data-role="listview"> ';
        
        if($data)
        {
            foreach($data as $k=>$d)
            {
                
                if($d['action'])
                {
                    $href = $d['action'];
                }
                else
                {
                    $href = '#';
                }
                
                
                $html .='<li>';
                
                if($d['action'])
                {
                    $html .='<a href="'.$href.'">';
                }


                if($options['images'])
                {
                    $html .='<img src="images/album-bb.jpg" />';
                }

                if(isset($options['title_key']))
                {
                    $title_key = $options['title_key'];
                    $html .='<h3>'.$d[$title_key].'</h3>';
                }

                foreach($d as $j=>$v)
                {
                    if($column = $columns[$j])
                    {
                        if ( $column == 'actions' )
                        {                        
                            $html .='<p><b>'.$v.'</b></p>';
                        }
                        else
                        {
                            $html .='<p><b>'.$column.' : '.$v.'</b></p>';
                        }
                    }
                }
                
                if($d['action'])
                {
                    $html .='</a>';
                }

                $html .='</li>';
            }
        }
        else
        {
            $html .='<li><h3>Nenhum registro encontrado.</h3></li>';
        }
        
        $html .= '                        
                        </ul> 
                        </div>
                </div>';
        
        return new MDiv($id,$html);
    }
    
    public function controlGroup($name, $label, $options, $legend=null)
    {
        $content = '<div data-role="fieldcontain">
                        <fieldset data-role="controlgroup" data-type="horizontal" data-role="fieldcontain"> ';
        
        $i=1;
        
        foreach($options as $val=>$lbl)
        {
            $content .= '<input type="radio" name="'.$name.'" id="'.$name.$i.'" value="'.$val.'" />
                         <label for="'.$name.$i.'">'.$lbl.'</label>';
            
            $i++;
        }
        
        $content .= '</fieldset>
                    </div>';
        
        $fields = new MDiv('div'.$name, $content);
        
        if($legend)
        {    
            $fields = new MBaseGroup('bg'.$name, $legend, array($fields));
        }
        
        return $fields;
    }
    
    public function button($id, $label, $action, $ajaxAction=null, $image=null)
    {
        if($ajaxAction)
        {
            $onclick = 'onclick="'.$ajaxAction.'"';
            $action = '#';
        }
        
        $lnk = '<a id="link'.$id.'" href="'.$action.'" '.$onclick.' data-role="button">'.$label;
        if ( $image )
        {
            $lnk .= ' <img src="' . $image . '">';
        }
        $lnk .= '</a>';
        
        return $div = new MDiv($id, $lnk);
        
    }
    
    /*
     * Verifica se a pessoa logada � o professor respons�vel da disciplina.
     * Est� verifica��o acontece apenas se o par�metro est� habilitado e existe um professor cadastrado com respons�vel
     * caso contr�rio, mant�m a funcionalidade original.
     */
    public function verificaProfessorResponsavel($groupId, $personId)
    {
        if(SAGU::getParameter('ACADEMIC', 'SOMENTE_PROFESSOR_RESPONSAVEL') == DB_FALSE)
        {
            return DB_TRUE;
        }
        else
        {
            $busGroup = new BusinessAcademicBusGroup();
            $grupo = $busGroup->getGroup($groupId);
            
            if( $grupo->professorResponsible )
            {
                if( $grupo->professorResponsible == $personId )
                {
                    return DB_TRUE;
                }
                else
                {
                    return DB_FALSE;
                }
            }
            else
            {
                return DB_TRUE;
            }
        }
    }
    
    /*
     * Retorna o personid da pessoa logada
     */
    public function retornaPersonIdPessoaLogada()
    {
        $MIOLO = MIOLO::getInstance();
        
        $user = $MIOLO->getLogin();
        $filters->mioloUserName = $user->id;
        $busPhysicalPerson = new BusinessBasicBusPhysicalPerson();
        $professor = $busPhysicalPerson->searchPhysicalPerson($filters);
        
        return $professor[0][0];
    }
    
    /**
     * @return string
     */
    public function getSaguReportPath()
    {
        $MIOLO = MIOLO::getInstance();
        
        $saguPath = $MIOLO->getConf("home.modules"). '/basic/reports/';
        $saguPath = str_replace('miolo26', 'miolo20', $saguPath);
        
        return $saguPath;
    }
    
    public function loadCustomFields($subjectId = null)
    {
        $fields = array();
        
        if ( strlen($subjectId) > 0 )
        {
            $customFieldIds = BasCustomField::getCustomFieldIdsBySubject($subjectId);

            // Carrega e exibe campos personalizados
            if ( count($customFieldIds) > 0 )
            {
                $customizedId = $subjectId;
    //            $cfData = BasCustomField::getFieldValuesById($customFieldIds, $customizedId);
                $cfData = new stdClass();
                $customFields = $this->mioloCustomFields = BasCustomField::listByCustomFieldIds($customFieldIds);
                $fields = $this->generateCustomFields($customFields, $cfData);
            }
        }

        return $fields;
    }
    
    /**
     * Codigo obtido de mform.class do miolo 2.0, metodo generateCustomFields()
     * 
     * @return array
     */
    public function generateCustomFields($mioloCustomFields = array(), $data = null)
    {
        $fields = array();
        
        $listNoYes = array(
            'f' => _M('No'),
            't' => _M('Yes'),
        );

        foreach ( $mioloCustomFields as $cfield )
        {
            $cfield instanceof MCustomField;
            
            $field = NULL;
            $validator = NULL;

            $cfield->suffix = $suffix;
            $id = $cfield->getInputId();
            $value = isset($data) ? $data->$id : $cfield->defaultValue;
            $label = _M($cfield->label, MIOLO::getCurrentModule());

            if ( $cfield->isRequired() )
            {
                $validator = new MRequiredValidator($id, $label);
            }

            switch ( $cfield->fieldFormat )
            {
                case MCustomField::FORMAT_BOOLEAN:
                    $field = new MSelection($id, $value, $label, $listNoYes);
                    break;

                case MCustomField::FORMAT_DATE:
                    $field = new MCalendarField($id, $value, $label);

                    if ( $cfield->isRequired() && $cfield->isEditable() && $cfield->isVisible() )
                    {
                        $field->validator->type = 'required';
                        $validator = NULL;
                    }

                    break;

                case MCustomField::FORMAT_DECIMAL:
                    $field = new MFloatField($id, $value, $label);
                    break;

                case MCustomField::FORMAT_INTEGER:
                    $field = new MTextField($id, $value, $label);
                    $validator = new MIntegerValidator($id, $label, $cfield->isRequired() ? 'required' : 'optional');
                    break;

                case MCustomField::FORMAT_LIST:
                    $field = new MSelection($id, $value, $label, $cfield->getListValues());
                    break;
                
                case MCustomField::FORMAT_LISTSQL:
                    $field = new MSelection($id, $value, $label, $cfield->getListSQL());
                    $field->hint = $cfield->getFieldHint();
                    break;

                case MCustomField::FORMAT_LONG_TEXT:
                    $field = new MMultilineField($id, $value, $label, 25, 5, 20);
                    break;

                case MCustomField::FORMAT_TEXT:
                    $field = new MTextField($id, $value, $label);
                    break;
            }

            if ( $cfield->maxLength != 0 )
            {
                if ( !$validator )
                {
                    $validator = new MRegExpValidator($id, $label);
                }

                $validator->min = $cfield->minLength;
                $validator->max = $cfield->maxLength;
            }

            if ( $field )
            {
                if ( !$cfield->isEditable() )
                {
                    $field->setReadOnly(true);
                    $validator = NULL;
                }

                if ( !$cfield->isVisible() )
                {
                    $field->addBoxStyle('display', 'none');
                    $validator = NULL;
                }

                $fields[] = $field;
            }

            if ( $validator != NULL )
            {
                $this->addValidator($validator);
            }
        }
        
        return $fields;
    }
    
    /**
     * Codigo obtido do miolo 2.0, mform.class, metodo saveCustomFields().
     * 
     * Save the custom field values.
     *
     * @param mixed $customizedId The primary key of the related table.
     * @return boolean Whether was successfully saved.
     */
    public function saveCustomFields($customizedId, $data = null, $identifier = null)
    {
        if ( strlen($identifier) > 0 )
        {
            $this->getCustomFields($identifier, $customizedId);
        }

        if ( count($this->mioloCustomFields) == 0 )
        {
            return NULL;
        }
        
        // Se nao passar dados, pega o padrao getData()
        if ( !$data )
        {
            $data = $this->getData();
        }
        
        $ok = false;

        foreach ( $this->mioloCustomFields as $cf )
        {
            $cf instanceof MCustomField;
            
            $inputId = $cf->getInputId();

            $customValue = new MCustomValue();
            $customValue->customizedId = $customizedId;
            $customValue->customFieldId = $cf->id;
            $customValue->value = $data->$inputId;
            
            // If customized id is set, then it's an edit action
            if ( isset($this->mioloCustomizedId) )
            {
                if ( $customValue->updateByData() )
                {
                    $ok = true;
                }
                else
                {
                    $ok = false;
                    break;
                }
            }
            else
            {
                if ( $customValue->insert() )
                {
                    $ok = true;
                }
                else
                {
                    $ok = false;
                    break;
                }
            }
        }

        return $ok;
    }
}
?>