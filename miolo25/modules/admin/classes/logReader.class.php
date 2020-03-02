<?php
class logReader
{
    public $index, $log;
    public function __construct($module)
    { 
        $MIOLO  = MIOLO::getInstance();

//        $index_path = $MIOLO->getAbsolutePath().'/var/log/'.$module.'-sql';

        $configLoader = new MConfigLoader(false);
        $configLoader->loadConf($module);
        $modLogsPath = $configLoader->getConf('home.logs');
        $logsPath   = $modLogsPath ? $modLogsPath : $MIOLO->getConf('home.logs');
        $index_path = $logsPath.'/'.$module.'-sql';
        if( file_exists($index_path.'.index') )
        {
            $mode = 'r+';
        }
        else
        {
            $mode = 'x+';
        }
        
        $this->index = fopen($index_path.'.index', $mode);
        $this->log   = fopen($index_path.'.log'  , 'r');

    
    }

    public function listByDate($startDate, $endDate)
    {
        if( $this->setPosition($startDate, $endDate) )
        {
            $tsEndDate = mktime(0,0,0, substr($endDate, 3,2), substr($endDate, 0,2), substr($endDate, 6,4));
            while( ($tsLogDate <= $tsEndDate) )// and !feof($this->log) )
            {
                $pointer = ftell($this->log);
                //o teste abaixo substitui o !feof(), pois o !feof() pega um a mais
                if( !($line    = fgets($this->log)) )
                {
                    break;
                }
                $aux     = explode(' - ', $line);
                $logIP   = trim($aux[0]);
                $logDate = trim(substr($aux[1], 1, 10));
                $tsLogDate = mktime(0,0,0, substr($logDate, 3,2), substr($logDate, 0,2), substr($logDate, 6,4));
                $logTime = trim(substr($aux[1], 12, 8));
                $logMod  = trim($aux[2]);
                $logUser = trim(substr($aux[3], 0, strpos($aux[3], '"')));
                $logSQL  = trim(substr($aux[3], strpos($aux[3], '"')));
                $dataLine = array($pointer, $logIP, $logDate, $logTime, $logMod, $logUser, $logSQL);

                //Grava no indice todas as datas que passam e ainda nÃ£o estÃ£o gravadas  
                if( ($logDate != $startDate) and !$this->getIndex($logDate) )
                {
                    $this->createIndex($logDate, $pointer);
                }
                if( $tsLogDate <= $tsEndDate )
                {
                    $data[] = $dataLine;
                }
            }
//            MIOLO::vd($data);
            return $data;
        }
        return null;
    }
   
    public function getIndex($date)
    {
        fseek($this->index, 0);
        while( ! feof($this->index) )
        {
            $line     = fgets($this->index);
            $lineDate = substr($line, 0, 10);
            if( $lineDate == $date )
            {
                $pointer = substr($line, 11);
                return $pointer;
            }
        }
        return false;
    }

    public function setPosition($startDate, $endDate)
    {
        $date      = $startDate;
        $tsDate    = mktime(0,0,0, substr($startDate, 3,2), substr($startDate, 0,2), substr($startDate, 6,4));
        $tsEndDate = mktime(0,0,0, substr($endDate, 3,2),   substr($endDate, 0,2),   substr($endDate, 6,4));
        do
        {
            if( $pointer = $this->getIndex($date) )
            {
                fseek($this->log, $pointer);
                return true;
            }
//            echo $date;
            $pointer = $this->createIndex($date);
            if( $pointer )
            {
                fseek($this->log, $pointer);
                return true;
            }
            $tsDate = mktime(0,0,0, substr($date, 3,2), substr($date, 0,2)+1, substr($date, 6,4));
            $date   = date('d/m/Y', $tsDate);
        }
        while($tsDate <= $tsEndDate);
//        MIOLO::vd($pointer);
        return false;
    }

    public function createIndex($date, $pointer = null)
    {
        if( $pointer )
        {
            $indexStr = "\n".$date.' '.$pointer;
            fwrite($this->index, $indexStr);
            return $pointer;
        }
        fseek($this->log, 0);
        while( ! feof($this->log) )
        {
            $pointer  = ftell($this->log);
            $line     = fgets($this->log);
            $aux      = explode(' - ', $line);
            $lineDate = substr($aux[1], 1, 10);
            if( ($lineDate == $date) and !$this->getIndex($date) )
            {
                $indexStr = "\n".$date.' '.$pointer;
                fwrite($this->index, $indexStr);
                return $pointer;
            }
        }
        return null;
    }

    public function getLog($pointer)
    {
        fseek($this->log, $pointer);
        if( !($line    = fgets($this->log)) )
        {
            return null;
        }
        $aux      = explode(' - ', $line);
        $logIP    = trim($aux[0]);
        $logDate  = trim(substr($aux[1], 1, 10));
        $logTime  = trim(substr($aux[1], 12, 8));
        $logMod   = trim($aux[2]);
        $logUser  = trim(substr($aux[3], 0, strpos($aux[3], '"')));
        $logSQL   = trim(substr($aux[3], strpos($aux[3], '"')));
        $dataLine = array($pointer, $logIP, $logDate, $logTime, $logMod, $logUser, $logSQL);
        return $dataLine;
    }
}
?>
