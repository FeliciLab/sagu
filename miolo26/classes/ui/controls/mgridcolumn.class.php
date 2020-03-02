<?php

/**
 * Grid column.
 *
 * @author Vilson Cristiano Gärtner [vilson@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2006/06/08
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2006-2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class MGridColumn extends MControl
{
    /**
     * @var MGrid Grid which this columns belongs to
     */
    public $grid;

    /**
     * @var string Column title.
     */
    public $title;

    /**
     * @var mixed Column footer.
     */
    public $footer;

    /**
     * @var array Map of data value to display value.
     */
    public $options;

    /**
     * @var string Column align - rigth, center, left.
     */
    public $align;

    /**
     * @var boolean Defines if the column content must wrap (FALSE) or not (TRUE).
     */
    public $nowrap;

    /**
     * @var string Column width in pixels or percent.
     */
    public $width;

    /**
     * @var boolean Defines if the column can be ordered.
     */
    public $order;

    /**
     * @var string Mask to order the column data. Currently only supported for date/time values.
     */
    public $orderMask;

    /**
     * @var string Value at current row.
     */
    public $value;

    /**
     * @var MControl Base control to render value.
     */
    public $basecontrol;

    /**
     * @var array Array of Control clonning of basecontrol.
     */
    public $control;

    /**
     * @var integer Column index in the data array.
     */
    public $index;
    
    /**
     *
     * @var array
     */
    private $replace;
    
    /**
     * 
     * @var boolean 
     */
    private $numberFormat;

    /**
     * Grid column constructor.
     *
     * @param string $title Column title.
     * @param string $align Column align - rigth, center, left.
     * @param boolean $nowrap Defines if the column content must wrap (FALSE) or not (TRUE).
     * @param string $width Column width in pixels or percent.
     * @param boolean $visible Defines if the column must be visible.
     * @param array $options Map of data value to display value.
     * @param boolean $order Defines if the column can be ordered.
     * @param string $orderMask Mask to order the column data. Currently only supported for date/time values.
     */
    public function __construct($title='', $align='left', $nowrap=false, $width=0, $visible=true, $options=null, $order=false, $orderMask='', $numberFormat=false)
    {
        parent::__construct();
        $this->setClass('data');
        $this->visible = $visible;
        $this->title = $title;
        $this->options = $options;
        $this->align = $align;
        $this->nowrap = $nowrap;
        $this->width = $width;
        $this->order = $order;
        $this->orderMask = $orderMask;
        $this->value = '';
        $this->index = 0;
        $this->footer = null;
        $this->basecontrol = new MLabel('');
        $this->control = array();
        $this->numberFormat = $numberFormat;
    }

    /**
     * @return string Generated column.
     */
    public function generate()
    {
        $i = $this->grid->getCurrentRow();
        $row = $this->grid->data[$i];
        $this->control[$i] = clone $this->basecontrol;
        $value = $row[$this->index];
        $this->control[$i]->value = $value;

        if ( $this->numberFormat )
        {
            $this->control[$i]->value = number_format($value, 2, ',', '.');
        }
        
        if ( $this->options )
        {
            if ( is_array(current($this->options)) )
            {
                $options = array( );
                foreach ( $this->options as $item )
                {
                    list($v, $o) = $item;
                    $options[$v] = $o;
                }

                $this->options = $options;
            }

            $this->control[$i]->value = $this->options[$value];

            if ( $this->grid->showid )
            {
                $this->control[$i]->value .= " ($value)";
            }
        }

        return $this->control[$i];
    }
    
    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getAlign()
    {
        return $this->align;
    }

    public function setAlign($align)
    {
        $this->align = $align;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
    
    public function getReplace()
    {
        return $this->replace;
    }

    public function setReplace($key, $replace)
    {
        $this->replace[$key] = $replace;
    }
}

?>