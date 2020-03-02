<?php
    if ($module == '')
    {
        $module = $MIOLO->mad;
    }

    if (!is_null($sa = $context->shiftAction()))
    {
        $a = $sa;
    }
    elseif ($module != $MIOLO->mad)
    {
        $a = 'main';
    }
	$handled = $MIOLO->invokeHandler($module,$a);

    if( ! $handled )
    {
        $ui = $MIOLO->getUI();
        if ($MIOLO->getConf('options.authmd5'))
        {
            $content = $ui->getForm($module,'frmLoginMD5');
        }
        else
        {
            $content = $ui->getForm($module,'frmLogin');
        }
        $theme->setContent($content);
    }
    include_once($MIOLO->getConf('home.modules') .'/main_menu.inc');
?>