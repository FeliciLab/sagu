<?php

$tipoAcesso = prtUsuario::obterTipoDeAcesso();

if ( $tipoAcesso == prtUsuario::USUARIO_COORDENADOR )
{
    $MIOLO->checkAccess('FrmPreferenciasCoordenador', A_ACCESS, TRUE);
}
else if ( $tipoAcesso == prtUsuario::USUARIO_ALUNO )
{
    $MIOLO->checkAccess('FrmPreferenciasAluno', A_ACCESS, TRUE);
}

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Preferências', $module), $module, $self);
$form = $ui->getForm($module, 'frmPreferencias');
$theme->insertContent($form);
?>
