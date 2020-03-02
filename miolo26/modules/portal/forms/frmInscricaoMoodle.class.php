<?php

$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('grids/GrdMoodleSubscriptionProfessor.class', 'services');
$MIOLO->uses('grids/GrdMoodleSubscriptionPupil.class', 'services');
$MIOLO->uses('types/AcpOfertaComponenteCurricular.class', 'pedagogico');

class frmInscricaoMoodle extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Inscrição no Moodle', MIOLO::getCurrentModule()));        
    }
    
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        
        $function = MIOLO::_REQUEST('function');

        $groupId = MIOLO::_REQUEST('groupid');
        $professorId = MIOLO::_REQUEST('professorid');
        $periodId = str_replace('_', '/', MIOLO::_REQUEST('periodid'));
        $idmodule = MIOLO::_REQUEST('idmodule');

        if ( $idmodule == 'pedagogico' )
        {
            // $groupId na verdade eh uma ofertacomponentecurricularid
            $groupData = AcpOfertaComponenteCurricular::obterInformacaoOfertaCC($groupId);
            
            $professores = AcpOfertaComponenteCurricular::obterProfessores($groupId);
            $proflist = '-';
            
            if ( count($professores) > 0 )
            {
                $proflist = implode(', ', $professores);
            }
            
            $info[] = new MLabel(_M('Disciplina', $module) . " : <b>" . $groupData['codcomponente'] . ' - ' . $groupData['nomecomponente'] . "</b>");
            $info[] = new MLabel(_M('Período', $module) . " : <b>" . $groupData['datainicialoferta'] . ' à ' . $groupData['datafinaloferta'] . "</b>");
            $info[] = new MLabel(_M('Professor(es)', $module) . " : <b>" . $proflist . "</b>");
        }
        else
        {
            $busGradeTyping = new BusinessAcademicBusGradeTyping();
            $groupData = $busGradeTyping->getGroupData($groupId);

            $busSchedule = new BusinessAcademicBusSchedule();
            $professors = $busSchedule->getGroupProfessors($groupId);

            if ( is_array($professors) )
            {
                $groupData->professor = implode(', ', $professors);
            }
            else
            {
                $groupData->professor = _M('Professor não definido', $module);
            }

            $info[] = new MLabel(_M('Disciplina', $module) . " : <b>" . $groupData->curricularComponent . "</b>");
            $info[] = new MLabel(_M('Período', $module) . " : <b>" . $groupData->periodId . "</b>");
            $info[] = new MLabel(_M('Professor(es)', $module) . " : <b>" . $groupData->professor . "</b>");
            $info[] = new MLabel(_M('Unidade', $module) . " : <b>" . $groupData->unit . "</b>");
        }

        foreach ($info as $i)
        {
            $i->addStyle('font-size', '12px');
        }
        
        $fields[] = $divInfo = new MDiv('divInfo', $info);
        $divInfo->addStyle('padding', '10px');
        $divInfo->addStyle('padding-left', '120px');
        
        $fields[] = MUtil::centralizedDiv(new MButton('btnSave1', _M('Criar disciplina no moodle e inscrever alunos'), ':inscrever'), 'divBtn1');
        
//$idmodule = 'academic';
        $busMoodle = new BusinessAcademicBusMoodle();
        // Sincroniza a turma com o moodle
        $busMoodle->synchronize($groupId, $idmodule);
        // Lista os professores
        $professorData = $busMoodle->listTeacherMoodleSubscription($groupId, $idmodule);
        // Lista os alunos
        $pupilData = $busMoodle->listStudentMoodleSubscription($groupId, $idmodule);
        
        // Grid dos professores
        $gridProfessor = new GrdMoodleSubscriptionProfessor();
        $gridProfessor->clearActions();
        $gridProfessor->showExportAsCSV = false;
        $gridProfessor->setData($professorData);
        $divProfessor = new MDiv('divProfessor', $gridProfessor);
        $divProfessor->setWidth('100%');
        $fields[] = new MDiv('divProfessor', new MBaseGroup('bsgProfessor', _M('Professor(es)', $module), array($divProfessor)));
        
        // Grid dos alunos
        $gridPupil = new GrdMoodleSubscriptionPupil();
        $gridPupil->clearActions();
        $gridPupil->showExportAsCSV = false;
        $gridPupil->setData($pupilData);
        $divPupil = new MDiv('divPupil', $gridPupil);
        $divPupil->setWidth('100%');
        $fields[] = new MDiv('divPupil', new MBaseGroup('bsgPupil', _M('Alunos', $module), array($divPupil)));
        
        $fields[] = MUtil::centralizedDiv(new MButton('btnSave2', _M('Criar disciplina no moodle e inscrever alunos'), ':inscrever'), 'divBtn2');
        
        parent::addFields($fields);
    }
    
    public function inscrever($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        $groupId = $_REQUEST['groupid'];

        try
        {

            $busMoodle = new BusinessAcademicBusMoodle();
            // Sincroniza a turma com o moodle
            $busMoodle->synchronize($groupId);
            $result = $busMoodle->makeIntegrationWithMoodle($groupId, 'pedagogico');

            if ( $result )
            {
                $this->setResponse(NULL, 'divInfo');
                $this->setResponse(NULL, 'divProfessor');                
                $this->setResponse(NULL, 'divPupil');
                $this->setResponse(NULL, 'divBtn1');
                $this->setResponse(NULL, 'divBtn2');
                
                new MMessageSuccess(_M('Inscrição efetuada com sucesso.'));
            }
            else
            {
                throw new Exception(_M('Não foi possível efetuar a inscrição no Moodle', $module));
            }
        }
        catch ( Exception $e )
        {
            new MMessageError($e->getMessage());
        }
        
        $this->setNullResponseDiv();
    }
    
}

?>
