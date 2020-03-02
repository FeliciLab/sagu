<?php

class MBusiness extends PersistentObject
{
    public $_bmodule; // the module of this class
    public $_bclass; // the name of this class
    public $_errors;
    public $_database;

    /**
     * @var MDatabase 
     */
    public $_db;

    public $_transaction;
    protected $_miolo;

    public function __construct($database = NULL, $data = NULL)
    {
        parent::__construct();
        $this->_miolo = MIOLO::getInstance(); 
        $this->_errors = array();
        $this->getDatabase($database);
        $this->_transaction = NULL;
    }

    public function onCreate($data = NULL)
    {
        if (is_null($data))
        {
            return;
        }
        elseif (is_object($data))
        {
            $this->setData($data);
        }
        else
        {
            $this->getById($data);
        }
    }

    public function getMiolo()
    {
        return $this->_miolo;
    }

    public function getById($data=NULL)
    {
    }

    function handleTypedAttribute($attribute, $operation, $type)
    {
        if ($type == 'lob')
        {
            $this->handleLobAttribute($attribute, $this->$attribute, $operation);
        }
    }

    public function checkError()
    {
        $err = $this->_db->getError();
        if ($err)
        {
            $this->_errors[] = $err;
        }
        return (count($this->_errors) > 0);
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function addError($err)
    {
        if ($err)
        {
            if (is_array($err))
            {
                $this->_errors = array_merge($this->_errors, $err);
            }
            else
            {
                $this->_errors[] = $err;
            }
        }
    }

    public function getData()
    {
        return $this;
    }

    public function setData($data = NULL)
    {
        if (is_null($data))
        {
            return;
        }
        foreach ($this as $attr=>$value)
        {
            $a = strtolower($attr);
            if (isset($data->$attr))
                $this->$attr = $data->$attr;
            elseif (isset($data->$a))
                $this->$attr = $data->$a;
        }
    }

    public function getDatabase($database = NULL)
    {
        if (is_null($database))
        {
            return;
        }
        $this->_database = $database;
        $this->_db = $this->_miolo->getDatabase($this->_database);
    }

    public function getDb()
    {
        if (is_null($this->_db))
        {
            throw new EBusinessException( _M('Error in Business: _db undefined! ') . "Class: {$this->_bclass} - Module: {$this->_bmodule}");
        }
        return $this->_db;
    }

    public function query(MSQL $sql, $parameters=NULL, $maxrows=0, $fetchType=NULL)
    {
        if ($db = $this->getDb())
        {
            if ($parameters)
            {
                $sql->setParameters($parameters);
            }

            $result = $db->getQuery($sql, $maxrows, $fetchType);
            $this->checkError();
            
            return $result;
        }
    }

    /**
     * For backward compatibility.
     * Method just for backward compatibility.
     * 
     * @param $sql (mixed) Sql string ou MSQL object
     * @param $parameters (string) MSQL object parametes
     * @param $maxrows (int) Max row count to return
     * 
     * @return (array) Query results   
     */
    public function objQuery($sql, $parameters = NULL, $maxrows = 0)
    {
        if ($db = $this->getDb() )
        {        
            if ( get_class($sql) == 'MSQL' )
            {
                if ($parameters)
                {
                    $sql->setParameters($parameters);
                }

                $oSql = $sql;
            }
            else
            {
                $oSql = new MSQL();
                $oSql->createFrom($sql);
                $oSql->setParameters($parameters);
            }           

            $result = $db->objQuery($oSql, $maxrows);
            $this->checkError();

            return $result;
        }
    }

    /**
     * Execute a sql instruction (insert, delete, update)
     * 
     * @param $sql (string) SQL instruction to execute 
     * @return (boolean) The result of SQL command
     */
    public function execute($sql)
    {
        if ($db = $this->getDb())
        {
            if ($this->_transaction)
            {
                $this->_transaction->addCommand($sql);
            }
            else
            { 
                $result = $db->execute($sql);
                $this->checkError();
                return $result;
            }
        }
    }
    
    public function getAffectedRows()
    {
        return $this->_db->getAffectedRows();
    }

    public function executeBatch($arrayOfCommands)
    {
        if ($db = $this->getDb())
        {
            if ($this->_transaction)
            {
                foreach($arrayOfCommands as $cmd)
                {
                    $this->_transaction->addCommand($cmd);
                }
            }
            else
            { 
                $result = $db->executeBatch($arrayOfCommands);
                $this->checkError();
                return $result;
            } 
        }
    }

    // backward compatibility
    public function objQueryRange($sql, &$range)
    {
        if ($db = $this->getDb())
        {
            $result = $db->queryRange($sql, $range);
            $this->checkError($this->_db);
            return $result;
        }
    }

    public function executeSP($sql, $parameters = null)
    {
        if ($db = $this->getDb())
        {
            $result = $this->_db->executeSP($sql, $parameters);
            $this->checkError($this->_db);
            return $result;
        }
    }

    public function getBusiness($module, $name = 'main', $data = NULL)
    {
        return $this->_miolo->getBusiness($module, $name, $data);
    }

    public function log($operacao, $descricao)
    {
        $login = $this->_miolo->getLogin();
        $objLog = $this->_miolo->getBusinessMAD('log');
        $ok = $objLog->log($operacao, $descricao, $login->idkey, $this->_bmodule, $this->_bclass);
        if (!$ok)
        { 
            $this->addError($objLog->getErrors());
        }
        return $ok;
    }

    public function setTransaction(MTransaction $transaction)
    {
        $this->_transaction = $transaction;
    }

    public function getTransaction()
    {
        return $this->_transaction;
    }

    public function beginTransaction($batch = true)
    {
        if ($db = $this->getDb())
        {
            $this->_transaction = $db->getTransaction();
            $this->_transaction->setBatch($batch);
        }
    }

    public function endTransaction()
    {
        if ($this->_transaction)
        {
            $this->_transaction->process();
        }
    }
    
    public function getManager()
    {
        return $this->_miolo;
    }
}
?>