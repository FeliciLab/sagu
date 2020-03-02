<?php

/**
 *  Classe para abstração com a base de dados da Avaliação institucional
 *  É herdado pelos demais types do sistema
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 2011/10/11
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2008 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

/*
 * Class ADatabase
 *
 */

class ADatabase
{
    const RETURN_ARRAY = 'array';
    const RETURN_TYPE = 'type';
    const RETURN_SQL = 'sql';
    const RETURN_OBJECT = 'object';
    const TYPE_INTEGER = 'integer';
    const TYPE_VARCHAR = 'varchar';
    const TYPE_TEXT = 'text';
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_INTERVAL = 'interval';

    //variável com a conexão
    private $db;
    static public $instance;
    
    public function __construct( $db = avinst )
    {
        $MIOLO = MIOLO::getInstance();
        $this->db = $MIOLO->getDatabase($db);        
    }
    
    /**
     * Seta a conexão com o banco de dados
     *
     * @param stdClass $db objeto contendo a conexão com o banco de dados
     */
    public function setDb($db)
    {
        $this->db = $db;
    }
    
	/**
     * Seta a uma instância da classe de conexão com o banco de dados
     *
     * @param stdClass $db objeto contendo a instância da classe de conexão com o banco de dados
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }
    
	/**
     * Retorna a instância da classe
     *
     * @param stdClass $db objeto contendo a conexão com o banco de dados
     * @return stdClass
     */
    public static function getInstance()
    {
    	if ( !self::$instance )
    	{
    		self::$instance = new ADatabase();
    	}
    	return self::$instance;
    }
    
    /**
     * Subistitui os valores dos parâmetros no sql
     *
     * @param String $sqlText contendo o sql com o parâmetro de substituição "?"
     * @param Array $sqlText contendo os valores para subistituir
     * @return String
     */
    public static function prepare($sqlText, $parameters=null)
    {
        global $MIOLO;

        /*if ($this->bind)
            return;*/

        if (!$parameters)
            return $sqlText;

        if (!is_array($parameters))
        {
            $parameters = array($parameters);
        }

        $prepared = '';
        $i = 0;

        while (true)
        {
            $pos = strpos($sqlText, '?');

            if ($pos == false)
            {
                $prepared .= $sqlText;
                break;
            }
            else
            {
                if ($pos > 0)
                {
                    $prepared .= substr($sqlText, 0, $pos);
                }

                if (substr($parameters[$i], 0, 1) == ':')
                {
                    $prepared .= substr($parameters[$i++], 1);
                }
                else
                {
                    $prepared .= is_null($p=$parameters[$i++]) ? 'NULL' : "'" . str_replace("'", "''", $p) . "'";
                    //$prepared .= "'" . addslashes($parameters[$i++]) . "'";
                }

                $sqlText = substr($sqlText, $pos + 1);
            }
        }

        $MIOLO->Assert($i == count($parameters), "SQL PREPARE: Parâmetros inconsistentes! SQL: $sqlText");
        return $prepared;
    }
    
	/**
     * Gera os filtros padrão do sistema a partir das informações dos atributos.
     *  
     * @return String contendo os filtros para o where do sql
     */
    public static function generateFilters($type)
    {
        $where = '';
        foreach ( $type->generateAttributesInfo() as $attribute => $info )
        {
            if ( strlen($type->$attribute)  >  0 )
            {
                switch ( $info->type )
                {
                    case self::TYPE_TEXT:
                        $type->$attribute = str_replace(array("'", '"'), '', $type->$attribute);                                                
                        $where.=" AND {$info->columnName} ILIKE '%{$type->$attribute}%'";
                        break;
                    case self::TYPE_VARCHAR:
                        $type->$attribute = str_replace(array("'", '"'), '', $type->$attribute);
                        $where.=" AND {$info->columnName} ILIKE '%{$type->$attribute}%'";
                        break;
                    case self::TYPE_DATE:
                        $where.=" AND {$info->columnName} = TO_DATE('{$type->$attribute}','" . DB_MASK_DATE . "')";
                        break;
                    case self::TYPE_TIMESTAMP:
                        $where.=" AND {$info->columnName} = TO_DATE('{$type->$attribute}','" . DB_MASK_TIMESTAMP . "')";
                        break;    
                    default:
                        $where.=" AND {$info->columnName} = '{$type->$attribute}'";
                        break;
                }                
            }    
        }
        
        return $where;
    }
    
    /**
     * Executa uma query no banco de dados.
     *  
     * @param String $sql contendo a consulta sql
     * @param Array $attributes os atributos que devem ser subtituídos no sql
     * @return Array
     */
    public static function query($sql, $params=null )
    {
        $business = self::getInstance();
        
        if( ! empty($params) )
        {
            $sql = self::prepare($sql,$params);
        }
        
	return $business->db->query($sql);
    }
    
    /**
     * Executa uma inserção, atualização ou deleção no banco de dados.
     *  
     * @param String $sql contendo o sql
     * @return Boolean
     */
    public static function execute($sql,$params=null)
    {
        $business = self::getInstance();
        
        if( ! empty($params) )
        {
            $sql = self::prepare($sql,$params);
        }
        
        if ($business->db->execute($sql))
        {
            return true;
        }
        else
        {
            throw new Exception($business->db->getError());
        }
    }
    
    /**
     * Busca o proximo valor para a sequencia.
     *  
     * @param String $sql contendo o sequencia
     * @return String
     */
    public static function nextVal($sequence)
    {
        $business = self::getInstance();
        $res = $business->db->query("SELECT nextval('$sequence'::regclass)");
	return $res[0][0];
    }
}
?>
