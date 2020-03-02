<?php

class Oracle8Connection extends MConnection
{
    public $executemode = OCI_COMMIT_ON_SUCCESS;

    public function __construct($conf)
    {
        parent::__construct($conf);
    }

    public function _connect($dbhost, $loginDB, $loginUID, $loginPWD, $persistent=TRUE, $parameters=NULL)
    {
        if ($persistent)
        {
            $this->id = OCIPLogon($loginUID, $loginPWD, $loginDB);
        }
        else
        {
            $this->id = OCILogon($loginUID, $loginPWD, $loginDB);
        }
    }

    public function _close()
    {
        OCILogOff ($this->id);
    }

    public function _error($resource = null)
    {
        $err = oci_error($resource ? $resource : $this->id);
        return ($err ? $err['message'] : false);
    }

    public function _parse($sql)
    {
        $sql = $this->_escape($sql);
        $statement = oci_parse($this->id, $sql);
        return $statement;
    }

    public function _bind($stmt, $ph, $pv)
    {
		if (is_array($pv))
		{
            ocibindbyname($stmt, $ph, $pv[0],$pv[1],$pv[2]);
		}
		else
		{
            ocibindbyname($stmt, $ph, $pv);
		}
    }

    public function _escape($sql)
    {
        $sql = str_replace("\'","''",$sql);
        $sql = str_replace('\"','"',$sql);
        return $sql;
    }

    public function _execute($sql)
    {
        if (is_object($sql))
        {
            if ($success = oci_execute($sql->stmt, $this->executemode))
            {
                $this->affectedrows = oci_num_rows($statement);
                if (!$sql->bind)
                {
                    oci_free_statement ($statement);
                }
            }
        }
        else
        {
            $sql = $this->_escape($sql);
            $statement = oci_parse($this->id, $sql);
            if ($success = oci_execute($statement, $this->executemode))
            {
                $this->affectedrows = oci_num_rows($statement);
                oci_free_statement ($statement);
            }
        }
        if (!$success)
        {
            $this->traceback[] = $this->_error($statement);
        }
        return $success;
    }

    /**
     * @return Oracle8Query Database query object.
     */
    public function _createquery()
    {
        return new Oracle8Query();
    }

    public function _chartotimestamp($timestamp, $format='DD/MM/YYYY HH24:MI:SS')
    {
        return ":TO_DATE('" . $timestamp . "','$format') ";
    }

    public function _chartodate($date, $format='DD/MM/YYYY')
    {
        return ":TO_DATE('" . $date . "','$format') ";
    }

    public function _timestamptochar($timestamp, $format='DD/MM/YYYY HH24:MI:SS')
    {
        return "TO_CHAR($timestamp,'$format') ";
    }

    public function _datetochar($date, $format='DD/MM/YYYY')
    {
        return "TO_CHAR($date,'$format') ";
    }
}
?>
