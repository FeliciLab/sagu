<?php

$tipoAcesso = prtUsuario::obterTipoDeAcesso();

if ( $tipoAcesso == prtUsuario::USUARIO_PROFESSOR )
{
    $MIOLO->checkAccess('FrmPerfilProfessor', A_ACCESS, TRUE);
}
else if ( $tipoAcesso == prtUsuario::USUARIO_COORDENADOR )
{
    $MIOLO->checkAccess('FrmPerfilCoordenador', A_ACCESS, TRUE);
}
else if ( $tipoAcesso == prtUsuario::USUARIO_ALUNO )
{
    $MIOLO->checkAccess('FrmPerfilAluno', A_ACCESS, TRUE);
}

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Perfil', $module), $module, $self);
$form = $ui->getForm($module, 'frmPerfilUsuario');
$theme->insertContent($form);
?>
