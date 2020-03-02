<?php

$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/prtDisciplinas.class.php', $module);
$MIOLO->uses('classes/prtTableRaw.class.php', $module);

class frmDeclaracaoTcc extends frmMobile
{

    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Declaração de orientação de TCC'));
    }
    
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $groupId = MIOLO::_REQUEST('groupId');
        $busGroup = new BusinessAcademicBusGroup();        
        
        if ( !$busGroup->isFinalExaminationGroup($groupId) )
        {
            $fields[] = MMessage::getStaticMessage('info', _M('Esta não é uma disciplina do tipo "Conclusão de Curso"'), MMessage::TYPE_INFORMATION);
        }
        else
        {
            $disciplina = new PrtDisciplinas();
            $alunos = $disciplina->obterMatriculasOrientados($groupId, $this->personid);
            $linhasDaTabela = NULL;
            $img = $MIOLO->getUI()->getImageTheme($module, 'bf-imprimir-on.png');

            foreach($alunos as $key => $aluno)
            {
                $action = MUtil::getAjaxAction('gerarRelatorio', "{$aluno[0]}");
                $link = new MImageLink('lnk_' . $key, _M('Gerar declaração'), NULL, $img);
                $link->addEvent('click', $action);
                $linhasDaTabela[$key][0] = $link;
                $linhasDaTabela[$key][1] = new MLabel($aluno[0], '', true);
                $linhasDaTabela[$key][2] = new MLabel($aluno[1], '', true);           
            }

            $table = new prtTableRaw(_M('Alunos'), $linhasDaTabela, array(
                '', 'Matrícula', 'Nome do aluno'
            ));

            foreach($linhasDaTabela as $key => $linha)
            {
                $table->addCellAttributes($key, 0, array('align' => 'center'));
            }

            $table->setWidth('100%');
            $fields[] = $table;
        }
        
        parent::addFields($fields);
    }
    
    public function gerarRelatorio($args)
    {
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $saguPath = $MIOLO->getConf("home.modules"). '/basic/reports/';
        $saguPath = str_replace('miolo26', 'miolo20', $saguPath);

        $report = new MJasperReport();        
        $rel = $report->executeJRXML($module, 'professor/finalExaminationDirectors', array(
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
