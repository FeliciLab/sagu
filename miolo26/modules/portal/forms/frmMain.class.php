<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/10/09
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
$MIOLO->uses('classes/prtUsuario.class.php', $module);
$MIOLO->uses('classes/prtDocumentos.class.php', $module);

$MIOLO->uses('classes/amanagelogin.class.php', 'avinst');
$MIOLO->uses('classes/avinst.class.php', 'avinst');
$MIOLO->uses('classes/adynamicform.class.php', 'avinst');
$MIOLO->uses('forms/frmDashboard.class.php', 'avinst');

$MIOLO->uses('classes/MatriculaWeb.class', 'academic');
$MIOLO->uses('classes/prtDisciplinas.class.php', $module);
$MIOLO->uses('types/avaAvaliacao.class.php', 'avinst');
$MIOLO->uses('types/avaPerfil.class.php', 'avinst');

class frmMain extends frmMobile
{
    public $respondeuAvaliacao = true;
    
    public $bloqueioPedagogico = false;
    public $mensagemBloqueio = NULL;
    
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
       
        $acesso = _M('Curso: ');
        
        self::$fazerEventHandler = FALSE;
        parent::__construct($acesso, MIOLO::getCurrentModule());
    }

    public function defineFields()
    {
        //ATENCAO: SEMPRE QUE FOR ADICIONADA UMA NOVA OPCAO NO MENU PRINCIPAL DO PORTAL
        //MANTER A ORDEM ALFABETICA DOS ITENS INTACTA
        
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        $busTransaction = new BusinessAdminTransaction();
        $prtDocumentos = new prtDocumentos();
        $documentos = $prtDocumentos->getDocumentos();
        
        // Setado contador devido ao ajax do sagu chamar novamente o define fields
        $count = SAGU::NVL($MIOLO->session->get('countDefine'), 0);
        
        if ( $MIOLO->session->get('passwordChanged') == DB_TRUE || $count == 1 )
        {
            $msg = _M('Senha alterada com sucesso.');
            $fields[] = MMessage::getStaticMessage('infoAlerta', _M($msg), MMessage::TYPE_SUCCESS);
            
            $jsCode = "setInterval(function(){document.getElementById('infoAlerta').style.display = 'none';},10000)";
            
            $MIOLO->page->onload($jsCode);
            
            $count = $count + 1;
            
            $MIOLO->session->set('passwordChanged', null);
            $MIOLO->session->set('countDefine', $count);
        }
        
        if ( SAGU::getParameter('PORTAL', 'VALIDA_SE_RESPONDEU_AVALIACAO_INSTITUCIONAL') == DB_TRUE && 
             !$this->validaSeRespondeuAvaliacaoInstitucional() )
        {
            $this->respondeuAvaliacao = false;
        }
        
        if ( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' && SAGU::getParameter('PORTAL', 'BLOQUEIA_CASO_INADIMPLENTE') == DB_TRUE )
        {
            $this->bloqueioPedagogico = $this->verificaBloqueioFinanceiro();
        }
        
        $habilitaDocumentosPortal = strtoupper(SAGU::getParameter('PORTAL', 'HABILITA_DOCUMENTOS_PORTAL'));
        
        $this->btInicio = false;
        $this->btVoltar = false;
        $this->autoSave = false;
        
        if( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
        {
            $panel = new mobilePanel('panel');
            
            $avaliacaoVigente = avaAvaliacao::verificaAvalicoesAbertas(avaPerfil::TIPO_COORDENADOR);
            
            //Acessar o Moodle
            if ( SAGU::getParameter('BASIC', 'MOODLE_INSTALLED') == 'YES'  && $this->respondeuAvaliacao )
            {		
                $panel->addActionMoodle(_M('Acessar o Moodle', $module));
            }
            
            //Agenda
            if ( $MIOLO->checkAccess('FrmAgendaCoordenador', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmAgendaCoordenador'), $ui->getImageTheme($module, 'calendar.png'), $module, 'main:agenda');
            }
            
            //Avaliacao Institucional
            if ( $MIOLO->checkAccess('frmDashboard', A_ACCESS, FALSE) && $MIOLO->checkAccess('FrmAvaliacaoCoordenador', A_ACCESS, FALSE) && ($avaliacaoVigente == DB_TRUE) && SAGU::getParameter('BASIC', 'MODULE_AVINST_INSTALLED') == 'YES' )
            {
                $panel->addAction($busTransaction->getTransactionName('frmDashboard'), $ui->getImageTheme($module, 'avaliacao.png'), 'avinst', 'main');
            }
            
            //Disciplinas
            if ( $MIOLO->checkAccess('FrmDisciplinasCoordenador', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction(_M($busTransaction->getTransactionName('FrmDisciplinasCoordenador')), $ui->getImageTheme($module, 'enroll.png'), $module, 'main:disciplinasCoordenador');
            }
            
            //Documentos
            if ( ( count($documentos['coordenador']) > 0 || prtDocumentos::possuiDocumentosCadastrados('coordenador')) && $this->respondeuAvaliacao && (strstr($habilitaDocumentosPortal, prtUsuario::USUARIO_COORDENADOR) || strstr($habilitaDocumentosPortal, prtUsuario::TODOS_USUARIOS)) )
            {
                $panel->addAction(_M('Documentos'), $ui->getImageTheme($module, 'docs.png'), $module, 'main:documentos', NULL, array('perfil' => 'C'));
            }
            
            //Estatisticas
            //So mostra pra coordenadores do academico (nao funciona pro pedagogico)
            $busCourseCoordinator = new BusinessAcademicBusCourseCoordinator();
            $coordenadorAcademico = $busCourseCoordinator->isCourseCoordinator(prtUsuario::obtemUsuarioLogado()->personId);            
            if ( $coordenadorAcademico )
            {
                if ( $MIOLO->checkAccess('FrmEstatisticasCoordenador', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
                {
                    $panel->addAction(_M('Estatísticas',$module), $ui->getImageTheme($module, 'stats.png'), $module, 'main:estatistica');
                }
            }
            
            //Gnuteca
            if ( ( SAGU::getParameter('BASIC', 'MODULE_GNUTECA_INSTALLED') == 'YES' ) && $MIOLO->checkAccess('FrmBibliotecaCoordenador', A_ACCESS, FALSE) )
            {
                $panel->addActionBiblioteca($busTransaction->getTransactionName('FrmBibliotecaCoordenador'));
            }
            
            //Mensagens
            if ( $MIOLO->checkAccess('FrmMensagensProfessor', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmMensagensProfessor'), $ui->getImageTheme($module, 'mail.png'), $module, 'main:mensagens');
            }
            
            //Perfil
            if ( $MIOLO->checkAccess('FrmPerfilCoordenador', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmPerfilCoordenador'), $ui->getImageTheme($module, 'perfil.png'), $module, 'main:perfil');
            }
            
            //Preferencias
            if ( $MIOLO->checkAccess('FrmPreferenciasCoordenador', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmPreferenciasCoordenador'), $ui->getImageTheme($module, 'preferences.png'), $module, 'main:preferencias');
            }
            
            //Solicitacao de protocolo
            if ( SAGU::getParameter('PROTOCOL', 'REQUEST_AUTOMATIC_NUMBER') == 'YES' )
            {
                if ( $MIOLO->checkAccess('FrmProtocoloCoordenador', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
                {
                    $panel->addAction($busTransaction->getTransactionName('FrmProtocoloCoordenador'), $ui->getImageTheme($module, 'protocol.png'), $module, 'main:protocolocoordenador');
                }
            }
        }
        elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR )
        {
            $panel = new mobilePanel('panel');
            
            $avaliacaoVigente = avaAvaliacao::verificaAvalicoesAbertas(avaPerfil::TIPO_PROFESSOR);
            
            //Acessar o moodle
            if ( SAGU::getParameter('BASIC', 'MOODLE_INSTALLED') == 'YES' && $this->respondeuAvaliacao )
            {
                $panel->addActionMoodle(_M('Acessar o Moodle', $module));
            }
            
            //Agenda
            if ( $MIOLO->checkAccess('FrmAgendaProfessor', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmAgendaProfessor'), $ui->getImageTheme($module, 'calendar.png'), $module, 'main:agenda');
            }
            
            //Avalicao institucional
            if ( $MIOLO->checkAccess('frmDashboard', A_ACCESS, FALSE) && $MIOLO->checkAccess('FrmAvaliacaoProfessor', A_ACCESS, FALSE) && ($avaliacaoVigente == DB_TRUE) && SAGU::getParameter('BASIC', 'MODULE_AVINST_INSTALLED') == 'YES' )
            {
                $panel->addAction($busTransaction->getTransactionName('frmDashboard'), $ui->getImageTheme($module, 'avaliacao.png'), 'avinst', 'main');
            }
            
            //Disciplinas
            if ( $MIOLO->checkAccess('FrmDisciplinasProfessor', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction(_M($busTransaction->getTransactionName('FrmDisciplinasProfessor')), $ui->getImageTheme($module, 'enroll.png'), $module, 'main:disciplinasProfessor');
            }
            
            //Documentos
            if ( ( count($documentos['professor']) > 0 || prtDocumentos::possuiDocumentosCadastrados('coordenador') ) && $this->respondeuAvaliacao && (strstr($habilitaDocumentosPortal, prtUsuario::USUARIO_PROFESSOR) || strstr($habilitaDocumentosPortal, prtUsuario::TODOS_USUARIOS)) )
            {
                $panel->addAction(_M('Documentos'), $ui->getImageTheme($module, 'docs.png'), $module, 'main:documentosProfessor');
            }
            
            //Gnuteca
            if ( ( SAGU::getParameter('BASIC', 'MODULE_GNUTECA_INSTALLED') == 'YES' ) && $MIOLO->checkAccess('FrmBibliotecaProfessor', A_ACCESS, FALSE) )
            {
                $panel->addActionBiblioteca($busTransaction->getTransactionName('FrmBibliotecaProfessor'));
            }
            
            //Grade de horarios
            if ( $MIOLO->checkAccess('FrmGradeHorarioProfessor', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmGradeHorarioProfessor'), $ui->getImageTheme($module, 'horario.png'), $module, 'main:gradeHorario');
            }
            
            //Mensagens
            if ( $MIOLO->checkAccess('FrmMensagensProfessor', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmMensagensProfessor'), $ui->getImageTheme($module, 'mail.png'), $module, 'main:mensagens');
            }
            
            //Perfil
            if ( $MIOLO->checkAccess('FrmPerfilProfessor', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmPerfilProfessor'), $ui->getImageTheme($module, 'perfil.png'), $module, 'main:perfil');
            }
            
            //Preferencias
            if ( $MIOLO->checkAccess('FrmPreferenciasProfessor', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmPreferenciasProfessor'), $ui->getImageTheme($module, 'preferences.png'), $module, 'main:preferenciasProfessor');
            }
            
            //Solicitacao de protocolo
            if ( SAGU::getParameter('PROTOCOL', 'REQUEST_AUTOMATIC_NUMBER') == 'YES' )
            {
                if ( $MIOLO->checkAccess('FrmProtocoloProfessor', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
                {
                    $panel->addAction($busTransaction->getTransactionName('FrmProtocoloProfessor'), $ui->getImageTheme($module, 'protocol.png'), $module, 'main:protocolo');
                }
            }
        }
        elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_BASICO )
        {
            $panel = new mobilePanel('panel');

            //Documentos
            if ( prtDocumentos::possuiDocumentosCadastrados() && (strstr($habilitaDocumentosPortal, prtUsuario::USUARIO_BASICO) || strstr($habilitaDocumentosPortal, prtUsuario::TODOS_USUARIOS)) )
            {
                $panel->addAction(_M('Documentos'), $ui->getImageTheme($module, 'docs.png'), $module, 'main:documentos');
            }
            
            //Financeiro
            if ( SAGU::getParameter('BASIC', 'MODULE_FINANCE_INSTALLED') == 'YES' )
            {
                if ( $MIOLO->checkAccess('FrmFinanceiroAluno', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
                {
                    $panel->addAction($busTransaction->getTransactionName('FrmFinanceiroAluno'), $ui->getImageTheme($module, 'finance.png'), $module, 'main:financeiro');
                }
            }
            
            //Perfil
            if ( $MIOLO->checkAccess('FrmPerfilAluno', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmPerfilAluno'), $ui->getImageTheme($module, 'perfil.png'), $module, 'main:perfil');
            }
        }
        elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_GESTOR )
        {
            $panel = new mobilePanel('panel');
            
            //Documentos
            if ( prtDocumentos::possuiDocumentosCadastrados() && (strstr($habilitaDocumentosPortal, prtUsuario::USUARIO_GESTOR) || strstr($habilitaDocumentosPortal, prtUsuario::TODOS_USUARIOS)) )
            {
                $panel->addAction(_M('Documentos'), $ui->getImageTheme($module, 'docs.png'), $module, 'main:documentos', array('perfil' => 'G'));
            }
            
            //Estatisticas
            $panel->addAction(_M('Estatísticas',$module), $ui->getImageTheme($module, 'stats.png'), $module, 'main:estatistica');
        }
        else
        {
            $avaliacaoVigente = avaAvaliacao::verificaAvalicoesAbertas(avaPerfil::TIPO_ALUNO);
            
            if ( strlen($this->mensagemBloqueio) > 0 )
            {
                $fields[] = MMessage::getStaticMessage('_mensagemBloqueio', $this->mensagemBloqueio, MMessage::TYPE_INFORMATION);
            }
            
            
            $panel = new mobilePanel('panel');
            
            //Acessar o Moodle
            if ( SAGU::getParameter('BASIC', 'MOODLE_INSTALLED') == 'YES' && $this->respondeuAvaliacao && !$this->bloqueioPedagogico )
            {
                $panel->addActionMoodle(_M('Acessar o Moodle', $module));
            }
            
            //Agenda
            if ( $MIOLO->checkAccess('FrmAgendaAluno', A_ACCESS, FALSE) && $this->respondeuAvaliacao && !$this->bloqueioPedagogico )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmAgendaAluno'), $ui->getImageTheme($module, 'calendar.png'), $module, 'main:agenda');
            }
            
            //Avaliacao institucional
            if ( $MIOLO->checkAccess('frmDashboard', A_ACCESS, FALSE) && $MIOLO->checkAccess('FrmAvaliacaoAluno', A_ACCESS, FALSE) && ($avaliacaoVigente == DB_TRUE) && !$this->bloqueioPedagogico && SAGU::getParameter('BASIC', 'MODULE_AVINST_INSTALLED') == 'YES' )
            {
                $panel->addAction($busTransaction->getTransactionName('frmDashboard'), $ui->getImageTheme($module, 'avaliacao.png'), 'avinst', 'main');
            }
            
            //Disciplinas
            if ( $MIOLO->checkAccess('FrmDisciplinasAluno', A_ACCESS, FALSE) && $this->respondeuAvaliacao && !$this->bloqueioPedagogico )
            {
                $panel->addAction(_M($busTransaction->getTransactionName('FrmDisciplinasAluno')), $ui->getImageTheme($module, 'enroll.png'), $module, 'main:disciplinas');
            }
            
            //Documentos
            if ( 
                $this->respondeuAvaliacao && 
                (
                        $MIOLO->checkAccess('DocHistoricoEscolar', A_ACCESS, FALSE) ||
                        $MIOLO->checkAccess('DocAtestadoMatricula', A_ACCESS, FALSE) ||
                        $MIOLO->checkAccess('DocBoletimNotasFrequencia', A_ACCESS, FALSE) ||
                        $MIOLO->checkAccess('DocEmentario', A_ACCESS, FALSE) ||
                        count($documentos['aluno']) > 0 ||
                        prtDocumentos::possuiDocumentosCadastrados('aluno')
                ) && 
                (strstr($habilitaDocumentosPortal, prtUsuario::USUARIO_ALUNO) || strstr($habilitaDocumentosPortal, prtUsuario::TODOS_USUARIOS)) &&
                prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO && !$this->bloqueioPedagogico
            )
            {
                $panel->addAction(_M('Documentos'), $ui->getImageTheme($module, 'docs.png'), $module, 'main:documentos', NULL, array('perfil' => 'A'));
            }
            
            //Financeiro
            if ( SAGU::getParameter('BASIC', 'MODULE_FINANCE_INSTALLED') == 'YES' )
            {
                if ( $MIOLO->checkAccess('FrmFinanceiroAluno', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
                {
                    $panel->addAction($busTransaction->getTransactionName('FrmFinanceiroAluno'), $ui->getImageTheme($module, 'finance.png'), $module, 'main:financeiro');
                }
            }
            
            //Grade de horarios
            if ( $MIOLO->checkAccess('FrmGradeHorario', A_ACCESS, FALSE) && $this->respondeuAvaliacao && !$this->bloqueioPedagogico )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmGradeHorario'), $ui->getImageTheme($module, 'horario.png'), $module, 'main:gradeHorario');
            }
            
            //Gnuteca
            if ( ( SAGU::getParameter('BASIC', 'MODULE_GNUTECA_INSTALLED') == 'YES' ) && $MIOLO->checkAccess('FrmBibliotecaAluno', A_ACCESS, FALSE) && !$this->bloqueioPedagogico )
            {
                $panel->addActionBiblioteca($busTransaction->getTransactionName('FrmBibliotecaAluno'));
            }
            
            //Historico escolar
            //ADICIONANDO VALIDACAO TEMPORARIA PARA NAO MOSTRAR ICONE AOS ALUNOS DO PEDAGOGICO
            if ( $MIOLO->checkAccess('FrmHistoricoAluno', A_ACCESS, FALSE) && $this->respondeuAvaliacao && !$this->bloqueioPedagogico && !(strlen(prtUsuario::obterInscricaoAtiva()) > 0) )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmHistoricoAluno'), $ui->getImageTheme($module, 'history.png'), $module, 'main:historicoEscolar');
            }
            
            //Matricula - mostra somente se existirem contratos disponíveis
            $personData = new stdClass();
            $personData->personId = $this->personid;
            $contratos = AcdContract::listAvailableContractsForEnroll($personData);
            
            foreach ( $contratos as $contrato )
            {                
                if ( MatriculaWeb::matriculaAbertaNoPortal($contrato) )
                {
                    if( $MIOLO->checkAccess('FrmEnrollWebAluno', A_ACCESS, FALSE) && !$this->bloqueioPedagogico && $this->respondeuAvaliacao )
                    {
                        $panel->addAction(_M('Matrícula'), $ui->getImageTheme($module, 'matriculaWeb.png'), 'services', 'main:pupil:enrollWeb', NULL, array('returnTo' => 'PORTAL'));
                    }
                    break;
                }
            }

            //Mensagens
            if ( $MIOLO->checkAccess('FrmMensagensAluno', A_ACCESS, FALSE) && $this->respondeuAvaliacao && !$this->bloqueioPedagogico )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmMensagensAluno'), $ui->getImageTheme($module, 'mail.png'), $module, 'main:mensagens');
            }
            
            //Mural
            if ( $MIOLO->checkAccess('FrmMuralAluno', A_ACCESS, FALSE) && $this->respondeuAvaliacao && !$this->bloqueioPedagogico )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmMuralAluno'), $ui->getImageTheme($module, 'mural.png'), $module, 'main:mural');
            }
            
            //Perfil
            if ( $MIOLO->checkAccess('FrmPerfilAluno', A_ACCESS, FALSE) && $this->respondeuAvaliacao )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmPerfilAluno'), $ui->getImageTheme($module, 'perfil.png'), $module, 'main:perfil');
            }
            
            //Preferencias
            if ( $MIOLO->checkAccess('FrmPreferenciasAluno', A_ACCESS, FALSE) && $this->respondeuAvaliacao && !$this->bloqueioPedagogico )
            {
                $panel->addAction($busTransaction->getTransactionName('FrmPreferenciasAluno'), $ui->getImageTheme($module, 'preferences.png'), $module, 'main:preferencias');
            }
            
            //Solicitacao de protocolo
            if ( SAGU::getParameter('PROTOCOL', 'REQUEST_AUTOMATIC_NUMBER') == 'YES' )
            {
                if ( $MIOLO->checkAccess('FrmProtocoloAluno', A_ACCESS, FALSE) && $this->respondeuAvaliacao && !$this->bloqueioPedagogico )
                {
                    $panel->addAction($busTransaction->getTransactionName('FrmProtocoloAluno'), $ui->getImageTheme($module, 'protocol.png'), $module, 'main:protocolo');
                }
            }
        }
        
        $registroDeFrequencia = null;
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR )
        {
            $registroDeFrequencia = PrtDisciplinas::verificaRegistroDeFrequencia($this->personid);
        }
            
        if ( count($registroDeFrequencia) > 0 )
        {
            foreach ( $registroDeFrequencia as $groupId )
            {
                $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
                $groupData = $busGroup->getGroup($groupId[0]);
                $msg .= _M("Existe pendência na digitação de frequencia para a disciplina {$groupData->groupId} - {$groupData->curriculumCurricularComponentName} do curso de {$groupData->curriculumCourseName}/{$groupData->curriculumCourseVersion}, para o aluno de contrato {$groupId[1]}. <br>");
            }
            
            $busCompany = new BusinessBasicBusCompany();
            $ies = $busCompany->getCompany(SAGU::getParameter('BASIC', 'DEFAULT_COMPANY_CONF'));
            
            // Demanda solicitada pelo Brandao
            $recipient[] = 'luciano_brandao@solis.com.br';
            $subject = 'Pendências no registro de freqüencia- ' . $ies->acronym;
            $body = $msg;
            $mail = new sendEmail($from, $fromName, $recipient, $subject, $body, array());

            $mail->sendEmail();
        }
        
                
        //Aviso para mostrar que o usuario possui outros tipos de acesso
        if (prtUsuario::obterMultiplasInscricoes()
         || prtUsuario::obterMultiplosContratos()
         || prtUsuario::temMaisDeUmNivel() )
        {
            $fields[] = MMessage::getStaticMessage('msgInformation', _M('Caso algum item que esteja procurando não esteja aparecendo, verifique se você está no perfil correto. Clique em "Acessar como".'), MMessage::TYPE_WARNING);
        }
        
        // Aviso que evita problemas juridicos
        $fields[] = MMessage::getStaticMessage('msgConteudo', _M('Conteúdo meramente informativo/consultivo, podendo ser alterado a critério da Secretaria.'), MMessage::TYPE_WARNING);

        //Obter dados para verificacao de pendencias nos documentos
        $personId = $this->getPersonId();
        $seInformarNoPortal = $this->getInformarPendenciaNoPortal($personId); //aqui retorna se e para bloquear o portal ou somente mostrar mensagem
        $limiteDias= $this->getDiasParaBloquearPortal();
        $dataBlock = $this->getDataDeBloqueio(); // se vier NULL então deve bloquear o acesso ao portal
        $seEhAluno = FALSE;
        
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
        {
            $seEhAluno = TRUE;
        }

        //Se estiver configurado para mostrar mensagens e bloquear portal por falta de documetacao vai entrar aqui
        //mais detalhes na table "basDocumentType" da base
        if ( $seInformarNoPortal )
        {
            $listDocs = $this->getDocumentosPendentes($personId);

            // Somente mostrar a mensagem sobre documentos pendentes (APENAS MOSTRA A MENSAGEM, NAO FAZ NENHUM BLOQUEIO)
            if ( strlen($listDocs) > 0 && $limiteDias == null || strlen($listDocs) > 0 && !$seEhAluno )
            {
                $fields[] = MMessage::getStaticMessage('msgDoc', $listDocs, MMessage::TYPE_ERROR);
            }

            // Mensagem sobre documentos pendentes e ainda MOSTRA A DATA QUE VAI BLOQUEAR)
            if ( strlen($listDocs) > 0 && count($limiteDias) > 0 &&  $dataBlock != NULL && $seEhAluno)
            {
                $msgBlock = 'Caso não entregue até ' . $dataBlock . ' seu acesso ao portal será bloqueado, até que a pendência seja resolvida.';
                $fields[] = MMessage::getStaticMessage('msgDoc', $listDocs, MMessage::TYPE_ERROR);
                $fields[] = MMessage::getStaticMessage('msgDoc2', $msgBlock, MMessage::TYPE_ERROR);
            }
        }
        //Se o prazo para entregar os documentos venceu vai entrar aqui e bloquear o portal
        if ( $seInformarNoPortal &&  $dataBlock == NULL && $limiteDias != NULL && $seEhAluno)
        {
            $fields[] = MMessage::getStaticMessage('msgDoc', $listDocs, MMessage::TYPE_ERROR);
            $fields[] = new MSeparator();            
            $msgBlock3 = 'Seu acesso ao portal está temporariamente bloqueado até que esta pendência esteja resolvida.';
            $fields[] = MMessage::getStaticMessage('msgDoc3', $msgBlock3, MMessage::TYPE_ERROR);
        }
        else // se NAO estiver bloqueado vai entrar aqui e carregar o painel completo
        {
            $fields[] = $panel;
        }
        
	parent::addFields($fields);
    }
    
    /**
     * Obter o personId da pessoa que estÃ¡ logada.
     */    
    public function getPersonId()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        $busTransaction = new BusinessAdminTransaction();
        $prtDocumentos = new prtDocumentos();
        $documentos = $prtDocumentos->getDocumentos();
        
        $userMiolo = trim($MIOLO->getLogin()->id);
        $busPerson = new BusinessBasicBusPerson();
        $dataPerson = $busPerson->getPersonByMioloUserName($userMiolo);
        $personId = $dataPerson->personId;

        return $personId;
    }
    
    /**
     * Retorna uma lista dos documentos que estao pendentes para a pessoa logada (se tiver pendencia)
     */
    public function getDocumentosPendentes($personId)
    {        
        $busDocument = new BusinessBasicBusDocument();
        $listDoc = $busDocument->checkInformarPendenciaNoPortal($personId);

        $sep = new MSeparator();

        if (count($listDoc) > 0)
        {            
            $msg = _M('Prezado(a), o(s) seguinte(s) documento(s) está(ão) pendente(s) e deve(m) ser apresentado(s) na secretaria acadêmica:');
            
            foreach ( $listDoc as $value)
            {
                $msg.= $sep .$value[0];
            }
        }
        
        return $msg;
    }
    
    /**
     * Retorna a quantidade de dias que bloqueara o acesso ao portal;
     * SE nas configuracoes tiver MIS DE UM documento informando prazo
     * sera retornado a data mais proxima para bloqueio;
     * 
     * @param type $personId
     * @return type
     */
    public function getDiasParaBloquearPortal()
    {
        $busDocument = new BusinessBasicBusDocument();
        $dias = $listDoc = $busDocument->checkLimiteDeDiasPendenciaNoPortal();
        return $dias;
    }
        
/**
 * Retorna TRUE ou FALSE
 * se a configuracao esta ativa para mostrar a mensagem e bloquear o portal * 
 * 
 * @param type $personId
 * @return type Boolean
 */
    public function getInformarPendenciaNoPortal($personId)
    {
        $busDocument = new BusinessBasicBusDocument();        
        $result = $busDocument->checkInformarPendenciaNoPortal($personId);
        $informar = false;

        if ( $result != null )
        {
            $informar = true;
        }
        return $informar;
    }
    
/**
 * Retorna a data de bloqueio em que sera feito o bloqueio;
 * Se retornar NULL entao ja expirou o prazo e o portal sera bloqueado;
 * Ee tiver mais de um documento informando prazo para bloquear (nas configuracoes), será contada a data 
 * mais proxima possivel
 * 
 * @return string
 */
    public function getDataDeBloqueio()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        
        $personId = $this->getPersonId();

        //Processar aqui se for modulo ACADEMICO
        if ( SAGU::getParameter('BASIC', 'MODULE_ACADEMIC_INSTALLED') == 'YES'  && strlen(prtUsuario::obterContratoAtivo()) > 0)
        {
             $contractAtivo= PrtUsuarioSagu::obterContratosAtivosDaPessoa($personId);
             if ( $contractAtivo )
                {
                    foreach ( $contractAtivo as $value )
                        {
                            $contractIdAtivo = $value;
                        }

                   $busLearningPeriod = $MIOLO->getBusiness('academic', 'BusLearningPeriod');
                   $currentPeriodId = $busLearningPeriod->obterPeriodoAtual();
                   $learningPeriod = $busLearningPeriod->getLearningPeriodByContractAndPeriod($contractIdAtivo, $currentPeriodId);        
                   //$learningPeriodId = $learningPeriod->learningPeriodId; 

                   $beginDate = $learningPeriod->beginDate; //Data de inicio do semestre
                }
        }
        //Processar aqui se for modulo PEDAGOGICO
        else if ( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' )
        {

            $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
            $disciplinasPedagogico = new PrtDisciplinasPedagogico();

            //Periodos
            $dadosTurmas = $disciplinasPedagogico->obterDadosDasTurmasDoAluno($personId);
            $periodos = array_merge((array) $periodos, (array) $turmas);
            
            
            foreach ( $dadosTurmas as $value )
            {
                $dataInicialdaOferta = $value[3]; //Data de inicio da oferta de turma               
            }
            
            $beginDate = $dataInicialdaOferta;
        }
       
        $qtdDias = $this->getDiasParaBloquearPortal(); //quantidade de dias para bloquear portal obtido da configuracao
        
        $dataDeBloqueio = SAGU::addIntervalInDate($beginDate, 'd', $qtdDias);        
        $dataFinal = null;
        $hoje = SAGU::getDateNow();//que dia e hoje??
        $diasFaltando = SAGU::dateDiff($dataDeBloqueio, $hoje);//quantos dias faltam??
        if ( $diasFaltando >= 0 ) //se faltar 1 dia ou mais informa a data, se nao retorna NULL no final
        {
            $dataFinal = $dataDeBloqueio;
        }
        
        //$msgBlock = 'Caso nÃ£o entregue atÃ© ' . $dataDeBloqueio . ' seu acesso ao portal serÃ¡ bloqueado, atÃ© que a pendÃªncia seja resolvida.';
         
        return $dataFinal; //Retorna a data em que será feito o bloqueio do portal
    }

    /**
     * Valida se o aluno respondeu a avaliacao institucinal para poder visualizar suas notas e frequencias.
     * 
     * @return boolean
     */
    public function validaSeRespondeuAvaliacaoInstitucional()
    {        
        $MIOLO = MIOLO::getInstance();
        $respondeu = true;
        
        $frmDashboard = new frmDashboard(true);
        $avaliacoes = $frmDashboard->getPanels();
        
        $refPessoa = $MIOLO->getLogin()->id;
        $perfisPessoa = AManageLogin::getLoginProfiles($refPessoa);
        
        // Verifica se a pessoa esta relacionada a alguma avaliacao.
        if ( is_array($avaliacoes) )
        {
            $avaAvaliacao = new avaAvaliacao();  
            foreach ( $avaliacoes as $avaliacao )
            {                
                // Verifica se a avaliacao esta ativa.
                if ( $avaAvaliacao->checkAvaliacaoAtiva($avaliacao->data->idAvaliacao) )
                {   
                    $frmDashboard->login->perfis = $perfisPessoa;
                    $avaAvaliacao->idAvaliacao = $avaliacao->data->idAvaliacao;
                    
                    // Valida os formularios da avaliacao. Por exemplo, se ainda estao disponiveis.
                    if ( $frmDashboard->checkForms($avaAvaliacao) >= 1 )
                    {
                        // Verifica se a pessoa ja respondeu os formularios.
                        foreach ( $avaAvaliacao->formularios as $formKey => $formulario )
                        {
                            $data = new stdClass();
                            $data->refAvaliador = $refPessoa;
                            $data->refFormulario = $formulario->idFormulario;
                            $data->tipoAcao = avaFormLog::FORM_LOG_SUCCESS;
                            
                            $avaFormLog = new avaFormLog();
                            $avaFormLog->defineData($data);
                            
                            if ( is_null($avaFormLog->search()) )
                            {
                                $respondeu = false;
                            }
                        }
                    }
                }
            }
        }
        
        return $respondeu;
    }
    
    public function verificaBloqueioFinanceiro()
    {
        $MIOLO = MIOLO::getInstance();

        $busInvoice = $MIOLO->getBusiness('finance', 'BusInvoice');
        $busDocument = $MIOLO->getBusiness('basic', 'BusDocument');
        
        $msg = array();
        
        // Verifica se o aluno tem pendencias financeiras
        $temPendenciasFinanceiras = $busInvoice->isDefaulter($this->personid);
        if ( $temPendenciasFinanceiras )
        {
            $msg[] = '* ' . _M('Possui débitos financeiros.');
        }
        
        // Verifica se o aluno tem documentos nao entregues
        $documentosNaoEntregues = $busDocument->checkMissingDocuments($this->personid);
        $temDocumentosNaoEntregues = count($documentosNaoEntregues) > 0;
        if ( $temDocumentosNaoEntregues )
        {
            $msg[] = '* ' . _M('Possui documentos não entregues.');
        }
        
        if ( count($msg) > 0 )
        {
            $this->mensagemBloqueio = '<p style="margin-left: 14px;">' . _M('Você possui as seguintes Pendências:<br><br>') . implode('<br>', $msg);
        }
        
        return $temPendenciasFinanceiras || $temDocumentosNaoEntregues;
    }
}

?>
