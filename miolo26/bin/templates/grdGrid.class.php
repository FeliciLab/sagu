<?php

class grd#Grid extends MGrid
{
    public function __construct()
    {
        $module = MIOLO::getCurrentModule();
#columnsText
        parent::__construct(NULL, $columns);

        $this->setTitle(_M('#title', $module));
    }
}

?>