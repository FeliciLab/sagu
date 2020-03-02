<?php

abstract class MConnection
{
    /**
     * @var MDatabase Database object.
     */
    public $db;

    /**
     * @var string Connection identifier.
     */
    public $id;

    /**
     * @var array A list of connection errors.
     */
    public $traceback = array();

    /**
     * @var integer Number of affected rows after an SQL instruction.
     */
    public $affectedrows;

    /**
     * @var MIOLO Miolo instance.
     */
    public $_miolo;

    /**
     * MConnection constructor.
     *
     * @param MDatabase $db Database object.
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->_miolo = $this->db->_miolo;
        $this->_miolo->uses('database/' . $db->system . '/mquery.class.php');
    }

    abstract public function _connect($dbhost, $loginDB, $loginUID, $loginPWD, $persistent=TRUE, $parameters=NULL);

    abstract public function _close();

    abstract public function _error();

    public function _escape($sql)
    {
        return $sql;
    }

    abstract public function _execute($sql);

    /**
     * @return MQuery Query instance. 
     */
    abstract public function _createquery();

    /**
     * @param $timestamp string Timestamp to convert.
     * @return string SQL instruction.
     */
    abstract public function _chartotimestamp($timestamp);

    /**
     * @param $date string Date to convert.
     * @return string SQL instruction.
     */
    abstract public function _chartodate($date);

    /**
     * @param $timestamp string Timestamp to convert.
     * @return string SQL instruction.
     */
    abstract public function _timestamptochar($timestamp);

    /**
     * @param $date string Date to convert.
     * @return string SQL instruction.
     */
    abstract public function _datetochar($date);

    /**
     * Open a connection to the specified data source.
     *
     * @param string $dbhost Database host.
     * @param string $loginDB Database login.
     * @param string $loginUID Database user.
     * @param string $loginPWD Database user password.
     * @param boolean $persistent Whether the connection must be persistent.
     * @param array $parameters Parameters.
     * @param integer $port Port number.
     * @return string Connection identifier.
     */
    public function open($dbhost, $loginDB, $loginUID, $loginPWD, $persistent=TRUE, $parameters=NULL, $port=NULL)
    {
        if ($this->id)
        {
            $this->close();
        }
        $this->_connect($dbhost, $loginDB, $loginUID, $loginPWD, $persistent, $parameters, $port);
        if (!$this->id)
        {
            $this->traceback[] = _M("Unable to estabilish database connection to host:") ." $dbhost, DB: $loginDB, Type: {$this->db->system}";
        }
        return $this->id;
    }

    public function close()
    {
        if ($this->id)
        {
            $this->_close($this->id);
            $this->id = 0;
        }
    }

    public function getError()
    {
        if (!$this->id)
        {
            $err = _M("No valid Database connection estabilished.");
        }
        elseif ($this->traceback)
        {
            $err .= "<br>" . implode("<br>", $this->traceback);
        }
        return $err;
    }

    public function getErrors()
    {
        return $this->traceback;
    }

    public function getErrorCount()
    {
        return count($this->traceback);
    }

    public function checkError()
    {
        if (empty($this->traceback))
        {
            return;
        }
        $n = count($this->traceback);
        if ($n > 0)
        {
            $msg = "";
            for ($i = 0; $i < $n; $i++)
            {
                $msg .= $this->traceback[$i] . "<br>";
            }
        }
        if ($msg != '')
        {
            throw new EDatabaseException($this->db->conf, $msg);
        }
    }

    public function execute($sql)
    {
        if ($sql == "") return;

        $this->_miolo->logSQL($sql, false, $this->db->conf);

        if (!($success = $this->_execute($sql)))
        {
            throw new EDatabaseExecException($this->getError());
        }

        return $success;
    }

    public function parse($sql)
    {
        $this->_miolo->logSQL(_M('Parse:') . $sql->command, false, $this->db->conf);
        $sql->stmt = $this->_parse($sql->command);
    }

    public function bind($sql, $parameters)
    {
        if ($parameters)
        {
            foreach ($parameters as $ph => $pv)
            {
                $this->_bind($sql->stmt, $ph, $pv);
            }
        }
    }

    public function getQuery($sql, $maxrows=NULL, $offset=NULL, $fetchType=NULL)
    {
        $this->_miolo->assert($this->id, $this->getErrors());
        try
        {
            $query = $this->_createquery();
            $query->setConnection($this);
            $query->setSQL($sql);
            $query->setFetchType($fetchType);
            if ($sql->bind)
            {
                if (!$sql->stmt)
                {
                    $this->parse($sql);
                }

                $this->bind($sql);
            }
            $query->open($maxrows, $offset, $sql->stmt);
        }
        catch( Exception $e )
        {
            throw $e;
        }

        return $query;
    }

    public function getQueryCommand($sqlCommand, $maxrows=NULL, $offset=NULL, $fetchType=NULL)
    {
        $this->_miolo->assert($this->id, $this->getErrors());
        $query = $this->_createquery();
        $query->setConnection($this);
        $query->setSQLCommand($sqlCommand);
        $query->setFetchType($fetchType);
        $query->open($maxrows, $offset);
        return $query;
    }
}
?>