<?php

$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('forms/frmDocumentos.class.php', $module);
$MIOLO->uses('classes/prtTableRaw.class.php', $module);

class frmEstadoDisciplinas extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Estado das disciplinas dos professores', MIOLO::getCurrentModule()));
    }
    
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $img = $MIOLO->getUI()->getImageTheme($module, 'bf-imprimir-on.png');
        
        $busCourseCoordinator = $MIOLO->getBusiness('academic', 'BusCourseCoordinator');
        
        $cursos = $busCourseCoordinator->obterCursosDoCoordenador($this->personid);
        $dadosTabela = array();
        foreach ( $cursos as $key => $curso )
        {
            $action = MUtil::getAjaxAction('gerarRelatorio', implode('|', $curso));
            $link = new MImageLink('lnkPrint_' . rand(), _M('Gerar relatório'), NULL, $img);
            $link->addEvent('click', $action);
            
            $dadosTabela[$key][] = $label = new MLabel($link);
            $label->addStyle('text-align', 'center');
            $dadosTabela[$key][] = $label = new MLabel('<b>' . $curso[0] . '</b>');
            $label->addStyle('font-size', '12px');
            $label->addStyle('text-align', 'center');
            $dadosTabela[$key][] = $label = new MLabel('<b>' . $curso[6] . '</b>');
            $label->addStyle('font-size', '12px');            
            $dadosTabela[$key][] = $label = new MLabel('<b>' . $curso[1] . '</b>');
            $label->addStyle('font-size', '12px');
            $label->addStyle('text-align', 'center');
            $dadosTabela[$key][] = $label = new MLabel('<b>' . $curso[3] . '</b>');
            $label->addStyle('font-size', '12px');
            $label->addStyle('text-align', 'center');
            $dadosTabela[$key][] = $label = new MLabel('<b>' . $curso[5] . '</b>');
            $label->addStyle('font-size', '12px');
            $label->addStyle('text-align', 'center');
            $dadosTabela[$key][] = $label = new MLabel('<b>' . $curso[7] . '</b>');
            $label->addStyle('font-size', '12px');
            $label->addStyle('text-align', 'center');
        }
        
        $table = new MTableRaw(_M('Cursos'), $dadosTabela, array('Ações', 'Código', 'Nome do curso', 'Versão', 'Turno', 'Unidade', 'Período'));
        $table->setWidth('100%');
        $fields[] = $table;
        
        parent::addFields($fields);
    }
    
    public function gerarRelatorio($args)
    {        
        $MIOLO = MIOLO::getInstance();
        $args = explode('|', $args);
        
        $frmDocumentos = new frmDocumentos();
        $reportFile = $frmDocumentos->encontrarArquivo('estadoDisciplinas', 'academic');

        if ( file_exists($reportFile) )
        {
            $report = new MJasperReport();
            $report->executeJRXML(NULL, $reportFile, array(
                'str_courseName' => $args[6],
                'str_unidade' => $args[5],
                'str_turno' => $args[3],
                'int_turnId' => $args[2],
                'int_unitId' => $args[4],
                'str_periodId' => $args[7],
                'str_courseId' => $args[0],
                'int_courseVersion' => $args[1],
            ));
        }
        else
        {
            new MMessageWarning(_M('O arquivo do relatório de estado das disciplinas não foi encontrado.'));
        }
        
        $this->setNullResponseDiv();
    }    
    
}

?>
