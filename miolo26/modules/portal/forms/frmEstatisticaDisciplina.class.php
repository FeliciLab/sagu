<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/10/23
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
$MIOLO->uses('classes/prtTableRaw.class.php', $module);
$MIOLO->uses('classes/prtUsuario.class.php', $module);
class frmEstatisticaDisciplina extends frmMobile
{
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Estatísticas', MIOLO::getCurrentModule()));
    }

    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busGroup = $MIOLO->getBusiness('academic', 'BusGroup');
        $busLearningPeriod = $MIOLO->getBusiness('academic', 'BusLearningPeriod');
        $busSchedule = $MIOLO->getBusiness('academic', 'BusSchedule');
        $busPhysicalPerson = $MIOLO->getBusiness('basic', 'BusPhysicalPerson');
        
        $groupId = MIOLO::_REQUEST('groupid');
        $groupData = $busGroup->getGroup($groupId);
        
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
        
        /*
        Número de alunos matriculados.
        Número de alunos cancelados.
        Número de alunos repetentes.
        Gráfico com o número de alunos por curso.
        Gráfico com o número de alunos acima e abaixo do exigido para aprovação e o número de alunos sem nota/conceito por avaliação/grau.
        Gráfico com o número de alunos que estariam aprovados, reprovados e reprovados por falta.
         * 
         */

        $fields[] = new MDiv('divDadosGerais', $this->dadosGerais());
        
        $labelAlunosCurso = new MLabel(_M('Alunos por curso'));
        $labelAlunosCurso->addStyle('padding-left', '12px');
        $labelAlunosCurso->addStyle('padding-top', '12px');
        $labelAlunosCurso->addStyle('font-weight', 'bold');
        $labelAlunosCurso->addStyle('color', 'navy');
        $labelAlunosCurso->addStyle('font-size', '20px');
        
        $labelAlunosAvaliacao = new MLabel(_M('Avaliação dos alunos'));
        $labelAlunosAvaliacao->addStyle('padding-left', '12px');
        $labelAlunosAvaliacao->addStyle('padding-top', '12px');
        $labelAlunosAvaliacao->addStyle('font-weight', 'bold');
        $labelAlunosAvaliacao->addStyle('color', 'navy');
        $labelAlunosAvaliacao->addStyle('font-size', '20px');
                
        $labelAlunosNotas = new MLabel(_M('Notas dos alunos'));
        $labelAlunosNotas->addStyle('padding-left', '12px');
        $labelAlunosNotas->addStyle('padding-top', '12px');
        $labelAlunosNotas->addStyle('font-weight', 'bold');
        $labelAlunosNotas->addStyle('color', 'navy');
        $labelAlunosNotas->addStyle('font-size', '20px');
        
        $fields[] = new MHContainer('cdsc', array(
            new MDiv('alunosCurso', array($labelAlunosCurso, $this->alunosCurso())), 
            new MDiv('alunosAvaliacao', array($labelAlunosAvaliacao, $this->alunosAvaliacao())),
            new MDiv('alunosNotas', array($labelAlunosNotas, $this->alunosNotas()))
        ));
        
        parent::addFields($fields);
    }
    
    public function dadosGerais()
    {   
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $vlrAlunosMatriculados = new MLabel($disciplinas->obterAlunosMatriculados(MIOLO::_REQUEST('groupid')));
        $vlrAlunosMatriculados->addStyle('font-weight', 'bold');
        $vlrAlunosCancelados = new MLabel($disciplinas->obterAlunosCancelados(MIOLO::_REQUEST('groupid')));
        $vlrAlunosCancelados->addStyle('font-weight', 'bold');
        $vlrAlunosRepetentes = new MLabel($disciplinas->obterAlunosRepetentes(MIOLO::_REQUEST('groupid')));
        $vlrAlunosRepetentes->addStyle('font-weight', 'bold');
        
        $table = new prtTableRaw(_M('Dados gerais'), array(array(
            $vlrAlunosMatriculados,
            $vlrAlunosCancelados,
            $vlrAlunosRepetentes
        )), array(
            _M('Número de alunos matriculados'),
            _M('Número de alunos cancelados'),
            _M('Número de alunos repetentes'),
        ), rand());
        
        $table->setWidth('100%');
        
        $table->addCellAttributes(0, 0, array('align' => 'center'));
        $table->addCellAttributes(0, 1, array('align' => 'center'));
        $table->addCellAttributes(0, 2, array('align' => 'center'));

        $fields[] = $table;
        
        return $fields;
    }
    
    public function alunosCurso()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();

        $fields[] = new MChart('alunosCurso_' . rand(), $disciplinas->obterAlunosPorCurso(MIOLO::_REQUEST('groupid')), NULL, MChart::TYPE_PIE);
        
        return $fields;
    }
    
    public function alunosAvaliacao()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $fields[] = new MChart('alunosAvaliacao_' . rand(), $disciplinas->obterAlunosAvaliacao(MIOLO::_REQUEST('groupid')), NULL, MChart::TYPE_PIE);
        
        return $fields;
    }
    
    public function alunosNotas()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $fields[] = new MChart('alunosNotas_' . rand(), $disciplinas->obterAlunosNotas(MIOLO::_REQUEST('groupid')), NULL, MChart::TYPE_PIE);
        
        return $fields;
    }

}

?>
