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
 * Counter process handler.
 * Contains the menus to access process submenus
 *
 * @author Leovan Tavares da Silva [leovan@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Equipe Sagu [sagu@solis.coop.br]
 *
 * @since
 * Class created on 12/07/2011
 *
 **/

$function = MIOLO::_REQUEST('function');
$ui = $MIOLO->getUI();

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$navbar->addOption(_M('Antecipação / Negociação', $module), $module, $action);
access::insert('Antecipação / Negociação', $action, 'default-16x16.png');

//Tela de negociacao alternativa ticket #33898
$param = SAGU::getParameter('FINANCE', 'NEGOCIACAO_FORM');

if ( $param == 'FrmNegotiation' )
{
    if ( (strlen($function) == 0) || ($function == 'search') )
    {
        $MIOLO->checkAccess('FrmNegotiation', A_ACCESS, true, true);

        $content = $ui->getForm($module,'FrmNegotiationSearch', $data);
        $content->setClose($MIOLO->getActionURL($module,substr($home, 0, strrpos($home, ':'))));
    }
    else
    {
        $MIOLO->checkAccess('FrmNegotiation', A_INSERT, true, true);

        $i=1;
        $steps[$i++] = new SStepInfo('FrmNegotiationOperation', _M('Operação', $module), $module);
        $steps[$i++] = new SStepInfo('FrmNegotiation', _M('Antecipação/ Negociação', $module), $module);

        $content = SStepByStepForm::getCurrentForm($steps);
    }
}
elseif ( $param == 'FrmNegotiationAlt')
{
    if ( (strlen($function) == 0) || ($function == 'search') )
    {
        $MIOLO->checkAccess('FrmNegotiation', A_ACCESS, true, true);

        $content = $ui->getForm($module,'FrmNegotiationSearch', $data);
        $content->setClose($MIOLO->getActionURL($module,substr($home, 0, strrpos($home, ':'))));
    }
    else
    {
        $MIOLO->checkAccess('FrmNegotiation', A_INSERT, true, true);

        $content = $ui->getForm($module,'FrmNegotiationAlt', $data);
        $content->setClose($MIOLO->getActionURL($module,substr($home, 0, strrpos($home, ':'))));
    }
}

$theme->clearContent();
$theme->insertContent($content);

?>
