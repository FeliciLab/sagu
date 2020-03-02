<?php
 /**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 * 
 * Este arquivo é parte do programa Sagu.
 * 
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * dbdownload handler.
 * Contains the menus to access turn registers
 *
 * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 *
 * @since
 * Class created on 30/08/2007
 *
 **/

$MIOLO->checkAccess('BASIC', A_ADMIN, true, true);

$dbName = $MIOLO->getConf('db.basic.name');
$dbUser = $MIOLO->getConf('db.basic.user');
$dbHost = $MIOLO->getConf('db.basic.host');
$dbPort = $MIOLO->getConf('db.basic.port');
$dbPass = $MIOLO->getConf('db.basic.password');

$bin = SAGU::getParameter('BASIC', 'POSTGRESQL_BASE_PATH') . '/bin/pg_dump';
// Older versions of PHP doesn't have sys_get_temp_dir, so
// this check is necessary here.
if ( function_exists('sys_get_temp_dir') )
{
    $tmpDir = sys_get_temp_dir();
}
else
{
    $tmpDir = '/tmp';
}
$tmpFile = 'dump-' . date('YmdHis') . '.sql.gz';
putenv("PGPASSWORD=$dbPass");
$pg_dump = "$bin $dbName -h $dbHost -p $dbPort -U$dbUser -f $tmpDir/$tmpFile -Z 9 -E UTF-8";
exec($pg_dump, $retData, $retVal);
if ( $retVal != 0 )
{
    $MIOLO->error($pg_dump . ': ' . $retVal);
}
else
{
    header('Content-Type: application/x-compressed-tar');
    header('Content-Length: ' . filesize("$tmpDir/$tmpFile"));
    header('Content-disposition: inline; filename="db' . $tmpFile . '"');
    //passthru("cat $tmpDir/$tmpFile");    
    //exec("rm $tmpDir/$tmpFile", $retData, $retVal);
    readfile("$tmpDir/$tmpFile");
    unlink("$tmpDir/$tmpFile");
}

exit;

?>
