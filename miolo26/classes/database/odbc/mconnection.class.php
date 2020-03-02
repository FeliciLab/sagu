<?php

class ODBCConnection extends MConnection
{
    public function __construct($conf)
    {
        parent::__construct($conf);
    }

    public function _connect($dbhost, $loginDB, $loginUID, $loginPWD, $persistent=TRUE, $parameters=NULL)
    {
        if ($persistent)
        {
            $this->id = odbc_pconnect($loginDB, $loginUID, $loginPWD);
        }
        else
        {
            $this->id = odbc_connect($loginDB, $loginUID, $loginPWD);
        }
    }

    public function _close()
    {
        odbc_close($this->id);
    }

    public function _error($resource = null)
    {
        return (($error = odbc_error($this->id)) ? odbc_errormsg($this->id) : false);
    }

    public function _execute($sql)
    {
        if ($statement = odbc_prepare($this->id, $sql))
        {
            if ($success = odbc_execute($statement))
            {
                $this->affectedrows = odbc_num_rows($statement);
                odbc_free_result($statement);
            }
            else
            {
                $this->traceback[] = $this->_error($statement);
            }
        }
        else
        {
            $this->traceback[] = $this->_error();
        }

        return $success;
    }

    /**
     * @return ODBCQuery Database query object.
     */
    public function _createquery()
    {
        return new ODBCQuery();
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