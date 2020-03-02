<?php

class MLookupObjectGrid extends MLookupGrid
{
    /**
      MLookupObjectGrid constructor
         $array - a array of objects
         $columns - array of columns objects
         $href - base url of this lookupgrid
         $pageLength - max number of rows to show (0 to show all)
    */

    public $array;

    public function __construct($array, $columns, $href, $pageLength = 15, $index = 0)
    {
        $this->array = $array;
        parent::__construct($columns, $href, $pageLength, $index);
    }

    public function generateData()
    {

        if ($this->array == NULL)
        {
            return;
        }

        foreach ($this->array as $i => $row)
        {
            foreach ($this->columns as $k => $col)
            {
                eval("\$v = \$row->{$col->attribute};");
                $this->data[$i][$k] = $v;
            }
        }

        if ($this->pageLength)
        {
            $this->pn->SetGridParameters($this->pageLength, $this->rowCount, $this->GetURL($this->filtered, $this->ordered), $this);
            $this->data = $this->getPage();
        }
        else
        {
            $this->pn = NULL;
        }
    }


}

?>