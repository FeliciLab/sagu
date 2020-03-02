<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Bruno Edgar Fuhr [bruno@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2013/10/28
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
$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/prtDisciplinas.class.php', $module);
$MIOLO->uses('classes/prtUsuario.class.php', $module);

class frmAvaliacao extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        $this->autoSave = false;
        parent::__construct(_M('Cadastro de avaliações', MIOLO::getCurrentModule()));
    }
    
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $prtDisciplinas = new PrtDisciplinas();        
        $groupId = MIOLO::_REQUEST('groupid') ? MIOLO::_REQUEST('groupid') : MIOLO::_REQUEST('groupId');
        $usaConceito = MUtil::getBooleanValue($prtDisciplinas->usaConceito($groupId));
        
        $busEvaluation = new BusinessAcademicBusEvaluation();
        $busDegree = new BusinessAcademicBusDegree();
        $busSchedule = $MIOLO->getBusiness('academic', 'BusSchedule');
        $busPhysicalPerson = $MIOLO->getBusiness('basic', 'BusPhysicalPerson');
        
        $user = $MIOLO->getLogin();
        $filters->mioloUserName = $user->id;
        $professor = $busPhysicalPerson->searchPhysicalPerson($filters);
        
        //Obtém todos os professores da oferecida
        $professores = $busSchedule->getGroupProfessors($groupId);
        foreach( $professores as $personId => $prof )
        {
            $professoresDaOferecida[] = $personId;
        }

        //Verifica se o professor logado é professor na disciplina oferecida
        if( !in_array($professor[0][0], $professoresDaOferecida) && !(prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR) )
        {
            //Bloqueia o acesso, pois o professor não é professor da disciplina oferecida
            $MIOLO->error(_M('Apenas professores da disciplina podem ter acesso a esta tela.'));
        }
        
        $campos[] = new MDiv();
        
        $options = $busDegree->obterNotasPassiveisDeAvaliacao(MIOLO::_REQUEST('groupid'));
        
        if ( MIOLO::_REQUEST('edit') )
        {
            $evaluation = $busEvaluation->getEvaluation(MIOLO::_REQUEST('evaluationid'));
        }
        
        $label = new MLabel(_M('Grau acadêmico:'));
        $label->addStyle('margin-left', '35px');
        $label->addStyle('width', '150px');
        $selection = new MSelection('degreeId', $evaluation->degreeId, NULL, $options);        
        $campos[] = new MHContainer('contGrau', array($label, $selection));
        
        $label = new MLabel(_M('Descrição:'));
        $label->addStyle('margin-left', '35px');
        $label->addStyle('width', '150px');
        $desc = new MTextField('description', $evaluation->description, NULL, 60);
        $campos[] = new MHContainer('contDesc', array($label, $desc));
        
        $label = new MLabel(_M('Data:'));
        $label->addStyle('margin-left', '35px');
        $label->addStyle('width', '150px');
        $date = new MCalendarField('date', $evaluation->dateForecast, NULL);
        $campos[] = new MHContainer('contData', array($label, $date));
        
        if ( !$usaConceito )
        {
            $label = new MLabel(_M('Peso:'));
            $label->addStyle('margin-left', '35px');
            $label->addStyle('width', '150px');
            $peso = new MIntegerField('weight', $evaluation->weight, NULL);
            $campos[] = new MHContainer('contPeso', array($label, $peso));
        }
        
        $label = new MLabel(_M('Quem pode digitar as notas:'));
        $label->addStyle('margin-left', '35px');
        $label->addStyle('width', '150px');
        $podeDigitar = new MSelection('podeDigitar', $evaluation->podeDigitar, '', BusinessServicesBusProfessor::listarTipoDeProfessor(true));
        $campos[] = new MHContainer('hctPodeDigitar', array($label, $podeDigitar, ));
        
        foreach($campos as $field)
        {
            $field->addStyle('margin-left', '5%');
            $field->addStyle('margin-right', '5%');
        }
        
        $campos[] = new MDiv();
        
        $fields[] = new MFormContainer('frmContainer', $campos);
        
        $btnVoltar = new MButton('btnVoltar', _M('Voltar'));
        $btnFinalizar = new MButton('btnFinalizar', _M('Salvar'));   
        $fields[] = MUtil::centralizedDiv(array($btnVoltar, $btnFinalizar), 'divBtns');
        
	parent::addFields($fields);
    }
    
    public function btnFinalizar_click($args)
    {
        $MIOLO = MIOLO::getInstance();

        $busEvaluation = new BusinessAcademicBusEvaluation();
        
        $data = new stdClass();
        $data->professorId = $this->personid;
        $data->groupId = $args->groupid;
        $data->degreeId = $args->degreeId;
        $data->description = $args->description;
        $data->dateForecast = $args->date;
        $data->weight = $args->weight ? $args->weight : 1;
        $data->podeDigitar = $args->podeDigitar ? $args->podeDigitar : BusinessServicesBusProfessor::TODOS_OS_PROFESSOR_DA_DISCIPLINA ;
        
        if ( !$args->degreeId || !$args->description )
        {
            if ( !$args->degreeId )
            {
                new MMessageWarning(_M('Informe o grau acadêmico.'));
            }
            else
            {
                new MMessageWarning(_M('Informe a descrição da avaliação.'));
            }
        }
        else
        {            
            if ( MIOLO::_REQUEST('new') )
            {
                if ( $busEvaluation->insertEvaluation($data) )
                {
                    new MMessageSuccess(_M('Avaliação inserida com sucesso.'));

                    $btnVoltar = new MButton('btnVoltar', _M('Voltar'));
                    $fields[] = MUtil::centralizedDiv(array($btnVoltar), 'divBtns');
                    $this->setResponse($fields, 'divBtns');
                }
                else
                {
                    new MMessageError(_M('Erro ao inserir a avaliação.'));
                }
            }
            elseif ( MIOLO::_REQUEST('edit') )
            {
                $data->evaluationId = $args->evaluationid;

                if ( $busEvaluation->updateEvaluation($data) )
                {
                    new MMessageSuccess(_M('Avaliação atualizada com sucesso.'));

                    $btnVoltar = new MButton('btnVoltar', _M('Voltar'));
                    $fields[] = MUtil::centralizedDiv(array($btnVoltar), 'divBtns');
                    $this->setResponse($fields, 'divBtns');
                }
                else
                {
                    new MMessageError(_M('Erro ao atualizar a avaliação.'));
                }
            }
        }
        
        $this->setNullResponseDiv();
    }
    
    public function btnVoltar_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->page->redirect($MIOLO->getActionURL($module, 'main:avaliacoes', NULL, array('groupid' => MIOLO::_REQUEST('groupid'))));
    }
    
}

?>
