<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MAutoLoad
{
    /**
     * Attribute Description.
     */
    private $xml;

    /**
     * Attribute Description.
     */
    private $classes = array();

    /**
     * Attribute Description.
     */
    private $compatibility;

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();

        $file = $MIOLO->getConf('home.classes') . '/etc/autoload.xml';
        $this->xml = new MSimpleXML($file);
        $this->compatibility = new MCompatibility();
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $className (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function getFile($className)
    {
        //echo $className.'<br>';
        if (($fileName = $this->classes[strtolower($className)]) == NULL)
        {
            $value = $this->xml->xpath("loadclass[name='$className']");
            $fileName = (string)$value[0]->file;
            if ($fileName == '')
            {
                $this->compatibility->evaluation($className);
            }
            else
            {
                $this->classes[strtolower($className)] = $fileName;
            }
        }
        return $fileName;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $className (tipo) desc
     * @param $fileName (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setFile($className, $fileName)
    {
        //echo "<br> setFile - $className = $fileName<br>";
        $this->classes[strtolower($className)] = $fileName;
    }

    public function loadFile($fileName, $module = NULL)
    {
        $xml = new MSimpleXML($fileName);
        foreach($xml->xml->loadclass as $name=>$value)
        {
            $file = $value->file;
            if ($module)
            {
                $MIOLO = MIOLO::getInstance();
                $file = $MIOLO->getModulePath( $module, $file );
            } 
            $this->classes[strtolower($value->name)] = $file;
        }
    }
}
?>
