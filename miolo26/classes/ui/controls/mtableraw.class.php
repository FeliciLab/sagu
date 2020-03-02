<?php

class MTableRaw extends MSimpleTable
{
    public $title;
    public $array;
    public $colTitle;
    public $zebra = false;
    public $table;

    public function __construct($title='', $array, $colTitle=null, $name='', $zebra = true)
    {
        parent::__construct($name);
        $this->title = $title;
        $this->array = $array;
        $this->colTitle = $colTitle;
        $this->setClass("mTableRaw");
        $this->zebra = $zebra;
    }

    public function setData($data)
    {
        $this->array = $data;
    }

    public function setAlternate($zebra=false)
    {
        $this->zebra = $zebra;
    }

    /**
     * Set table width
     *
     * @param string $width Width as '100px' or '50%'
     */
    public function setWidth($width)
    {
        $this->setAttribute('width', $width);
    }

    public function generate()
    {
        $title = $this->title;
        $array = $this->array;
        $colTitle = $this->colTitle;
        $t = $this;
        if ( $title )
        {
            $ncols = count($colTitle);

            // Generate table title as caption
            $t->setCaption($title);
        }
        if ( is_array($colTitle) )
        {
            $n = count($colTitle);

            // Generate column titles as th
            for ( $i = 0; $i < $n; $i++ )
            {
                $t->setHead($i, $colTitle[$i], " class=\"mTableRawColumnTitle\" ");
            }
        }
        if ( is_array($array) )
        {
            $nrows = count($array);

            // Generate table content
            for ( $i = 0; $i < $nrows; $i++ )
            {
                $rowClass = "mTableRawRow" . ($this->zebra ? ($i % 2) : '');
                $t->setRowClass($i, $rowClass);
                if ( is_array($array[$i]) )
                {
                    $ncols = count($array[$i]);
                    for ( $j = 0; $j < $ncols; $j++ )
                    {
                        $attr = $this->attributes['cell'][$i][$j];
                        if ( $attr == '' )
                        {
                            $attr = "width=0 align=\"left\" valign=\"top\"";
                        }
                        $t->setCell($i, $j, $array[$i][$j], $attr);
                    }
                }
                else
                {
                    $attr = $this->attributes['cell'][$i][0];
                    if ( $attr == '' )
                    {
                        $attr = "width=0 align=\"left\" valign=\"top\"";
                    }
                    $t->setCell($i, 0, $array[$i], $attr);
                }
            }
        }
        return parent::generate();
    }
}

?>