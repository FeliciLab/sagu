<?php

switch ( prtUsuario::obterTipoDeAcesso() )
{
    case prtUsuario::USUARIO_PROFESSOR:
        $MIOLO->checkAccess('FrmDisciplinasProfessor', A_ACCESS, TRUE);
        break;
    
    case prtUsuario::USUARIO_ALUNO:
        $MIOLO->checkAccess('FrmMensagensAluno', A_ACCESS, TRUE);
        break;
}

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Mensagens', $module), $module, $self);
if ( MIOLO::_REQUEST('chatWith') )
{
    $form = $ui->getForm($module, 'frmChat');
}
elseif ( MIOLO::_REQUEST('newMessage') )
{
    $form = $ui->getForm($module, 'frmNovaMensagem');
}
else
{
    $form = $ui->getForm($module, 'frmMensagens');
}
$theme->insertContent($form);
?>
