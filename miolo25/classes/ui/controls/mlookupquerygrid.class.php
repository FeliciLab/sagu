<?php
class MLookupQueryGrid extends MLookupGrid
{
    /**
      MLookupQueryGrid constructor
         $query - a query object
         $columns - array of columns objects
         $href - base url of this lookupgrid
         $pageLength - max number of rows to show (0 to show all)
    */

    public $query;

    public function __construct($query, $columns, $href, $pageLength = 15, $index = 0)
    {
        $this->query = $query;
        parent::__construct($columns, $href, $pageLength, $index);
    }

    public function generateData()
    {
        $this->data = $this->query->result;
        $this->rowCount = $this->query->getRowCount();
        if ($this->pageLength)
        {
            $filtered = $this->getFiltered();
            $this->pn->setGridParameters($this->pageLength, $this->rowCount, $this->getURL($filtered, $this->ordered), $this);
            $this->query->setPageLength($this->pageLength);
            $this->data = $this->query->getPage($this->pn->getPageNumber());
        }
        else
        {
            $this->pn = NULL;
        }
    }
}

?>