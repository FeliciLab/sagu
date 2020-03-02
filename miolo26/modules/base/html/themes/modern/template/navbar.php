<?php
if ($theme->getElement('navigation')->hasOptions())
{
    echo $theme->generateElement('navigation');
}
?>
