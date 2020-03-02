<?php

/*********************************************\
 | dia2sql.php v. 1.2.6                          |
 | ----------------------------------------------|
 | Daniel Afonso Heisler (daniel@solis.coop.br)  |
 \*********************************************/

include_once("lib/dia2sql.class");

$usage = "Usage: php ".basename(__FILE__)." -f <format> -i <input file> [-o output dir or file] [-m] [-s]\n";
$usage .= "Help:  php ".basename(__FILE__)." -h\n\n";
$help  = $usage;
$help .= "ARGUMENTS:\n";
$help .= "   -f <pgsql|mysql>  DB Format to generate (see FORMATS below)\n";
$help .= "   -i <file>         Filename to process\n";
$help .= "   -o <dir|file>     Directory or Filename to store SQL output (defaults to output.sql if option not specified)\n";
$help .= "   -m                Output each table's SQL into a seperate file\n";
$help .= "   -s                Sensitive case\n";
$help .= "   -h                Display this help message\n\n";
$help .= "FORMATS\n";
$help .= "   pgsql      Generate sql files in PostgreSQL format\n";
$help .= "   mysql      Generate sql files in MySQL format\n\n";

$opt = getopt("hmo:i:f:s");

if (isset($opt[h]))
{
    echo $help;
    exit;
}

elseif ( count($opt)>0 )
{
    if (!$opt[f] || !$opt[i])
    {
        echo $usage;
        exit;
    }
}
else
{
    $opt[f] = str_replace("--","",$format);
    $opt[i] = $file;
    $opt[o] = $directory;
    if ( $many == true )
    $opt[m] = true;

    if (!$opt[f] || !$opt[i])
    {
        echo $usage;
        exit;
    }
}

$format    = trim($opt[f]);
$file      = trim($opt[i]);
$many      = isset($opt[m]) ? true : false;
$directory = trim($opt[o]);
$directory = $directory ? $directory : ($many ? "output/" : "output.sql");
$directory .= ( is_dir($directory) && substr($directory,-1) != '/' ) ? '/' : '';

if ( !isset($lower) )
{
    $lower = ( isset($opt[s]) ) ? false : true;
}

if ( substr($directory, -1) == '/' && !file_exists($directory) )
{
    mkdir($directory);
}

if ( file_exists($file) )
{
    if ( $parse = file_get_contents("compress.zlib://" . $file) )
    {
        ###########################################################
        # Tira os acentos
        ###########################################################
        $parse = utf8_decode($parse);

        $a = array( "/[�����]/" =>"A",
                    "/[�����]/" =>"a",
                    "/[����]/"  =>"E",
                    "/[����]/"  =>"e",
                    "/[����]/"  =>"I",
                    "/[����]/"  =>"i",
                    "/[�����]/" =>"O",
                    "/[�����]/" =>"o",
                    "/[����]/"  =>"U",
                    "/[����]/"  =>"u",
                    "/�/"       =>"c",
                    "/�/"       =>"C");

        $parse = preg_replace(array_keys($a), array_values($a), $parse);
        ###########################################################
        # Tira os acentos
        ###########################################################

        $xml_parser = new xml_parser();
        $xml_parser->parse($parse);
        $data = $xml_parser->data;
        if ( !$data )
        die("Wrong type of archive.\nIt's not a XML file.\n");
        else
        {
            $dia2sql = new dia2sql($format, $data, $directory, $many, $lower);

            echo "Putting output in one file".($many ? " per table " : " ").(is_dir($directory) ? 'in the directory ' : 'called ')."'$directory'.\n\n";
            $tables = $dia2sql->getInstall();
            foreach ( $tables as $id => $value )
            {
                if ( $format == "--pgsql" )
                $string .= "\i " . strtolower($value) . ".sql\n";
                elseif ( $format == "--mysql" )
                $string .= "\. " . strtolower($value) . ".sql\n";
            }
            if ( $fp = @fopen( $directory . "/install", 'w' ) )
            {
                @fputs ( $fp, $string );
                @fclose( $fp );
                if ( $many )
                {
                    foreach ( $tables as $id => $value )
                    {
                        if ( $lower == true )
                        {
                            $value = strtolower($value);
                        }
                        if ( $format == "pgsql" )
                        $string .= "\i " . $value . ".sql\n";
                        elseif ( $format == "mysql" )
                        $string .= "\. " . $value . ".sql\n";
                    }
                    if ( $fp = @fopen( $directory . "/install", 'w' ) )
                    {
                        @fputs ( $fp, $string );
                        @fclose( $fp );
                    }
                }
            }
        }
    }
    else
    die("Could not read XML input.\nInvalid format.\n");
}
else
die("Could not open XML input.\nIncorrect file or path: '$file'.\n");
 
?>
