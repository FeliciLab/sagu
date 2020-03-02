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
 * Class created on 02/10/201
 *
 **/
$MIOLO->getClass( 'gnuteca3', 'GBackup');
$MIOLO->getClass( 'gnuteca3', 'controls/GFileUploader');
class FrmBackup extends GForm
{
    public function __construct()
    {
        $this->setTransaction('gtcBackup');
        parent::__construct( _M('Cópia de segurança', 'gnuteca3' ) );
    }

    public function mainFields()
    {
        $backupG = new GBackup('gnuteca3');
        $backupA = new GBackup('admin');

    	$fields[] = new MDiv('divDescription', _M('Esta interface permite fazer o download da cópia de segurança (backup) do Gnuteca. Arquivo sql compacatado.<br/> Tenha em mente que a geração de uma cópia de segurança pode demorar algum tempo.', $this->module), 'reportDescription');
        $fields[] = new MButton('btnDoBackup',_M('Download do backup da base Gnuteca3', $this->module), GUtil::getAjax('doBackup', 'gnuteca3' ) );

        if ( $backupA->getConf() != $backupA->getConf() )
        {
            $fields[] = new MButton('btnDoBackup2',_M('Download do backup da base Admin', $this->module), GUtil::getAjax('doBackup', 'admin' ) );
        }
        
        $fields[] = new MButton('btnDoBackupFiles',_M("Download de arquivos e capas", 'gnuteca3'), GUtil::getAjax('doBackupFiles') );

        $this->setFields( $fields );
    }

    public function doBackup( $args )
    {
        $backup = new GBackup( $args );
        $ok = $backup->execute();

        if ( $ok )
        {
            GFileUploader::downloadFile( $backup->getRelativeDumpFileName() );
        }
        else
        {
            $this->error( _M('Impossível salvar cópia de segurança, verifique permissões e o programa pg_dump.','gnuteca3' ) );
        }
    }
    
    public function doBackupFiles()
    {
        $busFile = new BusinessGnuteca3BusFile();
        $fileName = $busFile->backup();
        GFileUploader::downloadFile( $fileName );
    }
}
?>