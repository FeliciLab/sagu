<?php
class MDOMPDFReport extends MForm
{
    public $options;
    public $rawdata;
    public $slot;
    public $pdf;    // MDOMPDF object
    public $y;
    public $fontFamily;

    public function __construct($orientation = 'portrait', $paper='a4')
    {
        global $state;
        $MIOLO = MIOLO::getInstance();
        $page = $MIOLO->getPage();
		
       $this->setPDF(new MDOMPDF($orientation,$paper));
       parent::__construct();
       $this->slot = array();
       $this->rawdata = NULL;
       $this->initializeOptions();
       $this->setWidth(100);
    }

    public function setPaper($orientation = 'portrait', $paper='a4')
    {
        $this->pdf->dompdf->set_paper($paper, $orientation);
    }

    public function initializeOptions()
    {
        $this->options['showLines'] = 0;
        $this->options['showHeadings'] = 1;
        $this->options['showTableTitle'] = 1;
        $this->options['shaded'] = 1;
        $this->options['shadeCol'] = array(0.8,0.8,0.8);
        $this->options['shadeCol2'] = array(0.7,0.7,0.7);
        $this->options['fontSize'] = 10;
        $this->options['textCol'] = array(0,0,0);
        $this->options['titleFontSize'] = 14;
        $this->options['rowGap'] = 2;
        $this->options['colGap'] = 5;
        $this->options['lineCol'] = array(0,0,0);
        $this->options['xPos'] = 'center';
        $this->options['xOrientation'] = 'center';
        $this->options['width'] = 0;
        $this->options['maxWidth'] = 596;
        $this->options['minRowSpace'] = -100;
        $this->options['innerLineThickness'] = 1;
        $this->options['outerLineThickness'] = 1;
        $this->options['protectRows'] = 1;
    }
    
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    public function setPDF($pdf)
    {
        $this->pdf = $pdf;
    }
 
    public function getPDF()
    {
        return $this->pdf;
    }

    public function setWidth($width)
    {
        $this->setOption('width',$width);
    }

    public function execute()
    {
        $this->pdf->execute();
    }

    public function generateFile($fileName, $params=NULL)
    {
        $html = file_get_contents($fileName);
        $this->pdf->setInput($html,$params);
        $this->pdf->execute();
    }

    public function generate()
    {
        $body = new MDiv('',$this->generateBody());
        if (!is_null($this->bgColor)) $body->addStyle('backgroundColor',$this->bgColor);
        $this->box->setControls(array($body, $footer));
        $id = $this->getId();
        $form = $body;
        if (!is_null($this->align)) $form->addBoxStyle('text-align',$this->align);
        if (!is_null($this->fontFamily)) $form->addBoxStyle('fontFamily',$this->fontFamily);
        $theme = $this->manager->getTheme();
        $theme->setContent($form);
        $theme->setLayout('DOMPdf'); 
        $this->page->generateMethod = 'generateDOMPdf';
        $html = $this->page->generate();
        $this->pdf->setInput($html);
        $this->pdf->execute();
    }
}
?>