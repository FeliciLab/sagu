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
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 02/10/2011
 */
class GBackup
{
    private $database;

    public function __construct( $database = 'gnuteca3' )
    {
        $this->setDatabase($database);
    }

    /**
     * Retorna um objeto com as configurações do miolo.conf da base especifica
     *
     * @param string $database
     * @return stdClass
     */
    public function getConf( )
    {
        $MIOLO = MIOLO::getInstance();
        $conf = new stdClass();
        $database = $this->database;

        $conf->system = $MIOLO->getConf( "db.{$database}.system" );
        $conf->host = $MIOLO->getConf( "db.{$database}.host" );
        $conf->port = $MIOLO->getConf( "db.{$database}.port" );
        $conf->name = $MIOLO->getConf( "db.{$database}.name" );
        $conf->user = $MIOLO->getConf( "db.{$database}.user" );
        $conf->pass = $MIOLO->getConf( "db.{$database}.password" );

        return $conf;
    }

    public function execute()
    {
        $conf = $this->getConf();

        if ( strtolower( $conf->system ) != 'postgres' )
        {
            throw new Exception( _M('Somente é suportado cópia de segurança de sistemas Postgres','gnuteca3') );
        }

        $filename = $this->getDumpFileName();

        $exec = "export PGPASSWORD={$conf->pass}; pg_dump -U{$conf->user} -h{$conf->host} -p{$conf->port} {$conf->name} -f {$filename} -Z 9";
         
        $result = shell_exec($exec);

        if ( !file_exists($filename) )
        {
            throw new Exception( _M('Impossível salvar arquivo @1.' , 'gnuteca3',$filename ) );
        }

        return file_exists($filename);
    }

    /**
     * Retorna o caminho absoluto do arquivo de backup
     *
     * @return string
     */
    public function getDumpFileName()
    {
        return BusinessGnuteca3BusFile::getAbsoluteFilePath( 'tmp', 'dump'.$this->database, 'sql.gz');
    }

    /**
     * Retorna o caminho relativo do arquivo de backup
     *
     * @return string
     */
    public function getRelativeDumpFileName()
    {
        return 'tmp/dump'.$this->database. '.sql.gz';
    }

    /**
     * Define o nome da configuração da base de dados
     *
     * @param string $database
     */
    public function setDatabase( $database )
    {
        $this->database = $database;
    }

    /**
     * Obtem qual o nome da configuração da base
     *
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }
}
?>