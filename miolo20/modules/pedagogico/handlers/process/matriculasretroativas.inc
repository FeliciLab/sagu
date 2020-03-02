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
 * Form handler
 *
 * @author Jonas Gualberto Diel [jonas_diel@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jonas Gualberto Diel [jonas_diel@solis.com.br]
 * Joao Francisco Padilha Souza [joao@solis.com.br]
 *
 * @since
 * Class created on 29/08/2013
 */
$function = MIOLO::_REQUEST('function');
$ui = $MIOLO->getUI();
$module = MIOLO::getCurrentModule();

$navbar->addOption(_M('Matrículas', $module), $module, $action);
access::insert('Matrículas', $action, 'default-16x16.png');

$MIOLO->checkAccess('FrmEmployee', A_INSERT, true, true);

    $i=1;
    $steps[$i++] = new SStepInfo('FrmMatriculasRetroativas', _M('Inscrição e Turma', $module), $module);
    $steps[$i++] = new SStepInfo('FrmMatriculasRetroativasFase2', _M('Componentes curriculares', $module), $module);
    $steps[$i++] = new SStepInfo('FrmMatriculasRetroativasFase3', _M('Professores', $module), $module);

    $content = SStepByStepForm::getCurrentForm($steps);


$theme->clearContent();
$theme->insertContent($content);
?>
