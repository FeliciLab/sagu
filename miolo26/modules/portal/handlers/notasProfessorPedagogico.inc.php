<?php

if ( MIOLO::_REQUEST('isAdmin') == DB_TRUE )
{
    // Verifica a permissão no pedagógico, quando vier do sistema
    $MIOLO->checkAccess('FrmFrequenciasENotas', A_ACCESS, TRUE);
}
else
{
    $MIOLO->checkAccess('FrmNotasProfessor', A_ACCESS, TRUE);
}

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Notas', $module), $module, $self);
$form = $ui->getForm($module, 'frmNotasProfessorPedagogico');
$theme->insertContent($form);
?>
