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
 * Services handler.
 * Make options available according to user profile
 *
 * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 *
 * @since
 * Class created on 05/09/2007
 */

SAGU::defineUserIsFromServices();

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);

$home = 'main';
$module = SAGU::getFileModule(__FILE__);
$navbar->addOption(_M('Serviços', $module), $module, $home, null, array('function' => 'resetStack'));

// Informa o formulário se a pessoa que está acessando o formulário pelo sistema
define('USER_IS_FROM_SERVICES', DB_TRUE);

$MIOLO->getClass('basic', 'access');
$MIOLO->uses('classes/sagu.class', 'basic');

// MIOLO2 menu
include_once($MIOLO->GetConf('home.modules') . '/sagu2/handlers/menu.inc');

$ui = $MIOLO->getUI();

// Obtém o usuário da pessoa logada e obtém os dados dela
$mioloUserName = trim($MIOLO->getLogin()->id);
$busPerson = new BusinessBasicBusPerson();
$personData = $busPerson->getPersonByMioloUserName($mioloUserName);

// Acoes em que nao deve ser verificado pessoa
$passActions = array('main:trainingRequest', 'main:selectiveProcessSubscription', 'main:userRegister', 'main:moodleSubscription');

if ( is_null($personData) && ( !in_array($action, $passActions) ) && !SAGU::isDebugMode() )
{
    if( !( strlen($mioloUserName) > 0 ) )
    {
        $MIOLO->error(_M('Acesso negado.', $module));
    }
    $MIOLO->error(_M('Não há nenhuma pessoa associada ao usuário "@1".', $module, $mioloUserName));
}
else
{
    // Verifica os crachás que deveriam ser devolvidos até ontem
    if ( strlen($personData->personId) > 0 )
    {
        $filters = new stdClass();
        $filters->personId = $personData->personId;
        $filters->expectedReturnDateFinal = SAGU::addIntervalInDate(SAGU::getDateNow(), 'd', 1, ' - ');
        $badgeLoans = BasBadgeLoan::search($filters);

        foreach ( $badgeLoans as $badgeLoan )
        {
            $badge = new BasBadge($badgeLoan->badgeId);

            // Verifica se tem data de devolução
            if ( $badgeLoan->returnDate == null )
            {
                $hasBadgePending = true;
                $message .= "<br />-" . _M('Devolver o crachá @1.', $module, $badge->description);
            }
        }
    }
    
    $invoked = $MIOLO->invokeHandler($module, $context->shiftAction());

    // Se ainda não foi devolvido, não deixa acessar o portal
    if ( $hasBadgePending )
    {
        $MIOLO->error(_M('Para poder acessar o seu portal, você precisa:', $module) . $message);
    }
    else if ( SAGU::isAllowedAction() && ( strlen($mioloUserName) > 0 ) )
    {
        $btnClose = $MIOLO->getActionURL('sagu2', 'main');
        $pnlServices = new MActionPanel('pnlServices', _M('Serviços', $module), NULL, $btnClose, $ui->getImage($module, 'services-16x16.png'));
        $pnlServices->setControlSize('120px', '85px');

        // Array com os menus possíveis para a pessoa logada
        $menuItem = array();
        $services = array();

        createServicesMenu($menuItem, $services, $personData, $home);
        
        // Caso tenha acesso somente a um desses menus acima, redireciona-o diretamente para ele
        if ( count($services) == 1 )
        {
            if ( !$invoked )
            {
                $goAction = 'main:' . $services[0];
                $gotoUrl = str_replace('&amp;', '&', $MIOLO->getActionURL($module, $goAction));
                
                header('Location: ' . $gotoUrl);
            }
        }
        elseif ( count($services) == 0 && !SAGU::isAllowedAction()) // Sem acesso a nada, miolo error
        {
            //$MIOLO->error(_M('Você não está autorizado a acessar este conteúdo.', $module));
        }
        
        // Adiciona menus caso seja main ou main:professor, main:employee, etc...
        $tmpAction = str_replace('main:', '', $action);
        $inArray = in_array($tmpAction, $services);
        
        if ( $action == 'main' || $inArray )
        {
            appendServiceMenus($theme, $menuItem, $pnlServices, $ui);
        }
    }
}

function appendServiceMenus(&$theme, &$menuItem, &$pnlServices, &$ui)
{
    global $module;
    
    $sortMenuItem = SAGU::arraySort($menuItem, 0);
    
    foreach ( $sortMenuItem as $m )
    {
        $moduleAction = strlen($m[5]) > 0 ? $m[5] : $module;
        $pnlServices->addAction($m[0], $ui->getImage($module, $m[1]), $moduleAction, $m[2], $m[3], $m[4]);
    }
    
    $theme->appendContent($pnlServices);
    $sep = new MSeparator();
    $theme->appendContent($sep);
}

