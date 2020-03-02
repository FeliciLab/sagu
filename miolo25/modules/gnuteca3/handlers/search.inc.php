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
 * Configuration handler.
 * Contains the menus to access configuration submenus
 *
 * @author Jamiel Spezia
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 26/01/2007
 *
 **/
$module ='gnuteca3';

//$img = new MImage('icon', _M('BUSCAR', $module), GUtil::getImageTheme('search-16x16.png'));
//$navbar->addOption($img->generate()._M('BUSCAR', $module), $module, 'main:search', null, array('function'=>'resetStack'));

$theme->clearContent();
$theme->insertContent(  $MIOLO->getUI()->getForm('gnuteca3', "FrmSimpleSearch") );
createBreadCrumb();

//Esconde a navbar quando estiver na pesquisa e nao houver operador logado (ticket #5376)
if (!GOperator::isLogged())
{
    $navbar->clear();
}
?>