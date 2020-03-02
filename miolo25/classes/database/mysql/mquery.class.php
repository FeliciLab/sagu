<?php

class MysqlQuery extends MQuery
{
    public $id_result;

    public function __construct()
    {
        parent::__construct();
    }

    public function _query()
    {
        $this->fetched = true;
        $this->id_result = mysql_query($this->sql);

        if ($this->nrows = mysql_num_rows($this->id_result))
        {
            $this->result = Array
                (
                );

            $row = $this->offset ? $this->offset : 0;
            $mrows = $this->maxrows ? (($this->maxrows < $this->nrows) ? $this->maxrows : $this->nrows) : $this->nrows;
            $n = 0;

            while ( ($n < $mrows) && (mysql_data_seek($this->id_result, $row++)) )
            {
                $this->result[$n++] = mysql_fetch_row($this->id_result);
            }

            $this->nrows = $this->rowCount = $n;
        }
    }

    public function _error()
    {
        return mysql_error();
        ;
    }

    public function _close()
    {
        if ($this->id_result != null)
        {
            @mysql_free_result($this->id_result);
            $this->id_result = null;
        }
    }

    public function _setmetadata()
    {
        $numCols = mysql_num_fields($this->id_result);
        $this->columnsNo = Array
            (
            );

        for ($i = 0; $i < $numCols; $i++)
            $this->columnsNo[strtoupper(mysql_field_name($this->id_result, $i))] = $i;

        $this->ncols = $numCols = mysql_num_fields($this->id_result);
        $this->metadata = Array
            (
            );

        for ($i = 0; $i < $numCols; $i++)
        {
            $name = strtoupper(mysql_field_name($this->id_result, $i));
            $this->metadata['fieldname'][$i] = $name;
            $this->metadata['fieldtype'][$name] = $this->_getmetatype(mysql_field_type($this->id_result, $i));
            $this->metadata['fieldlength'][$name] = mysql_field_len($this->id_result, $i);
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
        elseif ($type == "STRING")
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