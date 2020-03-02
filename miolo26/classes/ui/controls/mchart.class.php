<?php

/**
 * Chart class.
 * It uses jqPlot. Examples can be found at: http://www.jqplot.com/tests/
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2012/07/05
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solu��es Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Solu��es Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */
class MChart extends MDiv
{
    /**
     * Constants for the supported chart types. 
     */
    const TYPE_AREA = 'CategoryAxisRenderer';
    const TYPE_BAR = 'BarRenderer';
    const TYPE_DONUT = 'DonutRenderer';
    const TYPE_LINE = 'Line';
    const TYPE_PIE = 'PieRenderer';

    const DEFAULT_STRING_FORMAT = '%.2f';

    /**
     * @var stdClass Configuration object.
     *
     * References:
     * http://www.jqplot.com/docs/files/jqPlotOptions-txt.html
     * http://www.jqplot.com/docs/index/Properties.html
     */
    public $conf;

    /**
     * @var array Three-dimensional array with chart data.
     */
    private $multipleData = array();

    /**
     * @var string Chart type. See TYPE_* constants.
     */
    private $type;

    /**
     * @var array Array with strings that must be used as objects or functions by jqplot.
     */
    private $replaces = array();
        
    /**
     * @var int Minimum number for the 'y' axis.
     */
    private $yMin;
    
    /**
     * @var int Maximum number for the 'y' axis.
     */
    private $yMax;

    /**
     * MChart constructor.
     *
     * @param string $id Unique identifier.
     * @param array $data Chart data.
     * @param string $title Chart title.
     * @param string $type Chart type. See TYPE_* constants.
     */
    public function __construct($id, $data=array(), $title='', $type=NULL)
    {
        parent::__construct($id);

        $this->page->addScript('jquery/excanvas.min.js');
        
        $this->page->addScript('jquery/jquery.mobile-1.1.0');
        $this->page->addScript('jquery/jquery.jqplot.min.js');
        $this->page->addScript('jquery/plugins/jqplot.canvasTextRenderer.min.js');
        $this->page->addScript('jquery/plugins/jqplot.enhancedLegendRenderer.min.js');

        $this->page->addStyleURL('scripts/jquery/jquery.jqplot.min.css');
        $this->page->addStyleURL('scripts/jquery/jquery.jqplot.miolo.css');


        $this->conf = new stdClass();
        $this->setMultipleData(array( $data ));

        $this->conf->title = $title;
        $this->conf->seriesDefaults->rendererOptions->showDataLabels = TRUE;
        $this->conf->seriesDefaults->rendererOptions->sliceMargin = 5;
        $this->conf->seriesDefaults->rendererOptions->shadowOffset = 0.5;
        $this->conf->seriesDefaults->rendererOptions->shadowAlpha = 0.05;
        $this->conf->seriesDefaults->rendererOptions->shadowDepth = 8;
        $this->conf->seriesDefaults->rendererOptions->animation->show = TRUE;
        $this->conf->grid->drawBorder = FALSE;

        $this->conf->seriesColors = array(
            // Highcharts colors
            '#4572A7', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92',
            // Kuler colors
            '#00A695', '#FF3300', '#FF6600', '#FFA600', '#FFD900',
        );
        

        $this->setType($type);

        $this->setWidth('450px');
        $this->setHeight('400px');
    }

    /**
     * @param string $width Set chart width (CSS).
     */
    public function setWidth($width)
    {
        $this->addStyle('width', $width);
    }

    /**
     * @param string $height Set chart height (CSS).
     */
    public function setHeight($height)
    {
        $this->addStyle('height', $height);
    }

    /**
     * @param integer $width Set bar width.
     */
    public function setBarWidth($width)
    {
        $this->conf->seriesDefaults->rendererOptions->barWidth = $width;
    }

    /**
     * @param string $type Set chart type. See TYPE_* constants.
     */
    public function setType($type)
    {
        $this->type = $type;

        switch ( $type )
        {
            case self::TYPE_AREA:
                $this->setXAsCategory();
                $this->conf->stackSeries = TRUE;
                $this->conf->seriesDefaults->fill = TRUE;
                break;

            case self::TYPE_BAR:
                $this->page->addScript('jquery/plugins/jqplot.barRenderer.min.js');
                $this->replaces[] = $this->conf->seriesDefaults->renderer = 'jQuery.jqplot.BarRenderer';
                $this->conf->axes->yaxis->tickOptions->formatString = '%.2f';

                $this->displayPointLabels();
                break;

            case self::TYPE_DONUT:
                $this->replaces[] = $this->conf->seriesDefaults->renderer = "jQuery.jqplot.$type";

                $this->page->addScript('jquery/plugins/jqplot.donutRenderer.min.js');
                $this->conf->seriesDefaults->rendererOptions->dataLabelFormatString = self::DEFAULT_STRING_FORMAT . '%';

                $this->displayLegend();
                $this->displayHints();
                break;

            case self::TYPE_PIE:
                $this->replaces[] = $this->conf->seriesDefaults->renderer = "jQuery.jqplot.$type";

                $this->page->addScript('jquery/plugins/jqplot.pieRenderer.min.js');
                $this->conf->seriesDefaults->rendererOptions->dataLabelFormatString = self::DEFAULT_STRING_FORMAT . '%';

                $this->displayLegend();
                $this->displayHints();
                break;

            default:
                $this->page->addScript('jquery/plugins/jqplot.canvasAxisLabelRenderer.min.js');
                $this->page->addScript('jquery/plugins/jqplot.highlighter.min.js');
                $this->conf->highlighter->show = TRUE;
                $this->conf->highlighter->sizeAdjust = 10;

                $this->conf->axes->xaxis->tickOptions->formatString = self::DEFAULT_STRING_FORMAT;
                $this->conf->axes->yaxis->tickOptions->formatString = '%.2f';
                break;
        }
    }

