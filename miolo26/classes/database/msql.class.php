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

	public $offsetSQL;

    public $bind;

    public $stmt;

    public $setOperation;

	public $limit;

	private $newJoins = array();

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

        // Suporte a passagem de parametros como array no setColumns() e outros
        if ( is_array($string) )
        {
            $string = implode(',', $string);
        }
        
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
        
        return $this;
    }
    
    /**
     * Limpa e define novamente as colunas
     *
     * @param array $columns 
     */
    public function setColumnsOverride(array $columns)
    {
        return $this->clearColumns()->setColumns($columns);
    }
    
    public function clearColumns()
    {
        $this->columns = null;
        
        return $this;
    }
    
    /**
     *
     * @return string
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param string $string Table names separated by comma. E.g.: 'table1, table2'
     */
    public function setTables($string)
    {
        $this->getTokens($string, $this->tables);
        
        return $this;
    }

    /**
     * @param string $string Group by columns separated by comma. E.g.: 'column1, column2'
     */
    public function setGroupBy($string)
    {
        $this->getTokens($string, $this->groupBy);
        
        return $this;
    }
    
    public function clearGroupBy()
    {
        $this->groupBy = null;
        
        return $this;
    }

    /**
     * @param string $string Order by columns separated by comma. E.g.: 'column1, column2 DESC'
     */
    public function setOrderBy($string)
    {
        $this->getTokens($string, $this->orderBy);
        
        return $this;
    }
    
    public function getOrderBy()
    {
        return $this->orderBy;
    }
    
    public function clearOrderBy()
    {
        $this->orderBy = null;
        
        return $this;
    }
    
    /**
     * Adiciona uma condicao padrao de equivalencia no WHERE
     *
     * @param string $column
     * @param string $value 
     */
    public function addEqualCondition($column, $value)
    {
        $this->setWhere($column . ' = ?', array($value));
        
        return $this;
    }
    
    public function addNotEqualCondition($column, $value)
    {
        $this->setWhere($column . ' <> ?', array($value));
        
        return $this;
    }
    
    public function addBetweenCondition($column, $value1, $value2)
    {
        $this->setWhere($column . ' BETWEEN ? AND ?', array($value1, $value2));
        
        return $this;
    }
    
    public function addGreaterCondition($column, $value)
    {
        $this->setWhere($column . ' > ?', array($value));
        
        return $this;
    }
    
    public function addGreaterEqualCondition($column, $value)
    {
        $this->setWhere($column . ' >= ?', array($value));
        
        return $this;
    }
    
    public function addSmallerCondition($column, $value)
    {
        $this->setWhere($column . ' < ?', array($value));
        
        return $this;
    }
    
    public function addSmallerEqualCondition($column, $value)
    {
        $this->setWhere($column . ' <= ?', array($value));
        
        return $this;
    }
    
    public function addNotIlikeCondition($column, $value)
    {
        $this->setWhere($column . ' NOT ILIKE ?', array($value));
        
        return $this;
    }
    
    public function addNotLikeCondition($column, $value)
    {
        $this->setWhere($column . ' NOT LIKE ?', array($value));
        
        return $this;
    }
    
    public function addLikeCondition($column, $value)
    {
        $this->setWhere($column . ' LIKE ?', array($value));
        
        return $this;
    }
    
    public function addLikeConditionUnaccent($column, $value)
    {
        $this->setWhere('UNACCENT(' . $column . ') LIKE UNACCENT(?)', array($value));
        
        return $this;
    }
    
    public function addIlikeCondition($column, $value)
    {
        $this->setWhere($column . ' ILIKE ?', array($value));
        
        return $this;
    }
    
    public function addIlikeConditionUnaccent($column, $value)
    {
        $this->setWhere('UNACCENT(' . $column . ') ILIKE UNACCENT(?)', array($value));
        
        return $this;
    }
    
    public function addWhereIn($column, array $values)
    {
        $this->setWhere($column . ' IN ' . $this->convertArrayToIn($values));
        
        return $this;
    }
    
    public function addWhereNotIn($column, array $values)
    {
        $this->setWhere($column . ' NOT IN ' . $this->convertArrayToIn($values));
        
        return $this;
    }

    /**
     * @param string $string Add conditional statement. E.g.: "column <> 'condition'"
     */
    public function setWhere($string, array $parameters = null)
    {
        $this->where .= (($this->where != '') && (substr($this->where, -1) != '(') && ($string != '') ? " and " : "") . $string;
     
        foreach ( (array) $parameters as $pValue )
        {
            $this->addParameter($pValue);
        }
        
        return $this;
    }
    
    /**
     *
     * @return string
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param string $string Add AND conditional statement. E.g.: "column <> 'condition'"
     */
    function setWhereAnd($string, array $parameters = null)
    {
        $this->where .= (($this->where != '') && (substr($this->where, -1) != '(') && ($string != '') ? " and " : "") . $string;
        
        foreach ( $parameters as $pValue )
        {
            $this->addParameter($pValue);
        }
        
        return $this;
    }

    /**
     * @param string $string Add OR conditional statement. E.g.: "column <> 'condition'"
     */
    function setWhereOr($string, array $parameters = null)
    {
        $this->where .= (($this->where != '') && (substr($this->where, -1) != '(') && ($string != '') ? " or " : "") . $string;
        
        foreach ( $parameters as $pValue )
        {
            $this->addParameter($pValue);
        }
        
        return $this;
    }
    
    public function getLimit()
    {
        return $this->limit;
    }