function createServicesMenu(&$menuItem, &$services, &$personData, $home)
{
    global $module;
    
    // Verifica se é pessoa jurídica
    /*if ( $personData->isLegalPerson )
    {
        $menuItem[] = array(_M('Serviço para a pessoa jurídica', $module), 'legalPerson-48x48.png', "$home:legalPerson");
        $services[] = 'legalPerson';
    }*/

    // Verifica se é pessoa física
    if ( $personData->isPhysicalPerson )
    {
        /*
            * Formulários para qualquer pessoa física:
            */
        
        // Dados pessoais
        if ( SAGU::userHasAccess('FrmPersonalData', A_ACCESS) )
        {
            $menuItem[] = array(_M('Dados pessoais', $module), 'personalData-48x48.png', "$home:personalData");
            $services[] = '';
        }

        // Inscrição para processo seletivo

        /*if ( SAGU::userHasAccess('FrmSelectiveProcessServices', A_ACCESS) && (SAGU::getParameter('BASIC', 'MODULE_SELECTIVEPROCESS_INSTALLED') == 'YES'))
        {
            $menuItem[] = array(_M('Inscrição para processo seletivo', $module), 'selectiveProcess-48x48.png', "$home:selectiveProcessSubscription", null, array('function' => 'insert', 'personIdSelected' => $personData->personId));
        }*/

        // Solicitação de estágio
        /*if ( SAGU::userHasAccess('mainServicesTrainingRequest', A_ACCESS) && (SAGU::getParameter('BASIC', 'MODULE_TRAINING_INSTALLED') == 'YES') )
        {
            $menuItem[] = array(_M('Solicitação de estágio', $module), 'trainingSubscription-48x48.png', "$home:trainingRequest");
        }*/
         
        // Verifica se é um funcionário
        /*
        if ( $personData->isEmployee )
        {
            $menuItem[] = array(_M('Serviços para o funcionário', $module), 'employee-48x48.png', "$home:employee");
            $services[] = 'employee';
        }*/

        // Verifica se é professor
        /*
        if ( $personData->isProfessor )
        {
            if ( SAGU::userHasAccess('mainServicesProfessor', A_ACCESS) )
            {
                $menuItem[] = array(_M('Serviços do professor', $module), 'professor-48x48.png', "$home:professor");
                $services[] = 'professor';
            }

            // Verifica também se é coordenador de curso
            if ( $personData->isCourseCoordinator )
            {
                $menuItem[] = array(_M('Serviço para coordenador de curso', $module), 'courseCoordinator-48x48.png', "$home:courseCoordinator");
                $services[] = 'courseCoordinator';
            }

            // E verifica também se é diretor de centro
            if ( $personData->isCenterDirector )
            {
                $menuItem[] = array(_M('Serviço para diretor de centro', $module), 'centerDirector-48x48.png', "$home:centerDirector");
                $services[] = 'centerDirector';
            }
        }*/

        // Verifica se é aluno
        /*
        if ( $personData->isStudent && SAGU::userHasAccess('mainServicesStudent', A_ACCESS) )
        {
            $menuItem[] = array(_M('Serviços para o aluno', $module), 'pupil-48x48.png', "$home:pupil");
            $services[] = 'pupil';
        }*/
        
        // Avaliação institucional
        /*if ( SAGU::userHasAccess('frmDashboard', A_ACCESS) )
        {
            $menuItem[] = array(_M('Avaliação institucional', 'avinst'), 'avaliacao.png', "main", null, null, 'avinst');
            $services[] = '';
        }*/
    }

    // Verifica se tem permissão para solicitar estágio
    /*if ( $personData->isTraining )
    {
        $menuItem[] = array(_M('Serviços de estágio', $module), 'training-48x48.png', "$home:training");
        $services[] = 'training';
    }*/
        
    // Verifica se se tem permissão para solicitar estágio
    /*if ( $personData->isAgreement )
    {
        $menuItem[] = array(_M('Serviços da conveniada', $module), 'agreement-48x48.png', "$home:agreement");
        $services[] = 'agreement';
    }*/

    // Verifica se se tem permissão para acessar as ações de preceptor da equipe
    if ( $personData->isTeamPreceptor )
    {
        $menuItem[] = array(_M('Serviços do preceptor', $module), 'preceptor-48x48.png', "$home:preceptor");
        $services[] = 'preceptor';
    }

    // Verifica se pessoa é ou ja foi residente multiprofissional
    if ( $personData->isResident || $personData->isPreceptorUnidadeTematica )
    {
        $menuItem[] = array(_M('Residência multiprofissional', $module), 'residency-48x48.png', "$home:residency");
        $services[] = 'residency';
    }
    
    // Verifica se pessoa é ou já foi residente medico
    if ( $personData->isResidentMedico || $personData->isPreceptorUnidadeTematicaMedico )
    {
        $menuItem[] = array(_M('Residência médica', $module), 'residency-48x48.png', "$home:resmedica");
        $services[] = 'resmedica';
    }
}
?>
