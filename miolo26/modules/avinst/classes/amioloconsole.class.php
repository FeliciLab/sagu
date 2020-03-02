<?php

/**
 * Classe utilizada por scripts php do módulo avinst executados direto no console
 */

class AMioloConsole
{
    public function __construct()
    {
        
    }
    
    public function getInstance($mioloPath = '/var/www/html/avinst/', $module = 'avinst')
    {
        // Defines padrão do sistema
        define('MIOLO_PATH', $mioloPath);
        define('SCRIPT_PATH', getcwd());
        ini_set('include_path', $mioloPath . 'html/:' . $mioloPath . 'classes/');
        chdir($mioloPath.'html/');
        // Variáveis necessárias para instanciar o MIOLO
        $_SERVER['DOCUMENT_ROOT'] = $mioloPath . 'html/';
        $_SERVER['HTTP_HOST']     = 'localhost';
        $_SERVER['REQUEST_URI']   = "/index.php?module=$module&action=main";
        $_SERVER['SCRIPT_NAME']   = "/index.php?module=$module";
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
            return "Parece que houveram problemas ao instanciar e utilizar as requisições do MIOLO25.";
        }

        // Carrega as configuraçõe do módulo avinst
        //$MIOLO->conf->loadConf('avinst');
        //$MIOLO->uses('classes/avinst.class.php','avinst');
        ob_end_clean();
        return $MIOLO;
    }
}
?>