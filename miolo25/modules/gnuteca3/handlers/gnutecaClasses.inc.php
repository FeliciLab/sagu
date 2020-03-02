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
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 *
 * @since
 * Class created on 06/11/2008
 *
 **/

//caso especial onde a função não exista e não seja possível instalar o pacote do php
if ( !function_exists('mime_content_type') )
{
    function mime_content_type($filename)
    {
        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));

        if (array_key_exists($ext, $mime_types))
        {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open'))
        {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else
        {
            return 'application/octet-stream';
        }
    }
}

$MIOLO = MIOLO::getInstance();
//$autoload->setFile('GDate',$MIOLO->getModulePath('basic', 'classes/GDate.class.php'));
$MIOLO->getClass( 'gnuteca3', 'GBusiness');
$MIOLO->getClass( 'gnuteca3', 'GString');
$MIOLO->getClass( 'gnuteca3', 'GOperator');
$MIOLO->getClass( 'gnuteca3', 'GForm');
$MIOLO->getClass( 'gnuteca3', 'controls/GContainer');
$MIOLO->getClass( 'gnuteca3', 'controls/GPrompt');
$MIOLO->getClass( 'gnuteca3', 'controls/GRadioButtonGroup');
$MIOLO->getClass( 'gnuteca3', 'controls/GGridActionIcon');
$MIOLO->getClass( 'gnuteca3', 'controls/GGrid');
$MIOLO->getClass( 'gnuteca3', 'controls/GAddChildGrid');
$MIOLO->getClass( 'gnuteca3', 'controls/GSearchGrid');
$MIOLO->getClass( 'gnuteca3', 'GMessages');
$MIOLO->getClass( 'gnuteca3', 'GMail');
$MIOLO->getClass( 'gnuteca3', 'GSendMail');
$MIOLO->getClass( 'gnuteca3', 'GOperation');
$MIOLO->getClass( 'gnuteca3', 'GUtil');
$MIOLO->getClass( 'gnuteca3', 'GFunction');
$MIOLO->getClass( 'gnuteca3', 'GPerms');
$MIOLO->getClass( 'gnuteca3', 'controls/GSelection');
$MIOLO->getClass( 'gnuteca3', 'GValidators');
$MIOLO->getClass( 'gnuteca3', 'controls/GUserMenu');
$MIOLO->getClass( 'gnuteca3', 'controls/GStatusBar');
$MIOLO->getClass( 'gnuteca3', 'controls/GWidget');
$MIOLO->getClass( 'gnuteca3', 'controls/GSubForm');
$MIOLO->getClass( 'gnuteca3', 'GDate');
$MIOLO->getClass( 'gnuteca3', 'controls/GToolBar');
$MIOLO->getClass( 'gnuteca3', 'controls/GMainMenu');
$MIOLO->getClass( 'gnuteca3', 'controls/GTabControl');
$MIOLO->getClass( 'gnuteca3', 'controls/GRealLinkButton');
$MIOLO->getClass( 'gnuteca3', 'controls/GRepetitiveField');
$MIOLO->getClass( 'gnuteca3', 'controls/GLookupField');
$MIOLO->getClass( 'gnuteca3', 'controls/GPersonLookup');
$MIOLO->getClass( 'gnuteca3', 'GServer');
$MIOLO->uses('classes/controls/GEditor.class.php', 'gnuteca3');
$MIOLO->uses( "/db/BusFile.class.php", 'gnuteca3');
$MIOLO->uses( "/db/BusDomain.class.php", 'gnuteca3');

?>
