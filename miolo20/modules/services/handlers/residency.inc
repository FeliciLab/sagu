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
 * @author Jonas Gualberto Diel [jonas_diel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jonas Gualberto Diel [jonas_diel@solis.coop.br]
 *
 * @since
 * Class created on 13/04/2011
 */

$home = 'main:residency';
$module = SAGU::getFileModule(__FILE__);
$navbar->addOption(_M('Serviços de residência multiprofissional', $module), $module, $home, null, array('function' => 'resetStack'));

if ( !$MIOLO->invokeHandler($module, 'residency/' . $context->shiftAction()) )
{
    $btnClose = $MIOLO->getActionURL('sagu2', 'main');
    $pnlPupil = new MActionPanel('pnlResidency', _M('Serviços de residência multiprofissional', $module), NULL, $btnClose, $MIOLO->getUI()->getImage($module, 'training-16x16.png'));
    $pnlPupil->setControlSize('100px', '70px');

    // Obtém o usuário da pessoa logada e obtém os dados dela
    $mioloUserName = trim($MIOLO->getLogin()->id);
    $busPerson = new BusinessBasicBusPerson();
    $personData = $busPerson->getPersonByMioloUserName($mioloUserName);

    unset($menuItem);
    $menuItem = array();
    
    // Verifica se pessoa é ou ja foi  preceptor de unidade tematica
    if ( $personData->isPreceptorUnidadeTematica )
    {
        $menuItem[] = array(_M('Consulta de assiduidade', $module), 'residencyPreceptor-32x32.png', "$home:unidadesTematicasDoPreceptor");
    }
    // Verifica se a pessoa é residente
    if ( $personData->isResident )
    {
        $menuItem[] = array(_M('Consulta de assiduidade', $module), 'residente-32x32.png', "$home:consultaDoResidente");
        $menuItem[] = array(_M('Trabalho de Conclusão', $module), 'icone-tcc.png', "$home:trabalhoConclusao");
    }

    if ( $personData->responsavelNucleoEnfase )
    {
        $menuItem[] = array(_M('Residentes e notas', $module), 'notas-32-32.png', "$home:digitacaoDeNotas");
    }

    $sortMenuItem = SAGU::arraySort($menuItem, 0);
    foreach ( $sortMenuItem as $m )
    {
        $imageURL = $MIOLO->getActionURL( 'services', "html:images:{$m[1]}" );
        
        $pnlPupil->addAction($m[0], $imageURL, $module, $m[2], $m[3], $m[4]);
    }
    $theme->appendContent($pnlPupil);
    $sep = new MSeparator();
    $theme->appendContent($sep);
}
?>