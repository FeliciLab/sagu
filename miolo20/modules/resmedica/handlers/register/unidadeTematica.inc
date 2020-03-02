<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda.
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
 *
 * Handler do formulário de unidade temática
 *
 * @author Arthur Lehdermann [arthur@solis.coop.br]
 *
 * \b Maintainers \n
 * Arthur Lehdermann [arthur@solis.coop.br]
 *
 * @since
 * Class created on 15/07/2011
 */

$function = MIOLO::_REQUEST('function');
$ui = $MIOLO->getUI();
$module = SAGU::getFileModule(__FILE__);

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$navbar->addOption(_M('Unidade temática', $module), $module, $action);
access::insert('Unidade temática', $action, 'default-16x16.png');

if ( (strlen($function) == 0) || ($function == 'search') )
{
    $MIOLO->checkAccess('FrmUnidadeTematicaMed', A_ACCESS, true, true);

    $content = $ui->getForm($module,'FrmUnidadeTematicaSearch', $data);
    $content->setClose($MIOLO->getActionURL($module,substr($home, 0, strrpos($home, ':'))));
}
elseif ( $function == 'update' || $function == 'delete' )
{
    if (strlen(MIOLO::_request('event')) > 0 || $_POST['__EVENTTARGETVALUE'] == 'btnSave:click')
    {
        $MIOLO->checkAccess('FrmUnidadeTematicaMed', A_UPDATE, true, true);
    }
    else
    {
        $MIOLO->checkAccess('FrmUnidadeTematicaMed', A_ACCESS, true, true);
    }

    $content = $ui->getForm($module,'FrmUnidadeTematicaEdicao', $data);
    $content->setClose($MIOLO->getActionURL($module,substr($home, 0, strrpos($home, ':'))));
}
else // insert
{
    $MIOLO->checkAccess('FrmUnidadeTematicaMed', A_INSERT, true, true);

    $i=1;
    $steps[$i++] = new SStepInfo('FrmUnidadeTematicaDadosGerais', _M('Dados gerais', $module), $module);
    $steps[$i++] = new SStepInfo('FrmUnidadeTematicaNucleosProfissionais', _M('Núcleos profissionais'), $module);
    $steps[$i++] = new SStepInfo('FrmUnidadeTematicaEnfases', _M('Ênfases'), $module);

    $content = SStepByStepForm::getCurrentForm($steps);
}

$theme->clearContent();
$theme->insertContent($content);
?>