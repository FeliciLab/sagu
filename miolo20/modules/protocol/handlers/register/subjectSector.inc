<?php
/**
 * <--- Copyright 2011-2011 de Facimed - Faculdade de Ciências Biométricas de Cacoal.
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
 * Acesso aos assuntos dos setores.
 *
 * @author Alessandro Rodrigues da Cruz [cpd@facimed.edu.br]
 *
 * \b Maintainers: \n
 * Equipe Solis [sagu2@solis.coop.br]
 * 
 * @since
 * Class created on 17/03/2011
 *
 **/

$module = MIOLO::getCurrentModule();
$action = 'main:register:subjectSector';
$title = _M('Encaminhamento', $module);
$managementForm = 'FrmSubjectSector';
$searchForm = '';

SAGU::handle($module, $action, $title, $searchForm, $managementForm, 'FrmSubject');
?>
