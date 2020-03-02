<?php
/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluç?es Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; voc? pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundaç?o
 * do Software Livre (FSF); na vers?o 2 da Licença.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇ?O a qualquer MERCADO
 * ou APLICAÇ?O EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * portugu?s para maiores detalhes.
 *
 * Voc? deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se n?o, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundaç?o do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 * @author Equipe SAGU [sagu@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Equipe SAGU [sagu@solis.coop.br]
 *
 * @since
 * Class created on 20/03/2012
 */

$module = SAGU::getFileModule(__FILE__);
$action = MIOLO::getCurrentAction();
$title = _M('Geração de contrato', $module);
$managementForm = 'FrmContractGeneration';

SAGU::handle($module, $action, $title, $managementForm, $managementForm);
?>