<?php

if(strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'mobile') || strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'android'))
{
    $campos = array();
    $botoes = array();

    $campos[] = new MLabel('Esta tela so pode ser acessada através do computador');
    $botoes[] = new MButton('botaoVoltar', _M('Voltar', $module), "history.back();");
    $campos[] = MUtil::centralizedDiv($botoes);
    
    $dialog = new MDialog('dialogoAcesso', _M('Aviso', $this->modulo), $campos);
    $this->manager->page->onload("$('#dialogoAcesso').trigger('pagecreate');");
    $dialog->show();
    $theme->appendContent($dialog);
    return;
}

?>
