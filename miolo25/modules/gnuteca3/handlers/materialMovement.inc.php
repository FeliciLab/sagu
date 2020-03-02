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
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 01/09/2008
 *
 **/

//FIXME por isto no materialMoviment.inc.php
$path = str_replace('handlers', '', dirname(__FILE__) );
include_once($path . "forms/FrmSimpleSearch.class.php");
include_once($path . "forms/FrmMaterialCirculationLoan.class.php");
include_once($path . "forms/FrmMaterialCirculationReserve.class.php");
include_once($path . "forms/FrmMaterialCirculationUserHistory.class.php");
include_once($path . "forms/FrmMaterialCirculationChangeStatus.class.php");
include_once($path . "forms/FrmMaterialCirculationChangePassword.class.php");

$content = $MIOLO->getUI()->getForm($module, 'FrmMaterialMovement', $data);

if (GPerms::checkAccess('gtcMaterialMovement'))
{
	$theme->setContent($content);
        createBreadCrumb();
}
else
{
    $loginUrl = $MIOLO->getConf('home.url').'/index.php?module=gnuteca3&action=main:login' ;
    $page->redirect( $loginUrl );
}
?>