<?php

$MIOLO->uses('classes/breport.class', 'base');
$MIOLO->uses('classes/prtTransaction.class.php', 'portal');
$MIOLO->uses('types/BasDocumentosPortal.class.php', 'basic');

class prtDocumentos
{
    
    private $reportsPath = null;
    
    private $documentosDisciplina = array();
    private $documentos = array();
    
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $bReport = new BReport();
        $this->reportsPath = $bReport->getReportPaths('portal');
        
        $this->reportsPath[] = $MIOLO->getAbsolutePath('reports/aluno', $module);
        $this->reportsPath[] = $MIOLO->getAbsolutePath('reports/professor', $module);
        $this->reportsPath[] = $MIOLO->getAbsolutePath('reports/coordenador', $module);
        
        foreach ( $this->reportsPath as $reportPath )
        {
            if ( substr_count($reportPath, 'pupil') > 0 || substr_count($reportPath, 'aluno') > 0 || substr_count($reportPath, 'professor') > 0 || substr_count($reportPath, 'coordenador') > 0 )
            {
                $dir = dir($reportPath);

                if ( $dir )
                {
                    while ( $file = $dir->read() )
                    {
                        if ( substr($file, -5) == 'jrxml' )
                        {
                            $report = new BReport($file, 'portal');
                            $parameters = $report->getParametersReport();
                            
                            if ( substr_count($reportPath, 'professor') > 0 )
                            {
                                if ( $this->hasGroupId($parameters) )
                                {
                                    $this->documentosDisciplina[$file] = $report;                                    
                                }
                                else
                                {
                                    $this->documentos['professor'][$file] = $report;
                                }
                            }
                            
                            if ( substr_count($reportPath, 'aluno') > 0 || substr_count($reportPath, 'pupil') > 0 )
                            {
                                $this->documentos['aluno'][$file] = $report;
                            }
                            
                            if ( substr_count($reportPath, 'coordenador') > 0 )
                            {
                                $this->documentos['coordenador'][$file] = $report;
                            }
                        }
                    }
                    
                    $dir->close();
                }
            }
        }
        
        $prtTransaction = new prtTransaction();
        
        // Documentos professor
        $docsProfessor = $prtTransaction->obterTransacoesDeDocumentosDoPortal('professor');
        foreach ( $docsProfessor as $docProfessor )
        {
            $info = explode('|', $docProfessor[4]);
            $file = $info[1] . '.jrxml';
            $modulo = $info[2];
            $filePath = $info[3];
            
            $report = new BReport($filePath, $modulo);
            
            $this->documentos['professor'][$file] = $report;
        }
        
        // Documentos aluno
        $docsAluno = $prtTransaction->obterTransacoesDeDocumentosDoPortal('pupil');
        foreach ( $docsAluno as $docAluno )
        {
            $info = explode('|', $docAluno[4]);
            $file = $info[1] . '.jrxml';
            $modulo = $info[2];
            $filePath = $info[3];
            
            $report = new BReport($filePath, $modulo);
            
            $this->documentos['aluno'][$file] = $report;
        }
        
        // Documentos coordenador
        $docsCoordenador = $prtTransaction->obterTransacoesDeDocumentosDoPortal('coordinator');
        foreach ( $docsCoordenador as $docCoordenador )
        {
            $info = explode('|', $docCoordenador[4]);
            $file = $info[1] . '.jrxml';
            $modulo = $info[2];
            $filePath = $info[3];
            
            $report = new BReport($filePath, $modulo);
            
            $this->documentos['coordenador'][$file] = $report;
        }
    }
    
    public function hasGroupId($parameters)
    {
        $keys = array_keys($parameters);
        
        return in_array('groupid', $keys) || in_array('groupId', $keys);
    }
    
    public function hasPersonId($parameters)
    {
        $keys = array_keys($parameters);
        
        return in_array('personid', $keys) || in_array('personId', $keys);
    }
    
    public function hasContractId($parameters)
    {
        $keys = array_keys($parameters);
        
        return in_array('contractid', $keys) || in_array('contractId', $keys);
    }
    
    public function hasInscricaoId($parameters)
    {
        $keys = array_keys($parameters);
        
        return in_array('inscricaoid', $keys) || in_array('inscricaoId', $keys);
    }
    
    public function getDocumentosDisciplina() {
        return $this->documentosDisciplina;
    }

    public function getDocumentos($perfil = null)
    {
        $documentos = $this->documentos;
        
        if ( $perfil )
        {
            $documentos = $documentos[$perfil];
        }
        
        return $documentos;
    }
    
    public static function possuiDocumentosCadastrados($perfil = null)
    {
        return BasDocumentosPortal::possuiDocumentosCadastrados($perfil);
    }
}

?>
