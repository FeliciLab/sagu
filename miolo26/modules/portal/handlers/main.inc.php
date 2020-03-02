<?php

global $module;

if ( !isset($module) )
{
    $module = $MIOLO->getConf('options.startup');
}

$ui = $MIOLO->getUI();
$action = MIOLO::_REQUEST('action');

$MIOLO->uses('classes/filaDeEspera.class.php', $module);

filaDeEspera::autenticacaoDoUsuario();

if ( $action == 'main' || $action == null )
{
    $form = $ui->getForm($module, 'frmMain');
    $theme->setContent($form);

    //Verifica se existe uma configuração para troca de senha e redireciona para tela de troca
    if( BusinessBasicBusConfiguracaoTrocaDeSenha::verificaTrocaDeSenha() == DB_TRUE )
    {
        $ui = $MIOLO->getUI();
        $form = $ui->getForm('portal', 'frmTrocaDeSenha');
        $theme->setContent($form);
    }

}
$shiftAction = $context->shiftAction();

if ( $shiftAction )
{
    $MIOLO->invokeHandler($module, $shiftAction);
}

?>
