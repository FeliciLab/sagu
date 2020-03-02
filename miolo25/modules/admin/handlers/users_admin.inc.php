<?php

    $home = 'main:admin';
    
    $navbar->addOption('UsuÃ¡rios',$module,$self);
    
    $ui = $MIOLO->getUI();
    
    $form = $ui->getForm($module,'frmUser');
    $theme->clearContent();
    $theme->insertContent($form);    

?>