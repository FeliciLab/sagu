<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 * 
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 * 
 * Class GnutecaBussines, extends the default MBussines,
 * including default database configuration and some usefull functions.
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 28/07/2008
 *
 **/
class GBusiness extends MBusiness
{
    /**
     * Atalho para o miolo
     * FIXME, se possível trocar por manager ou remover
     * @var Miolo
     */
    public $MIOLO;
    /**
     * Nome do módulo atual, geralmente gnuteca3
     * @var string
     */
    public $module ='gnuteca3';
    /**
     * objeto de conexão
     * @var
     */
    public $db; 
    /**
     * objeto MSQL gerador de sqls
     * @var MSQL
     */
    public $MSQL;
    /**
     * A string sql a ser executada
     * @var string sql A string sql a ser executada
     */
    public $sql; //
    /**
     * String com as colunas (todas)
     * @var string com as colunas (todas)
     */
    public $columns;
    /**
     * string com as colunas sem id
     * @var string com as colunas sem id
     */
    public $columnsNoId;
    /**
     * string com as colunas de chave
     * @var string com as colunas de chave
     */
    public $id;
    /**
     * string com a/as tabelas.
     * @var string
     */
    public $tables;
    /**
     * usado por campos repetitivos
     * @var string
     */
    public $removeData;
    /**
     * Variável utilizada para verificação se existe ou não a necessidade de aplicar o limit e offset para grid.
     * Ele é aplicado no sql antes da execução.
     *
     * @var boolean
     */
    protected $applyLimiAndOffset = false;

    /**
     * Define se é pra mudar o select se busca para count
     * @var boolean
     */
    protected $forCount = false;
    
    /**
     * Define o offset da query
     * @var int 
     */
    protected $offset;
    
    /**
     * Guarda os campos de resultado da última query
     * @var type 
     */
    protected $resultFields;

    /**
    * Class constructor. Create default variables and connects with gnuteca3 database.
    *
    * @param $tables        the table 's that the class will manage
    * @param $id            the id (primary key) of class table, can be comma separated, if has more than one
    * @Param $columnsNoId   the rest of table columsn that class will manage, can be comma separated.
    *
    **/
    function __construct( $tables = NULL, $id = NULL, $columnsNoId = NULL, $db = 'gnuteca3' )
    {
        parent::__construct();
        $this->MIOLO        = MIOLO::getInstance();
        $this->module       = 'gnuteca3';

        $this->setData(null);
        $this->setDb($db);
        $this->MSQL         = new MSQL();
        $this->tables       = $tables;
        $this->id           = $id;
        $this->columnsNoId  = $columnsNoId;
        $this->columns      = $id;
        
        if ( strlen( $columnsNoId ) > 0 )
        {
        	$this->columns .= ',' . $columnsNoId;
        }
    }

    /**
     * Define aplicação de limit e offset, utilizado por grids.
     *
     * @param boolean $limitAndOffset
     */
    public function setApplyLimitAndOffset($limitAndOffset)
    {
        $this->applyLimiAndOffset = $limitAndOffset;
    }

    /**
     * Seleciona o banco de dados
     * @param string $db
     */
    public function setDb($db = 'gnuteca3')
    {
        $this->db = $this->MIOLO->getDatabase($db);
    }

    /**
     * Define os dados no business
     * @param stdClass $data
     * @return boolean
     */
    public function setData($data)
    {
    	//Define removeData como false para nao ter que limpar manualmente em todos business que usam GRepetitiveField
        $this->removeData = false;
    	return parent::setData($data);
    }

    /**
    * Define the table id (code).
    *
    * ATTENTION: this function automatically mounts the columns using $this->columnsNoId
    *
    * @param $id the string that define the id, can be comma separated, if has more than one
    */
    public function setId($id)
    {
        $this->id = $id;
        $this->setColumns($id .','. $this->columnsNoId);
    }

    /**
    * Define the table columns (exept the id that is defined in other function)
    *
    * ATTENTION: this function automatically mounts the columns using $this->id
    *
    * @param $columnsNoId the string that define the columns, can be comma separated, if has more than one
    */
    public function setColumnsNoid($columnsNoId)
    {
        $this->columnsNoId = $columnsNoId;
        $this->setColumns($this->id .','. $this->columnsNoId);
    }

