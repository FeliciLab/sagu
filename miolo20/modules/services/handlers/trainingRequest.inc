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
 * @author Arthur Lehdermann [arthur@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Arthur Lehdermann [arthur@solis.coop.br]
 *
 * @since
 * Class created on 25/06/2011
 */

// Replicado a definição desta consntante por que este formulário não massa pelo main do services
SAGU::defineUserIsFromServices();

$module = 'training';
$function = MIOLO::_REQUEST('function');
$ui = $MIOLO->getUI();

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$navbar->addOption(_M('Solicitação de estágio', $module), $module, $action);
access::insert('Solicitação de estágio', $action, 'default-16x16.png');

// faz checkAccess somente se login.check for true
// serve para nao fazer o checkAccess quando estiver via modulo services
if ( $MIOLO->getConf('login.check') == 'true' )
{
    switch ( $function )
    {
        case 'insert':
            $MIOLO->checkAccess('FrmStageRequest', A_INSERT, true, true);
        break;
        case 'update':
            if (strlen(MIOLO::_request('event')) > 0 || $_POST['__EVENTTARGETVALUE'] == 'btnSave:click')
            {
                $MIOLO->checkAccess('FrmStageRequest', A_UPDATE, true, true);
            }
            else
            {
                $MIOLO->checkAccess('FrmStageRequest', A_ACCESS, true, true);
            }
        break;
        case 'delete':
            $MIOLO->checkAccess('FrmStageRequest', A_DELETE, true, true);
        break;
    }
}

$steps = SAGU::getTrainingRequestSteps();
$content = SStepByStepForm::getCurrentForm($steps);

// Desabilita a toolbar
$content->disableToolbar();

$theme->clearContent();
$theme->insertContent($content);
?>