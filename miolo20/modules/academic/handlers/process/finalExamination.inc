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
 * Moises Heberle [moises@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 *
 * @since
 * Class created on 07/02/2011
 *
 **/

$module = SAGU::getFileModule(__FILE__);
$function = MIOLO::_REQUEST('function');

$i=1;
if ( ($function != 'update') )
{
    $steps[$i++] = new SStepInfo('FrmFinalExaminationCurricularComponent', _M('Seleção de disciplina', $module), $module);
}
$steps[$i++] = new SStepInfo('FrmFinalExaminationTheme', _M('Tema', $module), $module);
$steps[$i++] = new SStepInfo('FrmFinalExaminationDirectors', _M('Orientadores', $module), $module);
$steps[$i++] = new SStepInfo('FrmFinalExaminationExaminingBoard', _M('Banca examinadora', $module), $module);
$steps[$i++] = new SStepInfo('FrmFinalExaminationNotes', _M('Notas', $module), $module);

SAGU::handle($module, $action, _M('Detalhamento do TCC', $module), 'FrmFinalExaminationSearch', $steps, 'FrmFinalExamination');
?>