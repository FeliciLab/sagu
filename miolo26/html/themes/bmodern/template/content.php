<?php
    $classContent = ( ($miolo->getConf('options.mainmenu') == 2) || ($miolo->getConf('options.mainmenu') == 3) ) ? 'mThemeContainerContentFullAjax' : 'mThemeContainerContent';
    $theme->setElementClass('content', $classContent);
    echo $theme->generateElement('content');
?>
