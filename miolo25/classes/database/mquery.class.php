<?php

abstract class MQuery extends MDataSet
{
    const FETCH_NUM = 'n';
    const FETCH_ASSOC = 'a';
    const FETCH_BOTH = 'b';
    const FETCH_OBJ = 'o';

    public $conn; // the connection object
    public $objSql; // the SQL object
    public $sql; // the SQL command string
    public $error; // the query's error message from the query execution
    public $statement; // a parsed sql command - used by some drivers
    public $fetched; // true for a valid result
    public $maxrows; // a max number of rows to be fetched
    public $offset; // a start point to fetch
    public $order; // fields names used to sort resultset
    public $filtered; // is filtered?
    public $pagelength; // pagelength for paging
    public $queryfilter; // object used to filter
    public $_miolo;     // MIOLO object

    /**
     * @var character Fetch type.
     */
    protected $fetchType = self::FETCH_NUM;

    public function __construct()
    {
        parent::__construct();
        $this->fetched = false;
        $this->row = -1;
        $this->error = NULL;
        $this->filtered = false;
        $this->pagelength = 0;
        $this->queryfilter = NULL;
        $this->_miolo = MIOLO::getInstance();
    }

    abstract public function _query();

    abstract public function _error();

    abstract public function _close();

    abstract public function _setmetadata();

    public function getError()
    {
        return $this->error;
    }

    /**
     * Execute query.
     *
     * @param integer $maxrows Maximum number of rows of the result.
     * @param integer $offset Query offset.
     * @param type $stmt Statement.
     * @return boolean Return true if the query was successful.
     * @throws EDatabaseQueryException 
     */
    public function open($maxrows=NULL, $offset=NULL, $stmt=NULL)
    {
        $this->_miolo->logSQL($this->sql, false, $this->conn->db->conf);

        $this->maxrows = $maxrows;
        $this->offset = $offset;

        if ($stmt != NULL)
        {
            $this->_querystmt($stmt);
        }
        else
        {
            $this->_query();
        }

        $this->_setmetadata();

        if ($this->rowCount)
        {
            $this->row = 0;
            $this->eof = $this->bof = false;
            $this->fetched = true;
        }
        else
        {
            $this->result = NULL;
            $this->row = -1;
            $this->eof = $this->bof = true;
            $this->fetched = false;
        }

        $this->error = $this->_error();

        if ($this->error)
            throw new EDatabaseQueryException($this->error);
        return ($this->result != NULL);
    }

    public function close()
    {
        if ($this->fetched)
        {
            $this->_close();
        }
    }

    public function setConnection(&$conn)
    {
        $this->conn = $conn;
    }

    public function setSQL(&$sql)
    {
        $this->sql = $this->conn->_escape($sql->select());
        $this->objSql = &$sql;
    }

    public function setSQLCommand($sqlCommand)
    {
        $this->sql = $this->conn->_escape($sqlCommand);
    }

    public function setOrder($order)
    {
        $order = explode(',', $order);
        $this->order = $order;

        foreach ($this->order as $o)
            $p[] = $this->getColumnNumber($o);

        $n = count($this->result[0]);

        foreach ($this->result as $key => $row)
        {
            for ($i = 0; $i < $n; $i++)
                $arr[$i][$key] = $row[$i];
        }

        foreach ($p as $i => $o)
            $sortcols .= ($i > 0 ? ",\$arr[$o]" : "\$arr[$o]");

        for ($i = 0; $i < $n; $i++)
            if (!in_array($i, $p))
                $sortcols .= ",\$arr[$i]";

        eval ("array_multisort({$sortcols}, SORT_ASC);");
        $this->result = array();

        for ($i = 0; $i < $n; $i++)
        {
            foreach ($arr[$i] as $key => $row)
                $this->result[$key][$i] = $row;
        }
    }

    public function isFiltered()
    {
        return $this->filtered;
    }

    public function addFilter($field, $oper, $value, $conector = 'AND')
    {
        if (!$this->queryfilter)
            $this->queryfilter = new QueryFilter($this);
        $this->queryfilter->addFilter($field, $oper, $value, $conector);
    }

