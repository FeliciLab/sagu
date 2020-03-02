<?php
    // DEFINES PARA SISTEMA
    define('DEBUG_DATA', false); 

    // Defines padrão do sistema
    define('MIOLO_PATH', '/var/www/html/avinst/');
    define('SCRIPT_PATH', getcwd());
    ini_set('include_path', MIOLO_PATH . 'html/:' . MIOLO_PATH . 'classes/');
    chdir(MIOLO_PATH.'html/');
    // Variáveis necessárias para instanciar o MIOLO
    $_SERVER['DOCUMENT_ROOT'] = MIOLO_PATH . 'html/';
    $_SERVER['HTTP_HOST']     = 'localhost';
    $_SERVER['REQUEST_URI']   = '/index.php?module=avinst&action=main';
    $_SERVER['SCRIPT_NAME']   = '/index.php?module=avinst';
    $_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'];
    
    // Inclui o miolo
    ob_start();
    include_once('../classes/miolo.class.php');
    
    // Retira a saída de informações
    // Tenta instanciar o MIOLO
    try
    {
        $MIOLO = MIOLO::GetInstance();
        $MIOLO->HandlerRequest();                
    }
    catch( Exception $e )
    {
        echo "Parece que houveram problemas ao instanciar e utilizar as requisições do miolo, por favor, verifique o script de instância do miolo25";
    }

    // Carrega as configuraçõe do módulo avinst
    $MIOLO->conf->loadConf('avinst');
    $MIOLO->uses('classes/avinst.class.php','avinst');
    $MIOLO->uses('classes/defines.class.php','avinst');
    ob_end_clean();
?>