<?php
$configs = $MIOLO->getSession()->getValue('configs');
if (is_null($configs))
{
    // Utiliza as configs registradas na área de configurações
    $MIOLO->uses('types/avaConfig.class.php', 'avinst');

    $config = new avaConfig();
    $configs = $config->search();
    $MIOLO->getSession()->setValue('configs', $configs);
}

if (is_array($configs))
{    
    foreach ($configs as $config)
    {
        define($config[0], $config[1]);
    }
}
?>
