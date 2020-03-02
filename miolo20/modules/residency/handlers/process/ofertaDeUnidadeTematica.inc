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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * \b Maintainers \n
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Luís Felipe Wermann [luis_felipe@solis.com.br]
 *
 * @since
 * Class created on 16/07/2011
 */

ini_set('max_execution_time', 3000);
ini_set('memory_limit', '-1');

$module = SAGU::getFileModule(__FILE__);
$action = MIOLO::getCurrentAction();
$title = _M('Oferta de unidade temática', $module);
$searchForm = 'FrmOfertaDeUnidadeTematicaSearch';

//Etapas
$steps[1] = new SStepInfo('FrmOfertaDeUnidadeTematica', _M('Oferta de unidade temática', $module), $module);
$steps[2] = new SStepInfo('FrmOfertaDeUnidadeTematicaEncontros', _M('Encontros da oferta', $module), $module);

SAGU::handle($module, $action, $title, $searchForm, $steps, array('transactionName' => 'FrmOfertaDeUnidadeTematica', 'checkAccess' => true));
?>