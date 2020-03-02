<?php

/**
 * Var Dump class.
 * This class Dumps/Displays the contents of a variable in a colored tabular format
 * Based on the idea, javascript and css code of Macromedia's ColdFusion cfdump tag
 * A much better presentation of a variable's contents than PHP's var_dump and print_r functions
 *
 * Thanks to Andrew Hewitt (rudebwoy@hotmail.com) for the idea and suggestion
 *
 * All the credit goes to ColdFusion's brilliant cfdump tag
 * Hope the next version of PHP can implement this or have something similar
 *
 * @author Kwaku Otchere [ospinto@hotmail.com] [http://dbug.ospinto.com]
 *
 * \b Maintainers \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 * 
 * @since
 * Introduced in MIOLO on July,25 2006
 *
 * \b @organization \n
 * SOLIS - Cooperativa de SoluÃ§Ãµes Livres \n
 * The MIOLO development team
 *
 * \b Copyright \n
 * Copyright (c) 2006-2011 - SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License \n
 * Licensed under GPL (for further details read the COPYING file or http://www.gnu.org/copyleft/gpl.html )
 *
 * \b History \n
 * See history in SVN repository
 *
 */

class dBug
{
    public $xmlDepth = array();
    public $xmlCData;
    public $xmlSData;
    public $xmlDData;
    public $xmlCount = 0;
    public $xmlAttrib;
    public $xmlName;
    public $arrType = array( "array", "object", "resource", "boolean" );

    /**
     * Constructor.
     *
     * @param mixed $var
     * @param string $forceType Type to force. Can be array, object or xml.
     */
    public function dBug($var, $forceType = "")
    {
        $MIOLO = MIOLO::getInstance();
        ob_start();

        // Array of variable types that can be "forced"
        $arrAccept = array( "array", "object", "xml" );

        if ( in_array($forceType, $arrAccept) )
        {
            $this->{"varIs" . ucfirst($forceType)}($var); 
        }
        else
        {
            $this->checkType($var);
        }

        $content = ob_get_contents();
        ob_end_clean();

        $js = "
    /* code modified from ColdFusion's cfdump code */
    function dBug_toggleRow(source)
    {
        target=(document.all) ? source.parentElement.cells[1] : source.parentNode.lastChild;
        dBug_toggleTarget(target,dBug_toggleSource(source));
    }

    function dBug_toggleSource(source)
    {
        if (source.style.fontStyle == 'italic')
        {
            source.style.fontStyle='normal';
            source.title='click to collapse';
            return 'open';
        }
        else
        {
            source.style.fontStyle='italic';
            source.title='click to expand';
            return 'closed';
        }
    }

    function dBug_toggleTarget(target,switchToState)
    {
        target.style.display=(switchToState=='open') ? '' : 'none';
    }

    function dBug_toggleTable(source)
    {
        switchToState=dBug_toggleSource(source);
        
        if(document.all)
        {
            table=source.parentElement.parentElement;

            for(var i=1;i<table.rows.length;i++)
            {
                target=table.rows[i];
                dBug_toggleTarget(target,switchToState);
            }
        }
        else
        {
            table=source.parentNode.parentNode;
            
            for (var i=1;i<table.childNodes.length;i++)
            {
                target=table.childNodes[i];
                if(target.style)
                {
                    dBug_toggleTarget(target,switchToState);
                }
            }
        }
    }

    // Make it moveable
    dojo.require('dojo.dnd.Moveable');
    new dojo.dnd.Moveable(dojo.byId('stdout'));
    ";

        $MIOLO->page->addJsCode($js);

        // Add close button
        $closeButton = '<div id="dBug_closeButton" onclick="javascript:dojo.byId(\'stdout\').innerHTML = \'\';"><a href="#" onclick="javascript:dojo.byId(\'stdout\').innerHTML = \'\'; return false;">Close</a></div>';

        if ( !MUtil::isAjaxEvent() )
        {
            echo $closeButton . $content;
        }
        else
        {
            $previousDebug = '';

            if ( is_array( $MIOLO->ajax->response->element ) )
            {
                foreach ( $MIOLO->ajax->response->element as $index => $element)
                {
                    if ( $element == 'stdout' )
                    {
                        $previousDebug .= $MIOLO->ajax->response->html[$index];

                        // Remove last response
                        unset($MIOLO->ajax->response->html[$index]);
                        unset($MIOLO->ajax->response->element[$index]);
                    }
                }
            }

            if ( strstr($previousDebug, $closeButton) === FALSE )
            {
                $previousDebug = $closeButton . $previousDebug;
            }

            $MIOLO->ajax->setResponse($previousDebug . $content, 'stdout');
        }
    }

