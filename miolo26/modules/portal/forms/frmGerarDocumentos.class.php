<?php

$MIOLO->uses('classes/prtDocumentos.class.php', $module);
$MIOLO->uses('classes/prtDisciplinas.class.php', $module);
$MIOLO->uses('forms/frmDocumentos.class.php', $module);
class frmGerarDocumentos extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Gerador de documentos'));
        
        $this->setJsValidationEnabled(true);
    }
    
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $prtDocumentos = new prtDocumentos();
        $prtDisciplinas = new PrtDisciplinas();
        
        $perfil = MIOLO::_REQUEST('perfil');
        $groupId = MIOLO::_REQUEST('groupId');
        $file = MIOLO::_REQUEST('file');
        $fileModule = MIOLO::_REQUEST('fileModule');
                        
        $documentos = NULL;
        if ( $perfil == 'C')
        {
            $documentos = $prtDocumentos->getDocumentos('coordenador');
        }
        elseif ( $perfil == 'A' )
        {
            $documentos = $prtDocumentos->getDocumentos('aluno');
            
            if ( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' )
            {
                $prtDocumentosPedagogico = new prtDocumentosPedagogico();
                $documentosPedagogico = $prtDocumentosPedagogico->getDocumentos($this->personid);
                $documentos = array_merge((array)$documentos, (array)$documentosPedagogico);
            }
        }
        elseif ( $perfil == 'P' )
        {
            $documentos = $prtDocumentos->getDocumentos('professor');
        }
        
        $documento = $documentos[$file];
        if ( !$documento )
        {
            $documentos = $prtDocumentos->getDocumentosDisciplina();
            $documento = $documentos[$file];            
        }
        
        if ( $documento )
        {
            $documento instanceof BReport;
            $parametros = $documento->getParametersReport();
            
            unset($parametros['REPORT_INFO']);
            unset($parametros['SUBREPORT_DIR']);
            
            if ( count($parametros) <= 0 )
            {
                $fields[] = MMessageInformation::getStaticMessage('msgInfo', _M('Não há nenhum parâmetro para a geração deste relatório.'), MMessage::TYPE_INFORMATION);
            }
            else
            {
                foreach ( $parametros as $key => $parametro )
                {
                    $isBoolean = $parametro['type'] == 'boolean';
                    $isInteger = $parametro['type'] == 'integer';
                    $required = MUtil::getBooleanValue($parametro['required']);
                    
                    if ( $parametro['control'] )
                    {
                        switch ( $parametro['control'] )
                        {
                            case 'selection':
                                if ( $parametro['options'] )
                                {
                                    
                                    $optionsExplode = explode(';', $parametro['options']);
                                    $options = array();
                                    foreach ( $optionsExplode as $optKey => $option )
                                    {
                                        $item = explode('=', $option);
                                        $item[0] = trim($item[0]);
                                        if ( strlen($item[0]) > 0 )
                                        {
                                            if ( $isBoolean )
                                            {
                                                $item[0] = MUtil::getBooleanValue($item[0]);
                                            }
                                            
                                            $options[$item[0]] = $item[1];
                                        }
                                    }

                                }
                                elseif ( $parametro['query'] )
                                {
                                    $sql = str_replace(array('$P{personid}', '$P{personId}'), $this->personid, $parametro['query']);
                                    $sql = str_replace(array('$P{groupid}', '$P{groupId}'), $groupId, $sql);
                                    $sql = str_replace(array('$coordinatorId', '$coordinatorid'), $this->personid, $sql);
                                    $options = bBaseDeDados::obterInstancia()->_db->query($sql);
                                }
                                
                                $fieldName = "parametros[$key]";
                                if ( $isBoolean )
                                {
                                    $fieldName = "parametros[boo_$key]";
                                }
                                elseif ( $isInteger )
                                {
                                    $fieldName = "parametros[int_$key]";
                                }
                                
                                $campos[] = new MSelection($fieldName, $parametro['value'], $parametro['label'], is_array($options) ? $options : array(), FALSE, '', '', !$required);
                                break;
                                
                            case 'calendar':
                                $campos[] = new MCalendarField("parametros[$key]", $parametro['value'], $parametro['label'], 40, $parametro['help']);
                                if ( $required )
                                {
                                    $validadores[] = new MRequiredValidator("parametros[$key]", $parametro['label']);
                                }
                                break;

                            default:
                                if ( $key != 'personid' && $key != 'contractid' )
                                {
                                    $campos[] = $field = new MTextField("parametros[$key]", $parametro['value'], $parametro['label'], 40, $parametro['help']);

                                    $key == 'personid' || $key == 'contractid';

                                    if ( $required )
                                    {
                                        $validadores[] = new MRequiredValidator("parametros[$key]", $parametro['label']);
                                    }
                                }
                                break;
                        }
                    }
                }
            }
            
            if ( is_array($validadores) )
            {
                $this->setValidators($validadores);
            }
                       
            $fields[] = new MDiv('divTitle', '<br><br>');
            $fields[] = new MFormContainer('frmContainer', $campos);
            $fields[] = MUtil::centralizedDiv(new MButton('btnGerarRelatorio', _M('Gerar relatório')));            
        }
        elseif ( strlen($fileModule) > 0 )
        {
            $reportFile = $MIOLO->getConf('options.miolo2modules') . '/' . $fileModule . '/reports/' . $file . '.jrxml';
            
            if (file_exists($reportFile) )
            {
                switch ($file)
                {
                    case 'bulletinOfNotesAndFrequencies':
                        $options = $prtDisciplinas->obterPeriodosDoAluno($this->personid, 'A.periodId DESC');
                        $campos[] = new MSelection('periodId', NULL, _M('Período'), is_array($options) ? $options : array());
                        $fields[] = new MDiv('divTitle', '<br><br>');
                        $fields[] = new MFormContainer('frmContainer', $campos);
                        $fields[] = MUtil::centralizedDiv(new MButton('btnBoletim', _M('Gerar boletim de notas e frequências')));
                        break;
                }                
            }
            else
            {
                new MMessageWarning(_M('O arquivo do relatório não foi encontrado.'));
            }
        }
        else
        {
            new MMessageWarning(_M('O arquivo do relatório não foi encontrado.'));
        }
        
        parent::addFields($fields);
    }
   
    public function btnBoletim_click($args)
    {
        if ( !$args->periodId )
        {
            new MMessageWarning(_M('Por favor, selecione o período.'));
        }
        else
        {
            $MIOLO = MIOLO::getInstance();
            $login = $MIOLO->getLogin();            
            $frmDocumentos = new frmDocumentos();
            $reportFile = $frmDocumentos->encontrarArquivo('bulletinOfNotesAndFrequencies', 'academic');
            $saguPath = $MIOLO->getConf("home.modules"). '/basic/reports/';
            $saguPath = str_replace('miolo26', 'miolo20', $saguPath);

            if ( file_exists($reportFile) )
            {
                $report = new SReport(array(
                    'module' => 'portal',
                    'reportName' => $reportFile,
                    'parameters' => array(
                        'int_contractid' => $_SESSION['contractId'],
                        'periodid' => $args->periodId,
                        'str_SAGU_PATH' => $saguPath,
                        'str_username' => $login->id
                        )
                ));

                if ( !$report->generate() )
                {
                    new MMessageWarning(_M('Não foi possível gerar o documento.' ));
                }
            }
            else
            {
                new MMessageWarning(_M('O arquivo do boletim de notas e frequências não foi encontrado.'));
            }
        }
    }
    
    public function btnGerarRelatorio_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/sreport.class', 'basic');
        $sReport = new SReport();
        
        $perfil = $args->perfil;
        $file = $args->file;
        
        $prtDocumentos = new prtDocumentos();
        $documentos = NULL;
        
        if ( $perfil == 'C')
        {
            $documentos = $prtDocumentos->getDocumentos('coordenador');
        }
        elseif ( $perfil == 'A' )
        {
            $documentos = $prtDocumentos->getDocumentos('aluno');
            
            if ( SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED') == 'YES' )
            {
                $prtDocumentosPedagogico = new prtDocumentosPedagogico();
                $documentosPedagogico = $prtDocumentosPedagogico->getDocumentos($this->personid);
                $documentos = array_merge((array)$documentos, (array)$documentosPedagogico);
            }
        }
        elseif ( $perfil == 'P' )
        {
            $documentos = $prtDocumentos->getDocumentos('professor');
        }
        
        $documento = $documentos[$file];
        if ( !$documento )
        {
            $documentos = $prtDocumentos->getDocumentosDisciplina();
            $documento = $documentos[$file];
        }
        
        if ( $documento )
        {
            $documento instanceof BReport;
            $docInfo = $documento->getReportInfo($documento->getReportFile());
            
            $parametrosRelatorio = $documento->getParametersReport();
            
            $personid = ($parametrosRelatorio['personid']['type'] == 'string') ? 'str_personid' : 'int_personid';
            $personId = ($parametrosRelatorio['personId']['type'] == 'string') ? 'str_personId' : 'int_personId';
            $contractid = ($parametrosRelatorio['contractid']['type'] == 'string') ? 'str_contractid' : 'int_contractid';
            $contractId = ($parametrosRelatorio['contractId']['type'] == 'string') ? 'str_contractId' : 'int_contractId';
                        
            $parametros = $args->parametros;
            $parametros[$personid] = $parametros['personid'] ? $parametros['personid'] : $this->personid;
            $parametros[$personId] = $parametros['personId'] ? $parametros['personId'] : $this->personid;
            $parametros[$contractid] = $parametros['contractid'] ? $parametros['contractid'] : $_SESSION['contractId'];
            $parametros[$contractId] = $parametros['contractId'] ? $parametros['contractId'] : $_SESSION['contractId'];
            $parametros['SUBREPORT_DIR'] = str_replace('miolo26', 'miolo20', $MIOLO->getConf('home.miolo') . '/cliente/iReport/' . $module . '/reports/');
            $parametros['SAGU_PATH'] = str_replace('miolo26', 'miolo20', $MIOLO->getConf('home.miolo') . '/modules/basic/reports/');
            $parametros['COD_VERIFICADOR_MSG'] = utf8_encode($sReport->obterCodigoVerificadorMsg());

            // Remover os parâmetros em branco para evitar erros no Jasper.
            foreach ($parametros as $key => $parametro )
            {
                if ( strlen($parametro) == 0 )
                {
                    unset($parametros[$key]);
                }
            }
            
            $report = new SReport(array(
                'module' => $module,
                'reportName' => $docInfo['filepath'],
                'parameters' => $parametros
            ));

            if ( !$report->generate() )
            {
                new MMessageWarning(_M('Não foram encontrados dados para a geração do documento.'));
            }
            else
            {
                new MMessageSuccess(_M('Documento gerado com sucesso.'));
            }
        }
        else
        {
            new MMessageWarning(_M('O arquivo do relatório não foi encontrado.'));
        }
        
        $this->setNullResponseDiv();
    }
}

?>
