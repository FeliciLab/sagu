<?php

class FirebirdQuery extends MQuery
{
    public $id_result;

    public function __construct()
    {
        parent::__construct();
    }

    public function _query()
    {
        $this->fetched = false;
        $str = 'SELECT ';

        if ($this->maxrows > 0)
            $str .= "FIRST $this->maxrows ";

        $str .= ($this->offset > 0) ? "SKIP $this->offset " : '';
        $this->sql = preg_replace('/^[ \t]*select/i', $str, $this->sql);
        $this->sql = str_replace('password', "\"PASSWORD\"", $this->sql);
        $this->id_result = ibase_query($this->conn->id, $this->sql, $sql->parameters);
        $this->error = $this->_error();

        if (!$this->error)
        {
            $this->rowCount = 0;

            while ($results = ibase_fetch_row($this->id_result))
            {
                $this->result[$this->rowCount++] = $results;
            }

            $this->fetched = true;
            $this->colCount = ibase_num_fields($this->id_result);
        }

        return (!$this->error);
    }

    public function _querystmt($stmt)
    {
        $this->_query();
    }

    public function _error()
    {
        $error = ibase_errmsg();
        return $error;
    }

    public function _close()
    {
        if ($this->id_result)
            ibase_free_result ($this->id_result);
    }

    public function _setmetadata()
    {
        $numCols = $this->colCount;
        $this->metadata = array(
            );

        for ($i = 0; $i < $numCols; $i++)
        {
            $col_info = ibase_field_info($this->id_result, $i);
            $alias = strtoupper($col_info['alias']);
            $name = ($alias != '') ? $alias : strtoupper($col_info['name']);
            $this->metadata['fieldname'][$i] = $name;
            $this->metadata['fieldtype'][$name] = $this->_getmetatype($col_info['type']);
            $this->metadata['fieldlength'][$name] = $col_info['length'];
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