<?php
$MIOLO->uses('classes/middleware/iReport/ajasperreport.class.php','avinst');

class IReport
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $report = new AJasperReport('avinst');
        //$parameters['int_id'] = $this->getFieldValue('idcurso');
        //$parameters['str_description'] = _M('Test');
        $html = $report->execute('avinst', 'report1', NULL, 'HTML', FALSE, TRUE);
        $controls = new MDiv(null,$html);
        //$MIOLO->ajax->setResponse($controls,'widgets');
    }    
}
?>


