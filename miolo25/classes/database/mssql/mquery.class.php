<?php

class MSSQLQuery extends MQuery
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
        $this->id_result = mssql_query($this->sql,$this->conn->id);
        $this->error = $this->_error();

        if (!$this->error)
        {
            if ($this->rowCount = mssql_num_rows($this->id_result))
            {
                for ($n = 0; $n < $this->rowCount; $this->result[$n] = mssql_fetch_array($this->id_result, MSSQL_NUM),$n++);
                $this->fetched = true;
            }

            $this->colCount = mssql_num_fields($this->id_result);
        }

        return (!$this->error);
    }

    public function _error()
    {
        $error = mssql_get_last_message();
        $error = NULL;
        return $error;
    }

    public function _close()
    {
        if ($this->id_result)
            mssql_free_result($this->id_result);
    }

    public function _setmetadata()
    {
        $numCols = $this->colCount;
        $this->metadata = array
            (
            );

        for ($i = 0; $i < $numCols; $i++)
        {
            $name = strtoupper(@mssql_field_name($this->id_result, $i));
            $this->metadata['fieldname'][$i] = $name;
            $this->metadata['fieldtype'][$name] = $this->_getmetatype(@mssql_field_type($this->id_result, $i));
            $this->metadata['fieldlength'][$name] = @mssql_field_length($this->id_result, $i);
            $this->metadata['fieldpos'][$name] = $i;
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
        elseif ($type == "DATE")
        {
            $rType = 'T';
        }

        return $rType;
    }
}

?>