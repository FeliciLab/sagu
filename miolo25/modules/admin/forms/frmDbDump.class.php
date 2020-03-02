<?php

/**
 * Form to download database dumps.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2011/12/02
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */
class frmDbDump extends MForm
{
    public function __construct()
    {
        parent::__construct(_M('Database Dump', MIOLO::getCurrentModule()));
        $this->eventHandler();
    }

    public function createFields()
    {
        $module = MIOLO::getCurrentModule();

        $fields[] = MMessage::getMessageContainer();

        $path = $this->getPgDumpPath();

        if ( $path == NULL )
        {
            $fields[] = new MTextLabel('pgDumpPath', _M('Not found', $module), _M('Postgres path (@1)', $module, 'pg_dump'), 'red');
        }
        else
        {
            $fields[] = new MTextLabel('pgDumpPath', _M('Found', $module) . ": $path", _M('Postgres path (@1)', $module, 'pg_dump'), 'green');
        }

        $conf = $this->manager->getConf('home.etc') . '/miolo.conf';
        $xml = simplexml_load_file($conf);

        $dbs = array();

        // get databases configuration from miolo.conf
        foreach ( $xml->db->children() as $db => $object )
        {
            if ( ((string) $object->system) == 'postgres' )
            {
                $dbs[$db] = $db;
            }
        }

        $fields[] = new MSelection('dbName', NULL, _M('Database', $module), $dbs);
        $fields[] = $div = new MDiv('fileResponse', NULL);
        $div->addStyle('display', 'none');

        $this->setFields($fields);

        $buttons[] = new MButton('download', _M('Download', $module));
        $this->setButtons($buttons);

        $valids[] = new MRequiredValidator('dbName');
        $this->setValidators($valids);
    }

    /**
     * @return string Get postgres dump application path.
     */
    public function getPgDumpPath()
    {
        $cmd = 'which pg_dump';
        $path = exec($cmd);

        if ( !file_exists($path) )
        {
            if ( file_exists('/usr/bin/pg_dump') )
            {
                $path = '/usr/bin/pg_dump';
            }
            elseif ( file_exists('/usr/local/bin/pg_dump') )
            {
                $path = '/usr/local/bin/pg_dump';
            }
            else
            {
                $path = NULL;
            }
        }

        return $path;
    }

    /**
     * Download database dump.
     * 
     * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
     * @since 30/08/2007
     * @copyright Copyright (c) 2007-2011 SOLIS - Cooperativa de Soluções Livres
     */
    public function download_click()
    {
        $module = MIOLO::getCurrentModule();
        $data = $this->getData();
        $db = $data->dbName;

        if ( !$db )
        {
            new MMessageError(_M('Database not found', $module));
        }

        $dbName = $this->manager->getConf("db.$db.name");
        $dbUser = $this->manager->getConf("db.$db.user");
        $dbHost = $this->manager->getConf("db.$db.host");
        $dbPort = $this->manager->getConf("db.$db.port");
        $dbPass = $this->manager->getConf("db.$db.password");

        if ( !$dbPort )
        {
            // Set default postgres port
            $dbPort = '5432';
        }

        $bin = $this->getPgDumpPath();

        if ( $bin == NULL )
        {
            new MMessageError('pg_dump: ' . _M('command not found', $module));
            return;
        }

        // Older versions of PHP doesn't have sys_get_temp_dir, only PHP 5 >= 5.2.1
        if ( function_exists('sys_get_temp_dir') )
        {
            $tmpDir = sys_get_temp_dir();
        }
        else
        {
            $tmpDir = '/tmp';
        }

        $tmpFile = 'dump-' . date('YmdHis') . '.sql.gz';
        putenv("PGPASSWORD=$dbPass");
        $pg_dump = "$bin $dbName -h $dbHost -p $dbPort -U$dbUser -f $tmpDir/$tmpFile -Z 9";
        exec($pg_dump, $output, $return);

        if ( $return != 0 )
        {
            $this->manager->error($pg_dump . ': ' . $return);
        }
        else
        {
            $file = "db$tmpFile";

            // Add "MIOLO_" prefix. For security reasons, download.php only download files with this prefix.
            rename("$tmpDir/$tmpFile", "/tmp/MIOLO_$file");

            if ( file_exists("/tmp/MIOLO_$file") )
            {
                $url = "http://" . $_SERVER['HTTP_HOST'] . "/files/download.php?file=$file";
                $iframe = "<iframe src='$url'></iframe>";
            }

            $this->setResponse($iframe, 'fileResponse');
        }
    }
}

?>