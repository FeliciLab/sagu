<?php

/**
 * Class MUtil.
 *
 * @author Thomas Spriestersbach [ts@interact2000.com.br]
 * @author Vilson Cristiano Gärtner [vgartner@univates.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2001/08/14
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2001-2002 UNIVATES Centro Universitário \n
 * Copyright (c) 2003-2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('m_util.js');

class MUtil
{
    /**
     * Function to add information to firebug's console and to Miolo's trace.
     *
     * @param $message string/array to be shown
     */
    public static function clog($args)
    {
        $MIOLO = MIOLO::getInstance();
        $result = MUtil::dataforFirebugConsole(func_get_args());

        if ( is_array($result) )
        {
            $clogs = '';
            foreach ( $result as $line => $info )
            {
                $clogs .= 'console.log(\'' . $info . '\');';
            }
            $MIOLO->page->addJsCode($clogs);
            $MIOLO->trace('[CLOG] ' . var_export(func_get_args(), true));
        }
    }

    /**
     * Function to add information to firebug's console error and to Miolo's trace.
     *
     * @param mixed $message Message to be shown
     */
    public static function cerror($args)
    {
        $MIOLO = MIOLO::getInstance();
        $result = MUtil::dataforFirebugConsole(func_get_args());

        if ( is_array($result) )
        {
            foreach ( $result as $line => $info )
            {
                $MIOLO->page->onload('console.error(\'' . $info . '\');');
            }
            $MIOLO->trace('[CERROR] ' . var_export(func_get_args(), true));
        }
    }

    /**
     * Support function for clog
     * @param $args string/array 
     * @return array 
     */
    private function dataforFirebugConsole($array)
    {
        $MIOLO = MIOLO::getInstance();

        if ( is_array($array) )
        {
            foreach ( $array as $line => $info )
            {
                if ( !is_string($info) )
                {
                    $info = print_r($info, 1);
                }
                $info = str_replace("\n", '\n', $info); // changes new line from php to js
                $info = str_replace("'", "\'", $info); // removes ' to avoid js syntax errors
                $result[] = $info;
            }
        }

        return $result;
    }

    /**
     * Function to add information log to /tmp/flog
     * @param $message string/array to be shown
     */
    public static function flog()
    {
        if ( file_exists('/tmp/flog') )
        {
            $numArgs = func_num_args();
            $dump = '';
            for ( $i = 0; $i < $numArgs; $i++ )
            {
                $dump .= var_export(func_get_arg($i), true) . "\n";
            }

            $f = fopen('/tmp/flog', 'a');
            fwrite($f, $dump);
            fclose($f);
        }
    }

    public function NVL($value1, $value2)
    {
        return ($value1 != NULL) ? $value1 : $value2;
    }

    public function ifNull($value1, $value2, $value3)
    {
        return ($value1 == NULL) ? $value2 : $value3;
    }

    public function setIfNull(&$value1, $value2)
    {
        if ( $value1 == NULL ) $value1 = $value2;
    }

    public function setIfNotNull(&$value1, $value2)
    {
        if ( $value2 != NULL ) $value1 = $value2;
    }

    /**
     * @todo TRANSLATION
     * Retorna o valor booleano da variável
     * Função utilizada para testar se uma variável tem um valor booleano, conforme definição: será verdadeiro de 
     *      for 1, t ou true... caso contrário será falso.
     *
     * @param mixed $value valor a ser testado
     *
     * @return boolean value
     *
     */
    public static function getBooleanValue($value)
    {
        $trues = array( 't', '1', 'true', 'True' );

        if ( is_bool($value) )
        {
            return $value;
        }

        return in_array($value, $trues);
    }

    /**
     * @todo TRANSLATION
     * Retorna o valor da variável sem os caracteres considerados vazios
     * Função utilizada para remover os caracteres considerados vazios
     *
     * @param mixed $value valor a ser substituido
     *
     * @return string value
     */
    public function removeSpaceChars($value)
    {
        $blanks = array( "\r" => '', "\t" => '', "\n" => '', '&nbsp;' => '', ' ' => '' );

        return strtr($value, $blanks);
    }

    /**
     * @todo TRANSLATION
     * Copia diretorio
     * Esta funcao copia o conteudo de um diretorio para outro
     *
     * @param string $sourceDir Diretorio de origem
     * @param string $destinDir Diretorio de destino
     *
     * @return string value
     */
    public function copyDirectory($sourceDir, $destinDir)
    {
        if ( file_exists($sourceDir) && file_exists($destinDir) )
        {
            $open_dir = opendir($sourceDir);

            while ( false !== ( $file = readdir($open_dir) ) )
            {
                if ( $file != "." && $file != ".." )
                {
                    $aux = explode('.', $file);

                    if ( $aux[0] != "" )
                    {
                        if ( file_exists($destinDir . "/" . $file) &&
                                filetype($destinDir . "/" . $file) != "dir" )
                        {
                            unlink($destinDir . "/" . $file);
                        }
                        if ( filetype($sourceDir . "/" . $file) == "dir" )
                        {
                            if ( !file_exists($destinDir . "/" . $file) )
                            {
                                mkdir($destinDir . "/" . $file . "/");
                                self::copyDirectory($sourceDir . "/" . $file, $destinDir . "/" . $file);
                            }
                        }
                        else
                        {
                            copy($sourceDir . "/" . $file, $destinDir . "/" . $file);
                        }
                    }
                }
            }
        }
    }

    /**
     * @todo TRANSLATION
     * Remove diretorio
     * Esta funcao remove recursivamente o diretorio e todo o conteudo existente dentro dele
     *
     * @param string $directory Diretorio a ser removido
     * @param boolean $empty 
     *
     * @return string value
     */
    public function removeDirectory($directory, $empty=FALSE)
    {
        if ( substr($directory, -1) == '/' )
        {
            $directory = substr($directory, 0, -1);
        }

        if ( !file_exists($directory) || !is_dir($directory) )
        {
            return FALSE;
        }
        elseif ( is_readable($directory) )
        {
            $handle = opendir($directory);

            while ( FALSE !== ( $item = readdir($handle) ) )
            {
                if ( $item != '.' && $item != '..' )
                {
                    $path = $directory . '/' . $item;

                    if ( is_dir($path) )
                    {
                        self::removeDirectory($path);
                    }
                    else
                    {
                        unlink($path);
                    }
                }
            }

            closedir($handle);

            if ( $empty == FALSE )
            {
                if ( !rmdir($directory) )
                {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
     * @todo TRANSLATION
     * Retorna o diretório temporario
     * Esta funcao retorna o diretório temporário do sistema operacional
     *
     * @return string directory name
     */
    static public function getSystemTempDir()
    {
        $tempFile = tempnam(md5(uniqid(rand(), TRUE)), '');
        if ( $tempFile )
        {
            $tempDir = realpath(dirname($tempFile));
            unlink($tempFile);

            return $tempDir;
        }
        else
        {
            return '/tmp';
        }
    }

    /**
     * Searches the array recursively for a given value and returns the corresponding key if successful.
     *
     * @param string $needle
     * @param array $haystack
     * @return mixed If found, returns the key, otherwise FALSE.
     */
    public static function array_search_recursive($needle, $haystack)
    {
        $found = FALSE;
        $result = FALSE;

        foreach ( $haystack as $k => $v )
        {
            if ( is_array($v) )
            {
                for ( $i = 0; $i < count($v); $i++ )
                {
                    if ( $v[$i] === $needle )
                    {
                        $result = $v[0];
                        $found = TRUE;
                        break;
                    }
                }
            }
            else
            {
                if ( $found = ($v === $needle) )
                {
                    $result = $k;
                }
            }

            if ( $found == TRUE )
            {
                break;
            }
        }

        return $result;
    }

    /**
     * Generates a ajax function call with the given arguments.
     * Use MUtil::getAjaxActionArgs() to get the given arguments on ajax request.
     *
     * @param string $event PHP function to be called.
     * @param array $args Arguments array.
     * @return string Javascript function call.
     */
    public static function getAjaxAction($event, $args='')
    {
        $MIOLO = MIOLO::getInstance();

        if ( is_object($args) )
        {
            $args = (array) $args;
        }

        if ( is_array($args) )
        {
            $strArgs = '';
            foreach ( $args as $param => $value )
            {
                $param = rawurlencode($param);
                $value = rawurlencode($value);
                $strArgs .= "$param=$value&";
            }

            $args = $strArgs;
        }

        return "miolo.doAjax('$event','$args','{$MIOLO->page->getFormId()}');";
    }

    /**
     * @return object Return the parameters generated by MUtil::getAjaxAction.
     */
    public static function getAjaxActionArgs()
    {
        $MIOLO = MIOLO::getInstance();
        $requestId = $MIOLO->page->getFormId() . '__EVENTARGUMENT';
        $ajaxArgs = array( );

        if ( $_REQUEST[$requestId] )
        {
            $url = rawurldecode($_REQUEST[$requestId]);
            $args = explode('&', $url);

            foreach ( $args as $a )
            {
                $param = explode('=', $a);
                $ajaxArgs[$param[0]] = $param[1];
            }
        }

        return (object) array_merge($_REQUEST, $ajaxArgs);
    }

    /**
     * @return boolean Return whether is the first access to page.
     */
    public static function isFirstAccessToPage()
    {
        $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        
        // Compatibilidade com instalacao sem virtualHost no apache (utilizando .htaccess)
        // nestes casos a URL fica normalizada, sem o sufixo /html/
        $urlReplaced = str_replace('/html/', '/', $url);
        
        return in_array($_SERVER['HTTP_REFERER'], array($url, $urlReplaced)) && self::isFirstAccessToForm();
    }

    /**
     * @return boolean Return whether is the first access to form.
     */
    public static function isFirstAccessToForm()
    {
        return !self::isAjaxEvent();
    }

    /**
     * @return boolean Return whether is an ajax event.
     */
    public static function isAjaxEvent()
    {
        $MIOLO = MIOLO::getInstance();
        $eventA = MIOLO::_REQUEST('__EVENTTARGETVALUE');
        $eventB = self::getDefaultEventValue();
        return ( $eventA || $eventB );
    }

    /**
     * @return string Default event name.
     */
    public static function getDefaultEvent()
    {
        $MIOLO = MIOLO::getInstance();
        return "{$MIOLO->page->getFormId()}__EVENTTARGETVALUE";
    }

    /**
     * @return string Default event value.
     */
    public static function getDefaultEventValue()
    {
        return MIOLO::_REQUEST(self::getDefaultEvent());
    }

    /**
     * @return array URL parameters without the default ones.
     */
    public static function getURLParameters()
    {
        $MIOLO = MIOLO::getInstance();
        $vars = $MIOLO->getContext()->getVars();

        unset($vars['module']);
        unset($vars['action']);
        unset($vars['function']);

        return $vars;
    }

    /**
     * Generates a formatted var_dump output.
     * This method uses Kwaku Otchere's dBug.class.php which dumps/displays the contents of a variable in a
     * colored tabular format based on the idea, javascript and css code of Macromedia's ColdFusion cfdump tag
     * <br>
     * @example
     * <code>
     * ...
     * MUtil::debug($varName);
     * MUtil::debug($myVariable, 'array');
     * ...
     * </code>
     *
     * $myVariable will be treated and dumped as an array type,
     * even though it might originally have been a string type, etc.
     * NOTE! $forceType is REQUIRED for dumping an xml string or xml file
     * <code>MIOLO::debug($strXml, "xml");</code>
     *
     * @param string $variable Variable to be shown.
     * @param string $forceType Optional parameter. If it's given, the variable
     * supplied to the function is forced to have that given type.
     */
    public static function debug($variable, $forceType=null)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('contrib/dbug.class.php');

        if ( $forceType != null )
        {
            new dBug($variable);
        }
        else
        {
            new dBug($variable, "$forceType");
        }
    }

    /**
     * Connect an AJAX function to the right click event upon the given control.
     *
     * @param object $control The control to enable the right click action.
     * @param string $action AJAX function.
     */
    public static function setRightClickAjaxAction($control, $action)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onload("mutil.setRightClickAjaxAction('{$MIOLO->page->getFormId()}', '$control->id', '$action');");
    }

    /**
     * Display the back trace history.
     *
     * @param integer $limit Limit of back trace history.
     */
    public static function debugBacktrace($limit=4)
    {
        $backtrace = debug_backtrace(false);

        // Removes MUtil::debugBacktrace line.
        unset($backtrace[0]);

        $i = 0;
        $traces = array();

        foreach ( $backtrace as $trace )
        {
            if ( $i == $limit ) break;

            $traces[] = array(
                _M('File') => $trace['file'] . ':' . $trace['line'],
                _M('Class') => $trace['class'],
                _M('Function') => $trace['function']
            );

            $i++;
        }

        self::debug($traces);
    }
    
    /**
     * Debug a var in /tmp/miolo_debug
     *
     * @param Variable $var
     * @param boolean $append make append content or not.
     */
    public static function MDEBUG($var, $append=false, $file = null)
    {
        if ( !$file )
        {
            $file = '/tmp/miolo_debug';
        }
        
        if($append)
            file_put_contents($file, var_export($var,1)."\r\n", FILE_APPEND);
        else
            file_put_contents($file, var_export($var,1)."\r\n");
    }

    /**
     * Get a div with align attribute set as center.
     *
     * @param array $content Controls.
     * @param string $id Div id.
     * @return MDiv Instance of the div component.
     */
    public static function centralizedDiv($content, $id=NULL)
    {
        return new MDiv($id, $content, NULL, 'align="center"');
    }
    
    public static function getBrowser()
    {   
        $browser = $_SERVER['HTTP_USER_AGENT'];
        
        if(strstr($browser, 'MSIE'))
        {
            $tipo = 'IE';
        }
        
        if(strstr($browser, 'Firefox'))
        {
            $tipo = 'Firefox';
        }
        
        if(strstr($browser, 'Chrome'))
        {
            $tipo = 'Google Chrome';
        }
        
        if(strstr($browser, 'Android'))
        {
            $tipo = 'Android';
        }
        
        if(strstr($browser, 'webOS'))
        {
            $tipo = 'webOS';
        }
        
        if(strstr($browser, 'iPhone'))
        {
            $tipo = 'iPhone';
        }
        
        if(strstr($browser, 'iPod'))
        {
            $tipo = 'iPod';
        }
        
        return $tipo;
    }
}

?>
