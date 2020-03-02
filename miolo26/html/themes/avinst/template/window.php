<div id="<?php echo $form ?>_container" class="mThemeContainer">
<div id="<?php echo $form ?>_content" dojoType="dojox.layout.ContentPane" layoutAlign="client" executeScripts="true" cleanContent="true">
<?php
    $classContent = ( ($miolo->getConf('options.mainmenu') == 2) || ($miolo->getConf('options.mainmenu') == 3) ) ? 'mThemeContainerContentFullAjax' : 'mThemeContainerContent';
    $theme->setElementClass('content', $classContent);
    echo $theme->generateElement('content');
?>
</div>
</div>

