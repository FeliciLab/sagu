<?php
/**
*
*/
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MLog extends MService
{
/**
 * Attribute Description.
 */
    private $errlog;

/**
 * Attribute Description.
 */
    private $sqllog;

/**
 * Attribute Description.
 */
    private $home;

/**
 * Attribute Description.
 */
    private $isLogging;

/**
 * Attribute Description.
 */
    private $level;

/**
 * Attribute Description.
 */
    private $handler;

/**
 * Attribute Description.
 */
    private $port;

/**
 * Attribute Description.
 */
    private $socket;

/**
 * Attribute Description.
 */
    private $host;
    public $content;


/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function __construct()
    {
        parent::__construct();
        $this->home = $this->manager->getConf('home.logs');
        $this->level = $this->manager->getConf('logs.level');
        $this->handler = $this->manager->getConf('logs.handler');
        $this->port = $this->manager->getConf('logs.port');
//        $this->host = $this->manager->getConf('logs.peer');
        if (empty($this->host))
        {
            $this->host = $_SERVER['REMOTE_ADDR'];
        }
    }    

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $logname (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setLog($logname)
    {   
        $this->manager->assert($logname, 'MIOLO::setLog:' . _M('Empty database configuration name!'));
        $this->errlog  = $this->home . "/$logname-error.log";
        $this->sqllog  = $this->home . "/$logname-sql.log";
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $sql (tipo) desc
 * @param $force (tipo) desc
 * @param $conf= (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function logSQL($sql,$force=false,$conf='?')
    {           
        if ($this->level < 2) return;

        // junta multiplas linhas em uma so
        $sql = preg_replace("/\n+ */"," ",$sql);
        $sql = preg_replace("/ +/"," ",$sql);
        
        // elimina espa?os iniciais e no final da instru??o SQL
        $sql = trim($sql);
        
        // traduz aspas " em ""
        $sql = str_replace('"','""',$sql);
        
        // data e horas no formato "dd/mes/aaaa:hh:mm:ss"
        $dts = $this->manager->getSysTime();
        
        $cmd = "/^\*\*\*|" .                                            // prefixo para comandos quaisquer
        "^ *SELECT|" .
        "^ *INSERT|^ *DELETE|^ *UPDATE|^ *ALTER|^ *CREATE|" .   // comandos significantes SQL
        "^ *BEGIN|^ *END|^ *COMMIT|^ *ROLLBACK|^ *GRANT|^ *REVOKE/i";
        
        $conf = sprintf("%-15s",$conf);
        $ip   = sprintf("%15s",$this->host);
        $uid  = sprintf("%-10s",$this->manager->login->id);
        
        $line = "$ip - [$dts] - $conf - $uid \"$sql\"";
        
        if ( $force || preg_match($cmd,$sql) )
        {
        // in case this works, make $this->sqllog obsolete
            // error_log($line."\n",3,$this->sqllog);
            $fileName = trim($conf) == '' ? 'general' : trim($conf);
            $logfile = $this->home . '/' . $fileName . '-sql.log';
                       
            error_log($line."\n",3,$logfile); 
        }
        
        $this->logMessage('[SQL]'.$line);
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $error (tipo) desc
 * @param $conf (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function logError($error,$conf='miolo')
    {
        if ($this->level == 0) return;

        $ip   = sprintf("%15s",$this->host);
        $uid  = sprintf("%-10s",$this->manager->auth->iduser);

        // data e horas no formato "dd/mes/aaaa:hh:mm:ss"
        $dts = $this->manager->getSysTime();
        
        $line = "$ip - $uid - [$dts] \"$error\"";
        
        // in case this works, make $this->errlog obsolete
        // error_log($line."\n",3,$this->errlog);
        $logfile = $this->home . '/' . $conf . '-error.log';
        
        error_log($line."\n",3,$logfile); 
        
        $this->logMessage('[ERROR]'.$line);
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function isLogging()
    {  
        return ($this->level > 0); 
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $msg (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function logMessage($msg)
    {
        if ( $this->isLogging() )
        {
            $handler = "Handler" . $this->handler;
            $this->{$handler}($msg);
        }
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $msg (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    private function handlerSocket($msg)
    {
        $host = $this->manager->getConf('logs.peer');
        if ( $this->port )
        {
            if ( ! $this->socket )
            {
                $this->socket = fsockopen($host, $this->port);
                
                if ( ! $this->socket )
                {
                    $this->trace_socket = -1;
                }
            }
            fputs($this->socket, $msg."\n");
        }
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $msg (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    private function handlerFile($msg)
    {
        $logfile = $this->home . '/' . trim($this->host) . '.log';
        $ts = $this->manager->getSysTime();
        error_log($ts . ': ' . $msg."\n",3,$logfile); 
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $msg (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    private function handlerDb($msg)
    {
        $level           = $this->level;
        $this->level     = 0;
        $isLogging       = $this->isLogging;
        $this->isLogging = false;
        $ts = $this->manager->getSysTime();
        $db = $this->manager->getDatabase('miolo');
        $idLog = $db->getNewId('seq_miolo_log', 'miolo_sequence'); 
        $sql = new sql('idlog, timestamp, msg, host', 'miolo_log');
        $db->execute($sql->insert(array($idLog,$ts,$msg,$this->host)));
        $this->isLogging = $isLogging;
        $this->level     = $level;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $msg (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    private function handlerScreen($msg)
    {
        $this->content .= "document.addDebugInformation('".str_replace("\n",'',addslashes(nl2br($msg."\n")))."');";
    }

}
?>
