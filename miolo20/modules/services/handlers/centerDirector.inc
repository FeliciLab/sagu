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
 * Professor services handler.
 * Used to access the professor services
 *
 * @author Arthur Lehdermann [arthur@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Arthur Lehdermann [arthur@solis.coop.br]
 *
 * @since
 * Class created on 15/06/2011
 */

$home = 'main:centerDirector';
$module = SAGU::getFileModule(__FILE__);
$navbar->addOption(_M('Serviços do diretor de centro', $module), $module, $home, null, array('function' => 'resetStack'));

// Obtém o usuário da pessoa logada e obtém os dados dela
$mioloUserName = trim($MIOLO->getLogin()->id);
$busPerson = $MIOLO->getBusiness('basic', 'BusPerson');
$personData = $busPerson->getPersonByMioloUserName($mioloUserName);

if ( !$personData->isCenterDirector )
{
    $MIOLO->error(_M('Você deve ser diretor de centro para acessar este conteúdo.', $module));
}
elseif ( !$MIOLO->invokeHandler($module, 'centerDirector/' . $context->shiftAction()) )
{
    $btnClose = $MIOLO->getActionURL('sagu2', 'main');

    if ( is_null($personData) )
    {
        $MIOLO->error(_M('Não foi possível obter os dados do usuário @1.', $module, $mioloUserName));
    }
    else
    {
        $pnlCenterDirector = new MActionPanel('pnlCenterDirector', _M('Diretor de centro',$module), NULL, $btnClose, $MIOLO->getUI()->getImage($module, 'centerDirector-16x16.png'));
        $pnlCenterDirector->setControlSize('100px', '70px');

        unset($menuItem);
        $menuItem[] = array(_M('Solicitação de material', $module), 'materialRequest-32x32.png', "main:materialRequest");
        $menuItem[] = array(_M('Atividade programada', $module), 'scheduledActivity-32x32.png', "main:scheduledActivity");
        $menuItem[] = array(_M('Atividade realizada', $module), 'realizedActivity-32x32.png', "main:realizedActivity");
        $menuItem[] = array(_M('Planilha de horas do diretor de centro', $module), 'generalCoordinatorSheet-32x32.png', "$home:generalCoordinatorSheet");
        $menuItem[] = array(_M('Agenda pessoal', $module), 'personalAgenda-32x32.png', "main:personalAgenda");

        $sortMenuItem = SAGU::arraySort($menuItem, 0);
        foreach ( $sortMenuItem as $m )
        {
            $pnlCenterDirector->addAction($m[0], $MIOLO->getUI()->getImage($module, $m[1]), $module, $m[2], $m[3]);
        }

        $pnlCenterDirector->addAction('Voltar', $MIOLO->getUI()->getImage($module, 'back-32x32.png'), $module,'main:services');

        $theme->appendContent($pnlCenterDirector);
        $sep = new MSeparator();
        $theme->appendContent($sep);
    }
}
?>