    /**
    * Define the Bussines Columns
    *
    * @param $columns the string with columns (separated by ',' )
    */
    public function setColumns($columns, $distinct = false)
    {
        if(is_array($columns))
        {
            $columns = $this->getColumnsString($columns);
        }

        $this->columns = $columns;
        $this->MSQL->setColumns($columns, $distinct);
    }

    /**
    * Define the Bussines tables
    *
    * @param $tables the string with tables
    */
    public function setTables($tables)
    {
        $this->tables = $tables;
        $this->MSQL->setTables($tables);
    }

    /**
    * Define the Bussines where
    *
    * @param $where the string with where
    * @param mixed $value Value(s) of SQL
    */
    public function setWhere($where, $value = NULL)
    {
        $this->MSQL->setWhere($where);

        if ($value)
        {
	        foreach ((array)$value as $val)
	        {
	        	$this->MSQL->addParameter($val);
	        }
        }
    }

    /**
     * Define um limite no select
     * @param integer $limit
     */
    public function setLimit( $limit )
    {
    	$this->MSQL->setRange( $limit );
    }
    
    /**
     * Define o offset no select
     * @param integer $offset 
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Adiciona uma condição de alternância
     * @param string $where
     */
    public function setWhereOr($where)
    {
        $this->MSQL->setWhereOr($where);
    }

    /**
     * Define SQL order
     *
     * @param $orderBy (String): Order by
     */
    public function setOrderBy($orderBy)
    {
        $this->MSQL->setOrderBy($orderBy);
    }

    /**
    * Clear the MSQL object and sql string;
    *
    */
    public function clear()
    {
        $this->MSQL->clear();
        unset($this->sql);
    }

    /**
    * Create a select sql.
    *
    * @param $args the args to prepare sql.
    */
    public function select($args = null)
    {
        //caso seja a situação da grid, muda os campos pra count e tira a ordenação
        if ( $this->forCount == true )
        {
            $this->MSQL->columns = '' ;
            $this->setColumns(' count(*) '); //muda as coluna
            $this->sql = $this->MSQL->select($args);
        
            $orderPos = stripos( $this->sql , 'ORDER');

            //tira o order
            if ( $orderPos > 0 )
            {
                $this->sql = substr( $this->sql, 0, $orderPos );
            }

           $this->forCount = false;
        }
        else
        {
            $this->sql = $this->MSQL->select($args);
        }

        
        return $this->sql;
    }

    /**
    * Automatically mount a simple list function
    * that can be used in a MSelection be example
    *
    * @param $object boolean, if you want to use in MSelection please pass $object as false;
    *
    */
    protected function autoList($object=FALSE, $associative=FALSE)
    {
        //$this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs  = $this->query($sql, $object);

        if ($associative)
        {
            $out = array();

            if ($rs)
            {
                foreach ($rs as $v)
                {
                    list($id, $value) = $v;
                    $out[ $id ] = $value;
                }
            }
            return $out;
        }

        return $rs;
    }

    /**
    * This functions manage the filters used in autoSelect function
    *
    * @param filters  You have to pass a array, by example
                     $filters  = array(
                    'classificationAreaId'  => 'equals',
                    'areaName'              => 'ilike' ,
                    'classification'        => 'ilike' ,
                    'ignoreClassification'  => 'ilike'
                    );
                    The function will search by classificationAreaId or classificationAreaIdS
                    it add "S" and try to find the information
    *
    */
    protected function addFilters($filters=NULL)
    {
        if ($filters)
        {

            foreach ($filters as $line => $info)
            {
                $temp = $line;
                if (!$this->$temp)
                {
                    $temp = $line.'S';
                }

                if ($info == 'like')
                {
                    if ($this->$temp)
                    {
                        $this->$temp = str_replace(' ', '%', $this->$temp);
                        $this->setWhere($line . ' like ?');
                        $args[] = '%'.$this->$temp.'%';
                    }
                }
                else
                if ($info == 'ilike')
                {
                    if ($this->$temp)
                    {
                        $this->$temp = str_replace(' ', '%', $this->$temp);
                        $this->setWhere('lower(unaccent('.$line . ')) like lower(unaccent(?))');
                        $args[] = '%'.$this->$temp.'%';
                    }
                }
                else if ($info == 'date')
                {
                    if ($this->$temp)
                    {
                        $this->setWhere($line . "::date = to_date(?,'dd/mm/yyyy')");
                        $args[] = $this->$temp . '::date';
                    }
                }
                else //($info == 'equals')
                {
                    if ($this->$temp)
                    {
                        if ( is_array($this->$temp ) )
                        {
                            $this->setWhere($line . ' in (\' '.implode("','", $this->$temp) .'\')');
                        }
                        else
                        {
                            $this->setWhere($line . ' = ?');
                            $args[] = $this->$temp;
                        }
                    }
                }
            }
        }
        return $args;
    }

