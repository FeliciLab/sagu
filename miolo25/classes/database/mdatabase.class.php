<?php
class MDatabase
{
    public $conf;       // identifies db configuration in miolo.conf
    public $system;     // what driver?
    public $host;       // host configuration
    public $port;       // port configuration
    public $db;         // db identifier in miolo.conf
    public $user;       // user in miolo.conf
    public $pass;       // password in miolo.conf

    /**
     * @var MConnection Connection handler.
     */
    public $conn;

    public $persistent; // persistent conection?
    public $parameters; // parameters for connection
    public $status;     // 'open' or 'close'
    public $_miolo;     // MIOLO object

    public function __construct($conf, $system, $host, $db, $user, $pass, $persistent, $parameters=NULL, $server=false, $jdbc_driver=NULL, $jdbc_db=NULL)

    {
        $this->_miolo = MIOLO::getInstance();
 
        $this->_miolo->assert($system, "Missing DB system!");
        $this->_miolo->assert($host, "Missing DB host!");
        $this->_miolo->assert($db, "Missing DB name!");

        $this->_miolo->uses('database/mconnection.class.php');
        $this->_miolo->uses('database/mdataset.class.php');
        $this->_miolo->uses('database/mquery.class.php');
        $this->_miolo->uses('database/midgenerator.class.php');
        $this->_miolo->uses('database/mtransaction.class.php');
        $this->_miolo->uses('database/msqljoin.class.php');
        $this->_miolo->uses('database/mschema.class.php');

        $this->_miolo->uses('database/' . $system . '/mconnection.class.php');

        $this->conf       = $conf;
        $this->system     = $system;
        $this->host       = $host;
        $this->db         = $db;
        $this->user       = $user;
        $this->pass       = $pass;
        $this->jdbc_driver = $jdbc_driver;
        $this->jdbc_db = $jdbc_db;
        $this->persistent = $persistent;
        $this->parameters = $parameters;
        $this->status     = '';

        $className = "{$system}Connection";
        $this->conn = new $className($this);

        if ($server) { $this->openServer(); } else { $this->open(); };
   }

   public function openServer()
   {
        $this->conn->open($this->host, NULL, $this->user, $this->pass, $this->persistent, $this->parameters);

        if ($err = $this->getError())
        {
            throw new EDatabaseException($this->conf, $err);
        }
        $this->status = 'open';
    }

   public function open()
   {
        $this->conn->open($this->host, $this->db, $this->user, $this->pass, $this->persistent, $this->parameters);

        if ($err = $this->getError())
        {
            throw new EDatabaseException($this->conf, $err);
        }
        $this->status = 'open';
    }

    public function close()
    {
        $this->conn->close();
        $this->status = 'close';
    }

    public function getError()
    {
        $err = $this->getErrors();
        return $err ? $err[0] : false;
    }

    public function getErrors()
    {
        return $this->conn->getErrors();
    }

    public function getTransaction()
    {
        $this->_miolo->uses('database/' . $this->system . '/mtransaction.class.php');
        $className = "{$this->system}Transaction";
        $transaction = new $className($this->conn);
        return $transaction;
    }

    public function getISR()
    {
        $this->_miolo->uses('database/misr.class.php');
        $isr = new MISR($this->conn);
        return $isr;
    }

    public function parse($sql)
    {
        $this->conn->parse($sql);
    }

    public function bind($sql, $parameters)
    {
        $this->conn->bind($sql, $parameters);
    }

    public function execute($sql)
    {
        $this->_miolo->profileEnter('Database::execute');

        if (is_array($sql))
        {
            $ok = $this->executeBatch($sql);
        }
        else
        {
            try
            {
                $ok = @$this->conn->execute($sql);
            }
            catch ( MDatabaseException $e )
            {
                throw $e;
            }
            catch( Exception $e )
            {
                $err = trim($this->getError());
                $this->_miolo->logError($err . '; SQL:' . $sql, $this->conf);

                throw new EDatabaseException($this->conf, $err);
            }
        }

        $this->_miolo->profileExit('Database::execute');
        return $ok;
    }

    public function executeBatch($sql_array)
    {
        $transaction = $this->getTransaction();

        if (!is_array($sql_array))
            $sql_array = array($sql_array);

        foreach ($sql_array as $sql)
        {
            $transaction->addCommand($sql);
        }

        try
        {
            $ok = $transaction->process();
        }
        catch( Exception $e )
        {
            throw new EDatabaseException($this->conf, $e->getMessage());
        }

        return $ok;
    }

    public function count($sql)
    {
        $query = $this->queryChunk($sql, 0, 0, $total);
        return $total;
    }

