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
 * Pupil services handler.
 * Used to access the training services
 *
 * @author Fabiano Tomasini [fabiano@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Fabiano Tomasini [fabiano@solis.coop.br]
 *
 * @since
 * Class created on 13/04/2011
 */

$home = 'main:training';
$module = SAGU::getFileModule(__FILE__);
$navbar->addOption(_M('Serviços de estágio', $module), $module, $home, null, array('function' => 'resetStack'));

if ( !$MIOLO->invokeHandler($module, 'training/' . $context->shiftAction()) )
{
    $btnClose = $MIOLO->getActionURL('sagu2', 'main');
    $mioloUserName = trim($MIOLO->getLogin()->id);

    $pnlPupil = new MActionPanel('pnlTraining', _M('Serviços de estágio', $module), NULL, $btnClose, $MIOLO->getUI()->getImage($module, 'training-16x16.png'));
    $pnlPupil->setControlSize('100px', '70px');

    unset($menuItem);
    $menuItem = array();
    $menuItem[] = array(_M('Histórico de estágios', $module), 'training-32x32.png', "$home:trainingHistory");
    //$menuItem[] = array(_M('Localização de estágio', $module), 'training-32x32.png', "$home:trainingLocation");
    
    if( $MIOLO->checkAccess('FrmDocumentRequest', A_ACCESS, false, true) )
    {
        $menuItem[] = array(_M('Solicitação de serviço', $module), 'documentRequest-32x32.png', "main:documentRequest");
    }
    
    if( $MIOLO->checkAccess('FrmPersonalAgenda', A_ACCESS, false, true) )
    {
        $menuItem[] = array(_M('Agenda pessoal', $module), 'personalAgenda-32x32.png', "main:personalAgenda");
    }
        
    $sortMenuItem = SAGU::arraySort($menuItem, 0);
    foreach ( $sortMenuItem as $m )
    {
        $pnlPupil->addAction($m[0], $MIOLO->getUI()->getImage($module, $m[1]), $module, $m[2], $m[3], $m[4]);
    }
    $theme->appendContent($pnlPupil);
    $sep = new MSeparator();
    $theme->appendContent($sep);
}
?>