<?php

class SQLiteConnection extends MConnection
{
    public function __construct($conf)
    {
        parent::__construct($conf);
    }

    public function _connect($dbhost, $loginDB, $loginUID, $loginPWD, $persistent=TRUE, $parameters=NULL)
    {
        if (false && $persistent)
        {
            $this->id = sqlite_open($loginDB);
        }
        else
        {
            $this->id = sqlite_open($loginDB);
        }
    }

    public function _close()
    {
        sqlite_close ($this->id);
    }

    public function _error($resource = null)
    {
        return (($error = sqlite_last_error($this->id)) ? sqlite_error_string($error) : false);
    }

    public function _execute($sql)
    {
        $success = @sqlite_exec($this->id, $sql);

        if ($success)
        {
            $this->affectedrows = sqlite_changes($this->id);
            unset ($rs);
        }
        else
        {
            $this->traceback[] = $this->_error($this->id);
        }

        return $success;
    }

    /**
     * @return SQLiteQuery Database query object.
     */
    public function _createquery()
    {
        return new SQLiteQuery();
    }

    public function _chartotimestamp($timestamp)
    {
        return $timestamp;
    }

    public function _chartodate($date)
    {
        return $date;
    }

    public function _timestamptochar($timestamp)
    {
        return $timestamp;
    }

    public function _datetochar($date)
    {
        return $date;
    }
}
?>
