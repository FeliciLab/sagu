<?php
class MDOMPDF extends MReport
{
    public $dompdf;

    public function __construct($paper = 'a4', $orientation = 'portrait')
    {
        $MIOLO = MIOLO::getInstance();

        $conf = $MIOLO->getAbsolutePath( 'classes/extensions/dompdf/dompdf_config.inc.php');
        require_once($conf);

        $old_limit = ini_set("memory_limit", "80M");
    
        if ($paper=='')
        {
            $paper = DOMPDF_DEFAULT_PAPER_SIZE;
        }
        if ($orientation=='')
        {
            $orientation = "portrait";
        }
        $this->dompdf = new DOMPDF();
        $this->dompdf->set_paper($paper, $orientation);
    }

    public function setInput($str,$params=NULL)
    {
        if ($params)
        {
           global $var;
           $var = $params;
        }

        if ( $str != '') {
          $this->dompdf->load_html($str);
        } else 
          $this->dompdf->load_html_file($file);
        
        if ( isset($base_path) ) {
          $this->dompdf->set_base_path($base_path);
        }
    }

    public function execute()
    {
        global $MIOLOCONF;
        $MIOLO = MIOLO::getInstance();
        $page = $MIOLO->getPage();

        $this->dompdf->render();

        $pdfcode = $this->dompdf->output();
        if ( strtolower(DOMPDF_PDF_BACKEND) == "gd" ) 
            $outfile = str_replace(".pdf", ".png", $outfile);
    
        $fname = substr(uniqid(md5(uniqid(""))), 0, 10) . '.pdf';
        $this->fileexp = $MIOLO->getConf('home.reports') . '/' . $fname;
        $fp = fopen($this->fileexp, 'x');
        fwrite($fp, $pdfcode);
        fclose ($fp);
        $this->fileout = $MIOLO->getActionURL('miolo', 'reports:' . $fname);

//        file_put_contents($this->fileout, $pdfcode);
        $page->redirect($this->fileout);
    }
}
?>
