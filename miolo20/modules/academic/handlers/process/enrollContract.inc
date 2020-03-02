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
 * Academic enroll handler.
 * Contains the menus to access enroll process
 *
 * @author Daniel Afonso Heisler [daniel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Class created on 13/10/2005
 *
 **/

$module = MIOLO::getCurrentModule();
$action = 'main:process:enrollContract';
$title = _M('Matrícula', $module);
$form = SAGU::getParameter('academic', 'ENROLL_FORM');

if($form != 'FrmEnrollWeb')
{
    SAGU::handle($module, $action, $title, $form, $form, array('transactionName' => 'FrmEnrollContract'));
}
else
{
    $module = SAGU::getFileModule(__FILE__);
    $title = _M('Matrícula', $module);
    $steps[1] = new SStepInfo('FrmEnrollWeb0', _M('Aluno', $module), 'services');
    $steps[2] = new SStepInfo('FrmEnrollWeb1', _M('Dados', $module), 'services');
    $steps[3] = new SStepInfo('FrmEnrollWeb2', _M('Disciplinas', $module), 'services');
    $steps[4] = new SStepInfo('FrmEnrollWeb3', _M('Finalização', $module), 'services');

    $MIOLO = MIOLO::getInstance();
            
    $options = 'FrmEnrollWeb';
    if ( $MIOLO->getCurrentModule() == 'academic' )
    {
        $options = array('checkAccess' => true,
                         'transactionName' => 'FrmEnrollContract');
    }
    
    SAGU::handle('services', $action, $title, $searchForm, $steps, $options);
}

?>
