<?php

class ConverterFactory
{
    public $trivialConverter;

    public function converterFactory()
    {
    }

    public function getConverter($className, $properties = null)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->uses("persistence/converter/" . strtolower($className) . ".class.php");
        eval("\$converter = new {$className}();");
        $converter->init($properties);
        return $converter;
    }

    public function getTrivialConverter()
    {
        if (!$this->trivialConverter)
            $this->trivialConverter = new TrivialConverter();

        return $this->trivialConverter;
    }
}
?>
