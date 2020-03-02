<div id="<?php echo $form?>_container" class="mThemeContainer">
    <div id="<?php echo $form?>_content" dojoType="dojox.layout.ContentPane" layoutAlign="client" executeScripts="true" cleanContent="true">
        <?php
        $theme->setElementClass('content', 'mThemeContainerContentFullAjax');
        echo $theme->generateElement('content');
        ?>
    </div>
</div>

