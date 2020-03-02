<?php

class AJasperReport extends MJasperReport
{
    public function __construct($db='admin', $connectionType='local')
    {
        parent::__construct($db,$connectionType);
    }

	/**
     * Generate the report from a jasper file.
     *
     * @param string $module Module where the report is located.
     * @param string $name Name of the report (without extension).
     * @param array $parameters Parameters to be passed to the report.
     * @param string $filetype Report file type.
     * @param boolean $save Whether to save the report or download it.
     * @return integer Returns 1 if succeeded.
     */
    public function execute($module, $name, $parameters=NULL, $filetype='PDF', $save=FALSE, $returnFileContent=FALSE)
    {
        $MIOLO = MIOLO::getInstance();
        $page = $MIOLO->getPage();

        $this->filetype = isset($filetype) ? $filetype : 'PDF';

        $filein = addslashes($MIOLO->GetModulePath($module, 'reports/' . $name . '.jasper'));

        $uniqid = uniqid(md5(uniqid("")));
        $this->fileout = $uniqid . "." . strtolower($this->filetype);
        $pathout = $MIOLO->getConf("home.reports") . '/' . $this->fileout;

        $comando = 'echo $JAVA_HOME';
        $pathJava = trim(shell_exec($comando));

        $pathMJasper = $MIOLO->getConf("home.extensions") . "/jasper";
        $pathLibs = "$pathMJasper/lib/deps/*:$pathMJasper/lib/ext/*:$pathMJasper/lib/ireport/*:$pathMJasper/lib/jasperreports/*";
        $classPath = "$pathLibs:$pathMJasper/:$pathJava/lib";

        return $this->fill($filein, $pathout, $filetype, $parameters, $classPath, $save, $returnFileContent);
    }

    /**
     * Generate the report based on the given parameters.
     *
     * @param string $fileIn Jasper or JRXML file.
     * @param string $fileOut Generated report file.
     * @param string $filetype Report file type.
     * @param array $parameters Parameters to be passed to the report.
     * @param string $classPath Libraries path.
     * @param boolean $save Whether to save the report or download it.
     * @return integer Returns 1 if succeeded. 
     */
    public function fill($fileIn, $fileOut, $filetype, $parameters, $classPath, $save, $returnFileContent)
    {
        $MIOLO = MIOLO::getInstance();
        $page = $MIOLO->getPage();

        if ( $this->connectionType == 'local' )
        {
            // Solution without TomCat
            $param = "";
            if ( is_array($parameters) )
            {
                foreach ( $parameters as $pn => $pv )
                {
                    $param .= '&' . $pn . "~" . $pv;
                }
            }

            $this->objDb = $MIOLO->GetDatabase($this->db);
            $dbUser = $this->objDb->user;
            $dbPass = $this->objDb->pass;
            $dbHost = $this->objDb->host;
            $dbName = $this->objDb->db;
            $dbPort = $this->objDb->port;

            if ( $this->objDb->system == 'postgres' )
            {
                $jdbcDriver = 'org.postgresql.Driver';
                $dbPort = $dbPort ? $dbPort : '5432';
                $jdbcDb = "jdbc:postgresql://{$dbHost}:{$dbPort}/{$dbName}";
            }
            elseif ( $this->objDb->system == 'mysql' )
            {
                $jdbcDriver = 'com.mysql.jdbc.Driver';
                $dbPort = $dbPort ? $dbPort : '3306';
                $jdbcDb = "jdbc:mysql://{$dbHost}:{$dbPort}/{$dbName}";
            }
            else
            {
                $jdbcDriver = 'org.postgresql.Driver';
                $jdbcDb = "jdbc:postgresql://localhost:5432/postgres";
                $dbUser = "postgres";
                $dbPass = "postgres";
            }

            $param = "relatorio~$fileIn" . $param . "&fileout~" . $fileOut . "&filetype~" . $this->filetype;

            $comando = 'echo $JAVA_HOME';
            $pathJava = trim(shell_exec($comando));
            $pathLog = $MIOLO->GetConf('home.logs');
            $uniqid = uniqid(md5(uniqid("")));
            $fileLog = $uniqid . ".txt";

            $pathJava = $pathJava ? $pathJava . '/bin/java' : 'java'; //if JAVA_HOME is unsetted, get java from default path

            $lang = $MIOLO->getConf('i18n.language');
            $cmd = "export LANG=$lang; " . $pathJava . " -classpath $classPath MJasper \"{$pathMJasper}\" \"{$param}\" \"{$dbUser}\" \"{$dbPass}\" \"{$jdbcDriver}\" \"{$jdbcDb}\"";
            $cmd .= " 2> $pathLog/$fileLog";

            exec($cmd, $output);

            // Remove the log file if it's empty
            $log = fopen("$pathLog/$fileLog", 'r');
            $fileSize = filesize("$pathLog/$fileLog");
            if ( $fileSize > 0 )
            {
                $logData = fread($log, $fileSize);
                fclose($log);
                if ( $logData == '' )
                {
                    unlink("$pathLog/$fileLog");
                }
            }

            // JAVA application without erros!
            if ( trim($output[0]) == "end" )
            {
                // Break line incompatibility problem with Windows and Unix
                if ( strtoupper(trim($this->filetype)) == "TXT" ) Mutil::unix2dos($fileOut);
                
                if($returnFileContent)
                {
                    $this->fileout = $MIOLO->getActionURL('miolo', 'reports:' . $this->fileout);
                    return file_get_contents($this->fileout);
                }

                if ( $save )
                {
                    $MIOLO->response->sendDownload($fileOut);
                }
                else
                {
                    $this->fileout = $MIOLO->getActionURL('miolo', 'reports:' . $this->fileout);
                    $MIOLO->getPage()->window($this->fileout);
                }
                return 1;
            }
            elseif ( trim($output[0]) == 'empty' )
            {
                return 0;
            }
            else
            {
                // Main message
                $detail = _M('The report could not be generated');

                // Link to the log file
                if ( $fileSize > 0 )
                {
                    $link = new MLink('', _M('here'), $MIOLO->GetActionURL('miolo', "logs:$fileLog"), NULL, MLink::TARGET_BLANK);
                    $link->setGenerateOnClick(false);
                    $detail .= ". ";
                    $detail .= _M('Click @1 for more details.', 'miolo', preg_replace("/<br>|\n/", '', $link->generate()));
                }

                // Output error
                if ( $output[0] != 'null' )
                {
                    $message = implode('<br/><br/>', $output);
                    $div = new MExpandDiv('errorDiv', "$detail<br/><br/>$message");
                    $detail = $div->generate();
                }

                throw new EControlException($detail);
            }
        }
        else if ( $this->connectionType == 'remote' )
        {
            //TomCat
            $this->fileout = $MIOLO->getConf("home.url_jasper") . "?bd={$this->db}&relatorio=$filein" . $param;
            $MIOLO->getPage()->window($this->fileout);
        }
    }
}
?>


