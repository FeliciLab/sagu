<?php
require_once '../mioloconsole.class.php';

/**
 * Class to make MIOLO updates through a ZIP file.
 *
 * @author Daniel Hartmann <daniel@solis.coop.br>
 */
class MIOLOUpdater extends MIOLOConsole
{
    /**
     * File name of the compressed (tar.gz or zip) files of MIOLO.
     */
    const UPDATE = 'update';

    /**
     * File name of the script (sh or php) to be executed before applying the patch.
     */
    const PRE_SCRIPT = 'pre';

    /**
     * File name of the script (sh or php) to be executed after applying the patch.
     */
    const POST_SCRIPT = 'post';

    /**
     * File name with the last update sequential number. This file location is the root of the installation.
     */
    const CURRENT_UPDATE_VERSION_FILE = '.update';

    /**
     * @var string Directory to put the backup of the installation.
     */
    private $backupDir;

    /**
     * @var array An array containing the names of the databases and the files to update them.
     */
    private $databaseUpdates;

    /**
     * @var array An array containing the names of the backed up databases.
     */
    private $databaseBackups = array();

    /**
     * @var string Relative path to backup file.
     */
    private $backupFile;

    /**
     * @var string Directory which has been backed up.
     */
    private $backedUpDir;

    /**
     * @var boolean Whether the update was successful.
     */
    private $succeeded = false;

    /**
     * @var string Directory to put temporarialy the update files.
     */
    private $temporaryDir;

    /**
     * @var object SimpleXML instance representing the update data file content.
     */
    private $updateData;

    /**
     * @var string Updater directory full path.
     */
    private $updaterDir;

    /**
     * @var string Log file.
     */
    private $logFile;

    /**
     * MIOLOUpdater constructor.
     *
     * @param string $updateDataFile File with update data.
     */
    public function __construct($updateDataFile)
    {
        parent::__construct();

        if ( !file_exists($updateDataFile) )
        {
            $this->error(_M('File "@1" does not exist', 'miolo', $updateDataFile));
        }

        // Initialize updater directories
        $this->updaterDir = $this->MIOLO->getConf('home.miolo') . '/bin';
        $this->temporaryDir = $this->updaterDir . '/updater';
        $this->backupDir = $this->temporaryDir . '/backup';


        // Read XML file
        $xml = simplexml_load_file($updateDataFile);

        $this->updateData = $xml;
        $this->databaseUpdates = $xml->sql;

        if ( !$this->databaseUpdates )
        {
            $this->databaseUpdates = array();
        }


        // Check update file location
        if ( strpos($xml->file, '/') === FALSE )
        {
            $this->updateData->file = dirname($updateDataFile) . "/$xml->file";
        }


        // Open and extract the zip file containing update data
        $zip = new ZipArchive();

        if ( $zip->open($this->updateData->file) !== TRUE )
        {
            $this->error(_M('Zip file "@1" couldn\'t be opened', 'miolo', $this->updateData->file));
        }

        $this->emptyDirectory($this->temporaryDir);

        $this->message(_M('Extracting update package...'));
        if ( $zip->extractTo($this->temporaryDir) )
        {
            $this->message("\t\t\t\t" . _M('OK') . "\n");

            for ( $i = 0; $i < $zip->numFiles; $i++ )
            {
                $fileData = $zip->statIndex($i);
                $this->message("\t" . $this->temporaryDir . '/' . $fileData['name'] . "\n");
            }
        }
        echo "\n";

        $zip->close();


        // Create the log file
        $logFile = $this->updaterDir . "/update_{$xml->version}_$xml->sequence.log";
        $this->logFile = fopen($logFile, 'a+');
    }

