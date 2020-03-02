<?php
// menus	    
if ( $miolo->getConf('options.mainmenu') == 2 )
{
    $idModule = 'mThemeContainerModule';
    $idMenu = 'mThemeContainerDhtmlMenu';
}
else if ( $miolo->getConf('options.mainmenu') == 3 )
{
    $idModule = 'mThemeContainerModule2';
    $needTable = true;
}
else
{
    $idMenu = 'mThemeContainerMenu';
}
$theme->setElementId('menus', $idMenu);


if ($theme->hasMenuOptions())
{
    if ($needTable == true)
    {
?>
      <div id="mThemeMenus">
      <div id="mThemeMenuBox">
         <div id="<?php echo $form ?>_menu">
        <table collspacing=0 cellpadding=0 cellspacing=1 border=0>
            <tr>
<?php
    }
    echo $theme->generateElement('menus');
    if ($needTable == true)
    {
?>
            </tr>
        </table>
        </div>
     </div>
    </div>

<?php
    }
}
?>