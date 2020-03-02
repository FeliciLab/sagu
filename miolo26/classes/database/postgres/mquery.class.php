<?php

class PostgresQuery extends MQuery
{
    public $id_result;

    /**
     * @var array It has all the results from a query call with many queries.
     */
    public $results = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function _query()
    {
        $this->fetched = false;
        $this->sql = $this->maxrows ? $this->sql . " LIMIT $this->maxrows" : $this->sql;
        $this->sql = $this->offset ? $this->sql . " OFFSET $this->offset" : $this->sql;

        pg_send_query($this->conn->id, $this->sql);

        $this->results = array();

        while ( $result = pg_get_result($this->conn->id) )
        {
            $this->results[] = $result;
        }

        // store the last query result on id_result attribute
        $this->id_result = end($this->results);

        $this->conn->throwError($this->id_result);
        $this->error = $this->_error();

        if (!$this->error)
        {
            if ($this->rowCount = pg_num_rows($this->id_result))
            {
                switch ( $this->fetchType )
                {
                    case self::FETCH_OBJ:
                        for ( $n = 0; $n < $this->rowCount; $this->result[$n] = pg_fetch_object($this->id_result, $n), $n++ );
                        break;

                    case self::FETCH_ASSOC:
                        for ( $n = 0; $n < $this->rowCount; $this->result[$n] = pg_fetch_array($this->id_result, $n, PGSQL_ASSOC), $n++ );
                        break;

                    case self::FETCH_BOTH:
                        for ( $n = 0; $n < $this->rowCount; $this->result[$n] = pg_fetch_array($this->id_result, $n), $n++ );
                        break;

                    case self::FETCH_NUM:
                    default:
                        for ( $n = 0; $n < $this->rowCount; $this->result[$n] = pg_fetch_array($this->id_result, $n, PGSQL_NUM), $n++ );
                }

                $this->fetched = true;
            }

            $this->colCount = pg_num_fields($this->id_result);
        }

        return (!$this->error);
    }

    /**
     * @return string Error message.
     */
    public function _error()
    {
        return $this->conn->getErrorField($this->id_result);
    }

    public function _close()
    {
        if ($this->id_result)
            pg_free_result($this->id_result);
    }

    public function _setmetadata()
    {
        $numCols = $this->colCount;
        $this->metadata = array
            (
            );

        for ($i = 0; $i < $numCols; $i++)
        {
            $name = strtoupper(@pg_field_name($this->id_result, $i));
            $this->metadata['fieldname'][$i] = $name;
            $this->metadata['fieldtype'][$name] = $this->_getmetatype(@pg_field_type($this->id_result, $i));
            $this->metadata['fieldlength'][$name] = @pg_field_size($this->id_result, $i);
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