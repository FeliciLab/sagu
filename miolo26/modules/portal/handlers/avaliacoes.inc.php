<?php

$MIOLO->checkAccess('FrmCadastroAvaliacoes', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Cadastro de avaliações', $module), $module, $self);

if ( MIOLO::_REQUEST('new') || MIOLO::_REQUEST('edit') )
{
    $form = $ui->getForm($module, 'frmAvaliacao');
}
else
{
    $form = $ui->getForm($module, 'frmAvaliacaoBusca');
}

$theme->insertContent($form);

?>
