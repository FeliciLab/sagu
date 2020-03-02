<?php

/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MSimpleXml
{
    /**
     * Attribute Description.
     */
    public $xml;

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $file (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function __construct($file)
    {
        $this->xml = simplexml_load_file($file);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $node (tipo) desc
     * @param &$array (tipo) desc
     * @param $k=') (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    private function _ToSimpleArray($node, &$array = array(), $k = '')
    {
        foreach ((array)$node as $key => $var)
        {
            $aKey = ($k != '') ? $k . '.' . $key : $key;

            if (is_object($var))
            {
                if (count((array)$var) == 0)
                {
                    $array[$aKey] = '';
                }
                else
                {
                    $this->_ToSimpleArray($var, $array, $aKey);
                }
            }
            elseif (is_array($var))
            {
                 $array[$aKey] = $var;
            }
            else
            { 
                 $array[$aKey] = utf8_decode((string)$var);
            }
        }
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param &$array) (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function toSimpleArray(&$array = array(), $node = NULL)
    {
        if ($node == NULL)
            $node = $this->xml;

        $this->_ToSimpleArray($node, $array);
        return $array;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param &$array) (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    private function _ToArray($xml)
    {
        if ( is_object($xml) && (get_class($xml) == 'SimpleXMLElement') )
        {
            $attributes = $xml->attributes();

            foreach ($attributes as $k => $v)
            {
                if ($v)
                    $a[$k] = (string)$v;
            }

            $x = $xml;
            $xml = get_object_vars($xml);
        }

        if (is_array($xml))
        {
            if (count($xml) == 0)
                return (string)$x; // for CDATA

            foreach ($xml as $key => $value)
            {
                $r[$key] = $this->_ToArray($value);
            }

            if (isset($a))
                $r['@'] = $a; // Attributes

            return $r;
        }

        return utf8_decode((string)$xml);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param &$array) (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function toArray(&$array = array(), $node = NULL)
    {
        if ($node == NULL)
            $node = $this->xml;

        return $this->_ToArray($node);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $argument (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function XPath($argument)
    {
        return $this->xml->xpath($argument);
    }
}
?>
