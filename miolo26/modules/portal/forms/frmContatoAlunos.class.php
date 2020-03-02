<?php

$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/prtDisciplinas.class.php', $module);
$MIOLO->uses('classes/prtTableRaw.class.php', $module);

class frmContatoAlunos extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Contato dos alunos'));
    }
    
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $groupId = MIOLO::_REQUEST('groupId');
        $disciplina = new PrtDisciplinas();
        
        $alunos = $disciplina->obterAlunosDaDisciplina($groupId);
        $linhasDaTabela = NULL;
        
        foreach($alunos as $key => $aluno)
        {
            $linhasDaTabela[$key][0] = new MLabel($aluno[0], '', true);
            $linhasDaTabela[$key][1] = new MLabel($aluno[1], '', true);
            $linhasDaTabela[$key][2] = new MLabel($aluno[2], '', true);
            $linhasDaTabela[$key][3] = new MLabel($aluno[3], '', true);
            $linhasDaTabela[$key][4] = new MLabel($aluno[4], '', true);
            $linhasDaTabela[$key][5] = new MLabel($aluno[5], '', true);            
        }
        
        $table = new prtTableRaw(_M('Contato dos alunos'), $linhasDaTabela, array(
            'Código', 'Nome do aluno', 'Email', 'Fone Res.', 'Fone Prof.', 'Celular'
        ));
        
        foreach($linhasDaTabela as $key => $linha)
        {
            $table->addCellAttributes($key, 0, array('align' => 'center'));
            $table->addCellAttributes($key, 3, array('align' => 'center'));
            $table->addCellAttributes($key, 4, array('align' => 'center'));
            $table->addCellAttributes($key, 5, array('align' => 'center'));
        }
        
        $table->setWidth('100%');
        $fields[] = $table;
        
        $fields[] = MUtil::centralizedDiv(new MButton('btnGerarRelatorio', _M('Gerar relatório')));
        
        parent::addFields($fields);
    }
    
    public function btnGerarRelatorio_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $groupId = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');
        
        $disciplina = $busGroup->getGroup($groupId);
        $nomeDaDisciplina = $disciplina->curriculumCurricularComponentName;

        $report = new MJasperReport();        
        $report->executeJRXML($module, 'contatoAlunos', array(
            'int_groupid' => $groupId,
            'disciplina' => $nomeDaDisciplina
        ));
    }
    
}

?>
