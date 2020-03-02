<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Luís Felipe Wermann [luis_felipe@solis.com.br]
 *
 * \b Maintainers: \n
 * Luís Felipe Wermann [luis_felipe@solis.com.br]
 *
 * @since
 * Creation date 05/05/2015
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2015 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */

$MIOLO->uses('forms/frmMobile.class.php', 'portal');
$MIOLO->uses('classes/prtDocumentos.class.php', 'portal');
$MIOLO->uses('classes/prtDocumentosPedagogico.class.php', 'portal');
$MIOLO->uses('classes/prtDocumentosPedagogico.class.php', 'portal');
$MIOLO->uses('types/AcpOfertaTurma.class', 'pedagogico');
$MIOLO->uses('types/AcpOfertaCurso.class', 'pedagogico');
$MIOLO->uses('types/AcpOfertaComponenteCurricular.class', 'pedagogico');
$MIOLO->uses('types/AcpComponenteCurricularMatriz.class', 'pedagogico');
$MIOLO->uses('types/AcpComponenteCurricular.class', 'pedagogico');
$MIOLO->uses('types/AcpComponenteCurricularDisciplina.class', 'pedagogico');
$MIOLO->uses('types/AcpComponenteCurricularTrabalhoConclusao.class', 'pedagogico');
$MIOLO->uses('types/AcpMatrizCurricularGrupo.class', 'pedagogico');
$MIOLO->uses('classes/breport.class.php', 'base');
$MIOLO->uses('classes/prtUsuario.class.php', 'portal');
$MIOLO->uses('db/BusCourse.class', 'academic');

