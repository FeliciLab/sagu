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
 * Link de acesso ao formulário para transferência de turma.
 *
 * @author Rodrigo Strate Fogaça [rodrigo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Rodrigo Strate Fogaça [rodrigo@solis.coop.br]
 * 
 * @since
 * Class created on 25/01/2012
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solucoes Livres \n
 * The SAGU Development Team
 *
 * \b Copyright: \n
 * Copyright (c) 2005-2007 SOLIS - Cooperativa de Solucoes Livres \n
 * Copyright (c) 2005-2007 UNIVATES - Centro Universitario \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html )
 *
 * \b History: \n
 * See: http://sagu.solis.coop.br
 *
 */
$module = MIOLO::getCurrentModule();
$action = 'main:process:classTransfer';
$title = _M('Transferência de turma', $module);
$form = 'FrmClassTransfer';
SAGU::handle($module, $action, $title, $form, $form);
?>