    public function applyFilter()
    {
        if (!$this->queryfilter)
            return;

        $this->result = $this->queryfilter->applyFilter($this->result);
        $this->filtered = true;
        $this->rowCount = count($this->result);

        if ($this->rowCount)
        {
            $this->row = 0;
            $this->eof = $this->bof = false;
            $this->fetched = true;
        }
        else
        {
            $this->result = NULL;
            $this->row = -1;
            $this->eof = $this->bof = true;
            $this->fetched = false;
        }
    }

    public function setPageLength($pagelength)
    {
        $this->pagelength = $pagelength;
    }

    public function getPageCount()
    {
        return (int)(($this->rowCount - 1 + $this->pagelength) / $this->pagelength);
    }

    public function getPage($pageno)
    {
        if ($this->result)
        {
            if ($this->pagelength)
            {
                return array_slice($this->result, $this->pagelength * ($pageno - 1), $this->pagelength);
            }
            else
                return $this->result;
        }
    }

    public function getCSV($filename = '', $separator =';')
    {
        $csvdump = new MCSVDump($separator);

        if ($this->result)
        {
            $csvdump->dump($this->result, $filename);
        }
        exit;
    }

    /**
     * @param character $fetchType Set fetch type. Use MQuery::FETCH_* constants.
     */
    public function setFetchType($fetchType)
    {
        $this->fetchType = $fetchType;
    }

    /**
     * @return character Get the fetch type.
     */
    public function getFetchType()
    {
        return $this->fetchType;
    }
}


class QueryFilter
{
    public $filters; //array with filters
    public $count = 0;
    public $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function addFilter($field, $oper, $value, $conector = 'AND')
    {
        $oper = strtolower($oper);

        if ($oper == 'like')
        {
            $value = str_replace("?", ".", $value);
            $value = str_replace("_", ".", $value);
            $value = str_replace("%", "(.*?)", $value);
            $value = "^" . $value . "(.*?)";
            $oper = 'regex';
        }

        $this->filters[$this->count]['field'] = $field;
        $this->filters[$this->count]['fieldpos'] = $this->query->getColumnNumber($field);
        $this->filters[$this->count]['oper'] = $oper;
        $this->filters[$this->count]['value'] = trim($value);
        $this->filters[$this->count]['sizevalue'] = strlen(trim($value));
        $this->filters[$this->count]['conector'] = strtoupper($conector);
        $this->count++;
    }

    public function applyFilter($data) // a multidimensional array
    {
        foreach ($this->filters as $f)
        {
            $value[$this->query->getColumnNumber($f['field'])] = $f['value'];
        }

        foreach ($data as $row)
        {
            $this->filtered = array
                (
                );

            foreach ($this->filters as $f)
            {
                $p = $f['fieldpos'];
                $n = $f['sizevalue'];
                $v = $f['value'];

                switch ($f['oper'])
                    {
                    case "=":
                        $this->filtered[] = (!strncasecmp($row[$p], $v, $n));

                        break;

                    case "!=":
                        $this->filtered[] = (strncasecmp($row[$p], $v, $n));

                        break;

                    case "like":
                        $this->filtered[] = (!strncasecmp($row[$p], $v, $n));

                        break;

                    case "regex":
                        $this->filtered[] = preg_match("/$v/i", $row[$p]);

                        break;

                    default: $this->filtered[] = (!strncasecmp($row[$p], $v, $n));
                    }
            }

            $filtered = $this->filtered[0];

            for ($i = 1; $i < count($this->filtered); $i++)
            {
                switch ($this->filters[$i]['conector'])
                    {
                    case "AND":
                        $filtered = $filtered && $this->filtered[$i];

                        break;

                    case "OR":
                        $filtered = $filtered || $this->filtered[$i];

                        break;
                    }
            }

            if ($filtered)
            {
                $result[] = $row;
            }
        }

        return $result;
    }
}

?>