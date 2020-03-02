<?php

$MIOLO = MIOLO::getInstance();

// Carrega arquivo de bootstrap, caso exista
$bsFile = dirname(__FILE__) . '/bootstrap.php';
if ( file_exists($bsFile) )
{
    require_once $bsFile;
}

#ini_set("error_report", "E_ALL & ~E_NOTICE");

#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# Support for gettext()
#---------------------------------------------------------------------
function setDomainLocale($domain)
{
    global $MIOLO, $TEXTDOMAIN;
    $TEXTDOMAIN = $domain;

    $lang = $MIOLO->getConf('i18n.language');
    $charset = $MIOLO->getConf('options.charset');

    if ( !$charset )
    {
        $charset = 'ISO-8859-1';
    }

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
    global $TEXTDOMAIN, $MIOLO;

    $dom = $dom ? $dom : 'miolo';

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

        $originalMsg = $msg;
        $msg = _($msg);

        /* Este codigo verifica se a traducao pelo miolo.po nao teve efeito.
         * Entao se o miolo nao traduziu ele busca no modulo para ver se ha uma traducao.
         * Este caso foi criado pois o sagu utiliza o _M sem passar o modulo. Seria muito
         * trabalhoso alterar todo o sagu para funcionar desta forma.
         */
        if ( $msg == $originalMsg )
        {
            $module = MIOLO::_REQUEST('module');
            if ( 'miolo' == $dom && !empty($module) )
            {
                setDomainLocale($module);
                $msg = _($msg);
                setDomainLocale($TEXTDOMAIN);
            }
        }
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
    $lcClassName = strtolower($className);
    $file = $autoload->getFile($lcClassName);

    if ( $file != '' )
    {
        include_once($file);
    }
    else if ( method_exists('sAutoload', 'SAGUAutoload') )
    {
        sAutoload::SAGUAutoload($className);
    }
}

function backtrace($depth = 20)
{
    $traces = debug_backtrace();
    $lines = array();
    
    for ($i=0; $i <= $depth; $i++)
    {
        $trace = $traces[$i];
        
        if ( $trace )
        {
            $lines[] = $trace['function'] . '()' . ' - ' . basename($trace['file']) . ':' . $trace['line'];
        }
    }
    
    return $lines;
}

function btr($depth = 20)
{
    $exp = var_export(backtrace($depth), true);
    flog($exp);
}

spl_autoload_register('miolo_autoload');
?>
