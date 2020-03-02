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
 * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 *
 * @since
 * Class created on 05/09/2007
 */

$home = 'main:professor';
$navbar->addOption(_M('Serviços do professor', $module), $module, $home, null, array('function' => 'resetStack'));

// Obtém o usuário da pessoa logada e obtém os dados dela
$mioloUserName = trim($MIOLO->getLogin()->id);
$busPerson = $MIOLO->getBusiness('basic', 'BusPerson');
$personData = $busPerson->getPersonByMioloUserName($mioloUserName);

$busDirectors = $MIOLO->getBusiness('academic', 'BusFinalExaminationDirectors');

$filters = new stdClass();
$filters->personId = $personData->personId;
$director = $busDirectors->searchFinalExaminationDirectors($filters);

if ( !$busPerson->isPersonProfessor($personData->personId) )
{
    $MIOLO->error(_M('Você deve ser professor para acessar este conteúdo.', $module));
}
elseif ( !$MIOLO->invokeHandler($module, 'professor/' . $context->shiftAction()) )
{
    $btnClose = $MIOLO->getActionURL('sagu2', 'main');

    if ( is_null($personData) )
    {
        $MIOLO->error(_M('Não foi possível obter os dados do usuário @1.', $module, $mioloUserName));
    }
    else
    {
        $pnlProfessor = new MActionPanel('pnlProfessor', _M('Professor', $module), NULL, $btnClose, $MIOLO->getUI()->getImage($module, 'professor-16x16.png'));
        $pnlProfessor->setControlSize('100px', '70px');

        $menuItem = array();
        $menuItem[] = array(_M('Disciplinas', $module), 'curricularComponents-32x32.png', "$home:curricularComponents", 'FrmProfessorCurricularComponents');
        $menuItem[] = array(_M('Solicitação de material', $module), 'materialRequest-32x32.png', "main:materialRequest", 'FrmMaterialRequest');
        $menuItem[] = array(_M('Agendar e confirmar realização de atividades', $module), 'scheduledActivity-32x32.png', "main:scheduledActivity", 'FrmScheduledActivity');
        $menuItem[] = array(_M('Planilha de horas do professor', $module), 'professorTimeSheet-32x32.png', "$home:professorTimeSheetServices", 'FrmProfessorTimeSheetServices');
        $menuItem[] = array(_M('Agenda pessoal', $module), 'personalAgenda-32x32.png', "main:personalAgenda", 'FrmPersonalAgenda');
        
        if ( $director )
        {
            $menuItem[] = array(_M('Detalhamento de TCC', $module), 'detalhamentoTcc.png', "$home:detalhamentoTcc", 'FrmDetalhamentoTcc');
        }
        
        $sortMenuItem = SAGU::arraySort($menuItem, 0);        
        foreach ( $sortMenuItem as $m )
        {
            if( $MIOLO->checkAccess($m[3], A_ACCESS, false, false) )
            {
                $pnlProfessor->addAction($m[0], $MIOLO->getUI()->getImage($module, $m[1]), $module, $m[2]);
            }
        }

        $pnlProfessor->addAction('Voltar', $MIOLO->getUI()->getImage($module, 'back-32x32.png'), $module,'main:services');
        
        $theme->appendContent($pnlProfessor);
        $sep = new MSeparator();
        $theme->appendContent($sep);
    }
}
?>