    /**
     * Create the main table header.
     *
     * @param string $type 
     * @param string $header 
     * @param integer $colspan Number of columns.
     */
    public function makeTableHeader($type, $header, $colspan = 2)
    {
        echo "<table cellspacing=2 cellpadding=3 class=\"dBug_" . $type . "\">
                <tr>
                    <td class=\"dBug_" . $type . "Header\" colspan=" . $colspan . " style=\"cursor:hand\" onClick='dBug_toggleTable(this)'>" . $header . "</td>
                </tr>";
    }

    /**
     * Create the table row header.
     * 
     * @param type $type
     * @param type $header 
     */
    public function makeTDHeader($type, $header)
    {
        echo "<tr>
                <td valign=\"top\" onClick='dBug_toggleRow(this)' style=\"cursor:hand\" class=\"dBug_" . $type . "Key\">" . $header . "</td>
                <td>";
    }

    /**
     * @return string Close table row.
     */
    public function closeTDRow()
    {
        return "</td>\n</tr>\n";
    }

    /**
     * Generate error message.
     * TODO: create constants for errors.
     *
     * @param string $type Error type.
     * @return string Error message.
     */
    public function error($type)
    {
        $error = "Error: Variable is not a";
        // thought it would be nice to place in some nice grammar techniques :)
        // this just checks if the type starts with a vowel or "x" and displays either "a" or "an"
        if ( in_array(substr($type, 0, 1), array( "a", "e", "i", "o", "u", "x" )) )
        {
            $error .= "n";
        }
        return ($error . " " . $type . " type");
    }

    /**
     * Check variable type.
     * TODO: create constants for types.
     *
     * @param string $var 
     */
    public function checkType($var)
    {
        switch ( gettype($var) )
        {
            case "resource" :
                $this->varIsResource($var);
                break;
            case "object" :
                $this->varIsObject($var);
                break;
            case "array" :
                $this->varIsArray($var);
                break;
            case "boolean" :
                $this->varIsBoolean($var);
                break;
            default :
                $var = ($var == "") ? "[empty string]" : $var;

                $additional = ($var == "[empty string]") ? '' : ' (' . gettype($var) . ', length: ' . strlen($var) . ')';

                echo "<table cellspacing=1 class='dBug_array'><tr>\n<td class='dBug_arrayKey'>\n<pre>" . $var . "</pre></td>\n<td>\n" . $additional . "</td>\n</tr>\n</table>\n";
                break;
        }
    }

    /**
     * Print string representation of boolean
     *
     * @param integer $var Zero or one.
     */
    public function varIsBoolean($var)
    {
        $var = ($var == 1) ? "TRUE" : "FALSE";
        echo $var;
    }

    // 
    /**
     * Check if variable is an array type.
     * 
     * @param mixed $var 
     */
    public function varIsArray($var)
    {
        $this->makeTableHeader("array", "array of size " . count($var));

        if ( is_array($var) )
        {
            foreach ( $var as $key => $value )
            {
                $this->makeTDHeader("array", $key);

                if ( in_array(gettype($value), $this->arrType) )
                {
                    $this->checkType($value);
                }
                else
                {
                    $value = (trim($value) == "") ? "[empty string]" : $value;
                    $additional = ($value == "[empty string]") ? '' : ' (' . gettype($value) . ', length: ' . strlen($value) . ')';

                    echo $value . "</td>\n<td>\n " . $additional . "</td>\n</tr>\n";
                }
            }
        }
        else
        {
            echo "<tr><td>" . $this->error("array") . $this->closeTDRow();
        }

        echo "</table>";
    }