    /**
     * Perform the update.
     */
    public function update()
    {
        if ( $this->updateData->sequence == $this->getCurrentVersion() )
        {
            $this->message(_M('The system is already up to dated, nothing to do.') . "\n");
            exit();
        }
        
        // Show data about the update
        $this->message(_M('Upgrading system to version @1', 'miolo', $this->updateData->version) . "\n");
        $this->message(_M('Update description') . ": {$this->updateData->description}\n\n");


        // Backup the installation
        $installationTarget = $this->MIOLO->getConf('home.miolo');
        $this->message(_M('Backing up data from "@1"...', 'miolo', $installationTarget) . "\n");
        $this->createBackup($installationTarget);
        $this->message("\t\t\t\t\t\t\t" . _M('OK') . "\n\n");


        // Execute shell pre script
        if ( file_exists("$this->temporaryDir/" . self::PRE_SCRIPT . '.sh') )
        {
            $this->message(_M('Executing shell pre script...'));
            list($result, $output, $return) = $this->execute('bash ' . "$this->temporaryDir/" . self::PRE_SCRIPT . '.sh');
            $this->message("\t\t\t" . _M('OK') . "\n");
            $this->message("\t" . implode("\n\t", $output) . "\n\n");
        }

        // Execute PHP pre script
        if ( file_exists("$this->temporaryDir/" . self::PRE_SCRIPT . '.php') )
        {
            $this->message(_M('Executing PHP pre script...'));
            list($result, $output, $return) = $this->execute('php ' . "$this->temporaryDir/" . self::PRE_SCRIPT . '.php');
            $this->message("\t\t\t" . _M('OK') . "\n");
            $this->message("\t" . implode("\n\t", $output) . "\n\n");
        }


        // Extract the UPDATE to the installation directory
        if ( file_exists("$this->temporaryDir/" . self::UPDATE . '.zip') )
        {
            $source = "$this->temporaryDir/" . self::UPDATE . '.zip';
            $target = $this->MIOLO->getConf('home.miolo');

            $this->extractZip($source, $target, $message);
            $this->message("\n");
        }
        else
        {
            $source = "$this->temporaryDir/" . self::UPDATE . '.tar.gz';
            $target = $this->MIOLO->getConf('home.miolo');
            $this->message(_M('Extracting the patch to "@1"...', 'miolo', $target));
            list($lastLine, $output, $return) = $this->extract($source, $target);

            $this->message("\t" . _M('OK') . "\n");
            $this->message("\t" . implode("\n\t", $output) . "\n");
        }


        // Database updates (PostgreSQL only)
        foreach ( $this->databaseUpdates as $dbUpdate )
        {
            $dbConf = trim($dbUpdate->dbconf);
            $system = $this->MIOLO->getConf("db.$dbConf.system");

            if ( $system != 'postgres' )
            {
                $this->prompt(
                    _M('The database "@1" is not postgres', 'miolo', $dbConf), 
                    _M('Database "@1" is in "@2" which is unsupported', 'miolo', $dbConf, $system)
                );

                $this->message(_M('The database update of "@1" hasn\'t been done.', 'miolo', $dbConf) . "\n");
                continue;
            }

            $this->message(_M('Database "@1"', 'miolo', $dbConf) . ":\n");

            $host = $this->MIOLO->getConf("db.$dbConf.host");
            $port = $this->MIOLO->getConf("db.$dbConf.port");
            $user = $this->MIOLO->getConf("db.$dbConf.user");
            $pwd = $this->MIOLO->getConf("db.$dbConf.password");
            $database = $this->MIOLO->getConf("db.$dbConf.name");
            $tmpDatabase = "{$database}_temp" . rand();

            $updateFile = "$this->temporaryDir/$dbUpdate->file";
            $databaseBackup = "{$database}_backup";
            $oldDatabaseBackup = "{$databaseBackup}_old";

            if ( $dbUpdate->file && file_exists($updateFile) )
            {
                // Create a temporary database
                $this->message("\t" . _M('Creating temporary database...'));
                $this->execute("export PGPASSWORD=\"$pwd\"; createdb -U $user -h $host -p $port -T $database $tmpDatabase");
                $this->message("\t\t\t" . _M('OK') . "\n");


                // Execute the SQL file in the temporary database
                $this->message("\t" . _M('Checking update script...'));
                $cmd = "psql -U $user -h $host -p $port $tmpDatabase -v ON_ERROR_STOP=1 -f $updateFile; echo \$?";
                list($result, $output, $return) = $this->execute($cmd, '', false);

                // If unsuccessful, die
                if ( $result != '0' )
                {
                    $this->error(_M('There\'s a problem with the database update script "@1".', 'miolo', $dbUpdate->file));
                }
                $this->message("\t\t" . _M('OK') . "\n");


                // Remove the temporary database
                $this->message("\t" . _M('Removing temporary database...'));
                $this->execute("export PGPASSWORD=\"$pwd\"; dropdb -U $user -h $host -p $port $tmpDatabase");
                $this->message("\t\t\t" . _M('OK') . "\n");
            }


            // Remove the old backup
            $this->message("\t" . _M('Removing old database backup...'));
            $this->execute("export PGPASSWORD=\"$pwd\"; dropdb -U $user -h $host -p $port $oldDatabaseBackup", '', false);
            $this->message("\t" . _M('OK') . "\n");


            // Rename the last backup
            $this->message("\t" . _M('Renaming last backup...'));
            $sql = "ALTER DATABASE $databaseBackup RENAME TO $oldDatabaseBackup";
            $this->execute("export PGPASSWORD=\"$pwd\"; psql -U $user -h $host -p $port $database -c \"$sql\"", '', false);
            $this->message("\t\t\t" . _M('OK') . "\n");


            // Create a backup of the current database
            $this->message("\t" . _M('Creating current backup...'));
            $this->execute("export PGPASSWORD=\"$pwd\"; createdb -U $user -h $host -p $port -T $database $databaseBackup");
            $this->databaseBackups[$dbConf] = $databaseBackup;
            $this->message("\t\t\t\t" . _M('OK') . "\n");


            if ( $dbUpdate->file && file_exists($updateFile) )
            {
                // Apply the update to the official database
                $this->message("\t" . _M('Applying the update script...') . "\n");
                $cmd = "psql -U $user -h $host -p $port $database -v ON_ERROR_STOP=1 -f $updateFile; echo \$?";
                list($result, $output, $return) = $this->execute($cmd, '', false);

                // If unsuccessful, die
                if ( $result != '0' )
                {
                    $this->error(_M('There\'s a problem with the database update script "@1".', 'miolo', $dbUpdate->file));
                }

                $this->message("\t" . implode("\n\t", $output) . "\n");
                $this->message("\t\t\t\t\t\t\t" . _M('OK') . "\n");
            }

            $this->message("\n");
        }


        // Execute shell post script
        if ( file_exists($this->temporaryDir . '/' . self::POST_SCRIPT . '.sh') )
        {
            $this->message(_M('Executing shell post script...'));
            list($result, $output, $return) = $this->execute('bash ' . $this->temporaryDir . '/' . self::POST_SCRIPT . '.sh');
            $this->message("\t\t\t" . _M('OK') . "\n");
            $this->message("\t" . implode("\n\t", $output) . "\n");
        }

        // Execute PHP post script
        if ( file_exists($this->temporaryDir . '/' . self::POST_SCRIPT . '.php') )
        {
            $this->message(_M('Executing PHP post script...'));
            list($result, $output, $return) = $this->execute('php ' . $this->temporaryDir . '/' . self::POST_SCRIPT . '.php');
            $this->message("\t\t\t" . _M('OK') . "\n");
            $this->message("\t" . implode("\n\t", $output) . "\n");
        }


        // Change update file
        $lastUpdate = $this->getCurrentVersion();
        $currentUpdate = $this->updateCurrentVersion($lastUpdate + 1);


        $this->message("\n" . _M('Your installation has been updated to version @1', 'miolo', $this->updateData->version) . "\n");

        $this->succeeded = true;
    }

