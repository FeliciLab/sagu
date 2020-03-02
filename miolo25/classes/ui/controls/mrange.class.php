<?php
class MRange
{
    public $page; // begin at 1
    public $offset; // begin at 0
    public $rows; // number of rows at page
    public $total; // total rows 

    public function __construct($page, $rows, $total)
    {
        $this->page = $page;
        $this->offset = ($page - 1) * $rows;
        $this->rows = (($this->offset + $rows) > $total) ? $total - $this->offset : $rows;
        $this->total = $total;
    }
}
?>