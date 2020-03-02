<?php
$MIOLO = MIOLO::getInstance();
$url = $MIOLO->getConf('home.url');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="ltr" xml:lang="pt-br" lang="pt-br" xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>MIOLO</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="<?php echo $url; ?>/themes/modern/miolo.css">
    </head>
    <body class="messageScreen">
        <div>
            <img src="<?php echo $url; ?>/themes/modern/images/logo_miolo_cop.png">
            <p>O sistema encontra-se em manutenção.</p>
            <div>Pedimos desculpa pelo transtorno.<br/>
            Aguarde um momento e tente novamente.</div>
        </div>
    </body>
</html>