    /**
     * Remove all files from a directory.
     */
    private function emptyDirectory($directory)
    {
        foreach ( scandir($directory) as $file )
        {
            $path = "$directory/$file";

            if ( !in_array($file, array('.', '..', '.svn')) && !is_dir($path) )
            {
                unlink($path);
            }
        }
    }

    /**
     * Extract the given zip file.
     *
     * @param string $source Full zip file path.
     * @param string $target Full path where the file must be extracted.
     */
    private function extractZip($source, $target)
    {
        $this->message(_M('Extracting the patch to "@1"...', 'miolo', $target) . "\t");
        
        if ( !file_exists($source) )
        {
            $this->error(_M('File "@1" does not exist', 'miolo', $source));
        }

        if ( !file_exists($target) )
        {
            $this->error(_M('Directory "@1" does not exist', 'miolo', $target));
        }

        // Open and extract the zip file
        $zip = new ZipArchive();

        if ( $zip->open($source) !== TRUE )
        {
            $this->error(_M('Zip file "@1" couldn\'t be opened', 'miolo', $source));
        }

        $result = $zip->extractTo($target);

        if ( $result )
        {
            $this->message(_M('OK') . "\n");

            for ( $i = 0; $i < $zip->numFiles; $i++ )
            {
                $fileData = $zip->statIndex($i);
                $this->message("\t" . $fileData['name'] . "\n");
            }
        }
        else
        {
            $zip->close();
            $this->error("\t" . _M('Couldn\'t extract zip patch.'));
        }

        $zip->close();

        return $result;
    }

