<?php

class MSSQLConnection extends MConnection
{
    public function __construct($conf)
    {
        parent::__construct($conf);
    }

    public function _connect($dbhost, $loginDB, $loginUID, $loginPWD, $persistent=TRUE, $parameters=NULL)
    {
        if (false && $persistent)
        {
            $this->id = mssql_pconnect($dbhost,$loginUID,$loginPWD);
            @mssql_select_db($loginDB, $this->id);
        }
        else
        {
            $this->id = mssql_connect($dbhost,$loginUID,$loginPWD);
            @mssql_select_db($loginDB, $this->id);
        }
    }

    public function _close()
    {
        mssql_close($this->id);
    }

    public function _error($resource = null)
    {
        $msg = mssql_get_last_message();
        return $msg;
    }

    public function _execute($sql)
    {
        $rs = mssql_query($sql,$this->id);
        if ($rs)
        { 
            $success = true;
            $this->affectedrows = mssql_rows_affected($this->id);
        }
        else
        {
            $success = false;
            $this->traceback[] = $this->_error();
        }

        return $success;
    }

    /**
     * @return MSSQLQuery Database query object.
     */
    public function _createquery()
    {
        return new MSSQLQuery();
    }

    public function _chartotimestamp($timestamp,  $format='DD/MM/YYYY HH24:MI:SS')
    {

		return $timestamp;

    }

    public function _chartodate($date, $format='DD/MM/YYYY')
    {
		
		return $date;

    }

    public function _timestamptochar($timestamp,  $format='DD/MM/YYYY HH24:MI:SS')
    {

        return "convert(varchar," . $timestamp . ",131) ";
    }

    public function _datetochar($date,  $format='DD/MM/YYYY')
    {

        return "convert(varchar," . $date . ",103) ";
    }
}
?>
