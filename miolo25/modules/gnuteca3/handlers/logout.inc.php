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
 * Class
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 **/
if (MIOLO::_REQUEST('loginType') == LOGIN_TYPE_USER || MIOLO::_REQUEST('loginType') == LOGIN_TYPE_USER_AJAX)
{
    $MIOLO->getBusiness($module, 'BusAuthenticate');
    BusinessGnuteca3BusAuthenticate::logoff();
    $newURL = $MIOLO->getConf('home.url').'/index.php?module=gnuteca3&action=main:search:simpleSearch';
}
else
{
    $MIOLO->getAuth()->logout();
    unset($_SESSION);
    $url = 'main:login';
    // redirect to common environment
    $args['return_to'] = MIOLO::_REQUEST('return_to');
    $args['redirect_action'] = MIOLO::_REQUEST('redirect_action');
    $args['loginType'] = MIOLO::_REQUEST('loginType');
    $newURL = $MIOLO->getActionURL( $module, $url, null, $args);
}

$page->onload("window.location = '$newURL';"); //força atualizção da url*/
?>