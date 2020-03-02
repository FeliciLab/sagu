<?php

class MJasperReport extends MReport
{
    /**
     * Java type constants.
     */
    const JAVA_BIG_DECIMAL = 'java.math.BigDecimal';
    const JAVA_BOOLEAN = 'java.lang.Boolean';
    const JAVA_DATE = 'java.util.Date';
    const JAVA_DOUBLE = 'java.lang.Double';
    const JAVA_FLOAT = 'java.lang.Float';
    const JAVA_INTEGER = 'java.lang.Integer';
    const JAVA_LONG = 'java.lang.Long';
    const JAVA_NUMBER = 'java.lang.Number';
    const JAVA_SHORT = 'java.lang.Short';
    const JAVA_STRING = 'java.lang.String';
    const JAVA_TIME = 'java.sql.Time';
    const JAVA_TIMESTAMP = 'java.sql.Timestamp';

    /**
     * Simplified type constants.
     */
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATE = 'date';
    const TYPE_FLOAT = 'float';
    const TYPE_INTEGER = 'integer';
    const TYPE_STRING = 'string';
    const TYPE_TIME = 'time';
    const TYPE_TIMESTAMP = 'timestamp';

    /**
     * @var string File type. Can be pdf, doc, xls, rtf, htm, html or rpt.
     */
    public $filetype;

    public $fileout;
    public $fileexp;
    public $objDb;
    public $db;
    public $connectionType;

    /**
     * @var string SQL query to be used by the report. Important: it works only with JRXML files.
     */
    private $query;
    
    private $absoluteFileOut;

    /**
     * MJasperReport constructor.
     *
     * @param string $db Database name.
     * @param string $connectionType Connection type.
     */
    public function __construct($db='admin', $connectionType='local')
    {
        $this->db = $db;
        $this->connectionType = $connectionType;
    }

    /**
     * Generate the report from a jasper file.
     *
     * @param string $module Module where the report is located.
     * @param string $name Name of the report (without extension).
     * @param array $parameters Parameters to be passed to the report.
     * @param string $filetype Report file type.
     * @param boolean $save Whether to save the report or download it.
     * @param boolean $onlyGenerate true to only generate the report file, without any browser action.
     * @return integer Returns 1 if succeeded.
     */
    public function execute($module, $name, $parameters=NULL, $filetype='PDF', $save=FALSE, $onlyGenerate=FALSE)
    {
        $MIOLO = MIOLO::getInstance();
        $page = $MIOLO->getPage();

        $this->filetype = isset($filetype) ? $filetype : 'PDF';

        if ( file_exists($name) )
        {
            $fileIn = $name;
        }
        else
        {
            $filein = addslashes($MIOLO->GetModulePath($module, 'reports/' . $name . '.jasper'));
        }
        
        //$filein = addslashes($MIOLO->GetModulePath($module, 'reports/' . $name . '.jasper'));

        $uniqid = uniqid(md5(uniqid("")));
        $this->fileout = $uniqid . "." . strtolower($this->filetype);
        $pathout = $MIOLO->getConf("home.reports") . '/' . $this->fileout;

        $comando = 'echo $JAVA_HOME';
        $pathJava = trim(shell_exec($comando));

        $pathMJasper = $MIOLO->getConf("home.extensions") . "/jasper";
        $pathLibs = "$pathMJasper/lib/deps/*:$pathMJasper/lib/ext/*:$pathMJasper/lib/ireport/*:$pathMJasper/lib/jasperreports/*";
        $classPath = "$pathLibs:$pathMJasper/:$pathJava/lib";

        return $this->fill($filein, $pathout, $filetype, $parameters, $classPath, $save, $onlyGenerate);
    }
    