    public function getNewId($sequence = 'admin', $tableGenerator = 'miolo_sequence')
    {
        $this->_miolo->uses('database/' . $this->system . '/midgenerator.class.php');
        $className = "{$this->system}IdGenerator";
        $idgenerator = new $className($this);
        try
        {
            $value = $idgenerator->getNewId($sequence, $tableGenerator);
        }
        catch( Exception $e )
        {
            throw new EDatabaseException('DB::getNewId: ' . trim($this->getError()), $this->conf);
        }

        return $value;
    }

    public function query($sql, $maxrows=0, $offset=NULL, $fetchType=NULL)
    {
        try
        {
            // $sql is a SQL command string
            $query = $this->conn->getQueryCommand($sql, $maxrows, $offset, $fetchType);
        }
        catch ( MDatabaseException $e )
        {
            throw $e;
        }

        return $query;
    }

    public function prepare($sql, $params) // backward compatibility
    {
        // $sql is a SQL command string
        $msql = new MSQL;
        $msql->createFrom($sql);   
        return $msql->prepare($params);
    }

    public function getQuery($sql, $maxrows=0, $fetchType=NULL)
    {
        if (isset($sql->range))
        {
            $query = $this->queryChunk($sql, $sql->range->rows, $sql->range->offset, $sql->range->total, $fetchType);
        }
        else
        {
            $query = $this->queryChunk($sql, $maxrows, 0, $total, $fetchType);
        }

        return $query;
    }

    public function objQuery(MSQL $sql, $maxrows = 0) // backward compatibility
    {
        return $this->getQuery($sql, $maxrows);
    }

    public function getQueryCommand($sqlCommand, $maxrows=NULL, $offset=NULL, $fetchType=NULL)
    {
        return $this->conn->getQueryCommand($sqlCommand, $maxrows, $offset, $fetchType);
    }

    public function getTable($tablename)
    {
        $sql = new sql("*", $tablename);
        $query = $this->getQuery($sql);
        return $query;
    }

    public function getTableInfo($tablename)
    {
        $this->_miolo->uses('database/' . $this->system . '/mschema.class.php');
        $className = "{$this->system}Schema";
        $schema = new $className($this->conn);
        return $schema->getTableInfo($tablename);
    }

    public function queryRange($sql, &$range) // backward compatibility
    {
        $oSql = new MSQL();
        $oSql->createFrom($sql);
        $query = $this->queryChunk($oSql, $range->rows, $range->offset, $range->total);
        return $query;
    }

    public function queryChunk($sql, $maxrows, $offset, &$total, $fetchType=NULL)
    {
        $this->_miolo->profileEnter('Database::queryChunk');
        $sql->setDb($this);
        try
        {
            $query = @$this->conn->getQuery($sql, $maxrows, $offset, $fetchType);
            $total = $query->getRowCount();

            if (!$sql->bind)
                $query->close();

            $this->_miolo->profileExit('Database::queryChunk');
            return $query;
        }
        catch( Exception $e )
        {
            $err = trim($this->getError());
            $this->_miolo->logError($err . '; SQL:' . $sql->command, $this->conf);

            throw new EDatabaseException($this->conf, $err . ';' . $e->getMessage());
        }
    }

    //
    // This function checks, if the last executed command caused a database
    // error. If this is the case, an exception is raised, informing the
    // reason(s).
    //
    public function assert($info = false)
    {
        $err = $this->conn->getErrors();
        if ($err)
        {
            throw new EDatabaseException($conf, $info . $err);
        }
    }

    public function getAffectedRows()
    {
        return $this->conn->affectedrows;
    }

    public function charToTimestamp($timestamp, $format='DD/MM/YYYY HH24:MI:SS')
    {
        return $this->conn->_chartotimestamp($timestamp, $format);
    }

    public function charToDate($date, $format='DD/MM/YYYY')
    {
        return $this->conn->_chartodate($date, $format);
    }

    public function timestampToChar($timestamp, $format='DD/MM/YYYY HH24:MI:SS')
    {
        return $this->conn->_timestamptochar($timestamp, $format);
    }

    public function dateToChar($date, $format='DD/MM/YYYY')
    {
        return $this->conn->_datetochar($date, $format);
    }

    public function LOBLoad($value)
    {
        $this->_miolo->Uses('database/' . $this->system . '/mlob.class');
        $className = "{$this->system}LOB";
        $lob = new $className($this->conn);
        return $lob->lobload($value);
    }

    public function LOBSave($value)
    {
        $this->_miolo->Uses('database/' . $this->system . '/mlob.class');
        $className = "{$this->system}LOB";
        $lob = new $className($this->conn);
        return $lob->lobsave($value);
    }

    public function handleLOB($object, $attribute, $value, $operation)

    {
        $this->_miolo->Uses('database/' . $this->system . '/mlob.class');
        $className = "{$this->system}LOB";
        $lob = new $className($this->conn);
        $lob->handle($object, $attribute, $value, $operation);
    }
}
?>