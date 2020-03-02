<?php

/**
   Miolo 2.0
   PDFReport - base class to PDF reports
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MPDFReportColumn extends MGridColumn
{
/**
 * Attribute Description.
 */
    public $name;


/**
 * Brief Description.
 * Complete Description.
 *
 * @param $name (tipo) desc
 * @param $title' (tipo) desc
 * @param $align='left' (tipo) desc
 * @param $nowrap= (tipo) desc
 * @param $width=0 (tipo) desc
 * @param $visible= (tipo) desc
 * @param $options= (tipo) desc
 * @param $order=false (tipo) desc
 * @param $filter=false (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function __construct($name,$title='', $align='left', $nowrap=false, $width=0, $visible=true, $options=null, $order=false, $filter=false)
    {
        parent::__construct($title, $align, $nowrap, $width, $visible, $options, $order, $filter);
        $this->name = $name;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generate()
    {
    }
}

/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MPDFReportControl extends MGridControl
{
/**
 * Attribute Description.
 */
    public $name;


/**
 * Brief Description.
 * Complete Description.
 *
 * @param $name (tipo) desc
 * @param $control (tipo) desc
 * @param $title' (tipo) desc
 * @param $align='left' (tipo) desc
 * @param $nowrap= (tipo) desc
 * @param $width=0 (tipo) desc
 * @param $visible= (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function __construct($name,$control, $title='',$align='left',$nowrap=false,$width=0,$visible=true)
    {
        parent::__construct($control, $title,$align,$nowrap,$width,$visible);
        $this->name = $name;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generate()
    {
    }
}

/**
   PDFReport - base class to PDF reports
 */
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MPDFReport extends MGrid
{
/**
 * Attribute Description.
 */
    public $options;

/**
 * Attribute Description.
 */
    public $rawdata;

/**
 * Attribute Description.
 */
    public $slot;

/**
 * Attribute Description.
 */
    public $ezpdf;  // ezPdfReport object

/**
 * Attribute Description.
 */
    public $pdf;    // MCezPdf object

/**
 * Attribute Description.
 */
    public $y;


/**
 * Brief Description.
 * Complete Description.
 *
 * @param $data (tipo) desc
 * @param $columns (tipo) desc
 * @param $pagelength1 (tipo) desc
 * @param $index=0 (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function __construct($data, $columns, $pageLength=1, $index=0, $orientation = 'portrait', $paper='a4')
    {
        global $state;
        $MIOLO = MIOLO::getInstance();
        $page = $MIOLO->getPage();
		
       $this->setPDF(new MezPDFReport('2',$orientation,$paper));
       parent::__construct($data, $columns, '', $pageLength, $index);
       $this->slot = array();
       $this->rawdata = NULL;
       $this->initializeOptions();
       $this->setWidth(100);
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
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
    
/**
 * Brief Description.
 * Complete Description.
 *
 * @param $option (tipo) desc
 * @param $value (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $pdf (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setPDF($pdf)
    {
        $this->ezpdf = $pdf;
        $this->pdf = $pdf->pdf;
    }
 
/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function getPDF()
    {
        return $this->ezpdf;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $width (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setWidth($width)
    {
        $width = $this->pdf->getWidthFromPercent($width);
        $this->setOption('width',$width);
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $column (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function addColumn($column)
    {
        $this->columns[$column->name] = $column;
        $this->columns[$column->name]->width = $this->pdf->getWidthFromPercent($column->width);
        $this->columns[$column->name]->index = count($this->columns);
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $columns (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setColumns($columns)
    {
        $this->columns = NULL;
        if ($columns != NULL)
        {
            if (! is_array($columns) ) $columns = array($columns);
            foreach($columns as $k=>$c)
            {
                $this->columns[$c->name] = $c;
                $this->columns[$c->name]->width = $this->pdf->getWidthFromPercent($c->width);
                $this->columns[$c->name]->index = $k;
            }
        }
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function getPage()
    {
        if (count($this->rawdata))
        {
           return array_slice($this->rawdata,$this->pn->idxFirst,$this->pn->gridCount);
        }
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generateReportHeader()
    {
        return NULL; 
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generatePageHeader()
    {
        return NULL; 
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generatePageFooter()
    {
        return NULL; 
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generateHeader()
    {
        $header[] = $this->generateReportHeader();
        $header[] = $this->generatePageHeader();
        return $header;
    }
    
/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generateColumnsHeading()
    {
        $tbl = array();
        $p = 0;     
        // generate column headings
        foreach ( $this->columns as $k=>$col )
        {
           if (( !$col->visible ) || ( !$col->title )) continue;
           $colTitle = $col->title;
           $tbl["$col->name"] = $colTitle;
        }
        return $tbl;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $tbl (tipo) desc
 * @param $row (tipo) desc
 * @param $i (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function generateColumns($tbl, $row, $i)
    {
        $MIOLO = MIOLO::getInstance();
                
        $cntRow = count($row);
        foreach($this->columns as $k=>$col)
        {
           if (( !$col->title ) || ( !$col->visible )) continue;
           $control = clone $col->basecontrol;  // clonning
           if ($col instanceof mpdfreportcolumn)
           {      
              $value = $row[$col->index];
              $col->value = $value;
              if ( $col->options )
              {
                 $value = $col->options[$value];
                 if ( $this->showid )
                 {
                    $value .= " ($row[$k])";
                 }
              }
              // by default, we align numbers to the right and text to the left
              $c = substr($value,0,1);
              if ( ! $col->align && ( $c == '-' || ( $c >= '0' && $c <= '9' ) ) )
              {
                  $col->align = 'right';
              }
              if ($col->href != '')
              {
                  $href = $col->href; 
                  for ( $r=0; $r<$cntRow; $r++ )
                     $href = str_replace("#$r#",trim($row[$r]),$href);
                  $href = str_replace('#?#',$value,$href);
                  $control->href = $href;
                  $control->action = $href;
                  $control->label = $value;
              }
              $control->value = $value;
           }
           elseif ($col instanceof mpdfreportcontrol)
           {
              $control->generate();
           }
           else
           {
              $MIOLO->error("ERROR: Unknown column class '{$col->className}'!");
           }
           $tbl[$i][$col->name] = $control;                     
        }
    }

    public function & GenerateEmptyMsg()
    {
        $tbl = new MSimpleTable('');
        $tbl->attributes['table'] = "cellspacing=\"0\" cellpadding=\"2\" border=\"0\" class=\"gridAttention\" align=\"center\" width=\"100%\""; 
        $tbl->attributes['row'][0] = "class=\"gridAttention\" align=\"center\"";        
        $tbl->cell[0][0] = new Text('',$this->emptyMsg);
        $tbl->cell[0][0]->setClass('gridAttention');
        return $tbl;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generateTableData()
    {   
        if ( $this->hasErrors() )
        {
            $this->generateErrors();
        }
        $tblData = array();
        if ( $this->data)
        {
            // generate data rows
            foreach ( $this->data as $i=>$row )
            {
                if (isset($this->rowmethod)){
                   call_user_func($this->rowmethod, $row, $this->columns, $this->slot, $this);
                }
                $this->generateColumns($tblData, $row, $i);

            } // foreach row
        } // if
        foreach($tblData as $r=>$row)
          foreach($row as $c=>$cell)
            $data[$r][$c] = $cell->value;
        return $data;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generatePageTitle()
    {
        $this->pdf->ezText($this->title,$this->options['titleFontSize'],array('justification'=>'center'));
        $this->pdf->ezSetDy($this->pdf->getFontDecender($this->options['titleFontSize']));
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $data (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function generateBody($data)
    {
        $titles = $this->generateColumnsHeading(); 
        $cols = array();
        foreach ( $this->columns as $k=>$col )
        {
           if (( !$col->visible ) || ( !$col->title )) continue;
           $cols[$col->name] = array('justification'=>$col->align,'width'=>$col->width);
        }
        $this->options['cols'] = $cols;
        if ($this->options['showTableTitle'])
           $title = $this->title;
        else
        {
           $this->generatePageTitle();
           $title = '';
        }   
        $this->y = $this->pdf->ezTable($data,$titles,$title,$this->options);
    }
    
/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generateFooter()
    {
        if (!$this->data) 
           $footer[] = $this->generateEmptyMsg();
        $footer[] = $this->generatePageFooter();
        $footer[] = $this->generateReportFooter();
        return $footer;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generateReport()
    {
        $this->pdf->ezSetMargins(30,30,30,30);
        $this->rawdata = $this->generateTableData();
        if ($this->pageLength)
	    {
		   $this->pn = new MGridNavigator($this->pageLength, $this->rowCount, '');
	    }
        else $this->pn = null;
        for($page=1;$page<=$this->pn->pageCount;$page++)
        {
           $this->pn->setPageNumber($page);
           $this->generatePageHeader();
           $this->generateBody($this->getPage());
           $this->generatePageFooter();
           if ($page != $this->pn->pageCount) $this->pdf->ezNewPage();
        }
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function pageBreak()
    {
        if (!$this->break)
        {
            $this->pdf->ezNewPage();           
            $this->break = true;
        }
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function clearPageBreak()
    {
        $this->break = false;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $trigger (tipo) desc
 * @param $class (tipo) desc
 * @param $module (tipo) desc
 * @param $param (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setTrigger($trigger, $class, $module, $param)
    {
        $this->pdf->setTrigger($trigger, $class, $module, $param);
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $value' (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setOutput($value='')
    {
        $this->ezpdf->setOutput();
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function execute()
    {
        $this->ezpdf->execute();
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generate()
    {
        $this->generateReport();
        $this->setOutput();
        $this->execute();
    }

}
?>
