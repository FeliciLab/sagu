    <div id="divForm"></div>
    <div id="extContent" class="mThemeContainerExt">
        <div id="<?php echo $form ?>_content" dojoType="dojox.layout.ContentPane" layoutAlign="client" executeScripts="true" cleanContent="true" class="formContent">
            <?php echo $theme->generateElement('content') ?>
            <script>gnuteca.closeAction();</script>
        </div>
    </div>
