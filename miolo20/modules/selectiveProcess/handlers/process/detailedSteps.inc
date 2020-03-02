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
 * @author Fabiano Tomasini [fabiano@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Fabiano Tomasini [fabiano@solis.coop.br]
 *
 * @since
 * Class created on 20/12/2010
 *
 **/

$function = MIOLO::_REQUEST('function');
$ui = $MIOLO->getUI();

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$navbar->addOption(_M('Detalhamento de etapas', $module), $module, $action);
access::insert('Detalhamento de etapas', $action, 'default-16x16.png');

//TODO alterar permissão

switch ( $function )
{
    case 'insert':
        $MIOLO->checkAccess('FrmDetailedSteps', A_INSERT, true, true);
    break;
    case 'update':
        if (strlen(MIOLO::_request('event')) > 0 || $_POST['__EVENTTARGETVALUE'] == 'btnSave:click')
        {
            $MIOLO->checkAccess('FrmDetailedSteps', A_UPDATE, true, true);
        }
        else
        {
            $MIOLO->checkAccess('FrmDetailedSteps', A_ACCESS, true, true);
        }
    break;
    case 'delete':
        $MIOLO->checkAccess('FrmDetailedSteps', A_DELETE, true, true);
    break;
}

$i=1;
$steps[$i++] = new SStepInfo('FrmSelectionStep', _M('Seleção da etapa', $module), $module);
$steps[$i++] = new SStepInfo('FrmEvaluation', _M('Avaliação', $module), $module);
$steps[$i++] = new SStepInfo('FrmStepTiebreak',  _M('Critério de desempate', $module), $module);
$steps[$i++] = new SStepInfo('FrmStepDocument',  _M('Documentos', $module), $module);
$steps[$i++] = new SStepInfo('FrmStepPlace',  _M('Local da prova', $module), $module);

$content = SStepByStepForm::getCurrentForm($steps);

$theme->clearContent();
$theme->insertContent($content);
?>
