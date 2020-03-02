<?php

switch ( prtUsuario::obterTipoDeAcesso() )
{
    case prtUsuario::USUARIO_PROFESSOR:
        $MIOLO->checkAccess('FrmAgendaProfessor', A_ACCESS, TRUE);
        break;
    
    case prtUsuario::USUARIO_COORDENADOR:
        $MIOLO->checkAccess('FrmAgendaCoordenador', A_ACCESS, TRUE);
        break;
    
    case prtUsuario::USUARIO_ALUNO:
        $MIOLO->checkAccess('FrmAgendaAluno', A_ACCESS, TRUE);
        break;
}

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Agenda', $module), $module, $self);
$form = $ui->getForm($module, 'frmAgenda');
$theme->insertContent($form);
?>
