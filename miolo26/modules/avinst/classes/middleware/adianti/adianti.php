<?php
class MiddleWareAdianti
{
    const ADIANTI_WS_ENCODING  = 'UTF-8';
    const ADIANTI_WS_EXCEPTION = true;
    const ADIANTI_WS_LOCATION  = 'http://www.devel.univates.br/reports/ws/report.ws.php';
    const ADIANTI_WS_URI       = 'http://www.devel.univates.br/';
    const ADIANTI_WS_TRACE     = 1;
    const ADIANTI_LOGIN        = 'avinst';
    const ADIANTI_PASSWORD     = 'aviteste';
    const ADIANTI_FILES_PWD    = '/reports/';
    private $webservice;
    
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
            $this->webservice = new soapClient(null, $params);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }
    
    //
    // Executa o pedido e retorna o relatório, passando o modelo e os parâmetros
    //
    public function generateReport($report, $param, $reportType = null)
    {
        $MIOLO = MIOLO::getInstance();
        try
        {
            if ($reportType == null)
            {
                $reportType = $param['format'];
            }
            $report = $this->webservice->generateReport(self::ADIANTI_LOGIN, self::ADIANTI_PASSWORD, $report, $param);
            $this->reportData = base64_decode($report);
            if ($this->reportData == null)
            {
            }
            if ($reportType == 'html')
            {
                return $this->reportData;
            }
            else
            {
                $dirPath = $MIOLO->getAbsolutePath(self::ADIANTI_FILES_PWD, 'avinst');
                $reportName = md5($report.  uniqid()).'.'.$reportType;
                file_put_contents($dirPath.$reportName, $this->reportData);
                $fileUrl = $MIOLO->getActionURL('avinst', 'reports:'.$reportName);
                return $fileUrl;
            }
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }
}
?>