    /**
     * Set X axis as category.
     */
    public function setXAsCategory()
    {
        $this->page->addScript('jquery/plugins/jqplot.categoryAxisRenderer.min.js');
        $this->replaces[] = $this->conf->axes->xaxis->renderer = 'jQuery.jqplot.CategoryAxisRenderer';
    }

    /**
     * Set Y axis as category.
     */
    public function setYAsCategory()
    {
        $this->page->addScript('jquery/plugins/jqplot.categoryAxisRenderer.min.js');
        $this->replaces[] = $this->conf->axes->yaxis->renderer = 'jQuery.jqplot.CategoryAxisRenderer';
    }

    /**
     * @param array $ticks Set X axis ticks.
     */
    public function setXTicks($ticks)
    {
        $this->setXAsCategory();
        $this->conf->axes->xaxis->ticks = $ticks;
    }

    /**
     * @param array $ticks Set Y axis ticks.
     */
    public function setYTicks($ticks)
    {
        $this->setYAsCategory();
        $this->conf->axes->yaxis->ticks = $ticks;
    }

    /**
     * Display point labels.
     */
    public function displayPointLabels()
    {
        $this->page->addScript('jquery/plugins/jqplot.pointLabels.min.js');
        $this->conf->seriesDefaults->pointLabels->show = TRUE;
    }

    /**
     * @param boolean $display Set if the legend box must be displayed.
     */
    public function displayLegend($display=TRUE)
    {
        $this->replaces[] = $this->conf->legend->renderer = 'jQuery.jqplot.EnhancedLegendRenderer';

        $this->conf->legend->show = $display;
        $this->conf->legend->location = 'e';
        $this->conf->legend->rendererOptions->seriesToggle = 'normal';
        $this->conf->legend->fontSize = '11px';
    }

    /**
     * Add JavaScript code to display simple hints when the mouse is over the chart data.
     */
    public function displayHints()
    {
        // Show hint with label
        $jsCode = <<<JS
\$("#$this->id").bind('jqplotDataHighlight', function(ev, seriesIndex, pointIndex, data) {
    var \$this = \$(this);
    \$this.attr('title', data[0]);
});

\$("#$this->id").bind('jqplotDataUnhighlight', function(ev, seriesIndex, pointIndex, data) {
    var \$this = \$(this);
    \$this.attr('title',"");
});
JS;
        $this->page->onload($jsCode);
    }

    /**
     * @param array $data Add chart data as bidimensional array.
     */
    public function addData($data)
    {
        $this->multipleData[] = $data;
    }

    /**
     * @return array Get multiple chart data.
     */
    public function getMultipleData()
    {
        return $this->multipleData;
    }

    /**
     * Set multiple data for the chart.
     *
     * @param array $multipleData Three-dimensional array.
     */
    public function setMultipleData($multipleData)
    {
        // FIXME: jqPlot does not support ISO-8859-1 strings
        foreach ( (array) $multipleData as $dataKey => $data ) 
        {
            foreach ( (array) $data as $lineKey => $line )
            {
                if ( is_array($line) )
                {
                    foreach ( $line as $itemKey => $item )
                    {
                        if( is_numeric($item) )
                        {
                            $multipleData[$dataKey][$lineKey][$itemKey] = floatval($item);
                        }
                        elseif ( is_string($item) )
                        {
                            $multipleData[$dataKey][$lineKey][$itemKey] = $item;
                        }
                    }
                }
                elseif( is_numeric($line) )
                {
                    $multipleData[$dataKey][$lineKey] = floatval($line);
                }
                elseif ( is_string($line) )
                {
                    $multipleData[$dataKey][$lineKey] = $line;
                }
            }
        }

        $this->multipleData = $multipleData;
    }

    /**
     * @return string Generate MChart.
     */
    public function generate()
    {
        if ( $this->getYMin() || $this->getYMin() == 0 )
        {
            $this->conf->axes->yaxis->min = $this->getYMin();
        }
    
        if ( $this->getYMax() || $this->getYMax() == 0 )
        {
            $this->conf->axes->yaxis->max = $this->getYMax();
        }
        
        $jsonData = json_encode($this->multipleData);
        $jsonConf = json_encode($this->conf);

        // Replace some strings that must be JavaScript objects or functions
        foreach ( $this->replaces as $replace )
        {
            $jsonConf = str_replace("\"$replace\"", $replace, $jsonConf);
        }

        $this->page->onload("$(document).ready(function () { $.jqplot.sprintf.decimalMark = ','; $.jqplot('$this->id', $jsonData, $jsonConf); });");

        return parent::generate();
    }
    
    public function getYMin() 
    {
        return $this->yMin;
    }

    public function setYMin($yMin) 
    {
        $this->yMin = $yMin;
    }

    public function getYMax() 
    {
        return $this->yMax;
    }

    public function setYMax($yMax) 
    {
        $this->yMax = $yMax;
    }
    
    public function setLegendLabels(array $labels)
    {
        $this->conf->legend->labels = $labels;
    }
    
    public function setXAxesLabel($label)
    {
        $this->conf->axes->xaxis->label = $label;
    }
    
    public function setYAxesLabel($label)
    {
        $this->conf->axes->yaxis->label = $label;
    }
}

?>