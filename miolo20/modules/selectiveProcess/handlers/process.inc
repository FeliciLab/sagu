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
 * Selective process register handler.
 * Contains the menus to access register submenus
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 15/12/2010
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);

$function = MIOLO::_request('function');
if ( $function == 'resetStack' )
{
    SAGU::resetStack();
}

$home = 'main:process';
$navbar->addOption(_M('Processo', $module), $module, $home, null, array('function'=>'resetStack'));

if ( !$MIOLO->invokeHandler($module,'process/'.$context->shiftAction()) &&
     ($action != 'main:process:inscription')  
   )
{
    $MIOLO->checkAccess('sprProcess', A_ACCESS, true, true);

    $ui       = $MIOLO->getUI();
    $btnClose = $MIOLO->getActionURL($module,substr($home,0,strrpos($home,':')));
    $icon     = $ui->getImage($module,'process-16x16.png');
    
    $pnlSelectiveProcess = new MActionPanel('pnlSelectiveProcess', _M('Processo', $module), null, $btnClose, $icon);
    $pnlSelectiveProcess->setIconType('small');
    $pnlSelectiveProcess->setControlSize(SAGU::getParameter('BASIC', 'MPANEL_DEFAULT_WIDTH'),SAGU::getParameter('BASIC', 'MPANEL_DEFAULT_HEIGHT'));

    $menuItem = array(  );

    if ( SAGU::getParameter('BASIC', 'MODULE_ACADEMIC_INSTALLED') == 'YES' 
            || SAGU::getParameter('BASIC', 'MODULE_PEDAGOGICO_INSTALLED' == 'YES'))
    {
        $menuItem[] = array(_M('Gerar contrato/inscrição',$module), 'default-16x16.png', "$home:generateContract");
        $menuItem[] = array(_M('Importar arquivo csv - inscrições',$module), 'default-16x16.png', "$home:uploadCsvFileSubscription");
        $menuItem[] = array(_M('Importar arquivo csv - notas',$module), 'default-16x16.png', "$home:uploadCsvFileNote");
        $menuItem[] = array(_M('Processo seletivo',$module), 'default-16x16.png', "$home:selectiveProcess");
        $menuItem[] = array(_M('Detalhamento de etapas',$module), 'default-16x16.png', "$home:detailedSteps");
        $menuItem[] = array(_M('Inscrição',$module), 'default-16x16.png', "$home:subscription");
        $menuItem[] = array(_M('Confirmação de matrícula',$module), 'default-16x16.png', "$home:enrollConfirm");
        $menuItem[] = array(_M('Classificação na etapa',$module), 'default-16x16.png', "$home:classification");
        $menuItem[] = array(_M('Digitação de notas',$module), 'default-16x16.png', "$home:evaluationPoints");
        $menuItem[] = array(_M('Alocação por sala', $module), 'default-16x16.png', "$home:roomAllocation");
        $menuItem[] = array(_M('Detalhamento de avaliações',$module), 'default-16x16.png', "$home:detailedEvaluation");
        $menuItem[] = array(_M('Reenvio do email de confirmação de inscrição',$module), 'default-16x16.png', "$home:notifySubscribers");
        $menuItem[] = array(_M('Integração com o módulo de residência',$module), 'default-16x16.png', "$home:generateResidentLink");
        $menuItem[] = array(_M('Chamada de suplentes',$module), 'default-16x16.png', "$home:sprSubstitutesCall");
    }

    $sortMenuItem = SAGU::arraySort($menuItem, 0);
    foreach ( $sortMenuItem as $m )
    {
       $pnlSelectiveProcess->addAction($m[0], $ui->getImage($module,$m[1]), $module, $m[2]);
    }

    $theme->clearContent();
    $theme->insertContent($pnlSelectiveProcess);
}
?>