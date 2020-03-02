<?php

class MSQL
{
    public $db;

    public $distinct;

    public $columns;

    public $tables;

    public $where;

    public $groupBy;

    public $having;

    public $orderBy;

    public $forUpdate;

    public $join;

    public $parameters;

    public $command;

    public $range;

    public $bind;

    public $stmt;

    var $setOperation;

    public function __construct($columns = '', $tables = '', $where = '', $orderBy = '', $groupBy = '', $having = '', $forUpdate = false)
    {
        $this->clear();
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $this->setGroupBy($groupBy);
        $this->setHaving($having);
        $this->setOrderBy($orderBy);
        $this->setForUpdate($forUpdate);
        $this->join = null;
        $this->parameters = null;
        $this->range = null;
        $this->db = null;
        $this->bind = false;
        $this->stmt = NULL;
    }

    private function getTokens($string, &$array)
    {
        if ($string == '')
            return;

        $source = $string . ',';
        $tok = '';
        $l = strlen($source);
        $can = 0;

        for ($i = 0; $i < $l; $i++)
        {
            $c = $source{$i};

            if (!$can)
            {
                if ($c == ',')
                {
                    $tok = trim($tok);
                    $array[$tok] = $tok;
                    $tok = '';
                }
                else
                {
                    $tok .= $c;
                }
            }
            else
            {
                $tok .= $c;
            }

            if ($c == '(')
                $can++;

            if ($c == ')')
                $can--;
        }
    }

