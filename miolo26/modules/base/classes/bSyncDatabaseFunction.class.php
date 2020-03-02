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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * 
 * @since
 * Class created on 06/10/2010
 *
 **/
class bSyncDatabaseFunction implements bSync
{
    protected $file;
    protected $syncModule;
    
    public function __construct( $file , $module )
    {
        if ( !$file )
        {
            throw new Exception( _M('É necessário informar um arquivo para sincronização de funções.') );
        }
        
        $this->file = $file;
        
        if ( !$module )
        {
            throw new Exception( _M('É necessário informar um modulo para sincronização de funções.') );
        }
        
        $this->module =$module;
        
        //cria função padrão drop_function_if_exists
        $this->createDropFunction();
    }
    
    public function syncronize()
    {
        $content = file_get_contents ( $this->file );
        
        if ( ! $content )
        {
            return false;
        }
        
        $fileFunctions = $this->getSqlFunctions($content);
        $dbFunctions = bCatalogo::listarFuncoes();
        $result->start = count( $dbFunctions ) -1; //desconsidera drop functions
        $result->file = count( $fileFunctions );
        
        //make the real syncronization
        $sqlCommands = explode('CREATE OR REPLACE ', $content );
        
        foreach ( $sqlCommands as $line => $sql )
        {
            //necessidade para rodar as funções e mostrar o erro corretamente
            if ( $sql )
            {
                $sql = 'CREATE OR REPLACE ' . $sql;
                bBaseDeDados::executar( $sql );
            }
        }
        
        $finalDbFunctions = bCatalogo::listarFuncoes();
        
        $result->final = count( $finalDbFunctions ) -1 ; //desconsidera drop functions
        
        //alinha funções por nome para fácil localização
        foreach ( $fileFunctions as $line => $info )
        {
            $functionsF[ $info->function ] = $info->params ? $info->params :  ' ';
        }
        
        $sqlResult ='';
        
        foreach ( $finalDbFunctions as $line => $info )
        {
            //registra sql para geração de funções faltantes
            if ( !$functionsF[ $info->function ]  )
            {
                $functions[ $info->function ][0] = $info->function;
                
                //funções "sobresalentes"
                if ( $info->function != 'plpgsql_call_handler'
                     && $info->function != 'plpgsql_validator'
                     && $info->function != 'drop_function_if_exists'
                   )
                {
                    $source = bCatalogo::obterCodigoFonteDaFuncao( $info->function );
                    $source = $source[0][0];
                    $sqlResult .= $source ."\n";
                }
            }
        }
        
        //funções que vem "sobrando" no select
        unset($functions['plpgsql_call_handler']);
        unset($functions['plpgsql_validator']);
        //função criada pela própria classe
        unset($functions['drop_function_if_exists']);
        
        
        $result->missing = $functions;
        $result->sql = $sqlResult;
       
        return $result;
    }
    
    /**
     * Faz parser do arquivo sql obtendo a listagem de funções
     * 
     * @param type $content conteúdo do arquivo sql
     * @return array of stdClass
     * 
     */
    protected function getSqlFunctions( $content )
    {
        $regexp = "/CREATE OR REPLACE FUNCTION (.*)\((.*)\)/";
        
        preg_match_all($regexp, $content, $matches);
        
        $functions = $matches[1];
        $parameters = $matches[2];
        
        foreach ( $functions as $line => $info)
        {
            $obj = new stdClass();
            $obj->function = strtolower( $info );
            $obj->params = $parameters[$line];
            $result[] = $obj;
        }
        
        return $result;
    }
    
    /**
     * Retorna um array com os arquivos de sincronização de base do módulo informado.
     * @param string $module
     * @return array 
     */
    public static function listSyncFiles($module)
    {
        $MIOLO = MIOLO::getInstance();
        $path = $MIOLO->getConf('home.miolo').'/modules/'.$module.'/syncdb/functions.sql';
       
        return glob($path);
    }

    /**
     * Cria uma função padrão no banco de dados que serve para drop funções somente
     * se elas existem
     */
    public function createDropFunction()
    {
        $sql ="
            CREATE OR REPLACE FUNCTION drop_function_if_exists( name varchar )
            RETURNS void as \$BODY\$
            DECLARE
            v_sql varchar;
            BEGIN
                FOR v_sql IN SELECT 'DROP FUNCTION '|| proname || '(' ||
                    (
                    SELECT array_to_string( array_agg(coalesce(varname,'') || ' ' ||
                                        ( SELECT coalesce(typname,'') 
                                            FROM pg_type
                                            WHERE oid::varchar = type::varchar 
                                        ) ) , ' , ' )
                                        FROM (    SELECT  unnest( coalesce(proargnames,ARRAY[''] ) ) as varname,
                                                        regexp_split_to_table( proargtypes::varchar , E'\\ +') as type
                                                FROM  pg_proc A
                                                WHERE lower(A.proname) = lower(name)
                                                and pg_proc.oid = oid                                               
                                        ) as foo ) || ');'                                              
            FROM pg_proc                                                                                             
            WHERE lower(proname) = lower( name )
            LOOP
                    EXECUTE v_sql;
                END LOOP;
            END
            \$BODY\$
            LANGUAGE 'plpgsql';


            CREATE OR REPLACE FUNCTION drop_function_if_exists( name varchar, param varchar )
            RETURNS void as \$BODY\$
            BEGIN
                PERFORM drop_function_if_exists(lower(name));
            END
            \$BODY\$
            LANGUAGE 'plpgsql';
        ";
        
        bBaseDeDados::executar( $sql );
    }
   
}
?>
