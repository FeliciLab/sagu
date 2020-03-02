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
 * @author Moises Heberle [moises@solis.coop.br]
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
 * Class created on 20/12/2010
 */

$function = MIOLO::_REQUEST('function');
$ui = $MIOLO->getUI();

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$navbar->addOption(_M('Processo seletivo', $module), $module, $action);
access::insert('Processo seletivo', $action, 'default-16x16.png');

if ( (strlen($function) == 0) || ($function == 'search') )
{
    $MIOLO->checkAccess('FrmSelectiveProcess', A_ACCESS, true, true);

    $content = $ui->getForm($module, 'FrmSelectiveProcessSearch', $data);
    $content->setClose($MIOLO->getActionURL($module, substr($home, 0, strrpos($home, ':'))));
}
else
{
    switch ( $function )
    {
        case 'insert':
            $MIOLO->checkAccess('FrmSelectiveProcess', A_INSERT, true, true);
        break;
        case 'update':
            if ( strlen(MIOLO::_request('event')) > 0 || $_POST['__EVENTTARGETVALUE'] == 'btnSave:click' )
            {
                $MIOLO->checkAccess('FrmSelectiveProcess', A_UPDATE, true, true);
            }
            else
            {
                $MIOLO->checkAccess('FrmSelectiveProcess', A_ACCESS, true, true);
            }
        break;
        case 'delete':
            $MIOLO->checkAccess('FrmSelectiveProcess', A_DELETE, true, true);
        break;
    }

    $i = 1;
    if ( $function != SForm::FUNCTION_UPDATE ) //No editar, pula o passo 1
    {
        $steps[$i++] = new SStepInfo('FrmSelectiveProcessChooser', _M('Escolha do processo seletivo', $module));
    }
    $steps[$i++] = new SStepInfo('FrmSelectiveProcess', _M('Processo seletivo', $module), $module);
    $steps[$i++] = new SStepInfo('FrmOptionCourse', _M('Opções de curso', $module), $module);
    $steps[$i++] = new SStepInfo('FrmStep', _M('Etapa', $module), $module);
    $steps[$i++] = new SStepInfo('FrmFinanceInformation', _M('Taxa de vestibular', $module), $module);

    $content = SStepByStepForm::getCurrentForm($steps);
}

$theme->clearContent();
$theme->insertContent($content);
?>