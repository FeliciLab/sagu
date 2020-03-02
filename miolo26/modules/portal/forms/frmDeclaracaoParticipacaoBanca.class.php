<?php

$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/prtDisciplinas.class.php', $module);
$MIOLO->uses('classes/prtTableRaw.class.php', $module);

class frmDeclaracaoParticipacaoBanca extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Declaração de participação em banca'));
    }
    
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busPeriod = $MIOLO->getBusiness('academic', 'BusPeriod');
        
        $periodos = $busPeriod->obterPeriodosParticipacaoBanca($this->personid);
        if ( $periodos )
        {
            $fields[] = new MDiv();
            $label = new MLabel(_M('Selecione abaixo o período desejado'));
            $label->addStyle('font-weight', 'bold');
            $label->addStyle('color', 'navy');
            $label->addStyle('margin-top', '8px');
            $label->addStyle('margin-left', '10px');
            $fields[] = MUtil::centralizedDiv(array($label));
            $fields[] = new MDiv();
            $fields[] = $selection = new MSelection('periodo', NULL, _M('Per&iacute;odo'), $periodos);
            $selection->addAttribute('onchange', MUtil::getAjaxAction('carregarMatriculas'));

            $fields[] = new MDiv('divMatriculas');
        }
        else
        {
            $fields[] = MMessage::getStaticMessage('msgInfo', _M('Não foram encontrados registros de participação em bancas.'), MMessage::TYPE_INFORMATION);
        }
        
        parent::addFields($fields);
    }
    
    public function carregarMatriculas($args)
    {
        if ( !$args->periodo )
        {
            $this->setResponse(new MDiv('divMatriculas', NULL), 'divMatriculas');
        }
        else
        {
            $MIOLO = MIOLO::getInstance();
            $module = MIOLO::getCurrentModule();
            $img = $MIOLO->getUI()->getImageTheme($module, 'bf-imprimir-on.png');

            $busEnroll = $MIOLO->getBusiness('academic', 'BusEnroll');
            $busContract = $MIOLO->getBusiness('academic', 'BusContract');
            $busPerson = $MIOLO->getBusiness('basic', 'BusPerson');

            $filtros = new stdClass();
            $filtros->periodId = $args->periodo;
            $filtros->finalExaminationExaminingBoard = true;
            $filtros->personId = $this->personid;

            $enrolls = $busEnroll->searchEnroll($filtros);
            $dadosTabela = array();
            foreach( $enrolls as $key => $enroll )
            {
                $action = MUtil::getAjaxAction('gerarRelatorio', "{$enroll[0]}");
                $link = new MImageLink('lnk_' . $key, _M('Gerar declaração'), NULL, $img);
                $link->addEvent('click', $action);

                $dadosTabela[$key][] = $link;
                $dadosTabela[$key][] = $enroll[0];

                $personId = $busContract->getPersonIdByContract($enroll[1]);
                $personName = $busPerson->getPersonName($personId);

                $dadosTabela[$key][] = $personName;            
            }

            $table = new prtTableRaw(_M('Alunos'), $dadosTabela, array(
                '', 'Matrícula', 'Nome do aluno'
            ));

            foreach($dadosTabela as $key => $linha)
            {
                $table->addCellAttributes($key, 0, array('align' => 'center'));
            }

            $table->setWidth('100%');

            $this->setResponse(new MDiv('divMatriculas', array($table)), 'divMatriculas');
        }
    }
    
    public function gerarRelatorio($args)
    {
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $saguPath = $MIOLO->getConf("home.modules"). '/basic/reports/';
        $saguPath = str_replace('miolo26', 'miolo20', $saguPath);

        $report = new MJasperReport();        
        $rel = $report->executeJRXML($module, 'professor/finalExaminationExaminingBoard', array(
            'int_enrollid' => $args,
            'int_personid' => $this->personid,
            'str_SAGU_PATH' => $saguPath,
        ));
        
        if ( $rel == 0 )
        {
            new MMessageWarning(_M('O documento não possui dados.'));
        }
        
        $this->setNullResponseDiv();
    }
    
}

?>
