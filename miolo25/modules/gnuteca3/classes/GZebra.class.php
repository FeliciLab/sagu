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
 * Classe para lidar com servidor Zebra, Z3950
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
 * Class created on 14/09/2011
 *
 **/
class GZebra
{
    protected $options;
    
    /**
     * Retorna o conteúdo do arquivo de log
     * 
     * @return GString 
     */
    public function getLog()
    {
        return new GString( file_get_contents( $this->getLogPath() ) );
    }
    
    /**
     * Retorna o caminho do arquivo de log
     */
    public function getLogPath()
    {
        $MIOLO = MIOLO::getInstance();
        $log = $MIOLO->getConf('home.logs');
        return $log.'/zebrasrv.log';
    }
    
    /**
     * Retorna a data da última operação do servidor
     */
    public function getLastLogDate()
    {
        $log = $this->getLog();
        
        //array_filter tira linhas em branco        
        $log = array_filter( $log->explode("\n") ); 
        
        //pega última linha
        $lastLine = $log[ count($log)-1 ];

        //obtem a data formatada
        $date = substr($lastLine,9,5) . '/'. date('Y') .' ' . substr($lastLine,0,8);
        $date = new GDate( $date );
        
        return $date;
    }
    
    public function isRunning()
    {
        $lastDate = $this->getLastLogDate();
        
        return  $lastDate == GDate::now();        
    }
    
    /**
     * Inicia o servidor zebra
     * FIXME não funciona
     * @deprecated
     */
    public function startServer()
    {
        if ( $this->isRunning() )
        {
            return false;            
        }
        
        $result = shell_exec( $this->getServerStartCommand() );
        
        return $result;
    }
    
    /**
     * Retorna o commando de iniciação do servidor
     * 
     * @return string
     */
    public function getServerStartCommand()
    {
        //-D daemon, segundo plano
        //-c arquivo de configuração
        //-l log em arquivo
        //-w define o caminho onde a base de dados ficará guardada
        //& faz o executar persistir
        
        $confPath = BusinessGnuteca3BusFile::getAbsoluteFilePath('zebra','zebra.cfg');
        $host = Z3950_SERVER_URL;
        $databaseFolder = BusinessGnuteca3BusFile::getAbsoluteFilePath('zebra');
        $logPath = $this->getLogPath();
        return "zebrasrv {$host} -D -c '{$confPath}' -l '{$logPath}' -w '{$databaseFolder}'";
    }
    
    /**
     * Remove os arquivos da base de dados do servidor Zebra
     * 
     * @return boolean
     */
    public function deleteDatabase()
    {
        $MIOLO = MIOLO::getInstance();
        $busFile = $MIOLO->getBusiness('gnuteca3','BusFile');
        $busFile->folder= 'zebra';
        $files = $busFile->searchFile(true);
        
        if ( is_array( $files ) )
        {
            foreach ( $files as $line => $file )
            {
                if ( ! ( $file->basename == "zebra.cfg" || $file->basename == "passwordfile") )
                {
                    if ( is_writable( $file->absolute ) )
                    {
                        unlink( $file->absolute );
                    }
                    else
                    {
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
}
?>