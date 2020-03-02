<?php
declare(ticks=1);

require_once 'miolo.class.php';

/**
 * MIOLOConsole class.
 * Extend this class to have MIOLO on your console scripts.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Luiz Gilberto Gregory Filho
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2011/10/27
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class MIOLOConsole
{
    /**
     * @var MIOLO MIOLO instance.
     */
    protected $MIOLO;

    /**
     * @var string Module name.
     */
    private $module;

    /**
     * MIOLOConsole constructor.
     * Initialize MIOLO.
     *
     * @global string $module Simulate current module.
     */
    public function __construct()
    {
        global $module;

        // Catch Fatal Error (Rollback)
        register_shutdown_function(array($this, 'fatalErrorShutdown'));

        // Catch Ctrl+C, kill and SIGTERM (Rollback)
        pcntl_signal(SIGTERM, array($this, 'sigintShutdown'));
        pcntl_signal(SIGINT, array($this, 'sigintShutdown'));


        $pathInfo = pathinfo(__FILE__);
        $path = realpath($pathInfo['dirname'] . '/..');

        $GLOBALS['MIOLO'] = $MIOLO = $this->getMIOLOInstance($path, $module);

        ob_start();
        $this->loadMIOLO();
        ob_end_clean();
    }

    /**
     * Create a MIOLO instance
     *
     * @param string $pathMiolo MIOLO direcotry path
     * @param string $module Module
     * @return object MIOLO instance
     */
    private function getMIOLOInstance($pathMiolo, $module)
    {
        global $_SERVER;

        ob_start();
        echo "MIOLO console\n\n";

        $this->module = $module;

        /*
         * Simulates apache variables that are required by MIOLO
         */
        $_SERVER['DOCUMENT_ROOT'] = "$pathMiolo/html";
        $_SERVER['SCRIPT_FILENAME'] = "$pathMiolo/html";
        $_SERVER['HTTP_HOST'] = 'miolo2.5';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['QUERY_STRING'] = strlen($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : 'module=' . $this->module . '&action=main';
        $_SERVER['REQUEST_URI'] = "http://{$_SERVER['HTTP_HOST']}/{$_SERVER['SCRIPT_NAME']}?{$_SERVER['QUERY_STRING']}";

        /*
         * Instantiates MIOLO
         */
        $this->MIOLO = MIOLO::getInstance();
        ob_end_clean();

        return $this->MIOLO;
    }

    /**
     * Load MIOLO configuration
     */
    private function loadMIOLO()
    {
        $this->MIOLO->handlerRequest();
        $this->MIOLO->conf->loadConf($this->module);
    }

    /**
     * Shows an error message and quit.
     *
     * @param string $message Error message.
     */
    protected function error($message)
    {
        die("$message\n");
    }

    /**
     * Show the user a message and wait for his response to proceed or not.
     *
     * @param string $message Main message.
     * @param string $noMessage Message to show on quiting.
     */
    protected function prompt($message, $noMessage)
    {
        echo("$message. " . _M('Do you want to proceed anyway? (y/N)'));
        $proceed = trim(fgets(STDIN));

        if ( $proceed != 'y' )
        {
            $this->error("$noMessage. " . _M('Cancelled by the user.'));
        }
    }

    /**
     * Execute the given command.
     *
     * @param string $cmd Command to be executed.
     * @param string $errorMessage Message to be shown in case of error.
     * @param boolean $endOnError Quit execution on error.
     * @return array Array with three messages: result (string), output (array), return (string).
     */
    protected function execute($cmd, $errorMessage='', $endOnError=true)
    {
        $result = exec($cmd, $output, $return);

        if ( $return !== 0 && $endOnError )
        {
            if ( $errorMessage == '' )
            {
                $errorMessage = $result;
            }

            $this->error($errorMessage);
        }

        return array($result, $output, $return);
    }

    /**
     * Display a message to the user.
     *
     * @param string $message Message to be printed.
     */
    protected function message($message)
    {
        echo $message;
    }

    /**
     * Method that is executed when a fatal error occurs.
     */
    public function fatalErrorShutdown()
    {
        $lastError = error_get_last();
        if ( !is_null($lastError) && $lastError['type'] === E_ERROR )
        {
            // Exiting will call __destruct
            exit();
        }
    }

    /**
     * Method, that is executed, if script has been killed by.
     * SIGINT: Ctrl+C
     * SIGTERM: kill
     *
     * @param integer $signal Signal sent by the user.
     */
    public function sigintShutdown($signal)
    {
        if ( $signal === SIGINT || $signal === SIGTERM )
        {
            // Exiting will call __destruct
            exit();
        }
    }
}

?>