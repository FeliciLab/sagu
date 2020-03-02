<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
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
 * @author Lucas Rodrigo Gerhardt [lucas_gerhardt@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Lucas Rodrigo Gerhardt [lucas_gerhardt@solis.coop.br]
 * 
 * @since
 * Class created on 10/07/2015
 *
 **/

$MIOLO->uses( 'classes/bSync.interface.php','base');
$MIOLO->uses( 'classes/bSyncDatabase.class.php','base');
$MIOLO->uses( 'classes/bSyncDatabaseContent.class.php','base');
$MIOLO->uses( 'classes/bSyncDatabaseFunction.class.php','base');
$MIOLO->uses( 'classes/bSyncDatabaseView.class.php','base');
$MIOLO->uses( 'classes/BString.class.php','base');
$MIOLO->uses( 'classes/bBaseDeDados.class.php','base');
$MIOLO->uses( 'classes/bCatalogo.class.php','base');

class bSyncExecute
{
    public static function executeSync( $syncModule )
    {
        //Definicao para base de dados funcionar adequadamente utilizando o modulo correto
        if ( !defined('DB_NAME') )
        {
           define('DB_NAME', $syncModule);
        }
        
        //Rodar start.php
        function_exists('consoleOutput') ? consoleOutput(_M("Aplicando start.php.")) : null;
        self::runStartScript($syncModule);
        
        //Sincronizar XMLs
        function_exists('consoleOutput') ? consoleOutput(_M("Aplicando alteracoes de arquivos XML.")) : null;
        self::syncAllXML($syncModule);
        
        //Sincronizar funcoes
        function_exists('consoleOutput') ? consoleOutput(_M("Aplicando alteracoes de funcoes na base de dados.")) : null;
        self::syncAllFunctions($syncModule);
        
        //Sincronizar views
        function_exists('consoleOutput') ? consoleOutput(_M("Aplicando alteracoes de visoes na base de dados.")) : null;
        self::syncAllViews($syncModule);
        
        //Rodar sync.php
        function_exists('consoleOutput') ? consoleOutput(_M("Aplicando sync.php.")) : null;
        self::runSyncScript($syncModule);
        
    }
    
    /**
     * 
     * @param string $syncModule
     */
    public static function runStartScript($syncModule)
    {
        $MIOLO = MIOLO::getInstance();
        
        //executa o script de inicializacao
        $startScript = $MIOLO->getConf('home.miolo').'/modules/'.$syncModule.'/syncdb/start.php';

        if ( file_exists( $startScript ) )
        {
            require $startScript;
        }
    }
    
    /**
     * Faz a sincronização de todos os arquivos XML da pasta syncdb
     * 
     * @param type $syncModule
     * @return type
     */
    public static function syncAllXML($syncModule)
    {
        $MIOLO = MIOLO::getInstance();
        $ignoreXmls = array();
   
        //obtem lista de arquivos xml a sincronizar
        $files = BSyncDatabaseContent::listSyncFiles( $syncModule );

        if ( is_array( $files ) )
        {
            foreach ( $files as $line => $file )
            {
                $tableExtra = null;
                $resultA = null;

                $fileBase = str_replace('.xml', '', basename($file));

                if ( !in_array(strtolower($fileBase), $ignoreXmls) )
                {
                    $basConfig = new BSyncDatabaseContent( );
                    $basConfig->setXmlPath($file);
                    $basConfig->setModule( $syncModule );

                    if ( strpos($file, 'miolo_') )
                    {
                        $basConfig->setModule( 'admin' );
                    }
                    else
                    {
                        $basConfig->setModule( $syncModule );
                    }

                    $result = $basConfig->syncronize();

                   if ( $fileBase == '00-changes' )
                   {
                       $versao = file_get_contents($MIOLO->getModulePath($syncModule, "VERSION"));
                       bBaseDeDados::consultar("SELECT syncDataBase("  . (int)str_replace('.','',$versao) . ")");
                   }

                   if ( $fileBase == '00-ignorexml' )
                   {
                        if ( bCatalogo::verificarExistenciaDaTabela(NULL, 'ignorexml', $syncModule) )
                        {
                           $xmlFiles = bBaseDeDados::consultar('SELECT lower(xmlname) FROM ignorexml');

                            if ( is_array($xmlFiles) )
                            {
                                foreach ( $xmlFiles as $file )
                                {
                                    $ignoreXmls[] = str_replace('.xml', '', $file[0]);
                                }
                            }
                        }
                    } 
                }
            }
        }
    }
    
    /**
     * Faz a sincronização das funções de base do arquivo functions.sql
     * 
     * @param string $syncModule
     */
    public static function syncAllFunctions($syncModule)
    {
        $functionFiles = BSyncDatabaseFunction::listSyncFiles( $syncModule );

        if ( is_array( $functionFiles ) ) 
        {
            foreach ( $functionFiles as $line => $function )
            {
                $function = new BSyncDatabaseFunction( $function , $syncModule );
                $fResult = $function->syncronize();
            }
        }
    }
    
    /**
     * Faz a sincronização das views de base do arquivo views.sql
     * 
     * @param string $syncModule
     */
    public static function syncAllViews($syncModule)
    {
        $views = BSyncDatabaseView::listSyncFiles( $syncModule );

        if ( is_array( $views ) ) 
        {
            foreach ( $views as $line => $view )
            {
                $view = new BSyncDatabaseView( $view, $syncModule );
                $vResult = $view->syncronize();
            }
        }
    }
    
    /**
     * Caso exista script de sincronização, executa-o
     * 
     * @param string $syncModule
     */
    public static function runSyncScript($syncModule)
    {
        $MIOLO = MIOLO::getInstance();
        $syncScript = $MIOLO->getConf('home.miolo').'/modules/'.$syncModule.'/syncdb/sync.php';

        if ( file_exists( $syncScript ) )
        {
            include $syncScript;
        }
    }


}

?>