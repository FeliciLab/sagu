<?php

$tipoAcesso = prtUsuario::obterTipoDeAcesso();

if ( $tipoAcesso == prtUsuario::USUARIO_PROFESSOR )
{
    $MIOLO->checkAccess('FrmProtocoloProfessor', A_ACCESS, TRUE);
}
else if ( $tipoAcesso == prtUsuario::USUARIO_COORDENADOR )
{
    $MIOLO->checkAccess('FrmProtocoloCoordenador', A_ACCESS, TRUE);
}
else if ( $tipoAcesso == prtUsuario::USUARIO_ALUNO )
{
    $MIOLO->checkAccess('FrmProtocoloAluno', A_ACCESS, TRUE);
}

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Solicitações de protocolo', $module), $module, $self);

if ( MIOLO::_REQUEST('new') )
{
    $form = $ui->getForm($module, 'frmProtocolo');
}
else
{
    $form = $ui->getForm($module, 'frmProtocoloBusca');
}

$theme->insertContent($form);

?>
