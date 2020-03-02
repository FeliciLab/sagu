<?php

$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('forms/frmDocumentos.class.php', $module);
$MIOLO->uses('classes/prtTableRaw.class.php', $module);

class frmHorasProfessores extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Planilha de horas dos professores', MIOLO::getCurrentModule()));
    }
    
    public function defineFields()
    {
        // Obtém o mês atual
        $month = SAGU::getDatePart(SAGU::getDateNow(), 'MONTH');
        // Obtém o ano atual
        $year = SAGU::getDatePart(SAGU::getDateNow(), 'YEAR');
        
        $lblBegin = new MLabel(_M('Data inicial:'));
        $lblBegin->addStyle('width', '120px');
        $lblBegin->addStyle('text-align', 'right');
        $lblBegin->addStyle('padding-left', '20px');
        
        $lblEnd = new MLabel(_M('Data final:'));
        $lblEnd->addStyle('width', '120px');
        $lblEnd->addStyle('text-align', 'right');
        $lblEnd->addStyle('padding-left', '20px');
        
        $beginMonth = new MSelection('beginMonth', $month, _M('', $module), SAGU::listMonths());
        $beginYear = new MSelection('beginYear', $year, _M('', $module), SAGU::listYears(2000, $year));
        
        $endMonth = new MSelection('endMonth', $month, _M('', $module), SAGU::listMonths());
        $endYear = new MSelection('endYear', $year, _M('', $module), SAGU::listYears(2000, $year));
        
        $contBegin = new MHContainer('divBegin', array($lblBegin, $beginMonth, $beginYear));
        $contEnd = new MHContainer('divEnd', array($lblEnd, $endMonth, $endYear));
                
        $fields[] = new MDiv();
        $fields[] = $contBegin;
        $fields[] = $contEnd;
        $fields[] = new MDiv();
        
        $btn = new MButton('btnListaProfessores', _M('Listar professores'), MUtil::getAjaxAction('obterTabelaDeProfessores'));
        $fields[] = new MDiv('divBtn', MUtil::centralizedDiv(array($btn)));
        
        $fields[] = new MDiv('divTable');
        
        parent::addFields($fields);
    }
    
    public function obterTabelaDeProfessores()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $img = $MIOLO->getUI()->getImageTheme($module, 'bf-imprimir-on.png');
                
        $busCourseCoordinator = $MIOLO->getBusiness('academic', 'BusCourseCoordinator');
        $busLearningPeriod = $MIOLO->getBusiness('academic', 'BusLearningPeriod');
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $busProfessorCC= $MIOLO->getBusiness('academic', 'BusProfessorCurricularComponent');
        
        $filters = new stdClass();
        $filters->coordinatorId = $this->personid;
        $courses = $busCourseCoordinator->searchCourseCoordinator($filters);;
        
        $professores = array();
        $codigosProfessores = array();
        $professorCount = 0;
        foreach ( $courses as $course )
        {            
            $filters = new stdClass();
            $filters->courseId = $course[0];
            $filters->courseVersion = $course[1];
            $filters->courseTurnId = $course[4];
            $filters->courseunitId = $course[6];
            $groups = $busGroup->searchGroup($filters);
            
            foreach ( $groups as $group )
            {
                $professoresDaDisciplina = $busGroup->getProfessorNamesOfGroup($group[0]);
                foreach( $professoresDaDisciplina as $codProfessor => $professorDaDisciplina )
                {
                    if ( !in_array($codProfessor, $codigosProfessores) )
                    {
                        $codigosProfessores[] = $codProfessor;
                        $action = MUtil::getAjaxAction('gerarRelatorio', $codProfessor);
                        $link = new MImageLink('lnkInfo_' . $codProfessor, _M('Gerar Relatório'), NULL, $img);
                        $link->addEvent('click', $action);

                        $professores[$professorCount][] = $label = new MLabel($link);
                        $label->addStyle('text-align', 'center');
                        $professores[$professorCount][] = $label = new MLabel('<b>' . $codProfessor . '</b>');
                        $label->addStyle('font-size', '12px');
                        $label->addStyle('text-align', 'center');
                        $professores[$professorCount][] = $label = new MLabel('<b>' . $professorDaDisciplina . '</b>');
                        $label->addStyle('font-size', '12px');
                        $professores[$professorCount][] = $label = new MLabel('<b>' . $course[2] . ' - ' . $course[3] . '</b>');
                        $label->addStyle('font-size', '12px');

                        $professorCount++;
                    }
                }
            }
        }
        
        $table = new prtTableRaw(_M('Professores'), $professores, array('Ações', 'Código', 'Nome', 'Curso'));
        $table->setWidth('100%');
        
        $this->setResponse($table, 'divTable');
    }

        public function gerarRelatorio($args)
    {
        $MIOLO = MIOLO::getInstance();
        $professorId = $args;
        $args = $_REQUEST;
        
        $busCourseCoordinator = $MIOLO->getBusiness('academic', 'BusCourseCoordinator');
        $filters = new stdClass();
        $filters->coordinatorId = $this->personid;
        $cursosDoCoordenador = $busCourseCoordinator->searchCourseCoordinator($filters);
        $cursos = array();
        foreach($cursosDoCoordenador as $cursoDoCoordenador)
        {
            $cursos[] = $cursoDoCoordenador[0];
        }
        $cursos = implode("','", $cursos);
        
        $frmDocumentos = new frmDocumentos();
        $reportFile = $frmDocumentos->encontrarArquivo('professorTimeSheet', 'academic');

        if ( file_exists($reportFile) )
        {
            $report = new MJasperReport();
            $report->executeJRXML(NULL, $reportFile, array(
                'str_generationDate' => SAGU::getDateNow(),
                'int_professorId' => $professorId,
                'int_beginMonth' => $args['beginMonth'],
                'int_beginYear' => $args['beginYear'],
                'int_endMonth' => $args['endMonth'],
                'int_endYear' => $args['endYear'],
                'str_courseId' => $cursos
            ));
        }
        else
        {
            new MMessageWarning(_M('O arquivo da planilha de horas não foi encontrado.'));
        }
        
        $this->setNullResponseDiv();
    }    
    
}

?>
