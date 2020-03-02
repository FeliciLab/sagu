<?php
class Adianti extends middleware
{
    // TODO: Criar tela para configurar constantes OU adicionar como parâmetros do sistema
    const ADIANTI_WS_ENCODING  = 'UTF-8';
    const ADIANTI_WS_EXCEPTION = true;
    const ADIANTI_WS_LOCATION  = ADIANTI_WS_LOCATION_CONFIG;
    const ADIANTI_WS_URI       = ADIANTI_WS_URI_CONFIG;
    const ADIANTI_WS_TRACE     = 1;
    const ADIANTI_LOGIN        = 'avinst';
    const ADIANTI_PASSWORD     = 'aviteste';
    const ADIANTI_FILES_PWD    = '/reports/';
    
    private $webservice;
    private $middlewareData;
    
    //
    // Cria a conexão com o adianti
    //
    public function __construct()
    {
        try
        {
            $MIOLO = MIOLO::getInstance();
            $params['location']   = self::ADIANTI_WS_LOCATION;
            $params['uri']        = self::ADIANTI_WS_URI;
            $params['trace']      = self::ADIANTI_WS_TRACE;
            $params['encoding']   = self::ADIANTI_WS_ENCODING;
            $params['exceptions'] = self::ADIANTI_WS_EXCEPTION;
            $params['connection_timeout'] = 10;
            $this->webservice = new soapClient(null, $params);
            $this->calls = array();
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }
    
    //
    // Chamada para colocar relatórios no pool a serem gerados pelo adianti
    //
    public function addCalls($widgetName, $callData)
    {
        if (is_array($callData))
        {
            $this->calls = array_merge($this->calls, $callData);
        }
        else
        {
            return false;
        }
        return true;
    }

    //
    // Gera os relatórios em lote, com apenas uma chamada
    //
    public function fetchCalls()
    {
        if (is_array($this->calls))
        {
            try
            {
                $content = $this->webservice->generateReports(self::ADIANTI_LOGIN, self::ADIANTI_PASSWORD, $this->calls);

                if (is_array($content))
                {
                    foreach ($content as $ws => $data)
                    {
                        $this->calls[$ws]['content'] = $this->parseContent($data, $this->calls[$ws]['format']);
                    }           
                }
                return $this->calls;
            }
            catch (Exception $e)
            {
                $this->errors = $e->getMessage();
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    //
    // Pega o conteúdo retornado do webservice e analisa.
    // Caso for do tipo arquivo, grava em um diretório temporário
    // e retorna o nome do arquivo
    // Caso contrário retorna o conteúdo puro
    //
    private function parseContent($report, $reportType)
    {
        $reportData = base64_decode($report);
        if ($reportData == null)
        {
            mb_convert_encoding($reportData, 'UTF-8');
        }
        if ($reportType == 'html')
        {
            return $reportData;
        }
        else
        {
            if ($reportType == 'png')
            {
                // PNG byte sequence: 137 80 78 71 13 10 26 10
                if (( (ord(substr($reportData,0,1)) == 137) AND
                      (ord(substr($reportData,1,1)) ==  80) AND
                      (ord(substr($reportData,2,1)) ==  78) AND
                      (ord(substr($reportData,3,1)) ==  71) ) == false)
                {
                    throw new Exception($reportData);
                    return $reportData;
                }
            }
            if ($reportType == 'odt')
            {
                // ODT byte sequence: 80 75 3 4
                if (( (ord(substr($reportData,0,1)) == 80) AND
                      (ord(substr($reportData,1,1)) ==  75) AND
                      (ord(substr($reportData,2,1)) ==  3) AND
                      (ord(substr($reportData,3,1)) ==  4) ) == false)
                {
                    throw new Exception($reportData);
                    return $reportData;
                }
            }
            $MIOLO = MIOLO::getInstance();
            $dirPath = $MIOLO->getAbsolutePath(self::ADIANTI_FILES_PWD, 'avinst');
            $reportName = md5($report.  uniqid()).'.'.$reportType;
            file_put_contents($dirPath.$reportName, $reportData);
            $fileUrl = $MIOLO->getAbsoluteUrl('reports/'.$reportName);
            return $fileUrl;
        }
        return false;
    }
}
?>