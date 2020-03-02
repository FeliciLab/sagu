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
 * Label Layout handler.
 * Contains the menus to access configuration submenus
 *
 * @author Luiz Gilberto Gregory Filho
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
 *
 * @since
 * Class created on 05/08/2008
 *
 **/
$frm            = "LibraryGroup";
$home           = "main:configuration:libraryGroup";
$closeButton    = "main:configuration";
$navbarTitle    = _M('Grupo de biblioteca', $module);

$img = new MImage('icon', $navbarTitle, GUtil::getImageTheme('libraryGroup-16x16.png') );
//$navbar->addOption( $img->generate() . $navbarTitle, $module, $home, null, array('function' => 'search'));

$ui         = $MIOLO->getUI();
$function   = MIOLO::_REQUEST('function');
$content    = null;

switch ( $function )
{
    case 'insert':
        
    case 'update':            
    
    case 'delete':
    
        $content = $ui->getForm($module, "Frm{$frm}");
        break;
    
    case 'search':            
                                        
    default:
        
        $content = $ui->getForm($module, "Frm{$frm}Search");
        break;
}

if ($content->checkAccess())
{
	$content->setClose($MIOLO->getActionURL($module, $closeButton));
	$theme->clearContent( $content );
	$theme->insertContent( $content );
}
?>