    /**
    * Automatically makes a search in database
    *
    * @param $filters you can pass the filters using AddFilter format
    *
    */
    protected function autoSearch($filters, $object=FALSE)
    {
        $args = $this->addFilters($filters);
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select($args);
        
        //seta offset na query
        if ( is_numeric($this->offset) )
        {
            $sql .= ' OFFSET ' . $this->offset;
        }
        
        //seta limit na query
        if ( is_numeric($this->MSQL->range) )
        {
            $sql .= ' LIMIT ' . $this->MSQL->range;
        }
        
        return $this->query($sql, $object);
    }

    /**
    * Function that implements a automatically get Function using $this->columns and $this->tables
    *
    * @param $args you can pass how many arga you want, but it need to be exactely the same order and quantity of $this->id;
    *
    */
    protected function autoGet()
    {
        $data = func_get_args();

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $temp = explode(',', $this->id);

        foreach ($temp as $line => $info)
        {
            $this->setWhere($info. ' = ?');
        }

        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject=true);
        $this->setData($rs[0]);
        return $rs[0];
    }

    /**
    * Create a insert sql.
    *
    * @param $args the args to prepare sql.
    */
    public function insert($args)
    {
        $this->sql = $this->MSQL->insert($args);
        return $this->sql;
    }

    /**
    * Automatically make an insert in databasem, it generate the sql and call databse function
    *
    * ATTENTION: if $this->id has a value it will use it to do the insert, otherwise the function do not will pass id , and consider an auto id generated by the database
    *
    */
    protected function autoInsert()
    {
        $ids = explode(',', $this->id);

        if ($ids)
        {
            foreach ($ids as $line =>$info)
            {
                $info = trim($info);
                if ($this->$info)
                {
                    $idValue[] = $this->$info;
                }
            }
        }

        $this->clear();
        //aqui entra se tem que preencher um valor para os Id
        if ($idValue)
        {
        	$columns = $this->getInverseColumns();
            $data = $this->associateData( $columns );
            $this->setColumns( $columns );
        }
        else //aqui entra quando o Id é auto gerado pelo banco
        {
            if ($this->columnsNoId)
            {
            	$data = $this->associateData( $this->columnsNoId );
                $this->setColumns($this->columnsNoId);
            }
            else
            {
            	return false;
            }
        }

        if ($this->tables)
        {
	        $this->setTables($this->tables);
	        $sql = $this->insert($data);
            $id  = $this->id;
            $sql = $sql . ' RETURNING ' . $this->id ;
            $rs  = $this->query($sql);

            $this->$id = $rs[0][0];

            return $rs[0][0] ? true : false;
        }
        else
        {
        	return false;
        }
    }

    /**
    * Create a update sql.
    *
    * @param $args the args to prepare sql.
    */
    public function update($args)
    {
        $this->sql = $this->MSQL->update($args);
        return $this->sql;
    }

    /**
    * Makes an automatically update in this table in database.
    *
    * @param no no param needed, you must set class atributes
    *
    */
    protected function autoUpdate()
    {
        $data = $this->associateData( $this->getInverseColumns() );
        $this->clear();
        $this->setColumns($this->columnsNoId);
        $this->setTables($this->tables);
        $arrayId = explode(',', $this->id);

        foreach ($arrayId as $line => $info)
        {
            $this->setWhere($info . '= ?');
        }

        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
    * Create a delete sql.
    *
    * @param $args the args to prepare sql.
    */
    public function delete($args)
    {
        $this->sql = $this->MSQL->delete($args);
        return $this->sql;
    }

    /**
    * Make an automatically delete on base, it make the sql and execute it.
    * ATTENTION: you have to pass the functions args in the same quantity and order that $this->id
    *
    * TODO:: INFORMAR ERROS MELHOR PARA O PROGRAMADOR
    *
    * @param $args can be various args in quantity you need, but you must respect the $this->id quantity and order
    *
    */
    protected function autoDelete()
    {
        $this->clear();
        $this->setTables($this->tables);
        $arrayId = explode(',', $this->id);

        foreach ($arrayId as $line => $info)
        {
            $this->setWhere($info . '= ?');
        }

        $sql = $this->delete(func_get_args());
        $rs  = $this->execute($sql);
        return $rs;
    }

    /**
    * Executes an sql using query function, if you not pass the $sql param it will take $this->sql, insert, update, delete and select functions set it.
    *
    * @param $sql the sql to query.
    * @param #queryToObj if is to apply queryToObj function that convert the result to a object
    */
    public function query($sql=NULL, $queryToObj=NULL)
    {
        $sql = $sql ? $sql : $this->sql;

        //verifica se precisa fazer concatenação para a grid
        if ( $this->applyLimiAndOffset == true )
        {
            //verifica se existe order by
            $orderPos = stripos( $sql , 'ORDER');
            
            //caso tenha e tenha ordenação aplicada na grid, retira-o
            if ( $orderPos > 0 && ( $_REQUEST['orderby'] || $_REQUEST['orderby'] === '0' ) )
            {
                $sql = substr( $sql, 0, $orderPos );
            }

            $sql .= $this->getLimitAndOffsetForGrid();
        }
        
        
        $this->applyLimiAndOffset = false;

        if ( $sql )
        {
            $query = $this->db->conn->getQueryCommand($sql, $maxrows);
            /*try
            {
                // $sql is a SQL command string
                $query = $this->db->conn->getQueryCommand($sql, $maxrows);
            }
            catch ( MDatabaseException $e )
            {
                throw new EDatabaseQueryException($e->getMessage());
            }*/

            //return $query;
            //$query =  $this->db->query($sql);
        }
        
        $this->resultFields = $query->metadata['fieldname'];

        if ( !$query->result )
        {
            return false;
        }

        if ($queryToObj)
        {
            return $this->queryToObj( $query->result );
        }

        return $query->result;
   }
   
   public function getResultFields()
   {
       if ( is_array( $this->resultFields ) )
       {
           foreach ( $this->resultFields as $line => $info)
           {
                $this->resultFields[$line] = ucfirst( strtolower($info) );
           }
       }
       
       return $this->resultFields;
   }

    /**
    * Executes a sql.
    *
    * @param $sql the sql to execute.
    */
    public function execute($sql=NULL)
    {
        $sql = $sql ? $sql : $this->sql;
        return $this->db->execute($sql);
    }
    
    /**
     * Strip "AS" and "Ref." SQL column
     *
     * @param $column (String): Column to parse
     *
     * @return $column (String): Column parsed
     */
    public function stripSQLColumn($column)
    {
        $col = trim( str_replace( array("\n","\r\n,","\r"), '', $column)); //tira linhas para funcionar o stripos corretamente
        $pos = strrpos( strtolower( $col ), ' as ');

        if ( ($pos) > 0 )
        {
            $col = substr($col, ($pos+3), strlen($col));
        }
        else
        {
            //Remove table name/alias from SQL column (eg. A.personId, strip "A.")
            $col = explode('.', $column);
            $col = $col[(count($col)-1)];
        }

        //depois de tirar os espaços do inicio e do fim, é possível remover 'as ' na frente.
        $col = str_ireplace('as ','',trim($col) );
        $col = str_replace('"','', $col ); //extract " when it needed

        return $col;
    }

    /**
    * Convert a MSQL query result to an object or array of objects (if is an multilpe dimension array)
    *
    * @param $query the result of the query
    * @param $columnsString the string separated by ',' with columns, you can pass null, the function will take the default $this->columns
    *
    * @return an array of object or a object, according passed data, can return false if has some erros or query is not an array
    */
    public function queryToObj($query, $columnsString=NULL)
    {
        if (!is_array($query))
        {
            return false;
        }

        if (is_array($query[0]))
        {
            foreach ($query as $line => $info)
            {
                $data[] = $this->queryToObj($info, $columnsString);
            }
            return $data;
        }

        $columns = $this->getColumnsArray($columnsString);

        if (count($query) != count($columns))
        {
            echo ( _M('QueryToObj - Erro: Diferença entre coluna e consulta.', 'gnuteca3') );
            return false;
        }

        foreach ($query as $line => $info)
        {
            $column = $this->stripSQLColumn($columns[$line]);
            $data->$column = $info;
        }

        return $data;
    }

    /**
     * Associate SQL columns with business data
     *
     * @param $columns (String): SQL columns string, separate by comma
     *
     * @return $array (Array)
     */
    public function associateData($columns = NULL)
    {
        if ($columns == NULL)
        {
            $columns = $this->columns;
        }

        $cols  = explode(',', $columns);

        if (!$cols)
        {
        	return false;
        }

        $array = array();
        $data  = $this->getData();

        if (!$data)
        {
        	return false;
        }

        foreach ($cols as $line)
        {
            $line = $this->stripSQLColumn( trim($line) );
            $array[] = $data->$line;
        }

        return $array;
    }

    /*
     * Get columns starting with normal columns and end by primary keys
     */
    public function getInverseColumns()
    {
	    $columns = '';

	    if ($this->columnsNoId)
	    {
	        $columns = $this->columnsNoId.',';
	    }
        
	    $columns .= $this->id;

	    return $columns;
    }

    /**
     * Transforma um array de colunas em uma string preparada para MSQL
     *
     * @param (Array) $columns
     * @return String
     */
    public function getColumnsString($columns = null)
    {
        if(!is_array($columns))
        {
            return false;
        }

        return implode(", ", $columns);
    }

     /**
     * Converte colunas em texto par um array, sem espaços
     *
     * FIXME esta função tem o mesmo efeito de getColumnsArray
     *
     * @deprecated
     * @param string $columns
     * @return array
     */
    public function columnsToArray($columns)
    {
    	//return $this->getColumnsArray( $columns );
        $exp  = explode(',', $columns);
    	$cols = array();

    	if (count($exp))
    	{
    		foreach ($exp as $val)
    		{
    			$cols[] = trim($val);
    		}
    	}
        
    	return $cols;
    }

    /**
    * Convert an string separated by ',' to an array, if you not pass
    * $columnsString parameters it will take the default $this->columns
    *
    * @param $columnsString the string separated by ',' with all columns
    *
    * @return array - the array with columns
    */
    public function getColumnsArray($columnsString=NULL)
    {
        if ($columnsString)
        {
            $columns = explode(',', $columnsString);
        }
        else
        {
            $columns = explode(',', $this->columns);
        }

        if ( is_array($columns) )
        {
            foreach ($columns as $line => $info)
            {
                $columns[$line] = trim($info);
            }
        }

        return $columns;
    }

    /**
     * Extract the columns part of a sql and explode it and return as a array
     *
     * @param string $sql
     * @return array the array with columns name
     */
    public function parseSqlToColumnsArray( $sql )
    {
        //Passo 1 - retira subselects
        $sql = preg_replace("/\((.*)[from|FROM](.*)\)/", "", $sql);
        
        $sql   = ' ' . trim( $sql );
        $posIni = stripos( $sql, 'select') + 6;
        $posFim = stripos( $sql, 'from'); //último from

        //Passo 2 - seoara conteúdo de select até from
        if ( $posIni && $posFim)
        {
            $sql = substr( $sql, $posIni, $posFim-$posIni );
        }
        
        $columns = explode(',',$sql);

        //Passo 3 - trata cada coluna
        if ( $columns )
        {
            $count = count($columns)-1;

            foreach ( $columns as $line => $info)
            {
                $columns[$line] = $this->stripSQLColumn( $columns[$line] );
            }

            $sql = substr( $string, 4, strlen($string) );
        }

        return $columns;
    }

    /**
     * Verifies if sql is valid to execute (or is only a select, not insert, update or delete)
     *
     * @param boolean $sql
     */
    public function verifySql( $sql )
    {
        $posDelete = stripos( ' '.$sql, 'delete');
        $posInsert = stripos( ' '.$sql, 'insert');
        $posUpdate = stripos( ' '.$sql, 'update');

        if ($posDelete > 0 || $posInsert > 0 || $posUpdate > 0)
        {
            return false;
        }

        return true;
    }

    /**
     * Execute an sql, changing the sql by the form data
     *
     * The sub sql is executed for each result of sql.
     *
     * @param string $sql the sql string
     * @param string $subSql the sub sql string
     * @param object $data the default ajax miolo data
     * @return array the database result
     */
    public function executeSelect( $sql , $subSql = null, $data = null)
    {
        //faz replace das variáveis
        if ($data)
        {
            $data = (array) ( $data );

            foreach ( $data as $line => $info)
            {
                $sql = str_replace( '$'.$line, $info, $sql);
            }
        }

        $gFunction = new GFunction();
        $gFunction->SetExecuteFunctions(true);

        $sql = $gFunction->interpret($sql, false);

        if ( $sql && $this->verifySql( $sql ))
        {
            $result = $this->query($sql);

           //faz a subsql caso necessário
            if ( $result && $subSql && $this->verifySql( $subSql ) )
            {
                foreach ( $result as $line => $info )
                {
                    $tempSubSql    = $subSql;
                    
                    if ( is_array( $info ))
                    {
                        foreach ( $info as $l => $i )
                        {
                            $tempSubSql    = str_replace( '$'.$l, $info[$l], $tempSubSql);
                        }
                    }
                    $subResult      = $this->query( $tempSubSql );
                    
                    if ( is_array($subResult) )
                    {
                        $result[$line] = array_merge($result[$line], $subResult[0] );
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Inicia uma transação na base de dados.
     */
    public function beginTransaction()
    {
        $this->execute("begin transaction;");
    }

    /**
     * Efetua o commit da transação corrente.
     */
    public function commitTransaction()
    {
        $this->execute("commit;");
    }

    /**
     * Faz rollback na transação o banco de dados;
     */
    public function rollbackTransaction()
    {
    	$this->execute("rollback;");
    }

    /**
     * Returna uma string com limit e offset para concatenação no final do select.
     *
     * @param integer $limit
     * @param integer $offset
     * @return string string com limit e offset para concatenação no final do select
     */
    public function getLimitAndOffsetForGrid( $limit = null, $offset = null)
    {
        $limit = $limit ? $limit : LISTING_NREGS;
        $page = MIOLO::_REQUEST('pn_page');
        $offset = $offset ? $offset : $page * LISTING_NREGS - LISTING_NREGS;

        $orderBy = $_REQUEST['orderby'];

        if ( $orderBy || $orderBy === '0' )
        {
            $orderBy = 'ORDER BY ' .($orderBy+1); //o contador da grid e do sql é diferente
        }

        if ( $offset > 0 )
        {
            return " $orderBy LIMIT $limit OFFSET $offset ";
        }
        else
        {
            return " $orderBy LIMIT $limit ";
        }
    }

    /**
     * Efetua a contagem de dados da tabela relacionada.
     *
     * @return integer contagem de dados da tabela
     */
    public function count( $alias )
    {
        $this->forCount = true ;
        $count = $this->$alias();
        return $count[0][0];

        /*if ( $this->MSQL )
        {
            $this->setColumns('count (*)');
            $this->setTables($this->tables);
            $this->sql = $this->MSQL->select();
            $result = $this->query();
            return $result[0][0];
        }*/ 
    }
    
    /**
     * Prepara um sql puro utilizando ? e um array de argumentos.
     * 
     * @param string $sql
     * @param array $args
     * @return string o sql com os ? trocado pelo array de argumentos
     */
    public function prepare($sql, $args)
    {
        $this->MSQL->command = $sql;
        
        return $this->MSQL->prepare($args);
    }
    
    /**
     * Lista os nomes das tabelas de um esquema
     * 
     * @param string $schema
     * @return array 
     */
    public function listTables( $schema = 'public')
    {
        if ( !$schema )
        {
            $schema = 'public';
        }
        
        $sql = "SELECT tablename,tablename FROM pg_catalog.pg_tables WHERE schemaname = '$schema' order by tablename;";
        
        return $this->query($sql);
    }
    
    /**
     * Lista os nomes dos campos de uma tabela
     * 
     * @param string $tablename nome da tabela
     * @return array 
     */
    public function listColumns($tablename)
    {
        $sql ="SELECT attname,attname from pg_catalog.pg_attribute a
          INNER JOIN pg_stat_user_tables c on a.attrelid = c.relid
               WHERE a.attnum > 0
                 AND NOT a.attisdropped
                 AND c.relname = '$tablename'
            ORDER BY c.relname, a.attname";
        
        return $this->query($sql);
    }

   /**
    * Seta o datestyle correto para o gnuteca DMY
    */
   public static function setDateStyle()
   {
       $gb = new GBusiness();
       $gb->query("SET DateStyle TO 'SQL, DMY';");
   }

}
?>
