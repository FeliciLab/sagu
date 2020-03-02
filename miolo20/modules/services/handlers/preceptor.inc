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

$home = 'main:preceptor';
$module = SAGU::getFileModule(__FILE__);
$navbar->addOption(_M('Serviços do preceptor', $module), $module, $home, null, array('function' => 'resetStack'));

if ( !$MIOLO->invokeHandler($module, 'preceptor/' . $context->shiftAction()) )
{
    $btnClose = $MIOLO->getActionURL('sagu2', 'main');
    $mioloUserName = trim($MIOLO->getLogin()->id);
    
    $pnlPupil = new MActionPanel('pnlTraining', _M('Serviços do preceptor', $module), NULL, $btnClose, $MIOLO->getUI()->getImage($module, 'preceptor-16x16.png'));
    $pnlPupil->setControlSize('100px', '70px');

    $menuItem = array();
    if ( $MIOLO->checkAccess('FrmTrainingLocation', A_ACCESS, false, true) )
    {
        $menuItem[] = array(_M('Avaliação de estágio', $module), 'notas-32-32.png', "main:training:trainingLocation");
    }     
    if ( $MIOLO->checkAccess('FrmMaterialRequest', A_ACCESS, false, true) )
    {
        $menuItem[] = array(_M('Solicitação de material', $module), 'materialRequest-32x32.png', "main:materialRequest");
    }
    if ( $MIOLO->checkAccess('FrmScheduledActivity', A_ACCESS, false, true) )
    {
        $menuItem[] = array(_M('Atividade programada', $module), 'scheduledActivity-32x32.png', "main:scheduledActivity");
    }
    if ( $MIOLO->checkAccess('FrmRealizedActivity', A_ACCESS, false, true) )
    {
        $menuItem[] = array(_M('Atividade realizada', $module), 'realizedActivity-32x32.png', "main:realizedActivity");
    }
    if ( $MIOLO->checkAccess('FrmPersonalAgenda', A_ACCESS, false, true) )
    {
        $menuItem[] = array(_M('Agenda pessoal', $module), 'personalAgenda-32x32.png', "main:personalAgenda");
    }
    if ( $MIOLO->checkAccess('FrmSubscriptionServices', A_ACCESS, false, true) )
    {
        $menuItem[] = array(_M('Autorização de estágio', $module), 'preceptorTeams-32x32.png', "$home:subscription");
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