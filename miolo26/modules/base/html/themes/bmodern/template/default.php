<div id="<?php echo $form ?>_container" class="m-theme-container">

    <div id="<?php echo $form ?>_container_top" class="m-container-top">
        <div class="banner"></div>
    </div>
    
<?php
$MIOLO = MIOLO::getInstance();
$MIOLO->uses( 'classes/bMainMenu.class.php','base');
$isloged = $MIOLO->auth->isLogged();
if ($isloged == true) {
    $theme->setElement('menu', new bMainMenu());
    $theme->setElementClass('menu', 'mThemeContainerMenu');
}
?>
    <div id="m-container-top">
      <?php echo $theme->generateElement('menu') ?>
    </div>

    <div id="<?php echo $form ?>_navbar">
        <?php // echo $theme->getElement('navigation')->hasOptions() ? $theme->generateElement('navigation') : '' ?>
    </div>
    
<?php
$classContent = (($miolo->getConf('options.mainmenu') == 2) || ($miolo->getConf('options.mainmenu') == 3) ) ? 'mThemeContainerContentFullAjax' : 'mThemeContainerContent';
$theme->setElementClass('content', $classContent);

//menus
echo $theme->getTemplate()->fetch('menu.php');

?>

    <div id="extContent" class="mThemeContainerExt">
        <div id="<?php echo $form ?>_content" dojoType="dojox.layout.ContentPane" layoutAlign="client" executeScripts="true" cleanContent="true">
            <?php echo $theme->generateElement('content') ?>
        </div>
    </div>
<?php

$MIOLO = MIOLO::getInstance();
$isloged = $MIOLO->auth->isLogged();
if ($isloged == true) {
    $theme->setElement('bottom', new bStatusBar());
    $theme->setElementClass('bottom', 'm-container-bottom');
}
?>
    <div id="<?php echo $form ?>_bottom">
        <?php echo $theme->generateElement('bottom') ?>
    </div>
</div>
