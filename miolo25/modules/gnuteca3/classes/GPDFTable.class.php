<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 * 
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 * 
 * Class GPDFTable
 *
 * @author Jan Slabon [http://fpdf.org/en/script/script12.php]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 26/04/2010
 *
 **/

$MIOLO->uses('classes/GPDF.class.php','gnuteca3');
$MIOLO->uses('classes/GDate.class.php', $module);

class GPDFTable extends GPDF
{
    public $tablewidths;
    public $footerset;
    public $lineHeight = 10;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        parent::__construct(null, $orientation, $unit, $format);
        $this->rMargin = $this->lMargin; //define margin direito igual a esquerda
        $this->addPage();
        $this->aliasNbPages();
        $this->setFont('Arial', '', 8);
    }

    function _beginpage($orientation, $format)
    {
        $this->page++;
        
        if(!$this->pages[$this->page]) // solves the problem of overwriting a page if it already exists
        {
            $this->pages[$this->page]='';
        }

        $this->state=2;
        $this->x=$this->lMargin;
        $this->y=$this->tMargin;
        $this->FontFamily='';

        //Check page size
        if($orientation=='')
        {
            $orientation=$this->DefOrientation;
        }
        else
        {
            $orientation=strtoupper($orientation[0]);
        }
        if($format=='')
        {
            $format=$this->DefPageFormat;
        }
        else
        {
            if(is_string($format))
            {
                $format=$this->_getpageformat($format);
            }
        }

        if($orientation!=$this->CurOrientation || $format[0]!=$this->CurPageFormat[0] || $format[1]!=$this->CurPageFormat[1])
        {
            //New size
            if($orientation=='P')
            {
                $this->w=$format[0];
                $this->h=$format[1];
            }
            else
            {
                $this->w=$format[1];
                $this->h=$format[0];
            }
            $this->wPt=$this->w*$this->k;
            $this->hPt=$this->h*$this->k;
            $this->PageBreakTrigger=$this->h-$this->bMargin;
            $this->CurOrientation=$orientation;
            $this->CurPageFormat=$format;
        }

        if($orientation!=$this->DefOrientation || $format[0]!=$this->DefPageFormat[0] || $format[1]!=$this->DefPageFormat[1])
        {
            $this->PageSizes[$this->page]=array($this->wPt, $this->hPt);
        }
    }

    function Footer()
    {
        // Check if Footer for this page already exists (do the same for Header())
        if(!$this->footerset[$this->page])
        {
            $this->SetY(-15);

            $date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_USER);
            $title = $this->title ? " - {$this->title}" : '';
            $this->Cell(0, 10, $date . $title, 0, 0, 'L');
            $this->Cell(0, 10, _M('Página', MIOLO::getCurrentModule()) . ' ' . $this->PageNo().'/{nb}', 0, 0, 'R');
            
            // set footerset
            $this->footerset[$this->page] = 1;
        }
    }

    protected function morepagestable($datas, $lineheight=8)
    {
        // some things to set and 'remember'
        $l = $this->lMargin;
        $startheight = $h = $this->GetY();
        $startpage = $currpage = $this->page;

        // calculate the whole width
        foreach($this->tablewidths AS $width)
        {
            $fullwidth += $width;
        }

        // Now let's start to write the table
        foreach($datas AS $row => $data)
        {
            $this->page = $currpage;
            // write the horizontal borders
            $this->Line($l,$h,$fullwidth+$l,$h);
            // write the content and remember the height of the highest col
            foreach($data AS $col => $txt)
            {
                $this->page = $currpage;
                $this->SetXY($l,$h);
                $this->MultiCell($this->tablewidths[$col],$lineheight,$txt);
                $l += $this->tablewidths[$col];

                if($tmpheight[$row.'-'.$this->page] < $this->GetY())
                {
                    $tmpheight[$row.'-'.$this->page] = $this->GetY();
                }

                if($this->page > $maxpage)
                {
                    $maxpage = $this->page;
                }
            }

            // get the height we were in the last used page
            $h = $tmpheight[$row.'-'.$maxpage];
            // set the "pointer" to the left margin
            $l = $this->lMargin;
            // set the $currpage to the last page
            $currpage = $maxpage;
        }
        // draw the borders
        // we start adding a horizontal line on the last page
        $this->page = $maxpage;
        $this->Line($l,$h,$fullwidth+$l,$h);
        // now we start at the top of the document and walk down
        for($i = $startpage; $i <= $maxpage; $i++)
        {
            $this->page = $i;
            $l = $this->lMargin;
            $t  = ($i == $startpage) ? $startheight : $this->tMargin;
            $lh = ($i == $maxpage)   ? $h : $this->h-$this->bMargin;
            $this->Line($l,$t,$l,$lh);
            foreach($this->tablewidths AS $width)
            {
                $l += $width;
                $this->Line($l,$t,$l,$lh);
            }
        }
        // set it to the last page, if not it'll cause some problems
        $this->page = $maxpage;
    }
    
    /**
     * Define columns widths of the table
     *
     * @param array $widths
     */
    public function setColumnWidths(array $widths)
    {
        $this->tablewidths = $widths;
    }
    
    /**
     * Enter description here...
     *
     * @param (MTableRaw) $model
     */
    public function addTable( MTableRaw $model )
    {
        if (!$this->tablewidths)
        { 
            //Define automaticamente largura das colunas
            if (is_array($model->array))
            {
                $sumWidth         = array();
                $width            = $this->w - ($this->lMargin + $this->rMargin);
                $totalColumns     = count($model->array[0]);
                $totalLines       = count($model->array);
                $minTolerableSize = 30; //tamanho minimo que uma coluna pode ter
                $maxTolerableSize = ($width / $totalColumns) * 1.5; //tamanho maximo que uma coluna pode ter (1.5 vezes a media)
                
                foreach ($model->array as $key => $val)
                {
                    foreach ($val as $_key => $_val)
                    {
                        $sumWidth[$_key] += $this->GetStringWidth($_val);
                    }
                }
                for($i=0; $i < count($sumWidth); $i++)
                {
                    //Calcula a media do tamanho da coluna
                    $average = $sumWidth[$i] / $totalLines;
                    $average = $average / $this->FontSize;

                    //Verifica se tamanho de coluna calculado nao extrapola os limites 
                    if ($average > $maxTolerableSize)
                    {
                        $average = $maxTolerableSize;
                    }
                    /*else if ($average < $minTolerableSize)
                    {
                        $average = $minTolerableSize;
                    }*/
                    
                    $this->tablewidths[] = $average * 2;
                }
                
                $totalWidths     = array_sum($this->tablewidths);
                $diferenceTotal  = $width - $totalWidths;
                $diferenceColumn = $diferenceTotal / $totalColumns;
                
                foreach ( $this->tablewidths as $key => $val )
                {
                    $this->tablewidths[$key] = $val + $diferenceColumn;
                }
            }
        }
        
        $this->setTitle( $model->title );
        $model->array = array_merge(array($model->colTitle),$model->array);
        $this->setFont(null, null); //volta fonte normal sem negrito
        $this->morepagestable( $model->array, $this->lineHeight );
    }
}
?>