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
 * Basic employee handler.
 * Contains the menus to access person registers
 *
 * @author Daniel Afonso Heisler [daniel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Class created on 12/09/2005
 *
 **/

$function = MIOLO::_REQUEST('function');
$ui = $MIOLO->getUI();
$module = MIOLO::getCurrentModule();

$navbar->addOption(_M('Funcionário', $module), $module, $action);
access::insert('Funcionário', $action, 'default-16x16.png');

if ( (strlen($function) == 0) || ($function == 'search') )
{
    $MIOLO->checkAccess('FrmEmployee', A_ACCESS, true, true);

    $content = $ui->getForm($module,'FrmEmployeeSearch', $data);
    $content->setClose($MIOLO->getActionURL($module,substr($home, 0, strrpos($home, ':'))));
}
else
{
    $MIOLO->checkAccess('FrmEmployee', A_INSERT, true, true);

    $busCenter = new BusinessAcademicBusCenter();

    $i=1;
    $steps[$i++] = new SStepInfo('FrmEmployee', _M('Funcionário', $module), $module);

    if ( count($busCenter->listCenter()) > 0 )
    {
        $steps[$i++] = new SStepInfo('FrmEmployeeCenter', _M('Centros', $module), $module);
    }

    $steps[$i++] = new SStepInfo('FrmFeriasFuncionario', _M('Férias funcionário', $module), $module);

    $content = SStepByStepForm::getCurrentForm($steps);
}

$theme->clearContent();
$theme->insertContent($content);


?>
