<?php

class Business#Module#Table extends MBusiness
{
    public function __construct($data=NULL)
    {
       parent::__construct('#module', $data);
    }

    public function search($filters=NULL)
    {
        $msql = new MSQL('*', '#table');
#condition
        $query = $this->getDb()->getQuery($msql);
        return $query->result;
    }
}

?>