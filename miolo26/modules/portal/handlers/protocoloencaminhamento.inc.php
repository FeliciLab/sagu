<?php

switch ( prtUsuario::obterTipoDeAcesso() )
{
    case prtUsuario::USUARIO_PROFESSOR:
        $MIOLO->checkAccess('FrmProtocoloProfessor', A_ACCESS, TRUE);
        break;
    
    case prtUsuario::USUARIO_COORDENADOR:
        $MIOLO->checkAccess('FrmProtocoloCoordenador', A_ACCESS, TRUE);
        break;

    case prtUsuario::USUARIO_ALUNO:
        $MIOLO->checkAccess('FrmProtocoloAluno', A_ACCESS, TRUE);
        break;
}

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Encaminhamento de protocolo', $module), $module, $self);
$form = $ui->getForm($module, 'frmProtocoloEncaminhamento');

$theme->insertContent($form);

?>
