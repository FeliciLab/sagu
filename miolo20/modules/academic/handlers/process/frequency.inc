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
 * Pupil frequency handler.
 * Used to allow the professor to register the frequencies for the specified group
 *
 * @author Armando Taffarel Neto [taffarel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Fabiano Tomasini [fabiano@solis.coop.br]
 *
 * @since
 * Class created on 10/09/2007
 *
 **/
$function = MIOLO::_request('function');

$home = 'main:process:frequency';
$navbar->addOption(_M('Registro de frequência', $module), $module, $home, null, array('function' => 'resetStack'));

$form = 'FrmFrequency';

$ui = $MIOLO->getUI();

// Verifica se tem a permissão 'Digitação de notas e frequência'. Se não tiver, verifica a 'Registro de frequência'. Ticket #34507.
if ( !$MIOLO->checkAccess('FrmGradesTyping', A_ACCESS) )
{
    $MIOLO->checkAccess($form, A_ACCESS, true, true);
}
$content = $ui->getForm($module, $form);
$content->setClose($MIOLO->getActionURL($module, substr($action, 0, strrpos($action, ':'))));

if ( $theme->page->generateMethod != 'generateAJAX' )
{
    $theme->clearContent();
    $theme->insertContent($content);
}
?>