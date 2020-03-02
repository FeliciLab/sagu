<?php

class ODBCQuery extends MQuery
{
    public $id_result;

    public function __construct()
    {
        parent::__construct();
    }

    public function _query()
    {
        $this->fetched = false;
        $this->sql = $this->maxrows ? $this->sql . " LIMIT $this->maxrows" : $this->sql;
        $this->sql = $this->offset ? $this->sql . " OFFSET $this->offset" : $this->sql;
        $this->id_result = odbc_exec($this->conn->id, $this->sql);
        $this->error = $this->_error();

        if (!$this->error)
        {
            $n = 0;
            while (odbc_fetch_into($this->id_result,$a)) $this->result[$n++] = $a;
            $this->rowCount = $n;
            $this->fetched = true;
            $this->colCount = odbc_num_fields($this->id_result);
        }
        else
        {
            throw new EDatabaseQueryException($this->error);
        }
        return (!$this->error);
    }

    public function _error()
    {
        return (($error = odbc_error($this->conn->id)) ? odbc_errormsg($this->conn->id) : false);
    }

    public function _close()
    {
        if ($this->id_result)
        {
            odbc_free_result($this->id_result);
            unset ($this->id_result);
        }
    }

    public function _setmetadata()
    {
        $numCols = $this->colCount;
        $this->metadata = array();
        for ($i = 1; $i <= $numCols; $i++)
        {
            $name = strtoupper(odbc_field_name($this->id_result, $i));
            $name = ($p = strpos($name, '.')) ? substr($name, $p + 1) : $name;
            $this->metadata['fieldname'][$i - 1] = $name;
            $this->metadata['fieldtype'][$name] = $this->_getmetatype(strtoupper(odbc_field_name($this->id_result, $i)));
            $this->metadata['fieldlength'][$name] = odbc_field_len($this->id_result, $i);
            $this->metadata['fieldpos'][$name] = $i - 1;
        }
    }

    public function _getmetatype($type)
    {
        $type = strtoupper($type);
        $rType = 'N';

        if ($type == "VARCHAR")
        {
            $rType = 'C';
        }
        elseif ($type == "CHAR")
        {
            $rType = 'C';
        }
        elseif ($type == "NUMBER")
        {
            $rType = 'N';
        }
        elseif ($type == "INTEGER")
        {
            $rType = 'N';
        }
        elseif ($type == "DATE")
        {
            $rType = 'T';
        }
        elseif ($type == "TIMESTAMP")
        {
            $rType = 'T';
        }

        return $rType;
    }
}

?>