public function setLimit($limit)
    {
        if ( is_numeric($limit) || strlen($limit) == 0 )
        {
            $this->limit = $limit;
        }
        
        return $this;
    }
    
    public function clearLimit()
    {
        $this->limit = null;
        
        return $this;
    }

    public function getOffsetSQL()
    {
        return $this->offsetSQL;
    }

    public function setOffsetSQL($offsetSQL)
    {
        if ( is_numeric($offsetSQL) || strlen($offsetSQL) == 0 )
        {
            $this->offsetSQL = $offsetSQL;
        }
        
        return $this;
    }

    public function clearOffsetSQL()
    {
        $this->offsetSQL = null;
        
        return $this;
    }

    public function setHaving($string)
    {
        $this->having .= (($this->having != '') && ($string != '') ? " and " : "") . $string;
        
        return $this;
    }

    public function setHavingAnd($string)
    {
        $this->having .= (($this->having != '') && ($string != '') ? " and " : "") . $string;
        
        return $this;
    }

    public function setHavingOr($string)
    {
        $this->having .= (($this->having != '') && ($string != '') ? " or " : "") . $string;
        
        return $this;
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
        
        return $this;
    }

    public function setLeftJoin($table1, $table2, $cond)
    {
        $this->setJoin($table1, $table2, $cond, 'LEFT');
    }

    public function setRightJoin($table1, $table2, $cond)
    {
        $this->setJoin($table1, $table2, $cond, 'RIGHT');

		return $this;
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
     * Retorna comando de DELETE para tabela e valores passados
     *
     * @param string $tableName
     * @param array $where 
     * 
     * @return string
     */
    public static function deleteTable($tableName, array $where)
    {
        $msql = new MSQL();
        $msql->setTables($tableName);

        foreach ( $where as $column => $value )
        {
            $msql->addEqualCondition($column, $value);
        }
        
        return $msql->delete();
    }
    

	public static function insertTable($tableName, array $values)
    {
        $msql = new MSQL();
        $msql->setColumns( array_keys($values) );
        $msql->setParameters( array_values($values) );
        $msql->setTables($tableName);
        
        return $msql->insert();
    }
    
    /**
     * Retorna comando de UPDATE para tabela e valores passados
     *
     * @param string $tableName
     * @param array $values 
     * @param array $where
     * 
     * @return string
     */
    public static function updateTable($tableName, array $values, array $where)
    {
        $msql = new MSQL();
        $msql->setColumns( array_keys($values) );
        $msql->setParameters( array_values($values) );
        $msql->setTables($tableName);
        
        foreach ( $where as $key => $val )
        {
            $msql->addEqualCondition($key, $val);
        }
        
        return $msql->update();
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
            $sqlText .= ' FROM   ' . implode($this->tables, ',') . ' ' . $this->getNewJoinsSQL();
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
        
        if ($this->limit != '')
        {
            $sqlText .= ' LIMIT ' . $this->limit;
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
     * Substitui as colunas para COUNT(*)
     */
    public function selectCount()
    {
        $new = clone($this);
        $new instanceof MSQL;
        $new->clearColumns()
                ->clearGroupBy()
                ->clearOrderBy()
                ->clearLimit()
                ->clearOffsetSQL()
                ->setColumns('COUNT(*)');
        
        return $new->select();
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
        
        return $this;
    }

    public function setOffset($offset, $rows)
    {
        if (!$this->range)
        {
            $this->range = new MQueryRange(0,0);
        }
        $this->range->offset = $offset;
        $this->range->rows = $rows;
        
        return $this;
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
    public function createFrom($sqltext, $params=array())
    {
        $this->command = $sqltext;
        $this->setParameters($params);
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
    
    /**
     * Adiciona tabela com INNER JOIN
     *
     * @param string $table
     * @param string $cond
     */
    public function addInnerJoin($table, $cond)
    {
        $join = new MSQLNewJoin();
        $join->setTable($table)->setType(MSQLNewJoin::TYPE_INNER)->setCondition($cond);
        $this->addNewJoin($join);

        return $this;
    }

    /**
     * Adiciona tabela com LEFT JOIN
     *
     * @param string $table
     * @param string $cond
     */
    public function addLeftJoin($table, $cond)
    {
        $join = new MSQLNewJoin();
        $join->setTable($table)->setType(MSQLNewJoin::TYPE_LEFT)->setCondition($cond);
        $this->addNewJoin($join);

        return $this;
    }
    
    /**
     *
     * @return array
     */
    public function getNewJoins()
    {
        return $this->newJoins;
    }

    private function setNewJoins(array $newJoins)
    {
        $this->newJoins = $newJoins;
    }
    
    private function addNewJoin(MSQLNewJoin $join)
    {
        $this->newJoins[] = $join;
    }
    
    /**
     * @return string 
     */
    private function getNewJoinsSQL()
    {
        $out = '';
        
        foreach ( $this->getNewJoins() as $join )
        {
            $join instanceof MSQLNewJoin;
            
            $out .= ' ' . $join->generateSQL();
        }
        
        return $out;
    }
    
    /**
     * Converte conjunto de valores para realidade SQL
     */
    public function convertArrayToIn(array $values = null)
    {
        $out = array();
        
        foreach ( $values as $value )
        {
            if ( $value instanceof MSQLExpr )
            {
                $out[] = $value;
            }
            else
            {
                $out[] = "'" . str_replace("'", "''", $value) . "'";
            }
        }
        
        return '(' . implode(', ', $out) . ')';
    }
    
    public function startSubCondition()
    {
        $this->where .= '(';
    }
    
    public function endSubCondition()
    {
        $this->where .= ')';
    }
}

/**
 * Representa uma expressao literal de SQL (ex.: NOW() ) 
 */
class MSQLExpr
{
    private $value;
    
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}

class MSQLNewJoin
{
    const TYPE_INNER = 'INNER';
    const TYPE_LEFT = 'LEFT';
    
    private $table;
    
    private $type;
    
    private $condition;
    
    public function getTable()
    {
        return $this->table;
    }

    public function setTable($table)
    {
        $this->table = $table;
        
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        
        return $this;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function setCondition($condition)
    {
        $this->condition = $condition;
        
        return $this;
    }

    /**
     *
     * @return string
     */
    public function generateSQL()
    {
        return $this->getType() . ' JOIN ' . $this->getTable() . ' ON ' . $this->getCondition();
    }
}
?>