    /**
     * Check if variable is an object type.
     *
     * @param mixed $var 
     */
    public function varIsObject($var)
    {
        $this->makeTableHeader("object", "object of type " . get_class($var));
        $arrObjVars = get_object_vars($var);

        if ( is_object($var) )
        {
            foreach ( $arrObjVars as $key => $value )
            {
                if ( is_object($value) )
                {
                    if ( !method_exists($value, '__toString'))
                    {
                        $value = get_class($value);
                    }
                    else
                    {
                        $value = (trim($value) == "") ? "[empty string]" : $value;
                        $additional = ($value == "[empty string]") ? '' : ' (' . gettype($value) . ', length: ' . strlen($value) . ')';
                    }
                }

                $this->makeTDHeader("object", $key);

                if ( in_array(gettype($value), $this->arrType) )
                {
                    $this->checkType($value);
                }
                else
                {
                    echo $value . $additional . $this->closeTDRow();
                }
            }
            $arrObjMethods = get_class_methods(get_class($var));
            foreach ( $arrObjMethods as $key => $value )
            {
                $this->makeTDHeader("object", $value);
                echo "[method]" . $this->closeTDRow();
            }
        }
        else
        {
            echo "<tr><td>" . $this->error("object") . $this->closeTDRow();
        }

        echo "</table>";
    }

    /**
     * Check if variable is a resource type.
     *
     * @param mixed $var 
     */
    public function varIsResource($var)
    {
        $this->makeTableHeader("resourceC", "resource of type " . get_resource_type($var), 1);
        echo "<tr>\n<td>\n";
        switch ( get_resource_type($var) )
        {
            case "fbsql result" :
            case "mssql result" :
            case "msql query" :
            case "pgsql result" :
            case "sybase-db result" :
            case "sybase-ct result" :
            case "mysql result" :
                $db = current(explode(" ", get_resource_type($var)));
                $this->varIsDBResource($var, $db);
                break;
            case "gd" :
                $this->varIsGDResource($var);
                break;
            case "xml" :
                $this->varIsXmlResource($var);
                break;
            default :
                echo get_resource_type($var) . $this->closeTDRow();
                break;
        }
        echo $this->closeTDRow() . "</table>\n";
    }

    /**
     * Check if variable is an xml type.
     *
     * @param mixed $var 
     */
    public function varIsXml($var)
    {
        $this->varIsXmlResource($var);
    }

    /**
     * Check if variable is an xml resource type.
     *
     * @param mixed $var
     */
    public function varIsXmlResource($var)
    {
        $xml_parser = xml_parser_create();
        xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_element_handler($xml_parser, array( &$this, "xmlStartElement" ), array( &$this, "xmlEndElement" ));
        xml_set_character_data_handler($xml_parser, array( &$this, "xmlCharacterData" ));
        xml_set_default_handler($xml_parser, array( &$this, "xmlDefaultHandler" ));

        $this->makeTableHeader("xml", "xml document", 2);
        $this->makeTDHeader("xml", "xmlRoot");

        // attempt to open xml file
        $bFile = (!($fp = @fopen($var, "r"))) ? false : true;

        // read xml file
        if ( $bFile )
        {
            while ( $data = str_replace("\n", "", fread($fp, 4096)) )
            {
                $this->xmlParse($xml_parser, $data, feof($fp));
            }
        }
        else
        //if xml is not a file, attempt to read it as a string
        {
            if ( !is_string($var) )
            {
                echo $this->error("xml") . $this->closeTDRow() . "</table>\n";
                return;
            }
            $data = $var;
            $this->xmlParse($xml_parser, $data, 1);
        }

        echo $this->closeTDRow() . "</table>\n";
    }

