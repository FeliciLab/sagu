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
 * LegalPerson services handler.
 * Used to access the legalPerson services
 *
 * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 *
 * @since
 * Class created on 05/09/2007
 */

$home = 'main:legalPerson';
$module = SAGU::getFileModule(__FILE__);
$navbar->addOption(_M('Serviço para a pessoa jurídica', $module), $module, $home, null, array('function' => 'resetStack'));

// Obtém o usuário da pessoa logada e obtém os dados dela
$mioloUserName = trim($MIOLO->getLogin()->id);
$busPerson = new BusinessBasicBusPerson();
$personData = $busPerson->getPersonByMioloUserName($mioloUserName);

if ( !$busPerson->isPersonLegal($personData->personId) )
{
    $MIOLO->error(_M('Você deve ser pessoa jurídica para acessar este conteúdo.', $module));
}
elseif ( !$MIOLO->invokeHandler($module, 'legalPerson/' . $context->shiftAction()) )
{
    $btnClose = $MIOLO->getActionURL('sagu2', 'main');

    if ( is_null($personData) )
    {
        $MIOLO->error(_M('Não foi possível obter os dados do usuário @1.', $module, $mioloUserName));
    }
    else
    {
        $pnlLegalPerson = new MActionPanel('pnlLegalPerson', _M('Pessoa jurídica', $module), NULL, $btnClose, $MIOLO->getUI()->getImage($module, 'legalPerson-16x16.png'));
        $pnlLegalPerson->setControlSize('100px', '70px');

        unset($menuItem);
        $menuItem[] = array(_M('Dados pessoais', $module), 'personalData-32x32.png', "$home:personalData");
        $menuItem[] = array(_M('Definição de avaliações', $module), 'evaluationDefinition-32x32.png', "$home:evaluationDefinition");
        $menuItem[] = array(_M('Material impresso', $module), 'printedMaterial-32x32.png', "$home:printedMaterial");
        $menuItem[] = array(_M('Links externos', $module), 'externalLinks-32x32.png', "$home:externalLinks");
        $menuItem[] = array(_M('Horário', $module), 'schedule-32x32.png', "$home:schedule");
        $menuItem[] = array(_M('Disciplinas', $module), 'curricularComponents-32x32.png', "$home:curricularComponents");

        $sortMenuItem = SAGU::arraySort($menuItem, 0);
        foreach ( $sortMenuItem as $m )
        {
            $pnlLegalPerson->addAction($m[0], $MIOLO->getUI()->getImage($module, $m[1]), $module, $m[2], $m[3]);
        }
        $theme->appendContent($pnlLegalPerson);
        $sep = new MSeparator();
        $theme->appendContent($sep);
    }
}
?>