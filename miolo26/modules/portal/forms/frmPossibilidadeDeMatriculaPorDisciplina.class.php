<?php

$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('forms/frmDocumentos.class.php', $module);
$MIOLO->uses('classes/prtTableRaw.class.php', $module);

class frmPossibilidadeDeMatriculaPorDisciplina extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Possibilidades de matrícula por disciplina', MIOLO::getCurrentModule()));
    }
    
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $img = $MIOLO->getUI()->getImageTheme($module, 'bf-imprimir-on.png');
        
        $tipoLabel = new MLabel(_M('Tipo do relatório:'));
        $tipoField = new MSelection('tipo', $this->GetFormValue('tipo', 'A'));
        $tipoField->options = array('A' => 'Analítico', 'S' => 'Sintético');
        $tipoField->addAttribute('style', 'font-size:16px;');
        $fields[] = new MVContainer('tipoHc', array($tipoLabel, $tipoField));
        
        $equivLabel = new MLabel(_M('Exibir equivalências no relatório:'));
	$equivLabel->addAttribute('style', 'margin-top:15px');
        $equivField = new MSelection('exibir_equivalencias', $this->GetFormValue('exibir_equivalencias', 'S'));
        $equivField->options = array('S' => 'Sim', 'N' => 'Não');
        $equivField->addAttribute('style', 'font-size:16px');
        $fields[] = new MVContainer('equivHc', array($equivLabel, $equivField));
        
        $busCourseCoordinator = $MIOLO->getBusiness('academic', 'BusCourseCoordinator');
        
        $cursos = $busCourseCoordinator->obterCursosDoCoordenador($this->personid, false, true);
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
        }
        
        $table = new MTableRaw(_M('Cursos'), $dadosTabela, array('Imprimir relatório', 'Código', 'Nome do curso', 'Versão', 'Turno', 'Unidade'));
        $table->setWidth('100%');
	$table->addAttribute('style', 'margin-top:20px');
        $fields[] = $table;
        
        parent::addFields($fields);
    }
    
    public function gerarRelatorio($args)
    {        
        $MIOLO = MIOLO::getInstance();
        $args = explode('|', $args);
        
        $saguPath = $MIOLO->getConf("home.modules"). '/basic/reports/';
        $saguPath = str_replace('miolo26', 'miolo20', $saguPath);
        
        $frmDocumentos = new frmDocumentos();
        $reportFile = $frmDocumentos->encontrarArquivo('possibilidades_matricula_equivalencias', 'academic');

        if ( file_exists($reportFile) )
        {
            $report = new MJasperReport();
            $return = $report->executeJRXML(NULL, $reportFile, array(
                'str_turnid' => $args[2],
                'str_unitid' => $args[4],
                'str_courseid' => $args[0],
                'str_courseversion' => $args[1],
                'str_tipo' => $MIOLO->_REQUEST('tipo'),
                'str_exibir_equivalencias' => $MIOLO->_REQUEST('exibir_equivalencias'),
                'str_SAGU_PATH' => $saguPath //alexandre-deibler
            ));
        }
        else
        {
            new MMessageWarning(_M('O arquivo do relatório de estado das disciplinas não foi encontrado.'));
        }

        if ( $return == 0 )
        {
            new MMessageWarning(_M('Não foram encontrados registros para gerar o documento.'));
        }
        
        $this->setNullResponseDiv();
    }    
    
}

?>