    /**
     * Parse XML.
     *
     * @param resource $xml_parser
     * @param string $data
     * @param boolean $bFinal 
     */
    public function xmlParse($xml_parser, $data, $bFinal)
    {
        if ( !xml_parse($xml_parser, $data, $bFinal) )
        {
            die(sprintf("XML error: %s at line %d\n", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
        }
    }

    /**
     * XML: inititiated when a start tag is encountered.
     *
     * @param type $parser
     * @param type $name
     * @param type $attribs 
     */
    public function xmlStartElement($parser, $name, $attribs)
    {
        $this->xmlAttrib[$this->xmlCount] = $attribs;
        $this->xmlName[$this->xmlCount] = $name;
        $this->xmlSData[$this->xmlCount] = '$this->makeTableHeader("xml","xml element",2);';
        $this->xmlSData[$this->xmlCount] .= '$this->makeTDHeader("xml","xmlName");';
        $this->xmlSData[$this->xmlCount] .= 'echo "<strong>' . $this->xmlName[$this->xmlCount] . '</strong>".$this->closeTDRow();';
        $this->xmlSData[$this->xmlCount] .= '$this->makeTDHeader("xml","xmlAttributes");';

        if ( count($attribs) > 0 )
        {
            $this->xmlSData[$this->xmlCount] .= '$this->varIsArray($this->xmlAttrib[' . $this->xmlCount . ']);';
        }
        else
        {
            $this->xmlSData[$this->xmlCount] .= 'echo "&nbsp;";';
        }

        $this->xmlSData[$this->xmlCount] .= 'echo $this->closeTDRow();';
        $this->xmlCount++;
    }

    /**
     * XML: initiated when an end tag is encountered.
     *
     * @param type $parser
     * @param type $name 
     */
    public function xmlEndElement($parser, $name)
    {
        for ( $i = 0; $i < $this->xmlCount; $i++ )
        {
            eval($this->xmlSData[$i]);
            $this->makeTDHeader("xml", "xmlText");
            echo (!empty($this->xmlCData[$i])) ? $this->xmlCData[$i] : "&nbsp;";
            echo $this->closeTDRow();
            $this->makeTDHeader("xml", "xmlComment");
            echo (!empty($this->xmlDData[$i])) ? $this->xmlDData[$i] : "&nbsp;";
            echo $this->closeTDRow();
            $this->makeTDHeader("xml", "xmlChildren");
            unset($this->xmlCData[$i], $this->xmlDData[$i]);
        }

        echo $this->closeTDRow();
        echo "</table>";
        $this->xmlCount = 0;
    }

    /**
     * XML: initiated when text between tags is encountered.
     *
     * @param type $parser
     * @param type $data 
     */
    public function xmlCharacterData($parser, $data)
    {
        $count = $this->xmlCount - 1;
        if ( !empty($this->xmlCData[$count]) )
        {
            $this->xmlCData[$count] .= $data; 
        }
        else
        {
            $this->xmlCData[$count] = $data;
        }
    }

    /**
     * XML: initiated when a comment or other miscellaneous texts is encountered.
     *
     * @param type $parser
     * @param type $data 
     */
    public function xmlDefaultHandler($parser, $data)
    {
        //strip '<!--' and '-->' off comments
        $data = str_replace(array( "&lt;!--", "--&gt;" ), "", htmlspecialchars($data));
        $count = $this->xmlCount - 1;

        if ( !empty($this->xmlDData[$count]) )
        {
            $this->xmlDData[$count] .= $data; 
        }
        else
        {
            $this->xmlDData[$count] = $data;
        }
    }

    /**
     * Check if variable is a database resource type.
     *
     * @param mixed $var
     * @param string $db 
     */
    public function varIsDBResource($var, $db = "mysql")
    {
        $numrows = call_user_func($db . "_num_rows", $var);
        $numfields = call_user_func($db . "_num_fields", $var);
        $this->makeTableHeader("resource", $db . " result", $numfields + 1);
        echo "<tr><td class=\"dBug_resourceKey\">&nbsp;</td>";

        for ( $i = 0; $i < $numfields; $i++ )
        {
            $field[$i] = call_user_func($db . "_fetch_field", $var, $i);
            echo "<td class=\"dBug_resourceKey\">" . $field[$i]->name . "</td>";
        }

        echo "</tr>";

        for ( $i = 0; $i < $numrows; $i++ )
        {
            $row = call_user_func($db . "_fetch_array", $var, constant(strtoupper($db) . "_ASSOC"));
            echo "<tr>\n";
            echo "<td class=\"dBug_resourceKey\">" . ($i + 1) . "</td>";
            for ( $k = 0; $k < $numfields; $k++ )
            {
                $tempField = $field[$k]->name;
                $fieldrow = $row[($field[$k]->name)];
                $fieldrow = ($fieldrow == "") ? "[empty string]" : $fieldrow;
                echo "<td>" . $fieldrow . "</td>\n";
            }
            echo "</tr>\n";
        }

        echo "</table>";

        if ( $numrows > 0 )
        {
            call_user_func($db . "_data_seek", $var, 0);
        }
    }

    /**
     * Check if variable is an image/gd resource type.
     *
     * @param mixed $var 
     */
    public function varIsGDResource($var)
    {
        $this->makeTableHeader("resource", "gd", 2);
        $this->makeTDHeader("resource", "Width");
        echo imagesx($var) . $this->closeTDRow();
        $this->makeTDHeader("resource", "Height");
        echo imagesy($var) . $this->closeTDRow();
        $this->makeTDHeader("resource", "Colors");
        echo imagecolorstotal($var) . $this->closeTDRow();
        echo "</table>";
    }
}

?>
