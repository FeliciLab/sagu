<?php

/**
 * <--- Copyright 2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Base.
 *
 * O Base é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Classe manipuladora da base de dados.
 *          
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @since
 * Class created on 25/06/2012
 */
class bBaseDeDados
{
    /**
     * @var MBusiness Instância do objeto MBusiness.
     */
    protected static $instancia = NULL; 
    
    /**
     * @var string Última instrução SQL executada no banco de dados. 
     */
    protected static $lastQuery;

    /**
     * Método estático para obter a instância do objeto MBusiness.
     * 
     * @return MBusiness Instância do MBusiness.
     */
    public static function obterInstancia($base = DB_NAME)
    {
        // Obtém a instância quando não tiver sido definido, ou quando a base definida é diferente da passada por parâmetro.
        if ( is_null(self::$instancia) || self::$instancia->_database != $base )
        {
            if ( $base == NULL )
            {
                $base = DB_NAME;
            }
            
            self::$instancia = new MBusiness($base);
        }

        return self::$instancia;
    }

    /**
     * Executa uma consulta SQL no banco.
     *
     * @param string $sql Consulta a ser executada na base de dados.
     * @return array de array Com resultado da consulta.
     */
    public static function consultar($sql, $base = NULL)
    {
        // Não executa função caso não exista a instrução.
        if ( !$sql )
        {
            return FALSE;
        }

        // FIXME: remover e ajustar código para chamar a função consultarBloco.
        if ( is_array($sql) )
        {
            $sql = implode(";\n", $sql);
        }

        // Converte string da instrução SQL para codificação certa.
        $sql = BString::construct($sql)->__toString();

        // Guarda última consultar para registro de erros.
        self::$lastQuery = $sql;
       
        // Executa a instrução na base de dados.
        $resultado = self::obterInstancia($base)->_db->query($sql);
        
        // Mantém compatibilidade com MIOLO 2.
        if ( $resultado instanceof PostgresQuery )
        {
            return $resultado->result;
        }
        else
        {
            return $resultado;
        }
    }

    /**
     * Executa uma consulta SQL no banco.
     *
     * @param array $sql Vetor de consultas a serem executada na base de dados.
     * @return array de array Com resultado da consulta.
     */
    public static function consultarBloco(array $sqls, $base = NULL)
    {
        // Converte array de SQL's em uma string.
        if ( is_array($sql) )
        {
            $sql = implode(";\n", $sql);
        }

        // Converte string da instrução SQL para codificação certa.
        $sql = BString::construct($sql)->__toString();

        // Guarda última consulta para registro de erros.
        self::$lastQuery = $sql;

        // Executa a instrução na base de dados.
        $resultado = self::obterInstancia($base)->_db->query($sql);

        // Mantém compatibilidade com MIOLO 2.
        if ( $resultado instanceof PostgresQuery )
        {
            return $resultado->result;
        }
        else
        {
            return $resultado;
        }
    }

    /**
     * Executa uma instrucao SQL no banco.
     *
     * @param string $sql Instrução SQL a ser executada.
     * @return boolean Retorna positivo caso for executado com sucesso.
     */
    public static function executar($sql, $base = NULL)
    {
        // Não executa método quando não existe SQL.
        if ( !strlen($sql) )
        {
            return FALSE;
        }
        
        // Converte string da instrução SQL para codificação certa.
        $sql = BString::construct($sql)->__toString();
        self::$lastQuery = $sql;

        return self::obterInstancia($base)->_db->execute($sql);
    }
    
    /**
     * Executa um bloco de instru��es SQL no banco.
     *
     * @param array $sql Vetor com instru��es SQL a serem executadas.
     * @return boolean Retorna positivo caso for executado com sucesso.
     */
    public static function executarBloco(array $sqls, $base = NULL)
    {
        // Converte array em string para converter.
        //$sqls = implode("\n", $sqls);
        
        // Converte string da instru��o SQL para codifica��o certa.
        //$sqls = BString::construct($sqls)->__toString();
        //self::$lastQuery = $sql;
        
        foreach ( $sqls as $sql )
        {
            self::executar($sql, $base);
        }
        
       // $sqls = explode("\n", $sqls);
        //return self::obterInstancia($base)->_db->execute($sql);
    }
    
    /**
     * Método estático para efetuar inclusão na base de dados.
     * 
     * @param string $sql Instrução SQL a ser executada.
     * @return array Valores inseridos na base de dados.
     */
    public static function inserir( $sql, $base = NULL)
    {
        $sql .= ' RETURNING *';
       
        // Converte string da instrução SQL para codificação certa.
        $sql = BString::construct($sql)->__toString();
        self::$lastQuery = $sql;
        
        $retorno = self::obterInstancia($base)->_db->query($sql);
        
        return $retorno->result[0];
    }

    /**
     * Obtem último erro de banco.
     * 
     * @return string Mensagem de erro. 
     */
    public static function obterUltimoErro()
    {
        return pg_last_error();
    }

    /**
     * Obtem último SQL realizado.
     * 
     * @return string Última instrução SQL executada no banco. 
     */
    public static function obterUltimaInstrucao()
    {
        return self::$lastQuery;
    }

    /**
     * Método estático para iniciar uma transação na base de dados.
     */
    public static function iniciarTransacao()
    {
        self::executar('BEGIN');
    }

    /**
     * Método estático para finalizar a transação atual na base de dados.
     */
    public static function finalizarTransacao()
    {
        self::executar('COMMIT');
    }

    /**
     * Método estático para reverter a transação atual na base de dados.
     */
    public static function reverterTransacao()
    {
        self::executar('ROLLBACK');
    }
    
    /**
     * Obtem o ultimo ID inserido baseando-se no nome da tabela passado.
     * 
     *
     * @param string $tableName
     * @return int
     */
    public static function obterUltimoIdInserido($tableName)
    {
        $max = null;
        $pkey = self::obterChavePrimaria($tableName);
        
        if ( strlen($pkey) > 0 )
        {
            $sql = "SELECT MAX({$pkey}) FROM {$tableName}";
            $msql = new MSQL();
            $msql->createFrom($sql);
            
            $result = self::consultar($msql);
            $max = $result[0][0];
        }
        
        return $max;
    }
    
    /**
     * Retorna o tipo e o nome das colunas chave primarias da tabela passada.
     *
     * @param string $schema $tableName 
     * @return array Ex.: contractid => integer 
     */
    public static function obterChavePrimaria($schema = 'public', $tableName)
    {
        $tableName = $schema ? $schema . '.' . $tableName : $tableName;
        
        try
        {
            $sql = "SELECT pg_attribute.attname, 
                           format_type(pg_attribute.atttypid, pg_attribute.atttypmod) 
                      FROM pg_index, pg_class, pg_attribute 
                     WHERE pg_class.oid = '{$tableName}'::regclass 
                       AND indrelid = pg_class.oid 
                       AND pg_attribute.attrelid = pg_class.oid 
                       AND pg_attribute.attnum = any(pg_index.indkey)
                       AND indisprimary";

            $result = SDatabase::query($sql);
            
            $resultColunas = array();
            foreach ( $result as $coluna )
            {
                $resultColunas[$coluna[0]] = $coluna[1];
            }

            return $resultColunas;
        }
        catch ( Exception $ex )
        {
            return null;
        }
    }
}

?>
