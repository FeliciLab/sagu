<?php
###################
# @author Moises
###################

# Este arquivo PHP deve estar no diretorio raiz do MIOLO para funcionar corretamente.
# CUIDADO: Caso haja uma base diferente para algum dos módulos (ex.: moodle) , será perdido.


if ( !isset($argv[1]) )
{
    exit('Use: php changedb.php "host=dbbackup&port=5432&name=sagu_URCAMP_diario&user=postgres&password=postgres"' . "\n");
}

parse_str($argv[1], $confs);

if ( !$confs['port'] )
{
    $confs['port'] = 5432;
}

$files[] = './miolo20/etc/miolo.conf';
$files[] = './miolo26/etc/miolo.conf';

foreach ( $files as $file )
{
    $info = simplexml_load_file( $file );

    foreach ( $info->db[0] as $module => $val )
    {
        foreach ( $confs as $ckey => $cval )
        {
            $val->$ckey = $cval;
        }

        // Concatena porta pois miolo26 exige
        if ( preg_match('/miolo26/', $file) )
        {
            $val->host = $val->host . ':' . $confs['port'];
        }
    }

    // save the updated document
    $info->asXML( $file );
}
?>
