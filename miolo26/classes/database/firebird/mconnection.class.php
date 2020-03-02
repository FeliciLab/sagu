<?php

class FirebirdConnection extends MConnection
{
    public function __construct($conf)
    {
        parent::__construct($conf);
    }

    public function _connect($dbhost, $loginDB, $loginUID, $loginPWD, $persistent=TRUE, $parameters=NULL)
    {
        $buffers = (isset($parameters['buffers'])) ? ($parameters['buffers']) : (null);
        $characterset = (isset($parameters['characterset'])) ? ($parameters['characterset']) : (null);
        $dialect = (isset($parameters['dialect'])) ? ($parameters['dialect']) : (null);
        $role = (isset($parameters['role'])) ? ($parameters['role']) : (null);

        if (false && $persistent)
        {
            $this->id = ibase_pconnect($loginDB, $loginUID, $loginPWD, $characterset, $buffers, $dialect, $role);
        }
        else
        {
            $this->id = ibase_connect($loginDB, $loginUID, $loginPWD, $characterset, $buffers, $dialect, $role);
        }
    }

    public function _close()
    {
        ibase_close ($this->id);
    }

    public function _error($resource = null)
    {
        return ibase_errmsg();
    }

    public function _parse(&$sql)
    {
        $sql = preg_replace("/:((.+) /", "\?", $sql);
        $statement = ibase_prepare($this->id, $sql);
        return $statement;
    }

    public function _bind($stmt, $ph, $pv)
    {
    }

    public function _execute($sql)
    {
        $prepared = ibase_prepare($this->id, $sql);

        if ($prepared)
        {
            $rs = ibase_execute($prepared);
            $success = false;

            if ($rs)
            {
                $success = true;
                $this->affectedrows = ibase_affected_rows($this->id);
                ibase_free_result ($rs);
            }
            else
            {
                $this->traceback[] = $this->_error($this->id);
            }
        }
        else
        {
            $this->traceback[] = $this->_error($this->id);
        }

        return $success;
    }

    /**
     * @return FirebirdQuery Database query object.
     */
    public function _createquery()
    {
        return new FirebirdQuery();
    }

    public function _chartotimestamp($timestamp)
    {
        return "'" . substr($timestamp, 7, 4) . '/' . substr($timestamp, 4, 2) . '/' . substr($timestamp, 1, 2) . ' ' . substr($timestamp, 11);
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
