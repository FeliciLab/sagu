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
 * Group handler.
 * Contains the menus to access group registers
 *
 * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Class created on 22/09/2005
 *
 **/

$MIOLO->trace( 'file:' . $_SERVER['SCRIPT_NAME'] );

$home = 'main:process:group';
$navbar->addOption( _M('Disciplina oferecida', $module ), $module, $home );

$MIOLO->getClass( 'basic', 'access' );
access::insert(_M('Disciplina oferecida', $module), $home, 'group-16x16.png' );
$data->home = $home;

$function = MIOLO::_request( 'function' );

$ui = $MIOLO->getUI();
if ( (strlen( $function ) == 0) || ($function == 'search') )
{
    $MIOLO->checkAccess( 'FrmGroupAcademic', A_ACCESS, true, true );
    
    $frmGroupSearch = $ui->getForm( $module, 'FrmGroupSearch', $data );
    $frmGroupSearch->setClose( $MIOLO->getActionURL( $module, substr( $home, 0, strrpos( $home, ':' ) ) ) );
    
    $content = $frmGroupSearch;
}
else
{
    if( strlen(MIOLO::_request( 'event' )) > 0 )
    {
        $MIOLO->checkAccess( 'FrmGroupAcademic', A_EXECUTE, true, true );
    }
    
    $frmGroup = $ui->getForm( $module, 'FrmGroup' );
    
    $content = $frmGroup;
}

if ( $theme->page->generateMethod != 'generateAJAX' )
{
    $theme->clearContent();
    $theme->insertContent( $content );
}

?>
