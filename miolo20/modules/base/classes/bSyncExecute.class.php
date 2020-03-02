<?php
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
        bSyncDatabase::runStartScript($syncModule);

        //Sincronizar XMLs
        function_exists('consoleOutput') ? consoleOutput(_M("Aplicando alteracoes de arquivos XML.")) : null;
        bSyncDatabaseContent::syncAllXML($syncModule);

        //Obter arquivos de views e funcoes
        function_exists('consoleOutput') ? consoleOutput(_M("Obtendo arquivos de visoes e funcoes da base de dados.")) : null;
        $files = self::obterArquivos($syncModule);

        //Dropar views e funcoes
        function_exists('consoleOutput') ? consoleOutput(_M("Dropando visoes e funcoes da base de dados.")) : null;
        self::droparViewsEFuncoes($files, $syncModule);
        
        //Rodar sync.php
        function_exists('consoleOutput') ? consoleOutput(_M("Aplicando sync.php.")) : null;
        bSyncDatabase::runSyncScript($syncModule);
        
        //Sincronizar views e funcoes
        function_exists('consoleOutput') ? consoleOutput(_M("Recriando/criando visoes e funcoes da base de dados.")) : null;
        self::sincronizarViewsEFuncoes($files, $syncModule);
    }

    /**
     * Sincronizar views e funcoes da base de dados.
     * 
     * @param array $files
     */
    public static function sincronizarViewsEFuncoes($files, $syncModule)
    {
        foreach ( $files as $dbSql )
        {
            if ( $dbSql[1] == 'v' )
            {
                $view = new bSyncDatabaseView($dbSql[0], $syncModule);
                $view->syncronize();
            }
            elseif ( $dbSql[1] == 'f' )
            {
                $function = new bSyncDatabaseFunction($dbSql[0], $syncModule);
                $function->syncronize();
            }
        }
        
        return true;
    }
    
    /**
     * Dropa todas as views e as funcoes registradas no arquivo de drop.
     * 
     * @param array $files
     * @param string $syncModule
     * 
     * @return boolean
     */
    public static function droparViewsEFuncoes( $files, $syncModule )
    {
        $MIOLO = MIOLO::getInstance();
        
        //Views: todas sao SEMPRE dropadas
        foreach ( $files as $dbSql )
        {
            if ( $dbSql[1] == 'v' )
            {
                $view = new bSyncDatabaseView($dbSql[0], $syncModule);
                $view->drop();
            }
        }
        
        //Funcoes: sao dropadas SOMENTE as funcoes encontradas no arquivo fDROP.sql
        $dropFile = new bSyncDatabaseFunction($MIOLO->getConf('home.miolo') . '/modules/basic/syncdb/functions/fDROP.txt', $syncModule);
        $dropFile->synchronizeDropFile();
        
        return true;
    }
    
    /**
     * Obtem arquivos de views e funcoes da base de dados.
     * 
     * @param string $syncModule
     * 
     * @return array
     */
    public static function obterArquivos($syncModule)
    {
        $functions = BSyncDatabaseFunction::listSyncFiles($syncModule);
        $views = bSyncDatabaseView::listSyncFiles($syncModule);
        
        $fv = array_merge($functions, $views);
        
        $final = array();
        foreach ( $fv as $content )
        {
            $pathInfo = pathinfo($content);
            $fileName = substr($pathInfo['filename'], 1);
            $final[$fileName][0] = $content;
            $final[$fileName][1] = substr($pathInfo['filename'], 0, 1);
        }
        
        ksort($final);
        
        return $final;
    }
}
?>
