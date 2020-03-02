<?php

class MTableXml extends MTableRaw
{
    public function __construct($title='', $file, $colTitle=null)
    {
        $xmlTree = new MXMLTree($file);
        $array = $xmlTree->getXMLTreeAsArray();
        parent::__construct($title, $array, $colTitle);
    }
}

?>