    private function getJoin()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('database/' . $this->db->system . '/msqljoin.class.php');
        $className = "{$this->db->system}SqlJoin";
        $join = new $className();
        $join->_sqlJoin($this);
    }

    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * Set the columns
     * Use this method to set which columns must be used.
     *
     * @param string $string Name of the columns.
     * @param boolean $distinct If you want a distinct select, inform TRUE.
     */
    public function setColumns($string, $distinct = false)
    {
        $this->getTokens($string, $this->columns);
        $this->distinct = $distinct;
    }

    /**
     * @param string $string Table names separated by comma. E.g.: 'table1, table2'
     */
    public function setTables($string)
    {
        $this->getTokens($string, $this->tables);
    }

    /**
     * @param string $string Group by columns separated by comma. E.g.: 'column1, column2'
     */
    public function setGroupBy($string)
    {
        $this->getTokens($string, $this->groupBy);
    }

    /**
     * @param string $string Order by columns separated by comma. E.g.: 'column1, column2 DESC'
     */
    public function setOrderBy($string)
    {
        $this->getTokens($string, $this->orderBy);
    }

    /**
     * @param string $string Add conditional statement. E.g.: "column <> 'condition'"
     */
    public function setWhere($string)
    {
        $this->where .= (($this->where != '') && ($string != '') ? " and " : "") . $string;
    }

    /**
     * @param string $string Add AND conditional statement. E.g.: "column <> 'condition'"
     */
    public function setWhereAnd($string)
    {
        $this->where .= (($this->where != '') && ($string != '') ? " and " : "") . $string;
    }

    /**
     * @param string $string Add OR conditional statement. E.g.: "column <> 'condition'"
     */
    public function setWhereOr($string)
    {
        $this->where .= (($this->where != '') && ($string != '') ? " or " : "") . $string;
    }

    public function setHaving($string)
    {
        $this->having .= (($this->having != '') && ($string != '') ? " and " : "") . $string;
    }

    public function setHavingAnd($string)
    {
        $this->having .= (($this->having != '') && ($string != '') ? " and " : "") . $string;
    }

    public function setHavingOr($string)
    {
        $this->having .= (($this->having != '') && ($string != '') ? " or " : "") . $string;
    }

    public function setJoin($table1, $table2, $cond, $type = 'INNER')
    {
        $this->join[] = array
            (
            $table1,
            $table2,
            $cond,
            $type
            );
    }

    public function setLeftJoin($table1, $table2, $cond)
    {
        $this->setJoin($table1, $table2, $cond, 'LEFT');
    }

    public function setRightJoin($table1, $table2, $cond)
    {
        $this->setJoin($table1, $table2, $cond, 'RIGHT');
    }

    public function setForUpdate($forUpdate = false)
    {
        $this->forUpdate = $forUpdate;
    }

    public function SetSetOperation($operation, MSQL $sql)
    {
        $this->setOperation[] = array(
            $operation,
            $sql
        );
    }

    public function bind($parameters = null)
    {
        $this->bind = true;
        if (!is_array($parameters))
        {
            $parameters = array($parameters);
        }
        foreach($parameters as $i=>$p)
        {
            $parameters[$i] = ':' . $parameters[$i];
        }
        $this->setParameters($parameters);
    }

    public function prepare($parameters = null)
    {
        $MIOLO = MIOLO::getInstance();

        if ($parameters === NULL)
            return;

        if (!is_array($parameters))
        {
            $parameters = array($parameters);
        }

        $i = 0;
        while (($pos=strpos($this->command,'?',$pos+1)) !== false) $pos_array[$i++] = $pos;

        $MIOLO->assert($i == count($parameters), "SQL PREPARE: " . _M('Invalid parameters!') . "SQL: {$this->command}");

        if ($i > 0)
        {
            $sqlText = '';
            $p = 0; 
            foreach ($pos_array as $i=>$pos)
            {
                $param = $parameters[$i];

                // If the parameter starts with a ':', it is used as it is, without adding any quotation marks
                if ( $param{0} == ':' ) 
                {
                    $param = substr($param, 1);
                }
                elseif ( ($param === '') || (is_null($param)) )
                {
                    $param = 'null';
                }
                else
                {
                    switch ( $this->db->system )
                    {
                        case 'postgres':
                            $param = "'" . pg_escape_string($param) . "'";
                            break;

                        case 'mysql':
                            $param = "'" . mysql_real_escape_string($param) . "'";
                            break;

                        case 'sqlite':
                            $param = "'" . sqlite_escape_string($param) . "'";
                            break;

                        default:
                            $param = "'" . addslashes($param) . "'";
                            break;
                    }
                }

                $sqlText .= substr( $this->command, $p, $pos-$p) . $param;
                $p = $pos + 1;
            }
            $sqlText .= substr( $this->command, $p);
            $this->command = $sqlText;
       }

        return $this->command;
    }

    /**
     * Returns insert command.
     * This method returns the sql insert command.
     *
     * @param array $parameters Array of values.
     * @return string Sql insert command.
     */
    public function insert($parameters = null)
    {
        $sqlText = 'INSERT INTO ' . implode($this->tables, ',') . ' ( ' . implode($this->columns, ',') . ' ) VALUES ( ';

        for ($i = 0; $i < count($this->columns); $i++)
            $par[] = '?';

        $sqlText .= implode($par, ',') . ' )';
        $this->command = $sqlText;

        if (isset($parameters))
            $this->setParameters($parameters);

        $this->prepare($this->parameters);
        return $this->command;
    }

    public function insertFrom($sql)
    {
        $sqlText = 'INSERT INTO ' . implode($this->tables, ',') . ' ( ' . implode($this->columns, ',') . ' ) ';
        $sqlText .= $sql;
        $this->command = $sqlText;
        return $this->command;
    }

    public function delete($parameters = null)
    {
        $MIOLO = MIOLO::getInstance();
        $sqlText = 'DELETE FROM ' . implode($this->tables, ',');
        $MIOLO->assert($this->where != '', "SQL DELETE: " . _M('Condition is missing!'));
        $sqlText .= ' WHERE ' . $this->where;
        $this->command = $sqlText;

        if (isset($parameters))
            $this->setParameters($parameters);

        $this->prepare($this->parameters);
        return $this->command;
    }

    public function update($parameters = null)
    {
        $MIOLO = MIOLO::getInstance();
        $sqlText = 'UPDATE ' . implode($this->tables, ',') . ' SET ';

        foreach ($this->columns as $c)
            $par[] = $c . '= ?';

        $sqlText .= implode($par, ',');
        $MIOLO->assert($this->where != '', "SQL UPDATE: " . _M('Condition is missing!'));
        $sqlText .= ' WHERE ' . $this->where;
        $this->command = $sqlText;

        if (isset($parameters))
            $this->setParameters($parameters);

        $this->prepare($this->parameters);
        return $this->command;
    }

    /**
     * Returns SQL select command.
     * This method returns the SQL select command.
     *
     * @param array $parameters Array of values.
     * @returns string SQL select command
     */
    public function select($parameters = null)
    {
        if ($this->join != NULL)
            $this->getJoin();

        $sqlText = 'SELECT ' . ($this->distinct ? 'DISTINCT ' : '') . implode($this->columns, ',');

        if ($this->tables != '')
        {
            $sqlText .= ' FROM   ' . implode($this->tables, ',');
        }

        if ($this->where != '')
        {
            $sqlText .= ' WHERE ' . $this->where;
        }

        if ($this->groupBy != '')
        {
            $sqlText .= ' GROUP BY ' . implode($this->groupBy, ',');
        }

        if ($this->having != '')
        {
            $sqlText .= ' HAVING ' . $this->having;
        }

        if ($this->orderBy != '')
        {
            $sqlText .= ' ORDER BY ' . implode($this->orderBy, ',');
        }

        if ($this->forUpdate)
        {
            $sqlText .= ' FOR UPDATE';
        }

        $this->command = $sqlText;

        if (isset($parameters))
            $this->setParameters($parameters);

        if ($this->setOperation != NULL)
        {
            $this->command .= $this->getSetOperation();
        }

        $this->prepare($this->parameters);
        return $this->command;
    }

    /**
     * Clear MSQL attributes. 
     */
    public function clear()
    {
        $this->columns = '';
        $this->tables = '';
        $this->where = '';
        $this->groupBy = '';
        $this->having = '';
        $this->orderBy = '';
        $this->parameters = null;
        $this->command = '';
    }

    public function setParameters()
    {
        $numargs = func_num_args();

        if ($numargs == 1)
        {
            if (!is_array($parameters = func_get_arg(0)))
            {
                if ($parameters === null)
                    return;

                $parameters = array($parameters);
            }
        }
        else
        {
            $parameters = func_get_args();
        }

        $this->parameters = $parameters;
    }

    public function addParameter($value)
    {
        $this->parameters[] = $value;
    }

    public function setRange()
    {
        $numargs = func_num_args();

        if ($numargs == 1)
        {
            $this->range = func_get_arg(0);
        }
        elseif ($numargs == 2)
        {
            $page = func_get_arg(0);
            $rows = func_get_arg(1);
            $this->range = new MQueryRange($page, $rows);
        }
    }

    public function setOffset($offset, $rows)
    {
        if (!$this->range)
        {
            $this->range = new MQueryRange(0,0);
        }
        $this->range->offset = $offset;
        $this->range->rows = $rows;
    }

    public function findStr($target, $source)
    {
        $l = strlen($target);
        $lsource = strlen($source);
        $pos = 0;

        while (($pos < $lsource) && (!$fim))
        {
            if ($source[$pos] == "(")
            {
                $p = $this->findStr(")", substr($source, $pos + 1));

                if ($p > 0)
                    $pos += $p + 3;
            }

            $fim = ($target == substr($source, $pos, $l));

            if (!$fim)
                $pos++;
        }

        return ($fim ? $pos : -1);
    }

    public function parseSqlCommand(&$cmd, $clause, $delimiters)
    {
        if (substr($cmd, 0, strlen($clause)) != $clause)
            return false;

        $cmd = substr($cmd, strlen($clause));
        $n = count($delimiters);
        $i = 0;
        $pos = -1;

        while (($pos < 0) && ($i < $n))
            $pos = $this->findStr($delimiters[$i++], $cmd);

        if ($pos > 0)
        {
            $r = substr($cmd, 0, $pos);
            $cmd = substr($cmd, $pos);
        }

        return $r;
    }

    /**
     * Populate the current MSQL instance based on a given SQL instruction.
     *
     * @param string $sqltext SQL instruction.
     */
    public function createFrom($sqltext)
    {
        $this->command = $sqltext;
        $sqltext = trim($sqltext) . " #";
        $sqltext = preg_replace("/(?i)select /", "select ", $sqltext);
        $sqltext = preg_replace("/(?i) from /", " from ", $sqltext);
        $sqltext = preg_replace("/(?i) where /", " where ", $sqltext);
        $sqltext = preg_replace("/(?i) order by /", " order by ", $sqltext);
        $sqltext = preg_replace("/(?i) group by /", " group by ", $sqltext);
        $sqltext = preg_replace("/(?i) having /", " having ", $sqltext);
        $this->setColumns($this->parseSqlCommand($sqltext, "select", array("from")));

        if ($this->findStr('JOIN', $sqltext) < 0)
        {
            $this->setTables($this->parseSqlCommand($sqltext, "from", array("where", "group by", "order by", "#")));
        }
        else
        {
            $this->join = $this->parseSqlCommand($sqltext, "from", array("where", "group by", "order by", "#"));
        }

        $this->setWhere($this->parseSqlCommand($sqltext, "where", array("group by", "order by", "#")));
        $this->setGroupBy($this->parseSqlCommand($sqltext, "group by", array("having", "order by", "#")));
        $this->setHaving($this->parseSqlCommand($sqltext, "having", array("order by", "#")));
        $this->setOrderBy($this->parseSqlCommand($sqltext, "order by", array("#")));
    }
}

?>