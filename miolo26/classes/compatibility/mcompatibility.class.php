<?php
class MCompatibility
{
    private $xml;
    private $notFound = array();

    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();

        $file = $MIOLO->getConf('home.classes') . '/etc/compatibility.xml';
        $this->xml = new MSimpleXML($file);
    }

    public function evaluation($className)
    {
        $MIOLO = MIOLO::getInstance();

        $value = $this->xml->xpath("loadclass[name='$className']");
        $fileName = (string)$value[0]->file;
        $MIOLO->trace("[COMPATIBILITY] $className ($fileName)");
            $MIOLO->trace->traceStack();
        if ($fileName == '')
        {
             if (! isset($this->notFound[$className]) )
             { 
                if ( strlen($MIOLO->getConf('options.miolo2modules')) )
                {

		if ( method_exists('sAutoload', 'SAGUAutoload') )
		{
		    //$className = substr($className,1);
		    
		    if( ( substr($className, 0, 8) == 'business' || in_array(substr($className, 0, 3), array('bas', 'acd', 'fin')) ) && $MIOLO->getConf('tempvar'))
		    {
			$MIOLO = MIOLO::getInstance();
			$ok = sAutoload::SAGUAutoload($MIOLO->getConf('tempvar'), $MIOLO->getConf('options.miolo2modules'), true);
		    }
		}
                }

		if(!$ok)
		{
		  $this->notFound["m{$className}"] = $className;
		  eval("class $className extends m{$className} {};");
		}
             }
             else
             {
                 $MIOLO->trace("[COMPATIBILITY] $className NOT FOUND");
                 $MIOLO->trace->traceStack();
             }
        }
        else
        {
            include_once($fileName);
        }
    }
}
?>
