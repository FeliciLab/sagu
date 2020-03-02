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
 * Basic center handler.
 * Contains the menus to access center registers
 *
 * @author Augusto A. Silva [augusto@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Augusto A. Silva
 *
 * @since
 * Class created on 04/09/2013
 *
 **/

$module = MIOLO::getCurrentModule();
$action = 'main:report:relatorioTitulosBaixadosDescontoFolha';
$title = _M('Relatório de títulos baixados por desconto em folha', $module);
$managementForm = 'FrmRelatorioTitulosBaixadosDescontoFolha';
$searchForm = 'FrmRelatorioTitulosBaixadosDescontoFolha';

SAGU::handle($module, $action, $title, $searchForm, $managementForm);
?>