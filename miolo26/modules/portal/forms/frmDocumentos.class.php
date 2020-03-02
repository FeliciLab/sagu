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

class frmDocumentos extends frmDocumentosPai
{
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();

        $prtDocumentos = new prtDocumentos();
        $panel = new mobilePanel('panel');
        
        if ( MIOLO::_REQUEST('pasta') != DB_TRUE )
        {
            if ( MIOLO::_REQUEST('perfil') == 'A' )
            {
                $documentos = $prtDocumentos->getDocumentos('aluno');
            }
            elseif ( MIOLO::_REQUEST('perfil') == 'C' )
            {
                $documentos = $prtDocumentos->getDocumentos('coordenador');
            }

            if ( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' )
            {
                $prtDocumentosPedagogico = new prtDocumentosPedagogico();
                $documentosPedagogico = $prtDocumentosPedagogico->getDocumentos($this->personid);
                $documentos = array_merge((array)$documentos, (array)$documentosPedagogico);
            }

            $image = $ui->getImageTheme($module, 'notas.png');
            foreach( $documentos as $documento )
            {
                $documento instanceof BReport;
                $docInfo = $documento->getReportInfo($documento->getReportFile());

                $panel->addActionAJAX($docInfo['title'], $image, 'gerarDocumento', $docInfo['filepath']);
            }

            if ( MIOLO::_REQUEST('perfil') == 'A' )
            {
                if ( $MIOLO->checkAccess('DocHistoricoEscolar', A_ACCESS, FALSE) )
                {
                    $panel->addActionAJAX(_M('Histórico escolar'), $image, 'gerarHistoricoEscolar');
                }

                if ( $MIOLO->checkAccess('DocAtestadoMatricula', A_ACCESS, FALSE) )
                {
                    $panel->addActionAJAX(_M('Atestado de matrícula'), $image, 'gerarAtestadoDeMatricula');
                }

                if ( $MIOLO->checkAccess('DocBoletimNotasFrequencia', A_ACCESS, FALSE) )
                {
                    $panel->addAction(_M('Boletim de notas e frequências'), $image, $module, 'main:gerarDocumentos', NULL, array(
                        'file' => 'bulletinOfNotesAndFrequencies',
                        'fileModule' => 'academic',
                        'perfil' => MIOLO::_REQUEST('perfil')
                    ));
                }

                if ( $MIOLO->checkAccess('DocEmentario', A_ACCESS, FALSE) )
                {
                    $panel->addActionAJAX(_M('Ementário'), $image, 'gerarEmentario');
                }
            }
            elseif ( MIOLO::_REQUEST('perfil') == 'C' )
            {
                if ( $MIOLO->checkAccess('DocHorasProfessores', A_ACCESS, FALSE) )
                {
                    $panel->addAction(_M('Planilha de horas dos professores'), $image, $module, 'main:horasprofessores');
                }

                if ( $MIOLO->checkAccess('DocEstadoDisciplinas', A_ACCESS, FALSE) )
                {
                    $panel->addAction(_M('Estado das disciplinas dos professores'), $image, $module, 'main:estadodisciplinas');
                }

                if ( $MIOLO->checkAccess('DocPossibilidadeDeMatriculaPorDisciplina', A_ACCESS, FALSE) )
                {
                    $panel->addAction(_M('Possibilidades de matrícula por disciplina'), $image, $module, 'main:possibilidadeDeMatriculaPorDisciplina');
                }
            }
        }

        $finalPanel = $this->obterDocumentosPortal($panel);
        
        $fields[] = $finalPanel;
        
	parent::addFields($fields);
    }
    
    public function gerarEmentario()
    {
        $reportFile = $this->encontrarArquivo('ementario', 'academic');
        
        if ( file_exists($reportFile) )
        {
            $report = new SReport(array(
                'module' => 'portal',
                'reportName' => $reportFile,
                'parameters' => array(
                    'int_contractid' => $_SESSION['contractId'],
                    'int_contractId' => $_SESSION['contractId']
                )
            ));
            
            if ( !$report->generate() )
            {
                new MMessageWarning(_M('Não foi possível gerar o documento.'));
            }
        }
        else
        {
            new MMessageWarning(_M('O arquivo de atestado não foi encontrado.'));
        }
    }
    
    public function gerarAtestadoDeMatricula()
    {
        $reportFile = $this->encontrarArquivo('enrollCertified', 'academic');
        
        if ( file_exists($reportFile) )
        {   
            $report = new SReport(array(
                'module' => 'portal',
                'reportName' => $reportFile,
                'parameters' => array(
                'int_contractid' => $_SESSION['contractId'],
                'int_contractId' => $_SESSION['contractId']
                )
            ));
            
            if ( !$report->generate() )
            {
                new MMessageWarning(_M('Não foi possível gerar o documento.'));
            }
        }
        else
        {
            new MMessageWarning(_M('O arquivo de atestado não foi encontrado.'));
        }
    }
    
    public function gerarHistoricoEscolar()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getClass('academic', 'RptScholarshipDescription');
        
        $fileName = '/reports/hist_escolar_' . $_SESSION['contractId'] . '.pdf';
        $reportPath = $MIOLO->getConf('options.miolo2modules');
        $reportPath = str_replace('modules', 'html', $reportPath);
        $reportPath .= $fileName;
        
        $data = new stdClass();
        $data->contractId = $_SESSION['contractId'];
        $data->portal = true;
        $data->fileName = $reportPath;
        
        $report = new RptScholarshipDescription($data);
        
        $url = $MIOLO->getAbsoluteURL($fileName);
        $url = str_replace('miolo26', 'miolo20', $url);

        $this->page->redirect($url);
        
        $this->setNullResponseDiv();
    }
    
    public function encontrarArquivo($fileName, $module = 'portal')
    {
        // Primeiro tenta obter o relatório na estrutura dinâmica
        $bReport = new BReport();
        $path = $bReport->getReportPaths('portal');        
        $reportName = $fileName . '_User';
        $reportFile = NULL;
        
        foreach ( $path as $p )
        {
            if ( file_exists($p . '/' . $reportName . '.jrxml') )
            {
                $reportFile = $p . '/' .  $reportName;
                break;
            }
        }
        
        if ( !$reportFile )
        {
            $reportName = $fileName;
            foreach ( $path as $p )
            {
                if ( file_exists($p . '/' .  $reportName . '.jrxml') )
                {                    
                    $reportFile = $p . '/' .  $reportName;
                    break;
                }
            }
        }
        
        // Se não, obtem o arquivo do módulo acadêmico
        if ( !$reportFile )
        {
            $MIOLO = MIOLO::getInstance();
            $reportFile = $MIOLO->getConf('options.miolo2modules') . '/' . $module . '/reports/' . $reportName . '.jrxml';
        }
        
        return $reportFile;
    }
}

?>
