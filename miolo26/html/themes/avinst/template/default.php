<div id="<?php echo $form ?>_container" class="mThemeContainer">

    <div id="<?php echo $form ?>_container_top" class="mThemeContainerTop">
            <?php echo $theme->generateElement('topmenu'); ?>
        <div id="topMenu" class="mDivTopSystem">
        </div>
        <!--<div class="banner"></div> -->
    </div>

    <div id="<?php echo $form ?>_menu"></div>

    <div id="<?php echo $form ?>_navbar">
        <?php echo $theme->getElement('navigation')->hasOptions() ? $theme->generateElement('navigation') : '' ?>
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
?>

    <div id="<?php echo $form ?>_bottom">
        <?php echo $theme->generateElement('bottom') ?>
    </div>
</div>
