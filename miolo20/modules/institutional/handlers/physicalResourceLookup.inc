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
 * Basic physical person lookup handler.
 * Contains the menus to access city registers
 *
 @author Nataniel Ingor da Silva [nataniel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Nataniel Ingor da Silva [nataniel@solis.coop.br]
 *
 * @since
 * Class created on 17/09/2013
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
//$MIOLO->checkAccess('FrmPhysicalPerson', A_INSERT, true, true);

$home = 'main:physicalResourceLookup';

$data->home        = $home;
$ui                = $MIOLO->getUI();
$frmPhysicalResourceLookup = $ui->getForm( $module, 'FrmPhysicalResourceLookup' );

$theme->insertContent($frmPhysicalResourceLookup);
?>