    /**
     * Extract the given tarball file (tar.gz only).
     *
     * @param string $source Full tarball file path.
     * @param string $target Full path where the file must be extracted.
     */
    private function extract($source, $target, $verbose=true)
    {
        if ( !file_exists($source) )
        {
            $this->error(_M('File "@1" does not exist', 'miolo', $source));
        }

        if ( !file_exists($target) )
        {
            $this->error(_M('Directory "@1" does not exist', 'miolo', $target));
        }

        $v = $verbose ? 'v' : '';
        $cmd = "tar x{$v}zf $source -C $target";

        return $this->execute($cmd);
    }

    /**
     * Create a backup of the given directory.
     *
     * @param string $dir Directory path to backup.
     */
    private function createBackup($dir)
    {
        if ( !file_exists($this->backupDir) )
        {
            mkdir($this->backupDir);
        }
        else
        {
            // FIXME: Should it clean the backup dir?
            //$this->emptyDirectory($backupDir);
        }

        $backupName = 'backup_' . date('Y-m-d_His');
        $cmd = "tar -cf $this->backupDir/$backupName.tar --exclude=$this->backupDir --exclude=$dir/var/log --exclude=$dir/package $dir";
        $this->execute($cmd);

        $this->backupFile = "$this->backupDir/$backupName.tar";
        $this->backedUpDir = $dir;
    }

    /**
     * Restore the installation backup if it has been made.
     */
    private function restoreBackup()
    {
        if ( $this->backupFile && !file_exists($this->backupFile) )
        {
            $this->error(_M('Backup file "@1" does not exist', 'miolo', $this->backupFile));
        }
        else
        {
            $cmd = "tar -xf $this->backupFile -C /";
            $this->execute($cmd);
        }
    }