    /**
     * Get the generated report filename.
     * 
     * @param string $module Module where the report is located.
     * @param string $name Name of the report (without extension).
     * @param array $parameters Parameters to be passed to the report.
     * @param string $filetype Report file type.
     * @param boolean $save Whether to save the report or download it.
     * @return string Path to the generated report file.
     */
    public function getReportFilePath($module, $name, $parameters=NULL, $filetype='PDF', $save=FALSE)
    {
        $MIOLO = MIOLO::getInstance();
        
        $path = null;
        if ( $this->execute($module, $name, $parameters, $filetype, $save, true) )
        {
            $path = $MIOLO->getConf("home.reports") . '/' . $this->fileout;
        }
        
        return $path;
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
     * @param boolean $onlyGenerate true to only generate the report file, without any browser action.
     * @return integer Returns 1 if succeeded. 
     */
    public function fill($fileIn, $fileOut, $filetype, $parameters, $classPath, $save, $onlyGenerate=FALSE)
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
                $h = explode(':',$dbHost);
                $host       = $h[0];
                
                if (is_null($dbPort) )
                {
                    $dbPort       = is_null($h[1]) ? ':5432' : null;
                    $jdbcDb = "jdbc:postgresql://{$dbHost}{$dbPort}/{$dbName}";
                }
                else
                {
                    $dbPort       = is_null($h[1]) ? $dbPort : null;
                    
                    if ( substr($dbPort, -1) == ':' )
                    {
                        $dbPort = substr($dbPort, 0, -1);
                    }
                    if ( strlen($dbPort) > 0 )
                    {
                        $dbPort = ':' . $dbPort;
                    }
                    
                    $jdbcDb = "jdbc:postgresql://{$dbHost}{$dbPort}/{$dbName}"; 
                }
                
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

                if ( !$onlyGenerate )
                {
                    if ( $save )
                    {
                        $MIOLO->response->sendDownload($fileOut);
                    }
                    else
                    {
                        $this->fileout = $MIOLO->getActionURL('miolo', 'reports:' . $this->fileout);
                        $MIOLO->getPage()->window($this->fileout);
                    }
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

    /**
     * Generate the report from a JRXML file.
     *
     * @param string $module Module where the report is located.
     * @param string $name Name of the report (without extension).
     * @param array $parameters Parameters to be passed to the report.
     * @param string $filetype Report file type.
     * @param boolean $save Whether to save the report or download it.
     * @return integer Returns 1 if succeeded.
     */
    public function executeJRXML($module, $name, $parameters=NULL, $filetype='PDF', $save=FALSE)
    {
        $MIOLO = MIOLO::getInstance();

        $this->filetype = isset($filetype) ? $filetype : 'PDF';

        if ( substr_count($name, 'jrxml') == 0 )
        {
            $name = $name . '.jrxml';
        }
        
        if ( file_exists($name) )
        {
            $fileIn = $name;
        }
        else
        {
            $fileIn = addslashes($MIOLO->GetModulePath($module, 'reports/' . $name));
            if ( !file_exists($fileIn) )
            {
                $fileIn = str_replace('miolo20', 'miolo26', $fileIn);
            }
        }

        // If query attribute is set, edit jrxml file, putting the query in it
        if ( $this->query )
        {
            $xml = simplexml_load_file($fileIn);

            $dom = dom_import_simplexml($xml);
            $node = $dom->ownerDocument;
            $queryString = $node->createElement('queryString');
            $queryString->appendChild($node->createCDATASection($this->query));
            $oldChild = $node->getElementsByTagName('queryString')->item(0);
            $dom->replaceChild($queryString, $oldChild);

            $xml = simplexml_import_dom($dom);

            // Create a temporary file
            $tempFile = $MIOLO->getConf("home.reports") . "/$name" . rand() . '.jrxml';

            $handler = fopen($tempFile, 'w');
            fwrite($handler, $xml->asXML());
            fclose($handler);

            $fileIn = $tempFile;
        }

        $uniqid = uniqid(md5(uniqid("")));
        $this->fileout = $uniqid . "." . strtolower($this->filetype);
        $pathout = $MIOLO->getConf("home.reports") . '/' . $this->fileout;
        
        $this->setAbsoluteFileOut($pathout);

        $comando = 'echo $JAVA_HOME';
        $pathJava = trim(shell_exec($comando));

        $pathMJasper = $MIOLO->getConf("home.extensions") . "/jasper";
        $pathLibs = "$pathMJasper/lib/deps/*:$pathMJasper/lib/ext/*:$pathMJasper/lib/ireport/*:$pathMJasper/lib/jasperreports/*";
        $classPath = "$pathLibs:$pathMJasper/:$pathJava/lib";

        $fill = $this->fill($fileIn, $pathout, $this->filetype, $parameters, $classPath, $save);

        // Remove the temporary JRXML
        if ( $tempFile )
        {
            unlink($tempFile);
        }

        return $fill;
    }

    /**
     * @param string $query SQL query.
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return string SQL query.
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get a simpler type based on the Java type.
     *
     * @param string $javaType Java type. E.g. "java.lang.String".
     * @return string Simpler type. E.g. "string".
     */
    private static function getTypeFromJavaType($javaType)
    {
        switch ( $javaType )
        {
            case self::JAVA_BOOLEAN:
                $type = self::TYPE_BOOLEAN;
                break;

            case self::JAVA_DATE:
                $type = self::TYPE_DATE;
                break;

            case self::JAVA_BIG_DECIMAL:
            case self::JAVA_DOUBLE:
            case self::JAVA_FLOAT:
            case self::JAVA_NUMBER:
                $type = self::TYPE_FLOAT;
                break;

            case self::JAVA_INTEGER:
            case self::JAVA_LONG:
            case self::JAVA_SHORT:
                $type = self::TYPE_INTEGER;
                break;

            case self::JAVA_TIME:
                $type = self::TYPE_TIME;
                break;

            case self::JAVA_TIMESTAMP:
                $type = self::TYPE_TIMESTAMP;
                break;

            case self::JAVA_STRING:
            default:
                $type = self::TYPE_STRING;
                break;
        }

        return $type;
    }

    /**
     * Get the report parameters.
     * Return an array with this format:
     *     array(
     *         'parameter1id' => array(
     *             'type' => some of TYPE_ constants,
     *             'ohterProperty' => 'propertyValue',
     *             ...
     *         ),
     *         'parameter2id' => array(
     *             'type' => some of TYPE_ constants,
     *             ...
     *         ),
     *         ...
     *     )
     *
     * @param string $module Report module.
     * @param string $report Report name.
     * @return array Parameters array.
     */
    public static function getParameters($module, $report)
    {
        $MIOLO = MIOLO::getInstance();
        
        if ( file_exists($report) )
        {
            $fileIn = $report;
        }
        else
        {
            $fileIn = addslashes($MIOLO->getModulePath($module, 'reports/' . $report . '.jrxml'));
            if ( !file_exists($fileIn) )
            {
                return NULL;
            }
        }

        $xml = simplexml_load_file($fileIn);

        $parameters = array();

        foreach ( $xml->parameter as $parameter )
        {
            // Create an array to represent a parameter
            $p = array();
            $name = (string) $parameter->attributes()->name;

            // Put the simplified parameter type on "type" attribute
            $javaType = (string) $parameter->attributes()->class;
            $p['type'] = self::getTypeFromJavaType($javaType);

            // Iterate through parameter properties to set them on the array
            foreach ( $parameter->property as $property )
            {
                $key = (string) $property->attributes()->name;
                $value = (string) $property->attributes()->value;

                $p[$key] = $value;
            }

            $parameters[$name] = $p;
        }
        
        return $parameters;
    }
    
    public function getAbsoluteFileOut()
    {
        return $this->absoluteFileOut;
    }

    public function setAbsoluteFileOut($absoluteFileOut)
    {
        $this->absoluteFileOut = $absoluteFileOut;
    }
}

?>