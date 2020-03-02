<?php

global $MIOLO;
if ( !$MIOLO->getConf('options.debug') )
{
    ini_set("error_reporting", "E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED");
    ini_set("log_errors","on");
    ini_set("error_log","../var/log/php_error.log");
}

#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# Support for gettext()
#---------------------------------------------------------------------
function setDomainLocale($domain)
{
    global $TEXTDOMAIN;
    $MIOLO = MIOLO::getInstance();
    $TEXTDOMAIN = $domain;

    $lang = $MIOLO->getConf('i18n.language');
    $charset = $MIOLO->getConf('options.charset');
    $localedir = $MIOLO->getConf('i18n.locale');

    $locale = "$lang.$charset";
    $fallback1 = "$lang.utf8";
    $fallback2 = "$lang.UTF-8";

    /*
     * Try to set the language with the encoding, if it does not exist, try only the language and then, if nothing else 
     * worked, try the default UTF-8 locales. This is important to make MIOLO work with the configured language. The
     * encoding itself does not matter here, it is changed below to the one defined on the configuration file.
     */
    $locale = setlocale(LC_ALL, $locale, $lang, $fallback1, $fallback2);

    bind_textdomain_codeset($TEXTDOMAIN, $charset);
    bindtextdomain($TEXTDOMAIN, $localedir);
    textdomain($TEXTDOMAIN);

    // FIXME: check if this is necessary. Maybe it is for using MIOLO on console.
    if ( $TEXTDOMAIN == 'miolo' )
    {
        putenv("LC_ALL=$locale");
    }
}

if (function_exists('_'))
{
    setDomainLocale('miolo');
}

#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# This function is a wrapper for the gettext support. In addition to
# the standard gettext() function, it provides support for up to three
# positional parameters represented by the placeholders <code>@1</code>,
# <code>@2</code> and <code>@3</code>.
#
# @example
# echo _M('@1 is a romance written by @2 in @3','foo','Tom Sawyer','Mark Twain','1890');
# // will result in:
# //   "Tom Sawyer is a romance written by Mark Twain in 1890"
#
# echo _M('@2 escreveu o romance @1 em @3','foo','Tom Sawyer','Mark Twain','1890');
# //
# // will result in:
# //   "Mark Twain escreveu o romance Tom Sawyer em 1890"
#---------------------------------------------------------------------
function _M($msg, $dom = 'miolo', $p1 = null, $p2 = null, $p3 = null)
{
    global $TEXTDOMAIN, $MIOLOCONF;

    if ($msg == '')
    {
        return $msg;
    }

    if (function_exists('_'))
    {
        if ($dom != $TEXTDOMAIN)
        {
            setDomainLocale($dom);
        }
        $msg = _($msg);
    }

    if ($p1 !== null)
    {
        $msg = str_replace('@1', $p1, $msg);
    }

    if ($p2 !== null)
    {
        $msg = str_replace('@2', $p2, $msg);
    }

    if ($p3 !== null)
    {
        $msg = str_replace('@3', $p3, $msg);
    }

    return $msg;
}

function miolo_autoload($className)
{
    global $autoload;

//echo "<br>autoload: $className<br>";
    if (defined('DOMPDF_DIR'))
    {
        $fileName = DOMPDF_autoload($className);
        if (file_exists($fileName))
           $autoload->setFile($className, $fileName);
    }
    $className = strtolower($className);
    if ( ($file = $autoload->getFile($className)) != '' )
    {
        include_once($file);
    }
}

spl_autoload_register('miolo_autoload');

$error_types = (int)ini_get("error_reporting");

set_error_handler('errorHandlerMiolo',$error_types);

function errorHandlerMiolo( $errno, $errstr, $errfile, $errline, $errcontext)
{
    $e = "Unkown Error";
    switch ($errno) {
        case E_ERROR:
            $e = "Error";
            break;

        case E_WARNING:
            $e = "Warning";
            break;

        case E_PARSE:
            $e = "Parse Error";
            break;

        case E_NOTICE:
            $e = "Notice";
            break;
    }

    echo "<b>{$e}:</b>&nbsp;{$errstr}&nbsp;in&nbsp;<b>{$errfile}</b> on line <b>{$errline}</b><br/>";

    /* Don't execute PHP internal error handler */
    return true;

/*
    $backtrace = debug_backtrace();
    
    foreach( $backtrace as $b=>$individual )
    {
        echo "Stack: ". $i++ ."<br/>";

        foreach( $individual as $key=>$value )
        {
            if ( is_array($key) )
            {
                $value = "";

                foreach ($key as $v)
                {
                    $value .= "$v, ";
                }
            }
            echo "$key:  ";

            if ( is_object($value) )
            {
                echo 'Classe: '.get_class($value);
            }
            else
            {
                echo "$value <br/>";
            }
        }
    }
*/
}

/*
 function errorHandlerMiolo( $errno, $errstr, $errfile, $errline, $errcontext)
 {
    $backtrace = debug_backtrace();
    
    foreach( $backtrace as $b=>$individual )
    {
        echo "Stack: ". $i++ ."<br/>";

        foreach( $individual as $key=>$value )
        {
            if ( is_array($key) )
            {
                $value = "";

                foreach ($key as $v)
                {
                    $value .= "$v, ";
                }
            }
            echo "$key:  ";

            if ( is_object($value) )
            {
                echo 'Classe: '.get_class($value);
            }
            else
            {
                echo "$value <br/>";
            }
        }
    }

    print_r($backtrace);
    $debug = array();

    $debug[] = "Line {$backtrace[0]['line']} of {$backtrace[0]['file']}";
    $debug = implode('<br />', $debug);
    print "<pre{$display}>{$label}: {$debug}:<br />";
    print_r($msg, 1);

 }
*/

function mdump($var)
{
    $MIOLO = MIOLO::getInstance();
    $MIOLO->trace(print_r($var,true));
}

?>