class frmDocumentosPai extends frmMobile
{    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Documentos', MIOLO::getCurrentModule()));
    }
    
    /**
     * Obtem documentos do portal e ajusta as pastas e arquivos.
     * 
     * @param control - Painel do formulário.
     * @return control - Painel ajustado.
     */
    public function obterDocumentosPortal($panel)
    {
        $class = 'mPanellCell' . ucfirst($panel->iconType);
        
        // Documentos do portal (cadastrados em Básico::Configurações::Documentos do Portal)
        $docsPortal = BasDocumentosPortal::listarDocumentosPortalDisponiveisParaOUsuario(prtUsuario::obterTipoDeAcesso(), $this->personid, prtUsuario::obterContratoAtivo(), prtUsuario::obterInscricaoAtiva());

        // Agrupar documentos para trabalhar com as pastas
        $docAgrup = $this->agruparDocumentosPorChave($docsPortal);
        
        // Elementos da URL
        $dentroDePasta = MIOLO::_REQUEST('pasta');
        $ofertaCursoId = MIOLO::_REQUEST('ofertaCursoId');
        $ofertaTurmaId = MIOLO::_REQUEST('ofertaTurmaId');
        $ofertaComponenteCurricularId = MIOLO::_REQUEST('ofertaComponenteCurricularId');
        $courseId = MIOLO::_REQUEST('courseId');
        
        // Senão estiver dentro de uma pasta, busca os documentos principais
        $finalPanel = $panel;
        if ( $dentroDePasta != DB_TRUE )
        {
            $finalPanel = $this->obterDocumentosPrincipais($panel, $docAgrup);
        }
        elseif ( strlen($ofertaComponenteCurricularId) > 0 )
        {
            // Cria documentos das ofertas
            foreach ( $docAgrup['ofertaCurso'][$ofertaCursoId]['ofertaTurma'][$ofertaTurmaId]['ofertaComponenteCurricular'][$ofertaComponenteCurricularId] as $key => $doc)
            {
                if ( is_numeric($key) )
                {
                    $finalPanel->addControl($this->criarDocumento($finalPanel, $doc));
                }
            }
        }
        elseif ( strlen($ofertaTurmaId) > 0 )
        {
            // Criar pastas de oferta de componente curricular e os documentos da oferta de turma
            foreach ( $docAgrup['ofertaCurso'][$ofertaCursoId]['ofertaTurma'][$ofertaTurmaId] as $key => $doc )
            { 
                if ( is_numeric($key) )
                {
                    $finalPanel->addControl($this->criarDocumento($finalPanel, $doc));
                }
                elseif( $key == 'ofertaComponenteCurricular' && count($doc) > 0 )
                {
                    $ofertaComponentesId = array_keys($doc);
                
                    foreach ( $ofertaComponentesId as $keyOfertaComponente )
                    {
                        $ofertaComponente = new AcpOfertaComponenteCurricular($keyOfertaComponente);
                        $chaves = array('ofertaCursoId' => $ofertaComponente->ofertaturma->ofertacursoid, 'ofertaTurmaId' => $ofertaComponente->ofertaturmaid, 'ofertaComponenteCurricularId' => $keyOfertaComponente);
                        $finalPanel->addControl($this->criarPasta($finalPanel, $chaves, "Componente curricular <br>" . $ofertaComponente->componentecurricularmatriz->componentecurricular->descricao), '', 'left', $class);
                    }
                }
            }
        }
        elseif ( strlen($ofertaCursoId) > 0 )
        {
            // Criar pastas da oferta de turma e os documentos da oferta de curso
            foreach ( $docAgrup['ofertaCurso'][$ofertaCursoId] as $key => $doc )
            { 
                if ( is_numeric($key) )
                {
                    $finalPanel->addControl($this->criarDocumento($finalPanel, $doc));
                }
                elseif( $key == 'ofertaTurma' && count($doc) > 0 )
                {
                    $ofertaTurmaIds = array_keys($doc);
                
                    foreach ( $ofertaTurmaIds as $keyOfertaTurma )
                    {
                        $ofertaTurma = new AcpOfertaTurma($keyOfertaTurma);
                        $chaves = array('ofertaCursoId' => $ofertaTurma->ofertacursoid, 'ofertaTurmaId' => $keyOfertaTurma);
                        $finalPanel->addControl($this->criarPasta($finalPanel, $chaves, "Turma " . $ofertaTurma->descricao), '', 'left', $class);
                    }
                }
            }
        }
        elseif ( strlen($courseId) > 0 )
        {
            // Cria documentos do curso
            foreach ( $docAgrup['courseId'][$courseId] as $key => $doc)
            {
                $finalPanel->addControl($this->criarDocumento($finalPanel, $doc));
            }
        }

        return $finalPanel;
    }
    
    /**
     * Filtra os documentos quando não se está dentro de nenhuma pasta.
     * 
     * @param control $panel
     * @param Array $docAgrup
     * @return control
     */
    public function obterDocumentosPrincipais($panel, $docAgrup)
    {
        $class = 'mPanellCell' . ucfirst($panel->iconType);
        
        foreach ( $docAgrup as $key => $doc )
        {
            // Senão estiver dentro de uma pasta e forem os documentos sem atrelamentos
            if ( $key == 'livre')
            {
                foreach ( $doc as $linha )
                {
                    $panel->addControl($this->criarDocumento($panel, $linha),'','left',$class); 
                }
            }
            
            // Senão estiver dentro de pasta, mostra todas as pastas de oferta de curso
            if ( $key == 'ofertaCurso' && count($docAgrup['ofertaCurso']) > 0 )
            {
                $ofertaCursoIds = array_keys($doc);
                
                foreach ( $ofertaCursoIds as $keyOfertaCurso )
                {
                    $ofertaCurso = new AcpOfertaCurso($keyOfertaCurso);
                    $chaves = array('ofertaCursoId' => $keyOfertaCurso);
                    $panel->addControl($this->criarPasta($panel, $chaves, "Curso " . $ofertaCurso->descricao), '', 'left', $class);
                }
            }
            
            // Senão estiver dentro de pasta, mostra todas as pastas de course (acadêmico)
            if ( $key == 'courseId' && count($docAgrup['courseId']) > 0 )
            {
                $courseIds = array_keys($doc);
                
                foreach ( $courseIds as $keyCourseId )
                {
                    $busCourse = new BusinessAcademicBusCourse();
                    $course = $busCourse->getCourse($keyCourseId);
                    $chaves = array('courseId' => $keyCourseId);
                    $panel->addControl($this->criarPasta($panel, $chaves, "Curso " . $course->name), '', 'left', $class);
                }
            }
        }
        
        return $panel;
    }
    
    /**
     * Agrupar em chaves os documentos do portal, para poder trabalhar com as pastas.
     * 
     * @param array $docsPortal
     * @return array
     */
    public function agruparDocumentosPorChave($docsPortal)
    {
        $docAgrup = array();
        
        foreach ( $docsPortal as $doc )
        {
            if ( strlen($doc->ofertaComponenteCurricularId) > 0 )
            {
                $ofertaComponente = new AcpOfertaComponenteCurricular($doc->ofertaComponenteCurricularId);
                $docAgrup['ofertaCurso'][$ofertaComponente->ofertaturma->ofertacursoid]['ofertaTurma'][$ofertaComponente->ofertaturmaid]['ofertaComponenteCurricular'][$doc->ofertaComponenteCurricularId][] = $doc;
            }
            elseif ( strlen($doc->ofertaTurmaId) > 0 )
            {
                $ofertaTurma = new AcpOfertaTurma($doc->ofertaTurmaId);
                $docAgrup['ofertaCurso'][$ofertaTurma->ofertacursoid]['ofertaTurma'][$doc->ofertaTurmaId][] = $doc;
            }
            elseif ( strlen($doc->ofertaCursoId) > 0 )
            {
                $docAgrup['ofertaCurso'][$doc->ofertaCursoId][] = $doc;
            }
            elseif ( strlen($doc->courseId) > 0 )
            {
                $docAgrup['courseId'][$doc->courseId][] = $doc;
            }
            else
            {
                $docAgrup['livre'][] = $doc;
            }
        }
        
        return $docAgrup;
    }
    
    /**
     * Cria o control da pasta para adicionar no painel do formulário.
     * 
     * @param control $panel
     * @param Array $chaves
     * @param string $nome
     * 
     * @return 
     */
    public function criarPasta($panel, $chaves, $nome)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $ui = $MIOLO->getUI();
        $imagePasta = $ui->getImageTheme($module, 'pasta.png');
        
        // Adiciona chaves comuns à pasta
        $chaves['perfil'] = prtUsuario::obterTipoDeAcesso();
        $chaves['pasta'] = DB_TRUE;
        
        $urlPasta = $MIOLO->getActionURL('portal', 'main:documentos', null, $chaves);
        $controlFolder = $panel->_getControl(_M('Materiais <br>' . $nome), $imagePasta, $urlPasta);
        
        return $controlFolder;
    }
    
    /**
     * Cria o control do documento para adicionar no painel do formulário.
     * 
     * @param control $panel
     * @param stdClass $documento
     * @return
     */
    public function criarDocumento($panel, $documento)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $ui = $MIOLO->getUI();
        $imageDocumentos = $ui->getImageTheme($module, 'docs.png');
        
        // Rotina emergencial para exibir todo o nome do documento
        $documento->titulo = $this->verificaTamanhoTitulo($documento->titulo);
        $countBr = substr_count($documento->titulo, "<br>");
        $size = $countBr > 0 ? (20 - (3 * $countBr)) : 20;

        $control = $panel->_getControl($documento->titulo, $imageDocumentos, '', null, (string)$size);
        $control->href = $documento->url . '" target="_blank" title="' . $documento->titulo . '"';
        
        return $control;
    }
    
    /*
     * Busca apenas os parâmetros interessantes ao SAGU.
     * Elimina, por exemplo: SAGU_PATH, REPORT_INFO, parâmetros hidden.
     * 
     * @return - Array contendo os parâmetros a serem efetivamente substituídos.
     */
    public function validarParametrosDoRelatorio($documento)
    {
        $params = $documento->getParametersReport();
        
        //Elimina parâmetros comuns ao SAGU
        unset($params['SAGU_PATH']);
        unset($params['REPORT_INFO']);
        
        //Elimina parâmetros hidden e que não possuam algum tipo de control
        $finalParams = array();
        foreach ( $params as $name => $content )
        {
            if ( strlen($content['control']) > 0 && $content['hidden'] != DB_TRUE )
            {
                $finalParams[$name] = $params[$name];
            }
        }
        
        return $finalParams;
    }
    
    /**
     * Adiciona quebra de linha no título do documento
     * 
     * @param type $titulo
     * @param type $size
     * @return type string
     */
    public function verificaTamanhoTitulo($titulo, $size = 40)
    {
        if ( strlen($titulo) > $size )
        {
            for ( $i = $size; $i < strlen($titulo); $i++ )
            {
                if ( $titulo[$i] == ' ' )
                {
                    $label = substr_replace($titulo, '<br>', $i, 1);
                    $i = $i + $size;
                    $titulo = $label;
                }
            }
        }
        
        return strlen($label) > 0 ? $label : $titulo;
    }
    
    public function gerarDocumento($filePath)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $prtDocumentos = new prtDocumentos();

        if ( MIOLO::_REQUEST('perfil') == 'A' )
        {
            $documentos = $prtDocumentos->getDocumentos('aluno');
        }
        elseif ( MIOLO::_REQUEST('perfil') == 'C' )
        {
            $documentos = $prtDocumentos->getDocumentos('coordenador');
        }
        elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR )
        {
            $documentos = $prtDocumentos->getDocumentos('professor');
        }

        if ( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' )
        {
            $prtDocumentosPedagogico = new prtDocumentosPedagogico();
            $documentosPedagogico = $prtDocumentosPedagogico->getDocumentos($this->personid);
            $documentos = array_merge((array)$documentos, (array)$documentosPedagogico);
        }

        $file = basename($filePath);
        $documento = $documentos[$file];
        if ( !$documento )
        {
            $documentos = $prtDocumentos->getDocumentosDisciplina();
            $documento = $documentos[$file];
        }

        $documento instanceof BReport;
        
        $docInfo = $documento->getReportInfo($documento->getReportFile());
        $docParametros = $this->validarParametrosDoRelatorio($documento);
        unset($docParametros['REPORT_INFO']);
        unset($docParametros['SUBREPORT_DIR']);
        $subReportDir = str_replace('miolo26', 'miolo20', $MIOLO->getConf('home.miolo') . '/cliente/iReport/' . $module . '/reports/');
        
        // Se tiver apenas o personId, gera com o personid da pessoa logada.
        if ( $prtDocumentos->hasPersonId($docParametros) && count($docParametros) == 1 )
        {
            $report = new SReport(array(
                'module' => $module,
                'reportName' => $filePath,
                'parameters' => array(
                    'personid' => $this->personid,
                    'personId' => $this->personid,
                    'SUBREPORT_DIR' => $subReportDir
                )
            ));
            
            if ( !$report->generate() )
            {
                new MMessageWarning(_M('Não foi possível gerar o documento.', $module ));
            }
        }
        // Se tiver apenas o groupId, gera com o groupId da disciplina.
        elseif ( $prtDocumentos->hasGroupId($docParametros) && count($docParametros) == 1 )
        {
            $report = new SReport(array(
                'module' => $module,
                'reportName' => $filePath,
                'parameters' => array(
                    'groupid' => MIOLO::_REQUEST('groupid'),
                    'groupId' => MIOLO::_REQUEST('groupid'),
                    'SUBREPORT_DIR' => $subReportDir
                )
            ));
            
            if ( !$report->generate() )
            {
                new MMessageWarning(_M('Não foi possível gerar o documento.', $module ));
            }
        }
        
        // Se tiver o groupId e o personId
        elseif ( $prtDocumentos->hasPersonId($docParametros) && $prtDocumentos->hasGroupId($docParametros) && count($docParametros) == 2 )
        {
            $report = new SReport(array(
                'module' => $module,
                'reportName' => $filePath,
                'parameters' => array(
                    'int_personid' => $this->personid,
                    'int_personId' => $this->personid,
                    'int_groupid' => MIOLO::_REQUEST('groupid'),
                    'int_groupId' => MIOLO::_REQUEST('groupid'),
                    'SUBREPORT_DIR' => $subReportDir
                )
            ));
            
            if ( !$report->generate() )
            {
                new MMessageWarning(_M('Não foi possível gerar o documento.', $module ));
            }
        }
            
        // Se tiver apenas o contractId, gera com o contractid da pessoa logada.
        elseif ( $prtDocumentos->hasContractId($docParametros) && count($docParametros) == 1 )
        {
            $report = new SReport(array(
                'module' => $module,
                'reportName' => $filePath,
                'parameters' => array(
                    'contractid' => $_SESSION['contractId'],
                    'contractId' => $_SESSION['contractId']
                )
            ));
            
            if ( !$report->generate() )
            {
                new MMessageWarning(_M('Não foi possível gerar o documento.', $module ));
            }
        }
        // Se possui inscrição no pedagógico ativa
        elseif ( $prtDocumentos->hasInscricaoId($docParametros) && count($docParametros) == 1 )
        {   
            $report = new SReport(array(
                'module' => $module,
                'reportName' => $filePath,
                'parameters' => array(
                    'int_inscricaoId' => (int) $_SESSION['inscricaoId'],
                    'int_inscricaoid' => (int) $_SESSION['inscricaoId'],
                    'str_inscricaoId' => $_SESSION['inscricaoId'],
                    'str_inscricaoid' => $_SESSION['inscricaoId']
                )
            ));
            
            if ( !$report->generate() )
            {
                new MMessageWarning(_M('Não foi possível gerar o documento.', $module ));
            }
        }
        //vai para o formulario de geracao de documentos do professor se for professor
        elseif( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR )
        {
            $MIOLO = MIOLO::getInstance();
            $module = MIOLO::getCurrentModule();

            $url = $MIOLO->getActionURL($module, 'main:gerarDocumentos', '', array(
                'groupId' => MIOLO::_REQUEST('groupid'),
                'file' => $file,
                'perfil' => 'P'
            ));
            
            $this->page->redirect($url);
        }
        // Senão vai para formulário de geração de documentos.
        else
        {
            $MIOLO = MIOLO::getInstance();
            $module = MIOLO::getCurrentModule();

            $url = $MIOLO->getActionURL($module, 'main:gerarDocumentos', '', array(
                'file' => $file,
                'perfil' => MIOLO::_REQUEST('perfil')
            ));

            $this->page->redirect($url);
        }

        $this->setNullResponseDiv();
        
    }
}