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
        
        if ( !is_array($file) )
        {
            $this->file[] = $file;
        }
        else
        {
            $this->file = $file;
        }
        
        if ( !$module )
        {
            throw new Exception( _M('É necessário informar um modulo para sincronização de funções.') );
        }
        
        $this->module = $module;
        
        //cria função padrão drop_function_if_exists
        $this->createDropFunction();
    }
    
    /**
     * Dropa as fun��es do arquivo de drop.
     */
    public function synchronizeDropFile()
    {
        //Concacetena o conte�do de todos os arquivos de sincronizacao em uma String
        $content = $this->concatenarArquivos();
        if ( ! $content )
        {
            return false;
        }
        
        $drops = explode(PHP_EOL, $content);
        
        foreach ( $drops as $f )
        {
            $sql = "SELECT * FROM DROP_FUNCTION_IF_EXISTS('" . $f . "');";
            bBaseDeDados::consultar($sql);
        }
        
        return true;
    }
    
    
    /**
     * Funcao principal de sincronizacao das funcoes da base de dados.
     * 
     * @return boolean  
     */
    public function syncronize()
    {
        $content = $this->concatenarArquivos();
        if ( ! $content )
        {
            return false;
        }
        
        bBaseDeDados::consultar($content);

        return true;
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
        $regexp = "/CREATE OR REPLACE FUNCTION ([^\)]*)\(([^\)]*)\)/";
        
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

        $caminho = $MIOLO->getConf('home.miolo').'/modules/'.$module.'/syncdb/functions/';
        $pasta = opendir($caminho);
        
        $files = array();
        while ( false !== ($filename = readdir($pasta)) ) 
        {
            if ( pathinfo($filename, PATHINFO_EXTENSION) == 'sql')
            {
                $files[] = $caminho . $filename;
            }
        }
        
        sort($files);
        
        return $files;
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
                FOR v_sql IN  SELECT 'DROP ' || (CASE WHEN p.proisagg 
                                                     THEN 
                                                         'AGGREGATE ' 
                                                     ELSE 
                                                         'FUNCTION ' 
                                                END) || quote_ident(n.nspname) || '.' || quote_ident(p.proname) || '(' 
                                                     || pg_catalog.pg_get_function_identity_arguments(p.oid) || ') CASCADE;' AS stmt
                                FROM   pg_catalog.pg_proc p
                                JOIN   pg_catalog.pg_namespace n ON n.oid = p.pronamespace
                               WHERE  p.proname ILIKE name
                               ORDER  BY 1
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
        
        bBaseDeDados::consultar( $sql );
    }
   
    /**
     * @return array
     */
    public static function syncAllFunctions($syncModule)
    {
        $files  = BSyncDatabaseFunction::listSyncFiles( $syncModule );
        
        $function = new BSyncDatabaseFunction( $files , $syncModule );
        $result = $function->syncronize();
        
        return $result;
    }
    
    /**
     * Retorna o conte�do de todos os arquivos da classe concatenados.
     * 
     * @return string
     */
    public function concatenarArquivos()
    {
        $content = '';
        foreach ( $this->file as $arquivo ) 
        {
            $content = $content . file_get_contents($arquivo);
        }
        
        return $content;
    }
}
?>