    /**
     * Restore the database backups.
     */
    private function restoreDatabaseBackups()
    {
        foreach ( $this->databaseBackups as $dbConf => $databaseBackup )
        {
            $dbConf = $dbUpdate->dbconf;
            $system = $this->MIOLO->getConf("db.$dbConf.system");

            if ( $system != 'postgres' )
            {
                continue;
            }

            $this->message("\t" . _M('Restoring database "@1"...', 'miolo', $database));

            $host = $this->MIOLO->getConf("db.$dbConf.host");
            $port = $this->MIOLO->getConf("db.$dbConf.port");
            $user = $this->MIOLO->getConf("db.$dbConf.user");
            $pwd = $this->MIOLO->getConf("db.$dbConf.password");
            $database = $this->MIOLO->getConf("db.$dbConf.name");

            // Remove the database created by the updater
            $this->execute("export PGPASSWORD=\"$pwd\"; dropdb -U $user -h $host -p $port $database");

            // Restore the backup
            $this->execute("export PGPASSWORD=\"$pwd\"; createdb -U $user -h $host -p $port -T $databaseBackup $database");

            $this->message("\t" . _M('OK') . "\n");
        }
    }

    /**
     * Get the current version of update file defined by CURRENT_UPDATE_VERSION_FILE constant.
     *
     * @return integer Current version.
     */
    public function getCurrentVersion()
    {
        $currentUpdateVersionFile = $this->MIOLO->getConf('home.miolo') . '/' . self::CURRENT_UPDATE_VERSION_FILE;

        if ( file_exists($currentUpdateVersionFile) )
        {
            $version = (int) file_get_contents($currentUpdateVersionFile);
        }
        else
        {
            $version = 0;
        }

        return $version;
    }

    /**
     * Create or update the file defined by CURRENT_UPDATE_VERSION_FILE constant.
     *
     * @param integer $version New version.
     * @return integer New version.
     */
    public function updateCurrentVersion($version)
    {
        $currentUpdateVersionFile = $this->MIOLO->getConf('home.miolo') . '/' . self::CURRENT_UPDATE_VERSION_FILE;

        file_put_contents($currentUpdateVersionFile, $version);

        return $version;
    }

    /**
     * Cleanup stuff done by the object.
     */
    public function __destruct()
    {
        if ( $this->succeeded )
        {
            echo _M('Thanks for using MIOLOUpdater.') . "\n\n";
        }
        else
        {
            // Restore system backup
            if ( $this->backedUpDir && $this->backupFile )
            {
                $this->message(_M('Restoring system backup...'));
                $this->restoreBackup();
                $this->message("\t" . _M('OK') . "\n\n");
            }

            // Restore database backups
            if ( count($this->databaseBackups) > 0 )
            {
                $this->message(_M('Restoring database backups...'));
                $this->restoreDatabaseBackups();
                $this->message("\t" . _M('OK') . "\n\n");
            }

            $this->message(_M('Couldn\'t update your system.') . "\n");
            $this->message(_M('Quitting updater.') . "\n\n");
        }

        // Close log file
        if ( $this->logFile )
        {
            fwrite($this->logFile, "\n");
            fclose($this->logFile);
        }

        // Restore miolo.conf backup
        $MIOLO_PATH = $this->MIOLO->getConf('home.miolo');
        $confBackup = "$this->backupDir/miolo.conf";
        if ( file_exists($confBackup) )
        {
            $cmd = "cp $confBackup $MIOLO_PATH/etc/miolo.conf";
            exec($cmd, $output, $return);

            if ( $return !== 0 )
            {
                $this->error(_M('Could not restore miolo.conf backup. Backup file in "@1".', NULL, $confBackup));
            }
        }

        // Remove "in maintenance" flag
        unlink("$MIOLO_PATH/.down");
    }

    /**
     * Override message method for logging purpose.
     *
     * @param string $message Message to be printed.
     */
    protected function message($message)
    {
        // Log the message
        $this->log("[INFO] $message");
        parent::message($message);
    }

    /**
     * Override error method for logging purpose.
     *
     * @param string $message Error message.
     */
    protected function error($message)
    {
        // Log the message
        $this->log("[ERROR] $message");
        parent::error($message);
    }

    /**
     * Store the message on log file.
     *
     * @param string $message Message to log.
     */
    private function log($message)
    {
        $prefix = '[' . date('d/m/Y H:i:s') . ']';
        fwrite($this->logFile, "\n$prefix $message");
    }
}

?>